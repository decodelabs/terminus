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

    protected ?string $style = null;
    protected Session $session;
    protected ?float $lastTime = null;
    protected int $char = 0;

    /**
     * Init with session and style
     */
    public function __construct(
        Session $session,
        ?string $style = null
    ) {
        $this->session = $session;
        $this->setStyle($style);
    }


    /**
     * Set style
     *
     * @return $this
     */
    public function setStyle(
        ?string $style
    ): static {
        $this->style = $style;
        return $this;
    }

    /**
     * Get style
     */
    public function getStyle(): ?string
    {
        return $this->style;
    }



    /**
     * Render
     *
     * @return $this
     */
    public function advance(): static
    {
        $time = microtime(true);

        if ($this->lastTime + self::Tick > $time) {
            return $this;
        }

        if ($this->session->isAnsi()) {
            if ($this->lastTime !== null) {
                $this->session->backspace();
            }

            $char = self::Chars[$this->char];
            $this->char++;

            if (!isset(self::Chars[$this->char])) {
                $this->char = 0;
            }

            $style = $this->style ?? 'yellow';
            $this->session->{$style}($char);
        } else {
            $this->session->write('.');
        }

        $this->lastTime = $time;

        return $this;
    }


    /**
     * Spin for a defined amount of time
     *
     * @return $this
     */
    public function waitFor(
        float $seconds
    ): static {
        if ($seconds <= 0) {
            throw Exceptional::InvalidArgument('Wait time must be a positive value');
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
     * Finalise
     *
     * @return $this
     */
    public function complete(
        ?string $message = null,
        ?string $style = null
    ): static {
        if ($this->session->isAnsi()) {
            if ($this->lastTime !== null) {
                $this->session->backspace();
            }

            if ($message === null) {
                $message = ' ';
            }
        } else {
            $this->session->write(' ');
        }

        if ($message !== null) {
            if ($style === null) {
                $style = 'success';
            }

            $this->session->{$style}($message);
        }

        return $this;
    }
}
