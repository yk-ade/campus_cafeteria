<?php
session_start();
session_unset();
session_destroy();

require_once 'includes/functions.php';
header("Location: " . qb_url('index.php'));
exit();
