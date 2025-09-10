/**
 * IIS AJAX Helper
 * Provides utilities for handling AJAX requests on IIS deployment
 */

(function ($) {
    'use strict';

    // Get base URL from meta tag or construct it
    function getBaseUrl() {
        var baseUrl = $('meta[name="base-url"]').attr('content');
        if (!baseUrl) {
            // Fallback: construct base URL from current location
            var protocol = window.location.protocol;
            var host = window.location.host;
            var pathname = window.location.pathname;

            // Remove the current page from pathname to get base path
            var pathParts = pathname.split('/');
            pathParts.pop(); // Remove the last part (current page)
            var basePath = pathParts.join('/');

            baseUrl = protocol + '//' + host + basePath;
        }
        return baseUrl;
    }

    // Ensure URL is absolute
    function makeAbsoluteUrl(url) {
        if (url.startsWith('http://') || url.startsWith('https://')) {
            return url;
        }

        var baseUrl = getBaseUrl();
        if (url.startsWith('/')) {
            return baseUrl + url;
        } else {
            return baseUrl + '/' + url;
        }
    }

    // Enhanced AJAX function with IIS-specific handling
    function iisAjax(options) {
        // Ensure URL is absolute
        if (options.url) {
            options.url = makeAbsoluteUrl(options.url);
        }

        // Add default headers if not present
        options.headers = options.headers || {};
        if (!options.headers['X-Requested-With']) {
            options.headers['X-Requested-With'] = 'XMLHttpRequest';
        }
        if (!options.headers['X-CSRF-TOKEN']) {
            options.headers['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');
        }

        // Add timeout if not specified
        options.timeout = options.timeout || 30000;

        // Enhanced error handling
        var originalError = options.error;
        options.error = function (xhr, status, error) {
            console.error('IIS AJAX Error:', {
                url: options.url,
                method: options.type || 'GET',
                status: xhr.status,
                statusText: xhr.statusText,
                error: error,
                response: xhr.responseText
            });

            // Handle specific IIS-related errors
            if (xhr.status === 404) {
                console.error('Route not found. Check web.config rewrite rules.');
            } else if (xhr.status === 500) {
                console.error('Server error. Check IIS logs and PHP configuration.');
            } else if (xhr.status === 0) {
                console.error('Network error. Check if the server is running and accessible.');
            }

            // Call original error handler if provided
            if (originalError && typeof originalError === 'function') {
                originalError.call(this, xhr, status, error);
            }
        };

        return $.ajax(options);
    }

    // Extend jQuery with IIS AJAX helper
    $.iisAjax = iisAjax;
    $.makeAbsoluteUrl = makeAbsoluteUrl;
    $.getBaseUrl = getBaseUrl;

    // Override default jQuery AJAX for better IIS compatibility
    var originalAjax = $.ajax;
    $.ajax = function (options) {
        // If it's a simple string URL, make it absolute
        if (typeof options === 'string') {
            options = { url: makeAbsoluteUrl(options) };
        } else if (options && options.url) {
            options.url = makeAbsoluteUrl(options.url);
        }

        return originalAjax.call(this, options);
    };

    // Initialize on document ready
    $(document).ready(function () {
        console.log('IIS AJAX Helper initialized. Base URL:', getBaseUrl());

        // Test AJAX connectivity
        $.iisAjax({
            url: '/test-ajax-connectivity',
            type: 'GET',
            success: function (response) {
                console.log('AJAX connectivity test successful');
            },
            error: function (xhr) {
                if (xhr.status === 404) {
                    console.log('AJAX connectivity test failed - route not found (this is expected if test route doesn\'t exist)');
                } else {
                    console.error('AJAX connectivity test failed:', xhr.status, xhr.statusText);
                }
            }
        });
    });

})(jQuery);
