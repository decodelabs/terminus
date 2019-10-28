<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus\Widget;

use DecodeLabs\Terminus\Session;
use DecodeLabs\Glitch;

class Password
{
    protected $message;
    protected $session;

    /**
     * Init with message
     */
    public function __construct(Session $session, string $message)
    {
        $this->session = $session;
        $this->message = $message;
    }

    /**
     * Get body of the question
     */
    public function getMessage(): string
    {
        return $this->message;
    }


    /**
     * Ask the question
     */
    public function prompt(): ?string
    {
        $done = false;

        while (!$done) {
            $this->renderQuestion();

            $snapshot = $this->session->snapshotStty();
            $this->session->toggleInputEcho(false);
            $password = $this->session->readLine();
            $this->session->restoreStty($snapshot);

            if ($this->validate($password)) {
                $done = true;
                $this->session->style('.red', '••••••••');
            }
        }

        return $password;
    }

    /**
     * Render question
     */
    protected function renderQuestion(): void
    {
        $this->session->style('.cyan', $this->message.' ');
        $this->session->write('> ');
    }

    /**
     * Check answer
     */
    protected function validate(string &$answer): bool
    {
        return true;
    }
}
