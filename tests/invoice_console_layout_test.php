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

assertContains('ledger-shell', $html, 'Index page must render the Swiss Ledger shell.');
assertContains('ledger-grid', $html, 'Index page must render the two-column ledger grid.');
assertContains('ledger-form', $html, 'Index page must render the Swiss Ledger control form.');
assertContains('metrics-strip', $html, 'Index page must render the simplified metrics strip.');
assertContains('detail-strip', $html, 'Index page must render the lower detail strip.');
assertContains('vat-segment', $html, 'Index page must render the VAT segmented toggle.');
assertContains('copy-rows', $html, 'Index page must render the two-row quick copy list.');
assertContains('btn-copy-inline', $html, 'Index page must render icon-style copy buttons for summary and quick-copy areas.');
assertContains('copy-metric', $html, 'Index page must render copy buttons for the summary metrics.');
assertContains('history-scroll-shell', $html, 'Index page must render a dedicated history scroll shell.');
assertContains('ledger-sidebar', $html, 'Index page must render the history sidebar.');
assertNotContains('id="txt-title"', $html, 'Index page must hide the title-case quick copy item.');
assertNotContains('id="txt-upper"', $html, 'Index page must hide the uppercase quick copy item.');

echo "invoice_console_layout_test passed\n";
