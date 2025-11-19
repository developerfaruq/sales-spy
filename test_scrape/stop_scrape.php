<?php
$flag = __DIR__ . '/stop.flag';
file_put_contents($flag, '1');
echo json_encode(['status' => 'ok', 'message' => 'Scraping will stop shortly.']);
