<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Io;

use DecodeLabs\Exceptional;
use DecodeLabs\Terminus\Session;

class Style
{
    protected const FgColors = [
        'black' => 30,
        'red' => 31,
        'green' => 32,
        'yellow' => 33,
        'blue' => 34,
        'magenta' => 35,
        'cyan' => 36,
        'white' => 37,
        '_select' => 38,
        'reset' => 39,
        'brightBlack' => 90,
        'brightRed' => 91,
        'brightGreen' => 92,
        'brightYellow' => 93,
        'brightBlue' => 94,
        'brightMagenta' => 95,
        'brightCyan' => 96,
        'brightWhite' => 97
    ];

    protected const BgColors = [
        'black' => 40,
        'red' => 41,
        'green' => 42,
        'yellow' => 43,
        'blue' => 44,
        'magenta' => 45,
        'cyan' => 46,
        'white' => 47,
        '_select' => 48,
        'reset' => 49,
        'brightBlack' => 100,
        'brightRed' => 101,
        'brightGreen' => 102,
        'brightYellow' => 103,
        'brightBlue' => 104,
        'brightMagenta' => 105,
        'brightCyan' => 106,
        'brightWhite' => 107
    ];

    protected const Options = [
        'bold' => [1, 22],
        'dim' => [2, 22],
        'italic' => [3, 23],
        'underline' => [4, 24],
        'blink' => [5, 25],
        'strobe' => [6, 25],
        'reverse' => [7, 27],
        'private' => [8, 28],
        'strike' => [9, 29],
    ];

    public ?string $foreground = 'reset' {
        set(
            ?string $foreground
        ) {
            $bits = 4;

            if ($foreground !== null) {
                if (preg_match('/^\:([0-9]{3})|\:([0-9]{3}\,[0-9]{3}\,[0-9]{3})|\#([a-fA-F0-9]{3,6})$/', $foreground, $colorMatches)) {
                    if (!empty($colorMatches[1])) {
                        $bits = 8;
                        $foreground = $colorMatches[1];
                    } elseif (isset($colorMatches[2]) && !empty($colorMatches[2])) {
                        $bits = 24;
                        $foreground = $colorMatches[2];
                    } elseif (!empty($colorMatches[3])) {
                        $bits = 24;
                        $foreground = $this->hexToRgb($colorMatches[3]);
                    }
                } elseif (!isset(self::FgColors[$foreground])) {
                    throw Exceptional::InvalidArgument(
                        message: 'Invalid foreground color: ' . $foreground
                    );
                }
            }

            $this->foreground = $foreground;
            $this->foregroundBits = $bits;
        }
    }

    public protected(set) int $foregroundBits = 4;

    public ?string $background = 'reset' {
        set(
            ?string $background
        ) {
            $bits = 4;

            if ($background !== null) {
                if (preg_match('/^\:([0-9]{3})|\:([0-9]{3}\,[0-9]{3}\,[0-9]{3})|\#([a-fA-F0-9]{3,6})$/', $background, $colorMatches)) {
                    if (!empty($colorMatches[1])) {
                        $bits = 8;
                        $background = $colorMatches[1];
                    } elseif (isset($colorMatches[2]) && !empty($colorMatches[2])) {
                        $bits = 24;
                        $background = $colorMatches[2];
                    } elseif (!empty($colorMatches[3])) {
                        $bits = 24;
                        $background = $this->hexToRgb($colorMatches[3]);
                    }
                } elseif (!isset(self::FgColors[$background])) {
                    throw Exceptional::InvalidArgument(
                        message: 'Invalid background color: ' . $background
                    );
                }
            }

            $this->background = $background;
            $this->backgroundBits = $bits;
        }
    }

    public protected(set) int $backgroundBits = 4;

    /**
     * @var array<string>
     */
    public array $options = [] {
        set(
            array $options
        ) {
            foreach ($options as $option) {
                if (empty($option)) {
                    continue;
                }

                if (!isset(self::Options[$option])) {
                    throw Exceptional::InvalidArgument(
                        message: 'Invalid option: ' . $option
                    );
                }
            }

            $this->options = array_unique($options);
        }
    }

    public bool $error = false;
    public int $linesBefore = 0;
    public int $linesAfter = 0;

    public int $tabs = 0 {
        set(
            int $tabs
        ) {
            if ($tabs < 0) {
                $tabs = 0;
            }

            $this->tabs = $tabs;
        }
    }

    public int $backspaces = 0 {
        set(
            int $backspaces
        ) {
            if ($backspaces < 0) {
                $backspaces = 0;
            }

            $this->backspaces = $backspaces;
        }
    }

    public static function isKeyword(
        string $string
    ): bool {
        return
            isset(self::FgColors[$string]) ||
            isset(self::Options[$string]);
    }

