<?php

ob_start();
include __DIR__ . '/../index.php';
$html = ob_get_clean();

function assertContains(string $needle, string $haystack, string $message): void
{
    if (strpos($haystack, $needle) === false) {
        fwrite(STDERR, "Assertion failed: {$message}\n");
        exit(1);
    }
}

assertContains('single-workspace', $html, 'Index page must render the single-workspace shell.');
assertContains('control-bar', $html, 'Index page must render the compact control bar.');
assertContains('metrics-grid', $html, 'Index page must render the compact metrics grid.');
assertContains('content-grid', $html, 'Index page must render the lower content grid.');

echo "invoice_console_layout_test passed\n";
