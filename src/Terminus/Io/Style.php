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
    public const FG_COLORS = [
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

    public const BG_COLORS = [
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

    public const OPTIONS = [
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

    /**
     * @var string|null
     */
    protected $foreground = 'reset';

    /**
     * @var int
     */
    protected $foregroundBits = 4;

    /**
     * @var string|null
     */
    protected $background = 'reset';

    /**
     * @var int
     */
    protected $backgroundBits = 4;

    /**
     * @var array<string>
     */
    protected $options = [];

    /**
     * @var bool
     */
    protected $error = false;

    /**
     * @var int
     */
    protected $linesBefore = 0;

    /**
     * @var int
     */
    protected $linesAfter = 0;

    /**
     * @var int
     */
    protected $tabs = 0;

    /**
     * @var int
     */
    protected $backspaces = 0;

    /**
     * Is string a color or option keyword?
     */
    public static function isKeyword(string $string): bool
    {
        return isset(self::FG_COLORS[$string]) || isset(self::OPTIONS[$string]);
    }

    /**
     * Parse modifier string
     */
    public static function parse(string $modifier): Style
    {
        if (!preg_match('/^([\^\+\.\<\>\!]*)((([a-zA-Z0-9]+|\#[a-fA-F0-9]+|\:[0-9]+)\|?)*)$/', $modifier, $matches)) {
            throw Exceptional::InvalidArgument(
                'Invalid style modifier: ' . $modifier
            );
        }

        $mods = $matches[1] ?? null;
        $style = $matches[2] ?? null;
        $parts = explode('|', $style);
        $fg = $bg = null;
        $options = [];

        foreach ($parts as $part) {
            $testPart = $part;

            if (preg_match('/^\:([0-9]{3})|\:([0-9]{3}\,[0-9]{3}\,[0-9]{3})|\#([a-fA-F0-9]{3,6})$/', $part, $colorMatches)) {
                $testPart = '_select';
            }

            if (isset(self::FG_COLORS[$testPart])) {
                if (!$fg) {
                    $fg = $part;
                } elseif (!$bg) {
                    $bg = $part;
                }
            } elseif (isset(self::OPTIONS[$testPart])) {
                $options[] = $part;
            } elseif (!empty($part)) {
                throw Exceptional::InvalidArgument(
                    'Invalid style part: ' . $part
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

    /**
     * Init with fg, gb and options
     */
    public function __construct(?string $foreground, ?string $background = null, string ...$options)
    {
        $this->setForeground($foreground);
        $this->setBackground($background);
        $this->setOptions(...$options);
    }

    /**
     * Set foreground color
     *
     * @return $this
     */
    public function setForeground(?string $foreground): Style
    {
        $bits = 4;

        if ($foreground !== null) {
            if (preg_match('/^\:([0-9]{3})|\:([0-9]{3}\,[0-9]{3}\,[0-9]{3})|\#([a-fA-F0-9]{3,6})$/', $foreground, $colorMatches)) {
                if (isset($colorMatches[1]) && !empty($colorMatches[1])) {
                    $bits = 8;
                    $foreground = $colorMatches[1];
                } elseif (isset($colorMatches[2]) && !empty($colorMatches[2])) {
                    $bits = 24;
                    $foreground = $colorMatches[2];
                } elseif (isset($colorMatches[3]) && !empty($colorMatches[3])) {
                    $bits = 24;
                    $foreground = $this->hexToRgb($colorMatches[3]);
                }
            } elseif (!isset(self::FG_COLORS[$foreground])) {
                throw Exceptional::InvalidArgument(
                    'Invalid foreground color: ' . $foreground
                );
            }
        }

        $this->foreground = $foreground;
        $this->foregroundBits = $bits;
        return $this;
    }

    /**
     * Get foreground color
     */
    public function getForeground(): ?string
    {
        return $this->foreground;
    }

    /**
     * Get foreground select color
     */
    public function getForegroundBits(): int
    {
        return $this->foregroundBits;
    }

    /**
     * Set background color
     *
     * @return $this
     */
    public function setBackground(?string $background): Style
    {
        $bits = 4;

        if ($background !== null) {
            if (preg_match('/^\:([0-9]{3})|\:([0-9]{3}\,[0-9]{3}\,[0-9]{3})|\#([a-fA-F0-9]{3,6})$/', $background, $colorMatches)) {
                if (isset($colorMatches[1]) && !empty($colorMatches[1])) {
                    $bits = 8;
                    $background = $colorMatches[1];
                } elseif (isset($colorMatches[2]) && !empty($colorMatches[2])) {
                    $bits = 24;
                    $background = $colorMatches[2];
                } elseif (isset($colorMatches[3]) && !empty($colorMatches[3])) {
                    $bits = 24;
                    $background = $this->hexToRgb($colorMatches[3]);
                }
            } elseif (!isset(self::FG_COLORS[$background])) {
                throw Exceptional::InvalidArgument(
                    'Invalid background color: ' . $background
                );
            }
        }

        $this->background = $background;
        $this->backgroundBits = $bits;
        return $this;
    }

    /**
     * Get background color
     */
    public function getBackground(): ?string
    {
        return $this->background;
    }

    /**
     * Get background select color
     */
    public function getbackgroundBits(): ?int
    {
        return $this->backgroundBits;
    }

    /**
     * Convert hex color to rgb
     */
    protected function hexToRgb(string $hex): string
    {
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

    /**
     * Set options
     *
     * @return $this
     */
    public function setOptions(string ...$options): Style
    {
        $this->options = [];

        foreach ($options as $option) {
            if (empty($option)) {
                continue;
            }

            if (!isset(self::OPTIONS[$option])) {
                throw Exceptional::InvalidArgument(
                    'Invalid option: ' . $option
                );
            }

            $this->options[] = $option;
        }

        $this->options = array_unique($this->options);
        return $this;
    }

    /**
     * Get options
     *
     * @return array<string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }


    /**
     * Set as error
     *
     * @return $this
     */
    public function setError(bool $flag): Style
    {
        $this->error = $flag;
        return $this;
    }

    /**
     * Is error
     */
    public function isError(): bool
    {
        return $this->error;
    }

    /**
     * Set pre new line count
     *
     * @return $this
     */
    public function setLinesBefore(int $lines): Style
    {
        $this->linesBefore = $lines;
        return $this;
    }

    /**
     * Get pre line count
     */
    public function getLinesBefore(): int
    {
        return $this->linesBefore;
    }

    /**
     * Set post new line count
     *
     * @return $this
     */
    public function setLinesAfter(int $lines): Style
    {
        $this->linesAfter = $lines;
        return $this;
    }

    /**
     * Get post line count
     */
    public function getLinesAfter(): int
    {
        return $this->linesAfter;
    }

    /**
     * Set tab count
     *
     * @return $this
     */
    public function setTabs(int $tabs): Style
    {
        if ($tabs < 0) {
            $tabs = 0;
        }

        $this->tabs = $tabs;
        return $this;
    }

    /**
     * Get tab count
     */
    public function getTabs(): int
    {
        return $this->tabs;
    }

    /**
     * Set backspaces
     *
     * @return $this
     */
    public function setBackspaces(int $spaces): Style
    {
        if ($spaces < 0) {
            $spaces = 0;
        }

        $this->backspaces = $spaces;
        return $this;
    }


    /**
     * Appy to message
     */
    public function apply(?string $message, Session $session): void
    {
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

    /**
     * Format message with style info
     */
    protected function format(?string $message): ?string
    {
        if ($message === null) {
            return null;
        }

        $setCodes = [];
        $unsetCodes = [];

        if ($this->foreground !== null) {
            switch ($this->foregroundBits) {
                case 4:
                    $setCodes[] = static::FG_COLORS[$this->foreground];
                    break;

                case 8:
                    $setCodes[] = static::FG_COLORS['_select'] . ';5;' . $this->foreground;
                    break;

                case 24:
                    $setCodes[] = static::FG_COLORS['_select'] . ';2;' . str_replace(',', ';', $this->foreground);
                    break;
            }

            $unsetCodes[] = static::FG_COLORS['reset'];
        }

        if ($this->background !== null) {
            switch ($this->backgroundBits) {
                case 4:
                    $setCodes[] = static::BG_COLORS[$this->background];
                    break;

                case 8:
                    $setCodes[] = static::BG_COLORS['_select'] . ';5;' . $this->background;
                    break;

                case 24:
                    $setCodes[] = static::BG_COLORS['_select'] . ';2;' . str_replace(',', ';', $this->background);
                    break;
            }

            $unsetCodes[] = static::BG_COLORS['reset'];
        }

        foreach ($this->options as $option) {
            $setCodes[] = static::OPTIONS[$option][0];
            $unsetCodes[] = static::OPTIONS[$option][1];
        }

        return sprintf("\e[%sm%s\e[%sm", implode(';', $setCodes), $message, implode(';', $unsetCodes));
    }
}
