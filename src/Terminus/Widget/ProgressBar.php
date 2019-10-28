<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus\Widget;

use DecodeLabs\Terminus\Session;
use DecodeLabs\Glitch;

class ProgressBar
{
    const EMPTY = '░';
    const FULL = '▓';

    protected $min = 0;
    protected $max = 100;

    protected $showPercent = true;
    protected $showCompleted = true;

    protected $started = false;
    protected $written = 0;
    protected $precision = 2;

    protected $session;

    /**
     * Init with session and style
     */
    public function __construct(Session $session, float $min=0.0, float $max=100.0, ?int $precision=null)
    {
        $this->session = $session;
        $this->setRange($min, $max);

        if ($precision === null) {
            if ($max > 100 || $min < -100) {
                $precision = 0;
            } elseif ($max > 10 || $min < -10) {
                $precision = 1;
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
     */
    public function setMin(float $min): ProgressBar
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
     */
    public function setMax(float $max): ProgressBar
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
     */
    public function setRange(float $min, float $max): ProgressBar
    {
        $this->setMin($min);
        $this->setMax($max);
        return $this;
    }

    /**
     * Get range
     */
    public function getRange(): array
    {
        return [$this->min, $this->max];
    }

    /**
     * Set precision
     */
    public function setPrecision(int $precision): ProgressBar
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
     */
    public function setShowPercent(bool $flag): ProgressBar
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
     */
    public function setShowCompleted(bool $flag): ProgressBar
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
     */
    public function advance(float $value): ProgressBar
    {
        $width = min($this->session->getWidth(), 82);

        if ($value < $this->min) {
            $value = $this->min;
        }

        if ($value > $this->max) {
            $value = $this->max;
        }

        $space = $width - 2;

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
        $percent = ($value - $this->min) / ($this->max - $this->min);
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
                $stringLength = strlen($stringVal);
                $valLength = strlen($maxVal);
                $numLength = $stringLength + 3 + $valLength;

                if ($value < $this->max) {
                    $this->session->style('#ffa500', str_pad($stringVal, $valLength));
                } else {
                    $this->session->style('brightWhite', $stringVal);
                }

                $this->session->style('white|dim', ' / ');
                $this->session->style('brightWhite', $maxVal.' ');
            }

            $this->session->style($color, str_repeat(self::FULL, (int)$chars));
            $this->session->style('dim', str_repeat(self::EMPTY, (int)($barSize - $chars)));

            if ($this->showPercent) {
                $this->session->style('white|bold', str_pad(ceil($percent * 100).'%', 5, ' ', STR_PAD_LEFT));
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
     */
    public function complete(): ProgressBar
    {
        $this->session->newLine();

        return $this;
    }
}
