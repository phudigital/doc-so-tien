<?php

$css = file_get_contents(__DIR__ . '/../styles.css');

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

assertContains('family=Manrope', $css, 'Stylesheet must import Manrope.');
assertContains('--font-ui: "Manrope"', $css, 'UI font must use Manrope.');
assertContains('--font-accent: "Manrope"', $css, 'Accent font must use Manrope for a unified look.');
assertNotContains('Kalam', $css, 'Handwritten Kalam font must be removed.');

echo "typography_font_test passed\n";
