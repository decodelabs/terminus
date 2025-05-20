<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Widget;

use DecodeLabs\Terminus\Session;
use DecodeLabs\Tightrope\RequiredSet;
use DecodeLabs\Tightrope\RequiredSetTrait;

class Password implements RequiredSet
{
    use RequiredSetTrait;

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
    protected Session $session;

    public function __construct(
        Session $session,
        ?string $message = null,
        bool $repeat = false,
        bool $required = true
    ) {
        $this->session = $session;
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
                    $this->session->error('Your password is required');
                    $this->session->newLine();
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
                        $this->session->error('Your repeat password is required');
                        $this->session->newLine();
                        continue;
                    }

                    break;
                }
            } else {
                $repeat = $password;
            }

            if ($password !== $repeat) {
                $this->session->error('Your passwords do not match');
                $this->session->newLine();
                continue;
            }

            break;
        }

        return $password;
    }

    protected function renderQuestion(
        string $message
    ): ?string {
        $this->session->style('cyan', $message);

        if (preg_match('/[^a-zA-Z0-0-_ ]$/', $this->message)) {
            $this->session->write(' ');
        } else {
            $this->session->style('cyan', ': ');
        }

        if ($this->session->hasStty()) {
            $snapshot = $this->session->snapshotStty();
            $this->session->toggleInputEcho(false);
            $password = (string)$this->session->readLine();
            $this->session->restoreStty($snapshot);

            if (strlen($password)) {
                $this->session->style('.brightYellow', '••••••••'); // @ignore-non-ascii
            } else {
                $this->session->newLine();
            }
        } else {
            $password = (string)$this->session->readLine();
        }

        if (!strlen($password)) {
            $password = null;
        }

        return $password;
    }
}
