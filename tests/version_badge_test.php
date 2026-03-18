<?php

ob_start();
include __DIR__ . '/../index.php';
$html = ob_get_clean();

require_once __DIR__ . '/../version.php';

function assertContains(string $needle, string $haystack, string $message): void
{
    if (strpos($haystack, $needle) === false) {
        fwrite(STDERR, "Assertion failed: {$message}\n");
        exit(1);
    }
}

assertContains('Phiên bản ' . APP_VERSION, $html, 'Index page must show the current app version.');

echo "version_badge_test passed\n";
