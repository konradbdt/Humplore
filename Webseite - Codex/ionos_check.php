<?php
header('Content-Type: text/plain; charset=utf-8');

echo "IONOS_CHECK_OK\n";
echo "TIME: " . date('c') . "\n";
echo "PHP: " . PHP_VERSION . "\n";
echo "FILE: " . __FILE__ . "\n";
echo "DIR:  " . __DIR__ . "\n";

$checks = [
  __DIR__ . '/profile.php',
  __DIR__ . '/config/database.php',
  __DIR__ . '/inc/buttomnav.php',
];

echo "\nPATH CHECKS\n";
foreach ($checks as $path) {
  $exists = file_exists($path) ? 'yes' : 'no';
  $readable = is_readable($path) ? 'yes' : 'no';
  echo $path . " | exists=" . $exists . " | readable=" . $readable . "\n";
}

echo "\nEXTENSIONS\n";
$exts = ['pdo', 'pdo_mysql', 'mysqli', 'mbstring'];
foreach ($exts as $ext) {
  echo $ext . '=' . (extension_loaded($ext) ? 'yes' : 'no') . "\n";
}

