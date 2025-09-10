/**
 * Chunked File Upload for Large Video Files
 * Optimizes upload performance by breaking large files into chunks
 */
class ChunkedUploader {
    constructor(options = {}) {
        this.chunkSize = options.chunkSize || 2 * 1024 * 1024; // 2MB chunks
        this.maxConcurrentChunks = options.maxConcurrentChunks || 3;
        this.retryAttempts = options.retryAttempts || 3;
        this.retryDelay = options.retryDelay || 1000;
        this.onProgress = options.onProgress || (() => { });
        this.onComplete = options.onComplete || (() => { });
        this.onError = options.onError || (() => { });
        this.csrfToken = options.csrfToken || $('meta[name="csrf-token"]').attr('content');
    }

    async uploadFile(file, uploadUrl) {
        try {
            // Check if file is large enough to benefit from chunking
            if (file.size < this.chunkSize * 2) {
                return this.uploadSingleFile(file, uploadUrl);
            }

            const fileId = this.generateFileId();
            const totalChunks = Math.ceil(file.size / this.chunkSize);
            const uploadedChunks = new Set();
            let uploadedBytes = 0;

            // Initialize upload session
            await this.initializeUpload(file, fileId, totalChunks, uploadUrl);

            // Upload chunks with concurrency control
            const uploadPromises = [];
            const semaphore = new Semaphore(this.maxConcurrentChunks);

            for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                const promise = semaphore.acquire().then(async (release) => {
                    try {
                        const chunk = file.slice(
                            chunkIndex * this.chunkSize,
                            Math.min((chunkIndex + 1) * this.chunkSize, file.size)
                        );

                        await this.uploadChunk(chunk, chunkIndex, fileId, uploadUrl);
                        uploadedChunks.add(chunkIndex);
                        uploadedBytes += chunk.size;

                        this.onProgress({
                            loaded: uploadedBytes,
                            total: file.size,
                            percentage: Math.round((uploadedBytes / file.size) * 100),
                            chunkIndex: chunkIndex,
                            totalChunks: totalChunks
                        });

                    } finally {
                        release();
                    }
                });

                uploadPromises.push(promise);
            }

            await Promise.all(uploadPromises);

            // Finalize upload
            const result = await this.finalizeUpload(fileId, uploadUrl);
            this.onComplete(result);
            return result;

        } catch (error) {
            this.onError(error);
            throw error;
        }
    }

    async uploadSingleFile(file, uploadUrl) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('single_upload', 'true');

            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    this.onProgress({
                        loaded: e.loaded,
                        total: e.total,
                        percentage: Math.round((e.loaded / e.total) * 100)
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
            xhr.open('POST', uploadUrl);
            xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
            xhr.send(formData);
        });
    }

    async initializeUpload(file, fileId, totalChunks, uploadUrl) {
        const response = await fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({
                action: 'initialize',
                file_id: fileId,
                file_name: file.name,
                file_size: file.size,
                file_type: file.type,
                total_chunks: totalChunks,
                chunk_size: this.chunkSize
            })
        });

        if (!response.ok) {
            throw new Error('Failed to initialize upload');
        }

        return response.json();
    }

    async uploadChunk(chunk, chunkIndex, fileId, uploadUrl) {
        let attempts = 0;

        while (attempts < this.retryAttempts) {
            try {
                const formData = new FormData();
                formData.append('chunk', chunk);
                formData.append('chunk_index', chunkIndex);
                formData.append('file_id', fileId);
                formData.append('action', 'upload_chunk');

                const response = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`Chunk upload failed: ${response.status}`);
                }

                return response.json();
            } catch (error) {
                attempts++;
                if (attempts >= this.retryAttempts) {
                    throw error;
                }
                await this.delay(this.retryDelay * attempts);
            }
        }
    }

    async finalizeUpload(fileId, uploadUrl) {
        const response = await fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({
                action: 'finalize',
                file_id: fileId
            })
        });

        if (!response.ok) {
            throw new Error('Failed to finalize upload');
        }

        return response.json();
    }

    generateFileId() {
        return 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

/**
 * Semaphore for controlling concurrent operations
 */
class Semaphore {
    constructor(maxConcurrent) {
        this.maxConcurrent = maxConcurrent;
        this.currentConcurrent = 0;
        this.queue = [];
    }

    async acquire() {
        return new Promise((resolve) => {
            if (this.currentConcurrent < this.maxConcurrent) {
                this.currentConcurrent++;
                resolve(() => this.release());
            } else {
                this.queue.push(() => {
                    this.currentConcurrent++;
                    resolve(() => this.release());
                });
            }
        });
    }

    release() {
        this.currentConcurrent--;
        if (this.queue.length > 0) {
            const next = this.queue.shift();
            next();
        }
    }
}

/**
 * Video Compression Utility
 */
class VideoCompressor {
    constructor(options = {}) {
        this.maxWidth = options.maxWidth || 1920;
        this.maxHeight = options.maxHeight || 1080;
        this.quality = options.quality || 0.8;
        this.maxSize = options.maxSize || 50 * 1024 * 1024; // 50MB
    }

    async compressVideo(file) {
        return new Promise((resolve, reject) => {
            const video = document.createElement('video');
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            video.onloadedmetadata = () => {
                // Calculate new dimensions
                let { width, height } = this.calculateDimensions(
                    video.videoWidth,
                    video.videoHeight,
                    this.maxWidth,
                    this.maxHeight
                );

                canvas.width = width;
                canvas.height = height;

                // Create MediaRecorder for compression
                const stream = canvas.captureStream(30); // 30 FPS
                const mediaRecorder = new MediaRecorder(stream, {
                    mimeType: 'video/webm;codecs=vp9',
                    videoBitsPerSecond: 2000000 // 2 Mbps
                });

                const chunks = [];
                mediaRecorder.ondataavailable = (e) => chunks.push(e.data);

                mediaRecorder.onstop = () => {
                    const compressedBlob = new Blob(chunks, { type: 'video/webm' });

                    // Check if compression was beneficial
                    if (compressedBlob.size < file.size && compressedBlob.size < this.maxSize) {
                        resolve(compressedBlob);
                    } else {
                        // If compression didn't help or file is still too large, use original
                        resolve(file);
                    }
                };

                // Start recording
                mediaRecorder.start();

                // Draw video frames
                const drawFrame = () => {
                    if (!video.paused && !video.ended) {
                        ctx.drawImage(video, 0, 0, width, height);
                        requestAnimationFrame(drawFrame);
                    }
                };

                video.play();
                drawFrame();

                // Stop recording after video ends
                video.onended = () => {
                    mediaRecorder.stop();
                };
            };

            video.onerror = () => reject(new Error('Failed to load video'));
            video.src = URL.createObjectURL(file);
        });
    }

    calculateDimensions(originalWidth, originalHeight, maxWidth, maxHeight) {
        let width = originalWidth;
        let height = originalHeight;

        if (width > maxWidth) {
            height = (height * maxWidth) / width;
            width = maxWidth;
        }

        if (height > maxHeight) {
            width = (width * maxHeight) / height;
            height = maxHeight;
        }

        return { width: Math.round(width), height: Math.round(height) };
    }
}

// Export for use in other files
window.ChunkedUploader = ChunkedUploader;
window.VideoCompressor = VideoCompressor;
