<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Widget;

use DecodeLabs\Terminus\Session;
use DecodeLabs\Tightrope\Manifest\Requirable;
use DecodeLabs\Tightrope\Manifest\RequirableTrait;

class Password implements Requirable
{
    use RequirableTrait;

    protected string $message = 'Please enter your password';
    protected string $repeatMessage = 'Please repeat your password';
    protected Session $session;
    protected bool $repeat = false;

    /**
     * Init with message
     */
    public function __construct(
        Session $session,
        ?string $message = null,
        bool $repeat = false,
        bool $required = true
    ) {
        $this->session = $session;
        $this->setMessage($message);
        $this->setRepeat($repeat);
        $this->setRequired($required);
    }

    /**
     * Set message body
     *
     * @return $this
     */
    public function setMessage(?string $message): static
    {
        if ($message === null) {
            $message = 'Please enter your password';
        }

        $this->message = $message;
        return $this;
    }

    /**
     * Get body of the question
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Set repeat message body
     *
     * @return $this
     */
    public function setRepeatMessage(?string $message): static
    {
        if ($message === null) {
            $message = 'Please repeat your password';
        }

        $this->repeatMessage = $message;
        return $this;
    }

    /**
     * Get repeat body of the question
     */
    public function getRepeatMessage(): string
    {
        return $this->repeatMessage;
    }

    /**
     * Set repeat
     *
     * @return $this
     */
    public function setRepeat(bool $flag): static
    {
        $this->repeat = $flag;
        return $this;
    }

    /**
     * Should repeat?
     */
    public function shouldRepeat(): bool
    {
        return $this->repeat;
    }


    /**
     * Ask the question
     */
    public function prompt(): ?string
    {
        while (true) {
            while (true) {
                $password = $this->renderQuestion($this->message);

                if ($password === null && $this->required) {
                    $this->session->error('Your password is required');
                    $this->session->newLine();
                    continue;
                }

                break;
            }

            if ($this->repeat && $password !== null) {
                while (true) {
                    $repeat = $this->renderQuestion($this->repeatMessage);

                    if ($repeat === null && $this->required) {
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

    /**
     * Render question
     */
    protected function renderQuestion(string $message): ?string
    {
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
