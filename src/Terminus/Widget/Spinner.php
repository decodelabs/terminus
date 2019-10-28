<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus\Widget;

use DecodeLabs\Terminus\Session;
use DecodeLabs\Glitch;

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
        $this->lastTime = $time;

        return $this;
    }


    /**
     * Finalise
     */
    public function complete(?string $message=null): Spinner
    {
        if ($this->lastTime !== null) {
            $this->session->backspace();
        }

        if ($message !== null) {
            $this->session->style('brightGreen|bold', $message);
        }

        $this->session->newLine();
        return $this;
    }
}
