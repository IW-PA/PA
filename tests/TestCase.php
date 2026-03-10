<?php

class AssertionFailedException extends Exception
{
}

abstract class TestCase
{
    private array $results = [];

    public function run(): array
    {
        $methods = array_filter(
            get_class_methods($this),
            function ($method) {
                return substr($method, 0, 4) === 'test';
            }
        );

        foreach ($methods as $method) {
            try {
                $this->$method();
                $this->results[] = [
                    'method' => $method,
                    'success' => true,
                    'message' => null,
                ];
            } catch (AssertionFailedException $e) {
                $this->results[] = [
                    'method' => $method,
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            } catch (Throwable $e) {
                $this->results[] = [
                    'method' => $method,
                    'success' => false,
                    'message' => get_class($e) . ': ' . $e->getMessage(),
                ];
            }
        }

        return $this->results;
    }

    protected function assertEquals($expected, $actual, string $message = ''): void
    {
        if ($expected != $actual) {
            $msg = $message ?: sprintf('Failed asserting that %s matches expected %s', var_export($actual, true), var_export($expected, true));
            throw new AssertionFailedException($msg);
        }
    }

    protected function assertSame($expected, $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            $msg = $message ?: sprintf('Failed asserting that %s is identical to expected %s', var_export($actual, true), var_export($expected, true));
            throw new AssertionFailedException($msg);
        }
    }

    protected function assertEqualsWithDelta(float $expected, float $actual, float $delta = 0.01, string $message = ''): void
    {
        if (abs($expected - $actual) > $delta) {
            $msg = $message ?: sprintf('Failed asserting that %.4f is within %.4f of %.4f', $actual, $delta, $expected);
            throw new AssertionFailedException($msg);
        }
    }

    protected function assertArrayHasKey(string $key, array $array, string $message = ''): void
    {
        if (!array_key_exists($key, $array)) {
            $msg = $message ?: sprintf('Failed asserting that array has key "%s"', $key);
            throw new AssertionFailedException($msg);
        }
    }
}

