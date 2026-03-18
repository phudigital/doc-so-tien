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

assertContains('family=Lexend', $css, 'Stylesheet must import Lexend.');
assertContains('family=Source+Sans+3', $css, 'Stylesheet must import Source Sans 3.');
assertContains('--font-heading: "Lexend"', $css, 'Heading font must use Lexend.');
assertContains('--font-ui: "Source Sans 3"', $css, 'UI font must use Source Sans 3.');
assertNotContains('Manrope', $css, 'Legacy Manrope font should be removed from the stylesheet.');
assertNotContains('Kalam', $css, 'Handwritten Kalam font must be removed.');

echo "typography_font_test passed\n";
