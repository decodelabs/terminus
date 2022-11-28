<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus;

interface Adapter
{
    public function hasStty(): bool;
    public function setStty(string $config): void;

    public function getShellWidth(): int;
    public function getShellHeight(): int;
    public function canColorShell(): bool;
}
