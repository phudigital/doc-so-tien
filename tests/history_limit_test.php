<?php

$_SERVER['REQUEST_METHOD'] = 'GET';
require __DIR__ . '/../process.php';

$history_file = __DIR__ . '/tmp_history_limit.json';

if (file_exists($history_file)) {
    unlink($history_file);
}

for ($i = 0; $i < 55; $i++) {
    saveToHistory(
        100000 + $i,
        0.08,
        true,
        [
            'pre_tax' => 90000 + $i,
            'vat' => 10000,
            'post_tax' => 100000 + $i,
        ]
    );
}

$history = loadHistory();

if (count($history) !== 50) {
    fwrite(STDERR, "Assertion failed: History must keep the latest 50 records.\n");
    exit(1);
}

if (file_exists($history_file)) {
    unlink($history_file);
}

echo "history_limit_test passed\n";
