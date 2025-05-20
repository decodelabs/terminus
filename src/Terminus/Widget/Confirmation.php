<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Widget;

use DecodeLabs\Coercion;
use DecodeLabs\Terminus\Session;

class Confirmation
{
    public string $message;
    public bool $showOptions = true;

    public ?bool $default = null {
        /**
         * @param bool|(callable():?bool)|null $default
         */
        set(
            bool|callable|null $default
        ) {
            if (is_callable($default)) {
                $default = Coercion::tryBool($default());
            }

            $this->default = $default;
        }
    }

    public ?string $input = null;
    protected Session $session;

    /**
     * @param bool|(callable():?bool)|null $default
     */
    public function __construct(
        Session $session,
        string $message,
        bool|callable|null $default = null
    ) {
        $this->session = $session;
        $this->message = $message;
        $this->default = $default;
    }

    public function prompt(): bool
    {
        $done = $answer = false;

        while (!$done) {
            $this->renderQuestion();

            if ($this->session->hasStty()) {
                $snapshot = $this->session->snapshotStty();
                $this->session->toggleInputBuffer(false);
                $this->session->toggleInputEcho(false);
                $answer = $this->session->read(1);
                $this->session->restoreStty($snapshot);
                $answer = trim((string)$answer);

                if (
                    $answer === '' &&
                    $this->default !== null
                ) {
                    $answer = $this->default ? 'y' : 'n';
                }

                $bool = Session::stringToBoolean($answer);

                if ($bool === null) {
                    $this->session->{'.red'}($answer);
                } elseif ($bool === true) {
                    $this->session->{'.brightGreen'}($answer);
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

        return (bool)$answer;
    }

    protected function renderQuestion(): void
    {
        $this->session->style('cyan', $this->message . ' ');

        if ($this->input !== null) {
            $this->session->style('brightYellow', $this->input . ' ');
        }

        if ($this->showOptions) {
            $this->session->style('white', '[');
            $this->session->style($this->default === true ? 'brightWhite|bold|underline' : 'white', 'y');
            $this->session->style('white', '/');
            $this->session->style($this->default === false ? 'brightWhite|bold|underline' : 'white', 'n');
            $this->session->style('white', '] ');
        }
    }

    protected function validate(
        mixed &$answer
    ): bool {
        if (
            empty($answer) &&
            $answer !== '0' &&
            $this->default !== null
        ) {
            $answer = $this->default;
        }

        if (!is_bool($answer)) {
            $answer = Session::stringToBoolean(Coercion::toString($answer));
        }

        if ($answer === null) {
            $this->session->style('.brightRed|bold', 'Sorry, try again..');
            return false;
        }

        return true;
    }
}
