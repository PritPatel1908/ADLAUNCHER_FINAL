# IIS Deployment Guide for Laravel ADLAUNCHER

## Problem Solved

This guide addresses the issue where AJAX POST and GET method routes fail when the Laravel project is published to IIS and accessed through a browser.

## Changes Made

### 1. Updated web.config

-   Enhanced URL rewriting rules for better Laravel route handling
-   Added CORS headers for AJAX requests
-   Removed duplicate MIME type entries that cause IIS errors
-   Simplified configuration to avoid common IIS issues

### 2. Enhanced AJAX Setup

-   Updated `ajax-setup.js` with better error handling and debugging
-   Added proper headers for AJAX requests
-   Added global error handlers for common IIS issues

### 3. Created IIS AJAX Middleware

-   New middleware: `IisAjaxMiddleware.php`
-   Handles CORS preflight requests
-   Ensures proper content types for AJAX responses
-   Added to global web middleware stack

### 4. Created IIS AJAX Helper

-   New JavaScript file: `iis-ajax-helper.js`
-   Provides utilities for absolute URL handling
-   Enhanced error handling for IIS-specific issues
-   Automatic base URL detection and correction

## Deployment Steps

### 1. IIS Configuration

1. Ensure URL Rewrite module is installed on IIS
2. Ensure PHP is properly configured with FastCGI
3. Set up application pool with appropriate .NET CLR version

### 2. File Deployment

1. Copy all project files to IIS web directory
2. Ensure `web.config` is in the `public` folder
3. Set proper permissions for the web directory

### 3. Environment Configuration

Create a `.env` file in the project root with:

```env
APP_NAME=ADLAUNCHER
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

# Database configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Session configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### 4. Laravel Configuration

Run these commands in the project directory:

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Include JavaScript Files

Add these script tags to your main layout file (before closing `</body>` tag):

```html
<script src="{{ asset('assets/js/ajax-setup.js') }}"></script>
<script src="{{ asset('assets/js/iis-ajax-helper.js') }}"></script>
```

### 6. Add Meta Tags

Add these meta tags to your main layout file (in `<head>` section):

```html
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta name="base-url" content="{{ url('/') }}" />
```

## Testing AJAX Routes

### 1. Test Basic Connectivity

Open browser developer tools (F12) and check the console for:

-   "IIS AJAX Helper initialized" message
-   Any AJAX connectivity test results

### 2. Test Specific Routes

Test your AJAX routes by:

1. Opening the application in browser
2. Performing actions that trigger AJAX calls
3. Checking browser network tab for successful requests
4. Monitoring console for any error messages

### 3. Common Issues and Solutions

#### Issue: 404 Not Found

-   **Cause**: URL rewrite rules not working
-   **Solution**: Verify URL Rewrite module is installed and web.config is correct

#### Issue: 419 CSRF Token Mismatch

-   **Cause**: CSRF token not being sent properly
-   **Solution**: Ensure meta tag is present and ajax-setup.js is loaded

#### Issue: 500 Internal Server Error

-   **Cause**: PHP configuration or Laravel setup issues
-   **Solution**: Check IIS logs, verify PHP configuration, ensure Laravel is properly configured

#### Issue: CORS Errors

-   **Cause**: Cross-origin request issues
-   **Solution**: Verify CORS headers in web.config and middleware

#### Issue: HTTP Error 500.19 - Duplicate MIME Type

-   **Cause**: Duplicate MIME type entries in web.config (e.g., .json already defined by IIS)
-   **Solution**: Remove duplicate MIME type entries from web.config. IIS already handles common types like .json

## Debugging Tips

### 1. Enable Detailed Errors

In `web.config`, ensure:

```xml
<httpErrors errorMode="Detailed" />
```

### 2. Check Browser Console

Look for:

-   AJAX error messages
-   Network request failures
-   JavaScript errors

### 3. Check IIS Logs

Location: `C:\inetpub\logs\LogFiles\`
Look for:

-   404 errors
-   500 errors
-   Request patterns

### 4. Test Routes Manually

Use tools like Postman or curl to test routes directly:

```bash
curl -X GET http://your-domain.com/locations/data
curl -X POST http://your-domain.com/location -H "X-CSRF-TOKEN: your-token"
```

## Additional Configuration

### 1. Application Pool Settings

-   Set .NET CLR Version to "No Managed Code"
-   Set Process Model Identity to appropriate user
-   Enable 32-bit applications if needed

### 2. PHP Configuration

Ensure these PHP extensions are enabled:

-   php_curl
-   php_mbstring
-   php_openssl
-   php_pdo_mysql
-   php_tokenizer
-   php_xml

### 3. File Permissions

Set appropriate permissions for:

-   `storage/` directory (write access)
-   `bootstrap/cache/` directory (write access)
-   `public/` directory (read access)

## Support

If you continue to experience issues:

1. Check the browser console for specific error messages
2. Verify all files are properly deployed
3. Test with a simple AJAX call first
4. Check IIS and PHP error logs
5. Ensure all dependencies are properly installed

The enhanced configuration should resolve most AJAX routing issues on IIS deployment.
