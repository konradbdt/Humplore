<?php
$_GET = ['type' => 'profile', 'user_id' => 3];
ob_start();
include __DIR__ . '/media.php';
$data = ob_get_clean();
file_put_contents(__DIR__ . '/_out_profile.bin', $data);
echo strlen($data), "\n";
