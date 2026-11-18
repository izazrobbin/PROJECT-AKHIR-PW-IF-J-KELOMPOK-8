<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'artana_ats');
define('DB_USER', 'root');
define('DB_PASS', '');

define('BASE_URL', '/artana_ats2');

date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}