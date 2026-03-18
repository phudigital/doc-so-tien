<?php

function runConversion(array $postData): array
{
    $script = <<<'PHP'
$_SERVER["REQUEST_METHOD"] = "POST";
$_POST = %s;
ob_start();
include "process.php";
$output = ob_get_clean();
echo $output;
PHP;

    $command = sprintf(
        'php -d display_errors=0 -r %s',
        escapeshellarg(sprintf($script, var_export($postData, true)))
    );

    $rawOutput = shell_exec($command);

    if (!is_string($rawOutput)) {
        throw new RuntimeException('Cannot execute conversion command.');
    }

    if (!preg_match('/(\{.*\})/s', $rawOutput, $matches)) {
        throw new RuntimeException('Cannot find JSON payload in response: ' . $rawOutput);
    }

    $decoded = json_decode($matches[1], true);

    if (!is_array($decoded)) {
        throw new RuntimeException('Invalid JSON response: ' . $matches[1]);
    }

    return $decoded;
}

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "Assertion failed: {$message}\n");
        exit(1);
    }
}

function assertFormattedThousand(string $amount, string $message): void
{
    $normalized = (int) str_replace('.', '', $amount);
    assertTrue(($normalized % 1000) === 0, $message);
}

$response = runConversion([
    'action' => 'convert',
    'amount' => 122000,
    'is_tax_included' => 'true',
    'vat_rate' => '0.08',
]);

assertTrue(($response['data']['suggestion']['amount_raw'] % 1000) === 0, 'Suggested total must be rounded to 1,000 VND.');
assertFormattedThousand($response['data']['suggestion']['amount_fmt'], 'Suggested total format must be a multiple of 1,000 VND.');
assertFormattedThousand($response['data']['suggestion']['pre_fmt'], 'Suggested pre-tax amount must be a multiple of 1,000 VND.');
assertFormattedThousand($response['data']['suggestion']['vat_fmt'], 'Suggested VAT amount must be a multiple of 1,000 VND.');

echo "suggestion_rounding_test passed\n";
