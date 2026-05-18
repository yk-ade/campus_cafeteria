<?php
require_once __DIR__ . '/functions.php';

function ensure_logged_in(): void
{
    require_login();
}

function ensure_admin(): void
{
    require_admin();
}
