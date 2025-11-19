<?php
header('Content-Type: application/json');

$cmd = 'node ../puppeteer-service/wix_scrapper.js';
exec($cmd . ' 2>&1', $output, $status);

if ($status === 0) {
  echo json_encode(['status' => 'success', 'message' => implode("\n", $output)]);
} else {
  echo json_encode(['status' => 'error', 'message' => implode("\n", $output)]);
}
?>
