<?php
/**
 * CareerPulse - Logout Script
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

logoutUser();
setFlash('info', 'You have been safely signed out. See you soon!');
header('Location: ' . baseUrl('index.php'));
exit;
