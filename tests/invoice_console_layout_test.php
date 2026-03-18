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

function assertNotContains(string $needle, string $haystack, string $message): void
{
    if (strpos($haystack, $needle) !== false) {
        fwrite(STDERR, "Assertion failed: {$message}\n");
        exit(1);
    }
}

assertContains('single-workspace', $html, 'Index page must render the single-workspace shell.');
assertContains('simple-shell', $html, 'Index page must render the simplified shell variant.');
assertContains('control-bar', $html, 'Index page must render the compact control bar.');
assertContains('metrics-grid', $html, 'Index page must render the compact metrics grid.');
assertContains('content-grid', $html, 'Index page must render the lower content grid.');
assertContains('vat-toggle', $html, 'Index page must render the VAT segmented toggle.');
assertContains('copy-list', $html, 'Index page must render the compact copy list.');
assertContains('btn-copy-inline', $html, 'Index page must render icon-style copy buttons for summary and quick-copy areas.');
assertContains('copy-metric', $html, 'Index page must render copy buttons for the summary metrics.');
assertContains('history-scroll-shell', $html, 'Index page must render a dedicated history scroll shell.');
assertNotContains('id="txt-title"', $html, 'Index page must hide the title-case quick copy item.');
assertNotContains('id="txt-upper"', $html, 'Index page must hide the uppercase quick copy item.');

echo "invoice_console_layout_test passed\n";
