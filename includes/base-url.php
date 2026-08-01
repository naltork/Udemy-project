<?php
// Base URL for the project, computed from the current request.
// Using PHP_SELF (the physical script) keeps this correct even on the
// rewritten /profile/<username> URLs, where REQUEST_URI has an extra segment.
if(!isset($base_url)){
    $base_url = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http')
                . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';
}