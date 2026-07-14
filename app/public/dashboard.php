<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$user = requireLogin();
header('Location: /dashboard_profile.php');
exit;
