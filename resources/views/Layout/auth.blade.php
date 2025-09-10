<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name') }} - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Streamline your business with our advanced CRM template. Easily integrate and customize to manage sales, support, and customer interactions efficiently. Perfect for any business size">
    <meta name="keywords"
        content="Advanced CRM template, customer relationship management, business CRM, sales optimization, customer support software, CRM integration, customizable CRM, business tools, enterprise CRM solutions">
    <meta name="author" content="Dreams Technologies">
    <meta name="robots" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}">
    <!-- Apple Icon -->
    <link rel="apple-touch-icon" href="{{ asset('assets/img/apple-icon.png') }}">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/tabler-icons/tabler-icons.min.css') }}">
    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="app-style">

    <!-- Disable text selection styles -->
    <style>
        /* Disable text selection on double-click */
        * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* Allow text selection for input fields and textareas */
        input,
        textarea,
        [contenteditable="true"] {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
    </style>
</head>

<body class="account-page bg-white">
    <!-- Begin Wrapper -->
    <div class="main-wrapper">
        @yield('content')
    </div>
    <!-- End Wrapper -->
    <!-- jQuery -->
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <!-- Bootstrap Core JS -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script src="{{ asset('assets/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js') }}"
        data-cf-settings="7b00a6d53684dbf46de7bd02-|49" defer></script>
    <script defer src="{{ asset('assets/scripts/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015.js') }}"
        integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ=="
        data-cf-beacon='{"rayId":"9715e367c8083a47","version":"2025.8.0","serverTiming":{"name":{"cfExtPri":true,"cfEdge":true,"cfOrigin":true,"cfL4":true,"cfSpeedBrain":true,"cfCacheStatus":true}},"token":"3ca157e612a14eccbb30cf6db6691c29","b":1}'
        crossorigin="anonymous"></script>

    <!-- Auth Forms AJAX Scripts -->
    <script>
        $(document).ready(function() {
            // Login Form AJAX
            $('#login-form').on('submit', function(e) {
                e.preventDefault();

                // Get form data
                var formData = {
                    email: $('#email').val(),
                    password: $('#password').val(),
                    remember: $('#remember').is(':checked') ? 1 : 0,
                    _token: $('input[name="_token"]').val()
                };

                // Clear previous error messages
                $('#alert-danger').hide();
                $('#alert-success').hide();

                $.ajax({
                    url: "{{ route('login.submit') }}",
                    type: "POST",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $('#login-btn').prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...'
                        );
                    },
                    success: function(response) {
                        $('#login-btn').prop('disabled', false).html('Sign In');

                        if (response.status == 200) {
                            $('#alert-success').show();
                            $('#success').text(response.message);

                            // Redirect to dashboard after successful login
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else {
                            $('#alert-danger').show();
                            $('#danger').text(response.message);

                            setTimeout(function() {
                                $('#alert-danger').hide();
                            }, 5000);
                        }
                    },
                    error: function(xhr) {
                        $('#login-btn').prop('disabled', false).html('Sign In');

                        var errors = xhr.responseJSON;
                        $('#alert-danger').show();

                        if (errors && errors.message) {
                            $('#danger').text(errors.message);
                        } else if (errors && errors.errors) {
                            // Handle validation errors
                            var errorMessage = '';
                            $.each(errors.errors, function(key, value) {
                                errorMessage += value + '<br>';
                            });
                            $('#danger').html(errorMessage);
                        } else {
                            $('#danger').text('Something went wrong. Please try again.');
                        }

                        setTimeout(function() {
                            $('#alert-danger').hide();
                        }, 5000);
                    }
                });
            });

            // Registration Form AJAX
            $('#register-form').on('submit', function(e) {
                e.preventDefault();

                // Get form data
                var formData = {
                    first_name: $('#first_name').val(),
                    last_name: $('#last_name').val(),
                    email: $('#email').val(),
                    password: $('#password').val(),
                    password_confirmation: $('#password_confirmation').val(),
                    _token: $('input[name="_token"]').val()
                };

                // Clear previous error messages
                $('#alert-danger').hide();
                $('#alert-success').hide();

                $.ajax({
                    url: "{{ route('register.submit') }}",
                    type: "POST",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $('#register-btn').prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...'
                        );
                    },
                    success: function(response) {
                        $('#register-btn').prop('disabled', false).html('Sign Up');

                        if (response.status == 200) {
                            $('#alert-success').show();
                            $('#success').text(response.message);

                            // Redirect to login page after successful registration
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else {
                            $('#alert-danger').show();
                            $('#danger').text(response.message);

                            setTimeout(function() {
                                $('#alert-danger').hide();
                            }, 5000);
                        }
                    },
                    error: function(xhr) {
                        $('#register-btn').prop('disabled', false).html('Sign Up');

                        var errors = xhr.responseJSON;
                        $('#alert-danger').show();

                        if (errors && errors.message) {
                            $('#danger').text(errors.message);
                        } else if (errors && errors.errors) {
                            // Handle validation errors
                            var errorMessage = '';
                            $.each(errors.errors, function(key, value) {
                                errorMessage += value + '<br>';
                            });
                            $('#danger').html(errorMessage);
                        } else {
                            $('#danger').text('Something went wrong. Please try again.');
                        }

                        setTimeout(function() {
                            $('#alert-danger').hide();
                        }, 5000);
                    }
                });
            });

            // Disable right-click context menu
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            });

            // Disable common keyboard shortcuts for right-click menu
            document.addEventListener('keydown', function(e) {
                // Disable F12 (Developer Tools)
                if (e.key === 'F12') {
                    e.preventDefault();
                    return false;
                }

                // Disable Ctrl+Shift+I (Developer Tools)
                if (e.ctrlKey && e.shiftKey && e.key === 'I') {
                    e.preventDefault();
                    return false;
                }

                // Disable Ctrl+Shift+J (Console)
                if (e.ctrlKey && e.shiftKey && e.key === 'J') {
                    e.preventDefault();
                    return false;
                }

                // Disable Ctrl+U (View Source)
                if (e.ctrlKey && e.key === 'u') {
                    e.preventDefault();
                    return false;
                }

                // Disable Ctrl+S (Save Page)
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                    return false;
                }
            });

            // Disable drag and drop
            document.addEventListener('dragstart', function(e) {
                e.preventDefault();
                return false;
            });

            // Disable text selection with mouse
            document.addEventListener('selectstart', function(e) {
                e.preventDefault();
                return false;
            });
        });
    </script>
</body>

</html>
