<?php
// Gmail SMTP settings for local development.
// 1. Enable 2-Step Verification on your Google account
// 2. Create an App Password: https://myaccount.google.com/apppasswords
// 3. Put your Gmail address and the 16-character app password below
$smtphost = 'smtp.gmail.com'; // SMTP Host Name
$smtpuser = 'torknancy@gmail.com'; // Your full Gmail address
$smtppass = 'vxdyyjqpcmnzlwse'; // 16-character Gmail App Password (NOT your Gmail login password)
$fromemail = 'torknancy@gmail.com'; // Gmail rewrites From to the authenticated account
$fromname = 'Admin Portal';