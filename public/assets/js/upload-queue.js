/**
 * Background Upload Queue System
 * Manages file uploads in the background with retry logic and progress tracking
 */
class UploadQueue {
    constructor(options = {}) {
        this.maxConcurrentUploads = options.maxConcurrentUploads || 2;
        this.retryAttempts = options.retryAttempts || 3;
        this.retryDelay = options.retryDelay || 2000;
        this.onProgress = options.onProgress || (() => { });
        this.onComplete = options.onComplete || (() => { });
        this.onError = options.onError || (() => { });
        this.csrfToken = options.csrfToken || $('meta[name="csrf-token"]').attr('content');

        this.queue = [];
        this.activeUploads = new Map();
        this.completedUploads = new Map();
        this.failedUploads = new Map();
        this.isProcessing = false;
    }

    /**
     * Add files to upload queue
     */
    addToQueue(files, options = {}) {
        files.forEach(file => {
            const uploadItem = {
                id: this.generateUploadId(),
                file: file,
                status: 'queued',
                progress: 0,
                attempts: 0,
                error: null,
                result: null,
                options: options,
                createdAt: new Date(),
                startedAt: null,
                completedAt: null
            };

            this.queue.push(uploadItem);
        });

        this.processQueue();
    }

    /**
     * Process the upload queue
     */
    async processQueue() {
        if (this.isProcessing) return;

        this.isProcessing = true;

        while (this.queue.length > 0 && this.activeUploads.size < this.maxConcurrentUploads) {
            const uploadItem = this.queue.shift();
            this.startUpload(uploadItem);
        }

        this.isProcessing = false;
    }

    /**
     * Start uploading a file
     */
    async startUpload(uploadItem) {
        uploadItem.status = 'uploading';
        uploadItem.startedAt = new Date();
        this.activeUploads.set(uploadItem.id, uploadItem);

        try {
            // Check if file needs compression
            let fileToUpload = uploadItem.file;
            if (uploadItem.file.type.startsWith('video/') && uploadItem.file.size > 100 * 1024 * 1024) {
                this.onProgress({
                    id: uploadItem.id,
                    status: 'compressing',
                    progress: 0,
                    message: 'Compressing video...'
                });

                const compressor = new VideoCompressor({
                    maxWidth: 1920,
                    maxHeight: 1080,
                    quality: 0.8
                });

                fileToUpload = await compressor.compressVideo(uploadItem.file);
            }

            // Choose upload method based on file size
            let result;
            if (fileToUpload.size > 50 * 1024 * 1024 && fileToUpload.type.startsWith('video/')) {
                result = await this.chunkedUpload(fileToUpload, uploadItem);
            } else {
                result = await this.singleUpload(fileToUpload, uploadItem);
            }

            uploadItem.status = 'completed';
            uploadItem.progress = 100;
            uploadItem.result = result;
            uploadItem.completedAt = new Date();

            this.completedUploads.set(uploadItem.id, uploadItem);
            this.activeUploads.delete(uploadItem.id);

            this.onComplete({
                id: uploadItem.id,
                result: result,
                uploadItem: uploadItem
            });

        } catch (error) {
            uploadItem.attempts++;
            uploadItem.error = error.message;

            if (uploadItem.attempts < this.retryAttempts) {
                uploadItem.status = 'retrying';
                uploadItem.progress = 0;

                this.onProgress({
                    id: uploadItem.id,
                    status: 'retrying',
                    progress: 0,
                    message: `Retrying upload (${uploadItem.attempts}/${this.retryAttempts})...`
                });

                // Add back to queue for retry
                setTimeout(() => {
                    this.queue.unshift(uploadItem);
                    this.processQueue();
                }, this.retryDelay * uploadItem.attempts);

            } else {
                uploadItem.status = 'failed';
                this.failedUploads.set(uploadItem.id, uploadItem);
                this.activeUploads.delete(uploadItem.id);

                this.onError({
                    id: uploadItem.id,
                    error: error,
                    uploadItem: uploadItem
                });
            }
        }

        // Continue processing queue
        this.processQueue();
    }

    /**
     * Chunked upload for large files
     */
    async chunkedUpload(file, uploadItem) {
        const uploader = new ChunkedUploader({
            chunkSize: 2 * 1024 * 1024, // 2MB chunks
            maxConcurrentChunks: 3,
            onProgress: (progress) => {
                this.onProgress({
                    id: uploadItem.id,
                    status: 'uploading',
                    progress: progress.percentage,
                    message: `Uploading ${file.name}... (${progress.percentage}%)`
                });
            },
            csrfToken: this.csrfToken
        });

        return await uploader.uploadFile(file, '/schedule/chunked-upload');
    }

