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
    protected Session $io;

    /**
     * @param bool|(callable():?bool)|null $default
     */
    public function __construct(
        Session $io,
        string $message,
        bool|callable|null $default = null
    ) {
        $this->io = $io;
        $this->message = $message;
        $this->default = $default;
    }

    public function prompt(): bool
    {
        $done = $answer = false;

        while (!$done) {
            $this->renderQuestion();

            if ($this->io->hasStty()) {
                $snapshot = $this->io->snapshotStty();
                $this->io->toggleInputBuffer(false);
                $this->io->toggleInputEcho(false);
                $answer = $this->io->read(1);
                $this->io->restoreStty($snapshot);
                $answer = trim((string)$answer);

                if (
                    (
                        $answer === "\x03" ||
                        $answer === "\e"
                    ) &&
                    function_exists('posix_kill') &&
                    function_exists('pcntl_signal_dispatch')
                ) {
                    posix_kill(posix_getpid(), SIGINT);
                    pcntl_signal_dispatch();
                }

                if (
                    $answer === '' &&
                    $this->default !== null
                ) {
                    $answer = $this->default ? 'y' : 'n';
                }

                $bool = Session::stringToBoolean($answer);

                if ($bool === null) {
                    $this->io->{'.red'}($answer);
                } elseif ($bool === true) {
                    $this->io->{'.brightGreen'}($answer);
                } else {
                    $this->io->{'.brightYellow'}($answer);
                }
            } else {
                $answer = $this->io->readLine();
            }

            if ($this->validate($answer)) {
                $done = true;
            }
        }

        return (bool)$answer;
    }

    protected function renderQuestion(): void
    {
        $this->io->style('cyan', $this->message . ' ');

        if ($this->input !== null) {
            $this->io->style('brightYellow', $this->input . ' ');
        }

        if ($this->showOptions) {
            $this->io->style('white', '[');
            $this->io->style($this->default === true ? 'brightWhite|bold|underline' : 'white', 'y');
            $this->io->style('white', '/');
            $this->io->style($this->default === false ? 'brightWhite|bold|underline' : 'white', 'n');
            $this->io->style('white', '] ');
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
            $this->io->style('.brightRed|bold', 'Sorry, try again..');
            return false;
        }

        return true;
    }
}
