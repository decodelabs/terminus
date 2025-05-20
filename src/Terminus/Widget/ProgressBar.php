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
    protected const Empty = '░'; // @ignore-non-ascii
    protected const Full = '▓'; // @ignore-non-ascii

    public float $min = 0;
    public float $max = 100;
    public int $precision = 2;

    public bool $showPercent = true;
    public bool $showCompleted = true;

    protected bool $started = false;
    protected int $written = 0;

    protected Session $session;

    /**
     * Init with session and style
     */
    public function __construct(
        Session $session,
        float $min = 0.0,
        float $max = 100.0,
        ?int $precision = null,
        bool $showPercent = true,
        bool $showCompleted = true
    ) {
        $this->session = $session;
        $this->showPercent = $showPercent;
        $this->showCompleted = $showCompleted;
        $this->setRange($min, $max);

        if ($precision === null) {
            if (
                $max > 100 ||
                $min < -100 ||
                (
                    $max == 0 &&
                    $min == 0
                )
            ) {
                $precision = 0;
            } elseif (
                $max > 1 ||
                $min < -1
            ) {
                $precision = min(2, max(
                    strlen(substr((string)strrchr(rtrim(number_format($this->min, 14 - (int)log10($this->min)), '0'), "."), 1)),
                    strlen(substr((string)strrchr(rtrim(number_format($this->max, 14 - (int)log10($this->max)), '0'), "."), 1))
                ));
            } else {
                $precision = 2;
            }
        }

        $this->precision = $precision;

        if ($this->min < 0) {
            $this->showCompleted = false;
        }
    }

    /**
     * @return $this
     */
    public function setRange(
        float $min,
        float $max
    ): static {
        $this->min = $min;
        $this->max = $max;
        return $this;
    }


    /**
     * @return $this
     */
    public function advance(
        float $value
    ): static {
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

            $this->session->style($color, str_repeat(self::Full, (int)$chars));
            $this->session->style('dim', str_repeat(self::Empty, (int)($barSize - $chars)));

            if ($this->showPercent) {
                $this->session->style('white|bold', str_pad(ceil($percent * 100) . '%', 5, ' ', STR_PAD_LEFT));
            }
        } else {
            if (!$this->started) {
                $this->session->writeLine(str_repeat('_', $space));
                $this->started = true;
            }

            while ($chars > $this->written) {
                $this->session->write(self::Full);
                $this->written++;
            }
        }

        return $this;
    }


    /**
     * @return $this
     */
    public function complete(): static
    {
        $this->advance($this->max);
        $this->session->newLine();

        return $this;
    }
}
