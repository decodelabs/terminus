<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Widget;

use DecodeLabs\Terminus\Session;

class ProgressBar
{
    public const EMPTY = '░'; // @ignore-non-ascii
    public const FULL = '▓'; // @ignore-non-ascii

    protected float $min = 0;
    protected float $max = 100;

    protected bool $showPercent = true;
    protected bool $showCompleted = true;

    protected bool $started = false;
    protected int $written = 0;
    protected int $precision = 2;

    protected Session $session;

    /**
     * Init with session and style
     */
    public function __construct(
        Session $session,
        float $min = 0.0,
        float $max = 100.0,
        ?int $precision = null
    ) {
        $this->session = $session;
        $this->setRange($min, $max);

        if ($precision === null) {
            if ($max > 100 || $min < -100 || ($max == 0 && $min == 0)) {
                $precision = 0;
            } elseif ($max > 1 || $min < -1) {
                $precision = min(2, max(
                    strlen(substr((string)strrchr(rtrim(number_format($this->min, 14 - (int)log10($this->min)), '0'), "."), 1)),
                    strlen(substr((string)strrchr(rtrim(number_format($this->max, 14 - (int)log10($this->max)), '0'), "."), 1))
                ));
            } else {
                $precision = 2;
            }
        }

        $this->setPrecision($precision);

        if ($this->min < 0) {
            $this->showCompleted = false;
        }
    }



    /**
     * Set min
     *
     * @return $this
     */
    public function setMin(float $min): static
    {
        $this->min = $min;
        return $this;
    }

    /**
     * Get min
     */
    public function getMin(): float
    {
        return $this->min;
    }

    /**
     * Set max
     *
     * @return $this
     */
    public function setMax(float $max): static
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Get max
     */
    public function getMax(): float
    {
        return $this->max;
    }

    /**
     * Set range
     *
     * @return $this
     */
    public function setRange(
        float $min,
        float $max
    ): static {
        $this->setMin($min);
        $this->setMax($max);
        return $this;
    }

    /**
     * Get range
     *
     * @return array<float>
     */
    public function getRange(): array
    {
        return [$this->min, $this->max];
    }

    /**
     * Set precision
     *
     * @return $this
     */
    public function setPrecision(int $precision): static
    {
        $this->precision = $precision;
        return $this;
    }

    /**
     * Get precision
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }



    /**
     * Toggle showing percent value
     *
     * @return $this
     */
    public function setShowPercent(bool $flag): static
    {
        $this->showPercent = $flag;
        return $this;
    }

    /**
     * Show percent value?
     */
    public function shouldShowPercent(): bool
    {
        return $this->showPercent;
    }

    /**
     * Toggle showing complected value
     *
     * @return $this
     */
    public function setShowCompleted(bool $flag): static
    {
        $this->showCompleted = $flag;
        return $this;
    }

    /**
     * Show percent value?
     */
    public function shouldShowCompleted(): bool
    {
        return $this->showCompleted;
    }



    /**
     * Render
     *
     * @return $this
     */
    public function advance(float $value): static
    {
        $width = min($this->session->getWidth(), 82);

        if ($value < $this->min) {
            $value = $this->min;
        }

        if ($value > $this->max) {
            $value = $this->max;
        }

        $space = $width - 2;

        if ($this->session->isAnsi()) {
            if ($this->showCompleted) {
                $maxLength = max(
                    strlen((string)round($this->min, $this->precision)),
                    strlen((string)round($this->max, $this->precision))
                );

                $numSpace = ($maxLength * 2) + 4;
            } else {
                $numSpace = 0;
            }

            if ($this->showPercent) {
                $percentSpace = 5;
            } else {
                $percentSpace = 0;
            }

            $barSize = $space - ($numSpace + $percentSpace);
        } else {
            $barSize = $space;
        }

        if ($this->min < 0) {
            $xMin = 0 - $this->min;
            $xMax = $this->max - $this->min;
        } else {
            $xMin = $this->min;
            $xMax = $this->max;
        }

        if ($xMax - $xMin == 0) {
            $percent = 1;
        } else {
            $percent = ($value - $xMin) / ($xMax - $xMin);
        }

        $chars = ceil($percent * $barSize);

        if ($percent < 0.99) {
            $color = 'yellow|bold';
        } else {
            $color = 'green|bold';
        }


        if ($this->session->isAnsi()) {
            if ($this->started) {
                $this->session->setCursor(1);
            } else {
                $this->session->setCursor(1);
                $this->started = true;
            }

            if ($this->showCompleted) {
                $stringVal = (string)number_format($value, $this->precision);
                $maxVal = (string)round($this->max, $this->precision);
                $valLength = strlen($maxVal);

                if ($value < $this->max) {
                    $this->session->style('#ffa500', str_pad($stringVal, $valLength));
                } else {
                    $this->session->style('brightWhite', $stringVal);
                }

                $this->session->style('white|dim', ' / ');
                $this->session->style('brightWhite', $maxVal . ' ');
            }

            $this->session->style($color, str_repeat(self::FULL, (int)$chars));
            $this->session->style('dim', str_repeat(self::EMPTY, (int)($barSize - $chars)));

            if ($this->showPercent) {
                $this->session->style('white|bold', str_pad(ceil($percent * 100) . '%', 5, ' ', STR_PAD_LEFT));
            }
        } else {
            if (!$this->started) {
                $this->session->writeLine(str_repeat('_', $space));
                $this->started = true;
            }

            while ($chars > $this->written) {
                $this->session->write(self::FULL);
                $this->written++;
            }
        }

        return $this;
    }


    /**
     * Finalise
     *
     * @return $this
     */
    public function complete(): static
    {
        $this->advance($this->max);
        $this->session->newLine();

        return $this;
    }
}