    /**
     * Single upload for smaller files
     */
    async singleUpload(file, uploadItem) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('single_upload', 'true');

        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const progress = Math.round((e.loaded / e.total) * 100);
                    this.onProgress({
                        id: uploadItem.id,
                        status: 'uploading',
                        progress: progress,
                        message: `Uploading ${file.name}... (${progress}%)`
                    });
                }
            });

            xhr.onload = () => {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        resolve(response);
                    } catch (e) {
                        reject(new Error('Invalid response format'));
                    }
                } else {
                    reject(new Error(`Upload failed: ${xhr.status}`));
                }
            };

            xhr.onerror = () => reject(new Error('Upload failed'));
            xhr.open('POST', '/schedule/chunked-upload');
            xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
            xhr.send(formData);
        });
    }

    /**
     * Get upload status
     */
    getStatus() {
        return {
            queued: this.queue.length,
            uploading: this.activeUploads.size,
            completed: this.completedUploads.size,
            failed: this.failedUploads.size,
            total: this.queue.length + this.activeUploads.size + this.completedUploads.size + this.failedUploads.size
        };
    }

    /**
     * Get all uploads
     */
    getAllUploads() {
        return {
            queued: [...this.queue],
            uploading: [...this.activeUploads.values()],
            completed: [...this.completedUploads.values()],
            failed: [...this.failedUploads.values()]
        };
    }

    /**
     * Clear completed uploads
     */
    clearCompleted() {
        this.completedUploads.clear();
    }

    /**
     * Clear failed uploads
     */
    clearFailed() {
        this.failedUploads.clear();
    }

    /**
     * Retry failed uploads
     */
    retryFailed() {
        const failedUploads = [...this.failedUploads.values()];
        this.failedUploads.clear();

        failedUploads.forEach(upload => {
            upload.status = 'queued';
            upload.progress = 0;
            upload.attempts = 0;
            upload.error = null;
            upload.result = null;
            upload.startedAt = null;
            upload.completedAt = null;
            this.queue.push(upload);
        });

        this.processQueue();
    }

    /**
     * Cancel all uploads
     */
    cancelAll() {
        this.queue = [];
        this.activeUploads.clear();
        this.completedUploads.clear();
        this.failedUploads.clear();
    }

    /**
     * Generate unique upload ID
     */
    generateUploadId() {
        return 'upload_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
}

/**
 * Upload Progress Manager
 * Manages UI updates for upload progress
 */
class UploadProgressManager {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.uploads = new Map();
    }

    /**
     * Add upload to progress tracking
     */
    addUpload(uploadId, fileName) {
        const uploadElement = this.createUploadElement(uploadId, fileName);
        this.container.appendChild(uploadElement);
        this.uploads.set(uploadId, uploadElement);
    }

    /**
     * Update upload progress
     */
    updateProgress(uploadId, progress) {
        const uploadElement = this.uploads.get(uploadId);
        if (!uploadElement) return;

        const progressBar = uploadElement.querySelector('.progress-bar');
        const progressText = uploadElement.querySelector('.progress-text');
        const statusText = uploadElement.querySelector('.status-text');

        progressBar.style.width = progress.progress + '%';
        progressBar.setAttribute('aria-valuenow', progress.progress);
        progressText.textContent = progress.progress + '%';
        statusText.textContent = progress.message || 'Uploading...';

        // Update status class
        uploadElement.className = `upload-item upload-${progress.status}`;
    }

    /**
     * Mark upload as completed
     */
    markCompleted(uploadId) {
        const uploadElement = this.uploads.get(uploadId);
        if (!uploadElement) return;

        uploadElement.className = 'upload-item upload-completed';
        const statusText = uploadElement.querySelector('.status-text');
        statusText.textContent = 'Upload completed successfully';
    }

    /**
     * Mark upload as failed
     */
    markFailed(uploadId, error) {
        const uploadElement = this.uploads.get(uploadId);
        if (!uploadElement) return;

        uploadElement.className = 'upload-item upload-failed';
        const statusText = uploadElement.querySelector('.status-text');
        statusText.textContent = 'Upload failed: ' + error;
    }

    /**
     * Remove upload from progress tracking
     */
    removeUpload(uploadId) {
        const uploadElement = this.uploads.get(uploadId);
        if (uploadElement) {
            uploadElement.remove();
            this.uploads.delete(uploadId);
        }
    }

    /**
     * Create upload element
     */
    createUploadElement(uploadId, fileName) {
        const div = document.createElement('div');
        div.className = 'upload-item upload-queued';
        div.innerHTML = `
            <div class="upload-header">
                <span class="upload-filename">${fileName}</span>
                <span class="upload-status">Queued</span>
            </div>
            <div class="progress mb-2">
                <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="upload-details">
                <span class="progress-text">0%</span>
                <span class="status-text">Waiting to start...</span>
            </div>
        `;
        return div;
    }

    /**
     * Clear all uploads
     */
    clear() {
        this.container.innerHTML = '';
        this.uploads.clear();
    }
}

// Export for use in other files
window.UploadQueue = UploadQueue;
window.UploadProgressManager = UploadProgressManager;
