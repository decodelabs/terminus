<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus\Widget;

use DecodeLabs\Terminus\Session;
use DecodeLabs\Glitch;

class Confirmation
{
    protected $message;
    protected $showOptions = true;
    protected $default;

    protected $session;

    /**
     * Init with message
     */
    public function __construct(Session $session, string $message, bool $default=null)
    {
        $this->session = $session;
        $this->message = $message;
        $this->setDefaultValue($default);
    }

    /**
     * Get body of the question
     */
    public function getMessage(): string
    {
        return $this->message;
    }


    /**
     * Should options be shown?
     */
    public function setShowOptions(bool $show): Confirmation
    {
        $this->showOptions = $show;
        return $this;
    }

    /**
     * Show options?
     */
    public function shouldShowOptions(): bool
    {
        return $this->showOptions;
    }


    /**
     * Set default value
     */
    public function setDefaultValue(?bool $default): Confirmation
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Get default value
     */
    public function getDefaultValue(): ?bool
    {
        return $this->default;
    }


    /**
     * Ask the question
     */
    public function prompt(): bool
    {
        $done = false;

        while (!$done) {
            $this->renderQuestion();

            if ($this->session->hasStty()) {
                $snapshot = $this->session->snapshotStty();
                $this->session->toggleInputBuffer(false);
                $this->session->toggleInputEcho(false);
                $answer = $this->session->read(1);
                $this->session->restoreStty($snapshot);

                if ($answer === "\n") {
                    if ($this->default !== null) {
                        $answer = $this->default ? 'y' : 'n';
                        $this->session->{'.yellow|dim'}($answer);
                    }
                } else {
                    $this->session->{'.brightYellow'}($answer);
                }
            } else {
                $answer = $this->session->readLine();
            }

            if ($this->validate($answer)) {
                $done = true;
            }
        }

        return $answer;
    }

    /**
     * Render question
     */
    protected function renderQuestion(): void
    {
        $this->session->style('cyan', $this->message.' ');

        if ($this->showOptions) {
            $this->session->style('white', '[');
            $this->session->style($this->default === true ? 'brightWhite|bold|underline' : 'white', 'y');
            $this->session->style('white', '/');
            $this->session->style($this->default === false ? 'brightWhite|bold|underline' : 'white', 'n');
            $this->session->style('white', ']');
        }

        $this->session->newLine();
        $this->session->write('> ');
    }

    /**
     * Check answer
     */
    protected function validate(&$answer): bool
    {
        if (!strlen($answer) && $this->default !== null) {
            $answer = $this->default;
        }

        if (!is_bool($answer)) {
            $answer = Session::stringToBoolean($answer);
        }

        if ($answer === null) {
            $this->session->style('.brightRed|bold', 'Sorry, try again..');
            return false;
        }

        return true;
    }
}
