<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus\Io;

use DecodeLabs\Terminus\Session;
use DecodeLabs\Glitch;

class Style
{
    const FG_COLORS = [
        'black' => 30,
        'red' => 31,
        'green' => 32,
        'yellow' => 33,
        'blue' => 34,
        'magenta' => 35,
        'cyan' => 36,
        'white' => 37,
        'select' => 38,
        'reset' => 39
    ];

    const BG_COLORS = [
        'black' => 40,
        'red' => 41,
        'green' => 42,
        'yellow' => 43,
        'blue' => 44,
        'magenta' => 45,
        'cyan' => 46,
        'white' => 47,
        'select' => 48,
        'reset' => 49
    ];

    const OPTIONS = [
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

    protected $foreground = 'reset';
    protected $foregroundSelect = null;
    protected $background = 'reset';
    protected $backgroundSelect = null;
    protected $options = [];
    protected $error = false;
    protected $linesBefore = 0;
    protected $linesAfter = 0;
    protected $tabs = 0;
    protected $backspaces = 0;

    /**
     * Parse modifier string
     */
    public static function parse(string $modifier): Style
    {
        $newLines = 1;
        $isError = false;

        if (!preg_match('/^([^a-zA-Z0-9]*)([a-z0-9\:\|]*)$/', $modifier, $matches)) {
            throw Glitch::EInvalidArgument('Invalid style modifier: '.$modifier);
        }

        $mods = $matches[1] ?? null;
        $style = $matches[2] ?? null;
        $parts = explode('|', $style);
        $fg = $bg = null;
        $options = [];

        foreach ($parts as $part) {
            $select = null;

            if (false !== (strpos($part, ':'))) {
                [$part, $select] = explode(':', $part, 2);
            }

            if (isset(self::FG_COLORS[$part])) {
                if (!$fg) {
                    $fg = $part.':'.$select;
                } elseif (!$bg) {
                    $bg = $part.':'.$select;
                }
            } elseif (isset(self::OPTIONS[$part])) {
                $options[] = $part;
            } elseif (!empty($part)) {
                throw Glitch::EInvalidArgument('Invalid style part: '.$part);
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
    public function __construct(?string $foreground, ?string $background=null, string ...$options)
    {
        $this->setForeground($foreground);
        $this->setBackground($background);
        $this->setOptions(...$options);
    }

    /**
     * Set foreground color
     */
    public function setForeground(?string $foreground): Style
    {
        $select = null;

        if ($foreground !== null) {
            if (false !== (strpos($foreground, ':'))) {
                [$foreground, $select] = explode(':', $foreground, 2);
            }

            if (!isset(self::FG_COLORS[$foreground])) {
                throw Glitch::EInvalidArgument('Invalid foreground color: '.$foreground);
            }

            if ($foreground !== 'select') {
                $select = null;
            } else {
                $select = (int)$select;
            }
        }

        $this->foreground = $foreground;
        $this->foregroundSelect = $select;
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
    public function getForegroundSelectIndex(): ?int
    {
        return $this->foregroundSelect;
    }

    /**
     * Set background color
     */
    public function setBackground(?string $background): Style
    {
        $select = null;

        if ($background !== null) {
            if (false !== (strpos($background, ':'))) {
                [$background, $select] = explode(':', $background, 2);
            }

            if (!isset(self::BG_COLORS[$background])) {
                throw Glitch::EInvalidArgument('Invalid background color: '.$background);
            }

            if ($background !== 'select') {
                $select = null;
            } else {
                $select = (int)$select;
            }
        }

        $this->background = $background;
        $this->backgroundSelect = $select;
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
    public function getbackgroundSelectIndex(): ?int
    {
        return $this->backgroundSelect;
    }

    /**
     * Set options
     */
    public function setOptions(string ...$options): Style
    {
        $this->options = [];

        foreach ($options as $option) {
            if (empty($option)) {
                continue;
            }

            if (!isset(self::OPTIONS[$option])) {
                throw Glitch::EInvalidArgument('Invalid option: '.$option);
            }

            $this->options[] = $option;
        }

        $this->options = array_unique($this->options);
        return $this;
    }

    /**
     * Get options
     */
    public function getOptions(): array
    {
        return $this->options;
    }


    /**
     * Set as error
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
            if ($this->foreground === 'select') {
                $setCodes[] = static::FG_COLORS[$this->foreground].';5;'.$this->foregroundSelect;
            } else {
                $setCodes[] = static::FG_COLORS[$this->foreground];
            }

            $unsetCodes[] = static::FG_COLORS['reset'];
        }

        if ($this->background !== null) {
            if ($this->background === 'select') {
                $setCodes[] = static::BG_COLORS[$this->background].';5;'.$this->backgroundSelect;
            } else {
                $setCodes[] = static::BG_COLORS[$this->background];
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
