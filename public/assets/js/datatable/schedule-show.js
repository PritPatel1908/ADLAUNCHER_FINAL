$(document).ready(function () {
    function initializeSelect2() {
        $('select[data-toggle="select2"]').each(function () {
            if (!$(this).hasClass('select2-initialized')) {
                $(this).select2({
                    placeholder: 'Select...',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $(this).closest('.offcanvas-body, .modal-body, body')
                });
                $(this).addClass('select2-initialized');
            }
        });
    }

    initializeSelect2();
    $(document).on('shown.bs.offcanvas', function () { setTimeout(initializeSelect2, 100); });

    // Edit schedule form submission with optimized upload
    $('#edit-schedule-form').on('submit', function (e) {
        e.preventDefault();
        console.log('Edit form submission started');

        var submitBtn = $(this).find('button[type="submit"]');
        var originalBtnText = submitBtn.html();
        submitBtn.html('Updating...').prop('disabled', true);

        // Show progress bar
        $('#edit-upload-progress-container').show();
        $('#edit-form-alert').hide();
        updateEditProgressBar(0, 'Preparing upload...');

        // Check for large video files and use chunked upload
        var hasLargeFiles = false;
        var mediaFiles = [];
        $('input[name="edit_media_file[]"]').each(function (index) {
            if (this.files[0]) {
                var file = this.files[0];
                console.log('Edit File ' + index + ':', file.name, 'Size:', file.size, 'bytes', 'Type:', file.type);

                if (file.size > 2 * 1024 * 1024 * 1024) { // 2GB limit
                    alert('File ' + file.name + ' is too large. Maximum size is 2GB.');
                    submitBtn.html(originalBtnText).prop('disabled', false);
                    return false;
                }

                if (file.size > 50 * 1024 * 1024 && file.type.startsWith('video/')) { // 50MB for videos
                    hasLargeFiles = true;
                }

                mediaFiles.push({
                    file: file,
                    index: index,
                    title: $('input[name="edit_media_title[]"]').eq(index).val(),
                    type: $('select[name="edit_media_type[]"]').eq(index).val(),
                    screenId: $('select[name="edit_media_screen_id[]"]').eq(index).val(),
                    duration: $('input[name="edit_media_duration_seconds[]"]').eq(index).val(),
                    startDate: $('input[name="edit_media_start_date_time[]"]').eq(index).val(),
                    endDate: $('input[name="edit_media_end_date_time[]"]').eq(index).val(),
                    playForever: $('input[name="edit_media_play_forever[]"]').eq(index).is(':checked'),
                    mediaId: $('input[name="edit_media_id[]"]').eq(index).val()
                });
            }
        });

        if (hasLargeFiles) {
            // Use chunked upload for large files
            uploadEditWithChunkedUpload(this, mediaFiles, submitBtn, originalBtnText);
        } else {
            // Use regular upload for smaller files
            uploadEditWithRegularMethod(this, submitBtn, originalBtnText);
        }
    });

    function showError(message) {
        var $wrap = $('#edit-form-alert');
        var $alert = $wrap.find('.alert');
        if ($alert.length === 0) { $wrap.html('<div class="alert alert-danger alert-dismissible fade show" role="alert"></div>'); $alert = $wrap.find('.alert'); }
        $alert.removeClass('alert-success').addClass('alert-danger');
        $alert.html(message + ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
        $wrap.show();
    }

    function showPageSuccessMessage(message) {
        // Check if there's already a success alert at the top
        var existingAlert = $('.container-fluid .alert-success');
        if (existingAlert.length > 0) {
            existingAlert.remove();
        }

        // Add success message at the top of the page
        var successHtml = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        $('.container-fluid').prepend(successHtml);

        // Auto-hide after 5 seconds
        setTimeout(function () {
            $('.container-fluid .alert-success').fadeOut();
        }, 5000);
    }


    function updateScheduleContent(schedule) {
        // Update main header section
        $('h5.mb-1').first().text(schedule.schedule_name || '');
        $('p.mb-2').first().text((schedule.device ? schedule.device.name : '') || (schedule.device ? schedule.device.unique_id : '') || 'N/A');

        // Header badges removed; no update needed

        // Overview Start/End removed; only update name
        $('.card-body .row .col-md-6:first .mb-4:first p').text(schedule.schedule_name || '');

        // Update Target section
        $('.card-body .row .col-md-6:last .mb-4:first p').text((schedule.device ? schedule.device.name : '') || 'N/A');
        $('.card-body .row .col-md-6:last .mb-4:nth-child(2) p').text((schedule.layout ? schedule.layout.layout_name : '') || 'N/A');
        // Screen removed from Target section

        // Update media section
        updateMediaSection(schedule.medias);
    }

    function updateMediaSection(medias) {
        const mediaContainer = $('.card-body .table-responsive tbody');
        const noMediaContainer = $('.text-center.py-4');

        if (medias && medias.length > 0) {
            // Hide no media message
            noMediaContainer.hide();

            // Clear existing media rows
            mediaContainer.empty();

            // Add new media rows
            medias.forEach(function (media) {
                const createdDate = media.formatted_created_date || 'N/A';
                const screenLabel = media.screen && media.screen.screen_no ? ('Screen ' + media.screen.screen_no) : 'N/A';
                const startAt = media.start_date_time_formatted || media.schedule_start_date_time_formatted || '';
                const endAt = media.play_forever ? 'Play Forever' : (media.end_date_time_formatted || media.schedule_end_date_time_formatted || '');

                const mediaRow = `
                    <tr>
                        <td>${media.title || ''}</td>
                        <td>${media.media_type ? media.media_type.charAt(0).toUpperCase() + media.media_type.slice(1) : ''}</td>
                        <td>${screenLabel}</td>
                        <td>${startAt || 'N/A'}</td>
                        <td>${endAt || 'N/A'}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary preview-media-btn"
                                    data-media-file="${media.media_file || ''}"
                                    data-media-type="${media.media_type || ''}"
                                    data-media-title="${media.title || ''}">
                                <i class="ti ti-eye"></i> Preview
                            </button>
                        </td>
                        <td>${createdDate}</td>
                    </tr>
                `;
                mediaContainer.append(mediaRow);
            });

            // Show table
            $('.table-responsive').show();
        } else {
            // Show no media message
            $('.table-responsive').hide();
            noMediaContainer.show();
        }
    }

    // Handle add edit media functionality (clone existing template and clear values)
    $(document).on('click', '#edit-add-media', function () {
        var $container = $('#edit-media-container');
        var $template = $container.find('.media-item').first().clone(true, true);

        if ($template.length === 0) {
            return; // nothing to clone
        }

        // Remove any media id (so it is treated as new)
        $template.removeAttr('data-media-id');
        $template.find('input[name="edit_media_id[]"]').val('');

        // Clear text/number/datetime inputs
        $template.find('input[type="text"]').val('');
        $template.find('input[type="number"]').val('');
        $template.find('input[type="datetime-local"]').val('');
        $template.find('input[type="file"]').val('');

        // Uncheck play forever
        $template.find('input[name="edit_media_play_forever[]"]').prop('checked', false);

        // Reset selects (including select2)
        $template.find('select').each(function () {
            $(this).val('');
            if ($(this).hasClass('select2')) {
                $(this).trigger('change.select2');
            }
        });

        // If there is a plaintext current file label, clear it
        $template.find('.form-control-plaintext small').text('');

        // Append the cleaned clone
        $container.append($template);

        // Re-initialize select2 for any new elements
        initializeSelect2();
    });

    // Handle remove media functionality
    $(document).on('click', '.remove-media', function () {
        const mediaId = $(this).data('media-id');
        if (mediaId) {
            // If it's an existing media item, add a hidden input to mark it for deletion
            const deleteInput = `<input type="hidden" name="delete_media_ids[]" value="${mediaId}">`;
            $('#edit-schedule-form').append(deleteInput);
        }
        $(this).closest('.media-item').remove();
    });

    // Media preview functionality
    $(document).on('click', '.preview-media-btn', function () {
        const mediaFile = $(this).data('media-file');
        const mediaType = $(this).data('media-type');
        const mediaTitle = $(this).data('media-title');

        // Update modal title
        $('#mediaPreviewModalLabel').text('Preview: ' + mediaTitle);

        // Clear previous content
        $('#mediaPreviewContent').empty();

        // Generate media URL
        const mediaUrl = window.location.origin + '/storage/' + mediaFile;

        let mediaHtml = '';

        // Create appropriate media element based on type
        if (mediaType === 'image' || mediaType === 'png' || mediaType === 'jpg' || mediaType === 'jpeg') {
            mediaHtml = `
                <div class="media-preview-container">
                    <img src="${mediaUrl}" alt="${mediaTitle}" class="img-fluid rounded">
                </div>
            `;
        } else if (mediaType === 'video' || mediaType === 'mp4') {
            mediaHtml = `
                <div class="media-preview-container">
                    <video controls class="w-100">
                        <source src="${mediaUrl}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            `;
        } else if (mediaType === 'audio') {
            mediaHtml = `
                <div class="media-preview-container">
                    <audio controls class="w-100">
                        <source src="${mediaUrl}" type="audio/mpeg">
                        Your browser does not support the audio tag.
                    </audio>
                </div>
            `;
        } else if (mediaType === 'pdf') {
            mediaHtml = `
                <div class="media-preview-container">
                    <iframe src="${mediaUrl}" type="application/pdf">
                        <p>Your browser does not support PDFs. <a href="${mediaUrl}" target="_blank">Click here to download the PDF</a></p>
                    </iframe>
                </div>
            `;
        } else {
            mediaHtml = `
                <div class="media-preview-placeholder">
                    <i class="ti ti-file" style="font-size: 3rem;"></i>
                    <h6 class="mt-2">Preview not available</h6>
                    <p>This file type cannot be previewed.</p>
                    <a href="${mediaUrl}" target="_blank" class="btn btn-primary">
                        <i class="ti ti-download"></i> Download File
                    </a>
                </div>
            `;
        }

        $('#mediaPreviewContent').html(mediaHtml);

        // Show modal
        $('#mediaPreviewModal').modal('show');
    });

    // Progress bar update function with enhanced styling
    function updateEditProgressBar(percent, status) {
        const progressBar = $('#edit-upload-progress-bar');
        const progressText = $('#edit-upload-progress-text');
        const statusText = $('#edit-upload-status-text');

        // Update progress bar width
        progressBar.css('width', percent + '%').attr('aria-valuenow', percent);
        progressText.text(percent + '%');
        statusText.text(status);

        // Add dynamic color classes based on progress
        progressBar.removeClass('progress-success progress-warning progress-error');

        if (percent >= 100) {
            progressBar.addClass('progress-success');
            statusText.css('color', '#28a745');
        } else if (percent >= 75) {
            progressBar.addClass('progress-warning');
            statusText.css('color', '#ffc107');
        } else if (percent >= 50) {
            statusText.css('color', '#17a2b8');
        } else if (percent >= 25) {
            statusText.css('color', '#6c757d');
        } else {
            statusText.css('color', '#6c757d');
        }

        // Add pulsing effect for active uploads
        if (percent > 0 && percent < 100) {
            progressBar.css('animation', 'pulse 1.5s ease-in-out infinite alternate');
        } else {
            progressBar.css('animation', 'none');
        }
    }

    // Chunked upload function for large files in edit form
    async function uploadEditWithChunkedUpload(form, mediaFiles, submitBtn, originalBtnText) {
        try {
            updateEditProgressBar(0, 'Initializing chunked upload...');

            const uploadPromises = [];
            const uploadedFiles = [];

            for (let i = 0; i < mediaFiles.length; i++) {
                const mediaFile = mediaFiles[i];
                const file = mediaFile.file;

                if (file.size > 50 * 1024 * 1024 && file.type.startsWith('video/')) {
                    // Use chunked upload for large video files
                    const uploader = new ChunkedUploader({
                        chunkSize: 2 * 1024 * 1024, // 2MB chunks
                        maxConcurrentChunks: 3,
                        onProgress: (progress) => {
                            const totalProgress = ((i / mediaFiles.length) * 100) + ((progress.percentage / mediaFiles.length));
                            updateEditProgressBar(Math.round(totalProgress), `Uploading ${file.name}... (${progress.percentage}%)`);
                        },
                        onError: (error) => {
                            throw new Error(`Failed to upload ${file.name}: ${error.message}`);
                        }
                    });

                    const promise = uploader.uploadFile(file, '/schedule/chunked-upload').then(result => {
                        console.log('Edit chunked upload completed for:', file.name, result);
                        return {
                            ...mediaFile,
                            file_path: result.file_path,
                            file_name: result.file_name
                        };
                    });
                    uploadPromises.push(promise);
                } else {
                    // Use single upload for smaller files
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('single_upload', 'true');

                    const promise = fetch('/schedule/chunked-upload', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        body: formData
                    }).then(async response => {
                        if (!response.ok) {
                            throw new Error(`Failed to upload ${file.name}`);
                        }

                        const result = await response.json();
                        console.log('Edit single upload completed for:', file.name, result);
                        return {
                            ...mediaFile,
                            file_path: result.file_path,
                            file_name: result.file_name
                        };
                    });

                    uploadPromises.push(promise);
                }
            }

            // Wait for all uploads to complete
            console.log('Edit: Waiting for', uploadPromises.length, 'upload promises to complete...');
            const uploadResults = await Promise.all(uploadPromises);
            console.log('Edit: All uploads completed. Upload results:', uploadResults);

            // Add the upload results to uploadedFiles
            uploadedFiles.push(...uploadResults);

            updateEditProgressBar(90, 'Updating schedule...');

            // Now update the schedule with uploaded file paths
            const scheduleData = {
                _method: 'PUT',
                schedule_name: $('input[name="schedule_name"]').val(),
                device_id: $('select[name="device_id"]').val(),
                layout_id: $('select[name="layout_id"]').val(),
                schedule_start_date_time: $('input[name="schedule_start_date_time"]').val(),
                schedule_end_date_time: $('input[name="schedule_end_date_time"]').val(),
                play_forever: $('input[name="play_forever"]').is(':checked'),
                medias: uploadedFiles.map(media => ({
                    id: media.mediaId,
                    title: media.title,
                    media_type: media.type,
                    screen_id: media.screenId,
                    duration_seconds: media.duration,
                    start_date_time: media.startDate,
                    end_date_time: media.endDate,
                    play_forever: media.playForever,
                    media_file: media.file_path
                }))
            };

            console.log('Sending edit schedule data:', scheduleData);

            const response = await fetch($(form).attr('action'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify(scheduleData)
            });

            console.log('Edit response status:', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Edit response error:', errorText);
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }

            const result = await response.json();
            console.log('Edit response result:', result);

            updateEditProgressBar(100, 'Complete!');

            if (result.success) {
                var $wrap = $('#edit-form-alert');
                var $alert = $wrap.find('.alert');
                if ($alert.length === 0) {
                    $wrap.html('<div class="alert alert-success alert-dismissible fade show" role="alert"></div>');
                    $alert = $wrap.find('.alert');
                }
                $alert.removeClass('alert-danger').addClass('alert-success');
                $alert.html('Schedule updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                $wrap.show();

                // Show success message at top of page
                showPageSuccessMessage('Schedule updated successfully!');

                // Update page content with new data
                if (result.schedule) {
                    updateScheduleContent(result.schedule);
                }

                // Close the offcanvas
                setTimeout(function () {
                    $('#offcanvas_edit').offcanvas('hide');
                }, 1200);
            } else {
                throw new Error(result.message || 'Failed to update schedule');
            }

        } catch (error) {
            console.error('Edit chunked upload error:', error);
            showError('Upload failed: ' + error.message);
        } finally {
            $('#edit-upload-progress-container').hide();
            submitBtn.html(originalBtnText).prop('disabled', false);
        }
    }

    // Regular upload function for smaller files in edit form
    function uploadEditWithRegularMethod(form, submitBtn, originalBtnText) {
        // Use FormData for file uploads
        const formData = new FormData(form);
        formData.append('_method', 'PUT');
        const actionUrl = $(form).attr('action');

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.round(evt.loaded / evt.total * 100);
                        console.log('Edit upload progress: ' + percentComplete + '%');
                        updateEditProgressBar(percentComplete, 'Uploading files... (' + percentComplete + '%)');
                    }
                }, false);
                return xhr;
            },
            success: function (response) {
                // Hide progress bar
                $('#edit-upload-progress-container').hide();

                if (response.success) {
                    // Show success message
                    var $wrap = $('#edit-form-alert');
                    var $alert = $wrap.find('.alert');
                    if ($alert.length === 0) { $wrap.html('<div class="alert alert-success alert-dismissible fade show" role="alert"></div>'); $alert = $wrap.find('.alert'); }
                    $alert.removeClass('alert-danger').addClass('alert-success');
                    $alert.html('Schedule updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
                    $wrap.show();

                    // Show success message at top of page
                    showPageSuccessMessage('Schedule updated successfully!');

                    // Update page content with new data
                    if (response.schedule) {
                        updateScheduleContent(response.schedule);
                    }

                    // Close the offcanvas
                    setTimeout(function () {
                        $('#offcanvas_edit').offcanvas('hide');
                    }, 1200);
                } else {
                    showError(response.message || 'Failed to update schedule');
                }
            },
            error: function (xhr) {
                // Hide progress bar
                $('#edit-upload-progress-container').hide();

                const res = xhr.responseJSON;
                let msg = 'Failed to update schedule.';
                if (res && res.errors) { msg = Object.values(res.errors)[0][0]; }
                showError(msg);
            },
            complete: function () {
                submitBtn.html(originalBtnText).prop('disabled', false);
            }
        });
    }

    // Function to get video duration
    function getVideoDuration(file, callback) {
        const video = document.createElement('video');
        video.preload = 'metadata';

        video.onloadedmetadata = function () {
            window.URL.revokeObjectURL(video.src);
            callback(Math.round(video.duration));
        };

        video.onerror = function () {
            window.URL.revokeObjectURL(video.src);
            callback(null);
        };

        video.src = URL.createObjectURL(file);
    }

    // Function to show toast notifications
    function showToast(title, message, type = 'info') {
        // Create toast element
        var toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'info' ? 'primary' : type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}</strong><br>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        // Add to toast container or create one
        var $toastContainer = $('#toast-container');
        if ($toastContainer.length === 0) {
            $toastContainer = $('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
            $('body').append($toastContainer);
        }

        var $toast = $(toastHtml);
        $toastContainer.append($toast);

        // Initialize and show toast
        var toast = new bootstrap.Toast($toast[0], { delay: 5000 });
        toast.show();

        // Remove toast element after it's hidden
        $toast.on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }

    // Handle video file selection for edit form
    $(document).on('change', '.edit-media-file-input', function () {
        const file = this.files[0];
        const $mediaItem = $(this).closest('.media-item');
        const $durationInput = $mediaItem.find('.edit-media-duration-input');
        const $mediaTypeSelect = $mediaItem.find('select[name="edit_media_type[]"]');

        if (file && file.type.startsWith('video/')) {
            // Show loading indicator
            $durationInput.prop('disabled', true).val('Loading...');

            getVideoDuration(file, function (duration) {
                $durationInput.prop('disabled', false);

                if (duration) {
                    $durationInput.val(duration);
                    // Auto-select video type if not already selected
                    if (!$mediaTypeSelect.val()) {
                        if (file.name.toLowerCase().endsWith('.mp4')) {
                            $mediaTypeSelect.val('mp4').trigger('change');
                        } else {
                            $mediaTypeSelect.val('video').trigger('change');
                        }
                    }

                    // Show success message
                    showToast('Video Duration', `Video duration detected: ${duration} seconds`, 'success');
                } else {
                    $durationInput.val('').attr('placeholder', 'Could not detect duration');
                    showToast('Error', 'Could not detect video duration. Please enter manually.', 'warning');
                }
            });
        } else if (file && file.type.startsWith('audio/')) {
            // Handle audio files
            getVideoDuration(file, function (duration) {
                if (duration) {
                    $durationInput.val(duration);
                    if (!$mediaTypeSelect.val()) {
                        $mediaTypeSelect.val('audio').trigger('change');
                    }
                    showToast('Audio Duration', `Audio duration detected: ${duration} seconds`, 'success');
                }
            });
        } else {
            // Clear duration for non-video/audio files
            $durationInput.val('');
        }
    });
});


