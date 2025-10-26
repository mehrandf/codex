<?php
require __DIR__ . '/functions.php';
ensure_data_file();
$links = load_links();
$id = $_GET['id'] ?? '';
$link = null;
foreach ($links as $item) {
    if (($item['id'] ?? '') === $id) {
        $link = $item;
        break;
    }
}
if ($link && !empty($link['url'])) {
    header('Location: ' . $link['url'], true, 301);
    exit;
}
http_response_code(404);
echo 'لینک موردنظر یافت نشد.';
