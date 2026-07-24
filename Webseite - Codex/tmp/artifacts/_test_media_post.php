<?php
$_GET = ['type' => 'post', 'post_id' => 17];
ob_start();
include __DIR__ . '/media.php';
$data = ob_get_clean();
file_put_contents(__DIR__ . '/_out_post.bin', $data);
echo strlen($data), "\n";
