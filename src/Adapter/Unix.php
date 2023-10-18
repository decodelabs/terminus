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
    /**
     * Can the shell support TTY
     */
    public function hasStty(): bool
    {
        static $output;

        if (isset($output)) {
            return $output;
        }

        exec('which stty', $result);
        return $output = !empty(trim($result[0]));
    }

    /**
     * Set stty config
     */
    public function setStty(string $config): void
    {
        system('stty \'' . $config . '\'');
    }


    /**
     * Get shell width
     */
    public function getShellWidth(): int
    {
        static $output;

        if (isset($output)) {
            return $output;
        }

        exec('tput cols 2>/dev/null', $result);
        return $output = (int)($result[0] ?? 80);
    }

    /**
     * Get shell height
     */
    public function getShellHeight(): int
    {
        static $output;

        if (isset($output)) {
            return $output;
        }

        exec('tput lines 2>/dev/null', $result);
        return $output = (int)($result[0] ?? 30);
    }

    /**
     * Get shell be coloured?
     */
    public function canColorShell(): bool
    {
        static $output;

        if (isset($output)) {
            return $output;
        }

        if (!defined('STDOUT')) {
            return $output = false;
        }

        if (function_exists('stream_isatty')) {
            return $output = stream_isatty(\STDOUT);
        }

        if (function_exists('posix_isatty')) {
            return $output = posix_isatty(\STDOUT);
        }

        if (($_SERVER['TERM'] ?? null) === 'xterm-256color') {
            return $output = true;
        }

        if (($_SERVER['CLICOLOR'] ?? null) === '1') {
            return $output = true;
        }

        return $output = false;
    }
}
