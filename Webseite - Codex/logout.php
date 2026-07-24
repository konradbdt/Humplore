<?php
require_once __DIR__ . '/app/bootstrap.php';

session_destroy(); // Alle Sessions beenden
header('Location: login.php'); // Zurueck zur Login-Seite
exit;
