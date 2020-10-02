<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus\Widget;

use DecodeLabs\Terminus\Session;

class Spinner
{
    const TICK = 0.08;
    const CHARS = ['-', '\\', '|', '/'];

    protected $style;
    protected $session;

    protected $lastTime;
    protected $char = 0;

    /**
     * Init with session and style
     */
    public function __construct(Session $session, string $style=null)
    {
        $this->session = $session;
        $this->setStyle($style);
    }


    /**
     * Set style
     */
    public function setStyle(?string $style): Spinner
    {
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
     */
    public function advance(): Spinner
    {
        $time = microtime(true);

        if ($this->lastTime + self::TICK > $time) {
            return $this;
        }

        if ($this->session->isAnsi()) {
            if ($this->lastTime !== null) {
                $this->session->backspace();
            }

            $char = self::CHARS[$this->char];
            $this->char++;

            if (!isset(self::CHARS[$this->char])) {
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
     * Finalise
     */
    public function complete(?string $message=null, ?string $style=null): Spinner
    {
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