    public static function parse(
        string $modifier
    ): Style {
        if (!preg_match('/^([\^\+\.\<\>\!]*)((([a-zA-Z0-9]+|\#[a-fA-F0-9]+|\:[0-9]+)\|?)*)$/', $modifier, $matches)) {
            throw Exceptional::InvalidArgument(
                message: 'Invalid style modifier: ' . $modifier
            );
        }

        $mods = $matches[1];
        $style = $matches[2];
        $parts = explode('|', $style);
        $fg = $bg = null;
        $options = [];

        foreach ($parts as $part) {
            $testPart = $part;

            if (preg_match('/^\:([0-9]{3})|\:([0-9]{3}\,[0-9]{3}\,[0-9]{3})|\#([a-fA-F0-9]{3,6})$/', $part, $colorMatches)) {
                $testPart = '_select';
            }

            if (isset(self::FgColors[$testPart])) {
                if (!$fg) {
                    $fg = $part;
                } elseif (!$bg) {
                    $bg = $part;
                }
            } elseif (isset(self::Options[$testPart])) {
                $options[] = $part;
            } elseif (!empty($part)) {
                throw Exceptional::InvalidArgument(
                    message: 'Invalid style part: ' . $part
                );
            }
        }

        $output = new self($fg, $bg, ...$options);

        if (preg_match('/([\^]+)/', $mods, $matches)) {
            $output->linesBefore = -strlen($matches[1]);
        }

        if (preg_match('/([\+]+)/', $mods, $matches)) {
            $output->linesBefore = strlen($matches[1]);
        }

        if (preg_match('/([\.]+)/', $mods, $matches)) {
            $output->linesAfter = strlen($matches[1]);
        }

        if (preg_match('/([>]+)/', $mods, $matches)) {
            $output->tabs = strlen($matches[1]);
        }

        if (preg_match('/([<]+)/', $mods, $matches)) {
            $output->backspaces = strlen($matches[1]);
        }

        if (false !== strpos($mods, '!')) {
            $output->error = true;
        }

        if (false !== strpos($mods, '!!')) {
            $output->error = false;
        }

        return $output;
    }

    public function __construct(
        ?string $foreground,
        ?string $background = null,
        string ...$options
    ) {
        $this->foreground = $foreground;
        $this->background = $background;
        $this->options = $options;
    }

    protected function hexToRgb(
        string $hex
    ): string {
        $hex = str_replace('#', '', $hex);
        $length = strlen($hex);

        switch ($length) {
            case 6:
                $rx = substr($hex, 0, 2);
                $gx = substr($hex, 2, 2);
                $bx = substr($hex, 4, 2);
                break;

            case 3:
                $rx = str_repeat(substr($hex, 0, 1), 2);
                $gx = str_repeat(substr($hex, 1, 1), 2);
                $bx = str_repeat(substr($hex, 2, 1), 2);
                break;

            default:
                $rx = $gx = $bx = '0';
                break;
        }

        return hexdec($rx) . ',' . hexdec($gx) . ',' . hexdec($bx);
    }


    public function apply(
        ?string $message,
        Session $session
    ): void {
        if ($this->linesBefore < 0) {
            $this->error ?
                $session->deleteErrorLine(-1 * $this->linesBefore) :
                $session->deleteLine(-1 * $this->linesBefore);
        } elseif ($this->linesBefore > 0) {
            $this->error ?
                $session->newErrorLine($this->linesBefore) :
                $session->newLine($this->linesBefore);
        }

        if ($this->backspaces > 0) {
            $this->error ?
                $session->backspaceError($this->backspaces) :
                $session->backspace($this->backspaces);
        }

        if ($this->tabs > 0) {
            $this->error ?
                $session->tabError($this->tabs) :
                $session->tab($this->tabs);
        }

        if ($session->isAnsi()) {
            $message = $this->format($message);
        }

        $this->error ?
            $session->writeError($message) :
            $session->write($message);

        if ($this->linesAfter > 0) {
            $this->error ?
                $session->newErrorLine($this->linesAfter) :
                $session->newLine($this->linesAfter);
        }
    }

    protected function format(
        ?string $message
    ): ?string {
        if ($message === null) {
            return null;
        }

        $setCodes = [];
        $unsetCodes = [];

        if ($this->foreground !== null) {
            switch ($this->foregroundBits) {
                case 4:
                    $setCodes[] = self::FgColors[$this->foreground];
                    break;

                case 8:
                    $setCodes[] = self::FgColors['_select'] . ';5;' . $this->foreground;
                    break;

                case 24:
                    $setCodes[] = self::FgColors['_select'] . ';2;' . str_replace(',', ';', $this->foreground);
                    break;
            }

            $unsetCodes[] = self::FgColors['reset'];
        }

        if ($this->background !== null) {
            switch ($this->backgroundBits) {
                case 4:
                    $setCodes[] = self::BgColors[$this->background];
                    break;

                case 8:
                    $setCodes[] = self::BgColors['_select'] . ';5;' . $this->background;
                    break;

                case 24:
                    $setCodes[] = self::BgColors['_select'] . ';2;' . str_replace(',', ';', $this->background);
                    break;
            }

            $unsetCodes[] = self::BgColors['reset'];
        }

        foreach ($this->options as $option) {
            $setCodes[] = self::Options[$option][0];
            $unsetCodes[] = self::Options[$option][1];
        }

        return sprintf("\e[%sm%s\e[%sm", implode(';', $setCodes), $message, implode(';', $unsetCodes));
    }
}
