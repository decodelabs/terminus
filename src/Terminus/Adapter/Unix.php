<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Adapter;

use DecodeLabs\Terminus\AdapterAbstract;

class Unix extends AdapterAbstract
{
    private ?bool $hasStty = null;
    private ?int $shellWidth = null;
    private ?int $shellHeight = null;
    private ?bool $canColorShell = null;

    /**
     * Can the shell support TTY
     */
    public function hasStty(): bool
    {
        if (isset($this->hasStty)) {
            return $this->hasStty;
        }

        exec('which stty', $result);
        return $this->hasStty = !empty(trim($result[0]));
    }

    /**
     * Set stty config
     */
    public function setStty(
        string $config
    ): void {
        system('stty \'' . $config . '\'');
    }


    /**
     * Get shell width
     */
    public function getShellWidth(): int
    {
        if (isset($this->shellWidth)) {
            return $this->shellWidth;
        }

        exec('tput cols 2>/dev/null', $result);
        return $this->shellWidth = (int)($result[0] ?? 80);
    }

    /**
     * Get shell height
     */
    public function getShellHeight(): int
    {
        if (isset($this->shellHeight)) {
            return $this->shellHeight;
        }

        exec('tput lines 2>/dev/null', $result);
        return $this->shellHeight = (int)($result[0] ?? 30);
    }

    /**
     * Get shell be coloured?
     */
    public function canColorShell(): bool
    {
        if (isset($this->canColorShell)) {
            return $this->canColorShell;
        }

        if (!defined('STDOUT')) {
            return $this->canColorShell = false;
        }

        if (function_exists('stream_isatty')) {
            return $this->canColorShell = stream_isatty(\STDOUT);
        }

        if (function_exists('posix_isatty')) {
            return $this->canColorShell = posix_isatty(\STDOUT);
        }

        if (($_SERVER['TERM'] ?? null) === 'xterm-256color') {
            return $this->canColorShell = true;
        }

        if (($_SERVER['CLICOLOR'] ?? null) === '1') {
            return $this->canColorShell = true;
        }

        return $this->canColorShell = false;
    }
}
