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

assertContains('console-shell', $html, 'Index page must render the invoice console shell.');
assertContains('summary-grid', $html, 'Index page must render a dedicated summary grid.');
assertContains('workspace-grid', $html, 'Index page must render the workspace grid.');
assertContains('history-panel visible', $html, 'Index page must render the console-style history panel.');

echo "invoice_console_layout_test passed\n";
