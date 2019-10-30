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
    protected $message = 'Please enter your password';
    protected $repeatMessage = 'Please repeat your password';
    protected $session;
    protected $repeat = false;
    protected $required = true;

    /**
     * Init with message
     */
    public function __construct(Session $session, ?string $message=null, bool $repeat=false, bool $required=true)
    {
        $this->session = $session;
        $this->setMessage($message);
        $this->setRepeat($repeat);
        $this->setRequired($required);
    }

    /**
     * Set message body
     */
    public function setMessage(?string $message): Password
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
     */
    public function setRepeatMessage(?string $message): Password
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
     */
    public function setRepeat(bool $flag): Password
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
     * Set required
     */
    public function setRequired(bool $flag): Password
    {
        $this->required = $flag;
        return $this;
    }

    /**
     * Is required?
     */
    public function isRequired(): bool
    {
        return $this->required;
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
        $this->session->style('.cyan', $message.' ');
        $this->session->write('> ');

        if ($this->session->hasStty()) {
            $snapshot = $this->session->snapshotStty();
            $this->session->toggleInputEcho(false);
            $password = $this->session->readLine();
            $this->session->restoreStty($snapshot);

            if (strlen($password)) {
                $this->session->style('.brightRed', '••••••••');
            }

            $this->session->newLine();
        } else {
            $password = $this->session->readLine();
        }

        if (!strlen($password)) {
            $password = null;
        }

        return $password;
    }
}
