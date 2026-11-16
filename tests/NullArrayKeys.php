<?php

/**
 * Terminus
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Tests\NullArrayKeys;

require dirname(__DIR__) . '/vendor/autoload.php';

error_reporting(E_ALL);
set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

function expectSame(mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new \RuntimeException('Unexpected null-key regression result: ' . var_export($actual, true));
    }
}

try {
    $io = \DecodeLabs\Terminus\Session::getDefault();
    $capture = $io->capture(static function () use ($io): void {
        (new \ReflectionMethod($io, 'log'))->invoke($io, null, 'Hello {name}', ['name' => 'world']);
        $io->log('unknown', 'Unknown level');
    });
    $capture->resolve();
    expectSame("Hello world\nUnknown level\n", (string)$capture);
} finally {
    restore_error_handler();
}

echo "Null array-key regression checks passed.\n";
