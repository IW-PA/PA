<?php

require_once __DIR__ . '/TestCase.php';
require_once __DIR__ . '/ForecastServiceTest.php';

$tests = [
    new ForecastServiceTest(),
];

$hasFailures = false;

foreach ($tests as $test) {
    $class = get_class($test);
    echo "Running {$class}...\n";

    foreach ($test->run() as $result) {
        $status = $result['success'] ? '✔ PASS' : '✘ FAIL';
        $message = $result['message'] ? ' — ' . $result['message'] : '';
        echo "  {$status}: {$result['method']}{$message}\n";

        if (!$result['success']) {
            $hasFailures = true;
        }
    }

    echo "\n";
}

if ($hasFailures) {
    echo "Tests completed with failures.\n";
    exit(1);
}

echo "All tests passed successfully.\n";
exit(0);

