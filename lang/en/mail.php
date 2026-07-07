<?php

return [

    'two_factor' => [
        'subject' => 'Your verification code',
        'greeting' => 'Verify your sign-in',
        'intro' => 'Use the code below to finish signing in. This code expires in :minutes minutes.',
        'footer' => "If you didn't try to sign in, you can safely ignore this email.",
    ],

    'password_reset' => [
        'subject' => 'Reset your password',
        'greeting' => 'Reset your password',
        'intro' => 'We received a request to reset the password for your account.',
        'action' => 'Reset password',
        'expires' => 'This password reset link will expire in :count minutes.',
        'footer' => "If you didn't request a password reset, no further action is required.",
    ],

];
