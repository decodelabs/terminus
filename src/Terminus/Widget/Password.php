<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Widget;

use DecodeLabs\Terminus\Session;

class Password
{
    private const string DefaultMessage = 'Please enter your password';
    private const string DefaultRepeatMessage = 'Please repeat your password';

    public string $message = self::DefaultMessage {
        set(
            ?string $message
        ) {
            $this->message = $message ?? self::DefaultMessage;
        }
    }

    public string $repeatMessage = self::DefaultRepeatMessage {
        set(
            ?string $message
        ) {
            $this->repeatMessage = $message ?? self::DefaultRepeatMessage;
        }
    }

    public bool $repeat = false;
    public bool $required = true;
    protected Session $io;

    public function __construct(
        Session $io,
        ?string $message = null,
        bool $repeat = false,
        bool $required = true
    ) {
        $this->io = $io;
        $this->message = $message;
        $this->repeat = $repeat;
        $this->required = $required;
    }


    public function prompt(): ?string
    {
        while (true) {
            while (true) {
                $password = $this->renderQuestion($this->message);

                if (
                    $password === null &&
                    $this->required
                ) {
                    $this->io->error('Your password is required');
                    $this->io->newLine();
                    continue;
                }

                break;
            }

            if (
                $this->repeat &&
                $password !== null
            ) {
                while (true) {
                    $repeat = $this->renderQuestion($this->repeatMessage);

                    if (
                        $repeat === null &&
                        $this->required
                    ) {
                        $this->io->error('Your repeat password is required');
                        $this->io->newLine();
                        continue;
                    }

                    break;
                }
            } else {
                $repeat = $password;
            }

            if ($password !== $repeat) {
                $this->io->error('Your passwords do not match');
                $this->io->newLine();
                continue;
            }

            break;
        }

        return $password;
    }

    protected function renderQuestion(
        string $message
    ): ?string {
        $this->io->style('cyan', $message);

        if (preg_match('/[^a-zA-Z0-0-_ ]$/', $this->message)) {
            $this->io->write(' ');
        } else {
            $this->io->style('cyan', ': ');
        }

        if ($this->io->hasStty()) {
            $snapshot = $this->io->snapshotStty();
            $this->io->toggleInputEcho(false);
            $password = (string)$this->io->readLine();
            $this->io->restoreStty($snapshot);

            if (strlen($password)) {
                $this->io->style('.brightYellow', '••••••••'); // @ignore-non-ascii
            } else {
                $this->io->newLine();
            }
        } else {
            $password = (string)$this->io->readLine();
        }

        if (!strlen($password)) {
            $password = null;
        }

        return $password;
    }
}
