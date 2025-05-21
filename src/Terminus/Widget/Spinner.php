<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Widget;

use DecodeLabs\Exceptional;
use DecodeLabs\Terminus\Session;

class Spinner
{
    protected const Tick = 0.08;
    protected const Chars = ['-', '\\', '|', '/'];

    public ?string $style = null;

    protected ?float $lastTime = null;
    protected int $char = 0;
    protected Session $io;

    public function __construct(
        Session $io,
        ?string $style = null
    ) {
        $this->io = $io;
        $this->style = $style;
    }


    /**
     * @return $this
     */
    public function advance(): static
    {
        $time = microtime(true);

        if ($this->lastTime + self::Tick > $time) {
            return $this;
        }

        if ($this->io->isAnsi()) {
            if ($this->lastTime !== null) {
                $this->io->backspace();
            }

            $char = self::Chars[$this->char];
            $this->char++;

            if (!isset(self::Chars[$this->char])) {
                $this->char = 0;
            }

            $style = $this->style ?? 'yellow';
            $this->io->{$style}($char);
        } else {
            $this->io->write('.');
        }

        $this->lastTime = $time;

        return $this;
    }


    /**
     * @return $this
     */
    public function waitFor(
        float $seconds
    ): static {
        if ($seconds <= 0) {
            throw Exceptional::InvalidArgument(
                message: 'Wait time must be a positive value'
            );
        }

        $tick = 100000;
        $sleep = $seconds * 1000000;

        while ($sleep > 0) {
            $this->advance();
            usleep($tick);
            $sleep -= $tick;
        }

        return $this;
    }


    /**
     * @return $this
     */
    public function complete(
        ?string $message = null,
        ?string $style = null
    ): static {
        if ($this->io->isAnsi()) {
            if ($this->lastTime !== null) {
                $this->io->backspace();
            }

            if ($message === null) {
                $message = ' ';
            }
        } else {
            $this->io->write(' ');
        }

        if ($message !== null) {
            if ($style === null) {
                $style = 'success';
            }

            $this->io->{$style}($message);
        }

        return $this;
    }
}
