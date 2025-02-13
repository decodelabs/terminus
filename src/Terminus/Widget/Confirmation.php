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
    protected string $message;
    protected bool $showOptions = true;
    protected ?bool $default = null;
    protected ?string $input = null;
    protected Session $session;

    /**
     * Init with message
     *
     * @param bool|(callable():?bool)|null $default
     */
    public function __construct(
        Session $session,
        string $message,
        bool|callable|null $default = null
    ) {
        $this->session = $session;
        $this->setMessage($message);
        $this->setDefaultValue($default);
    }

    /**
     * Set message body
     *
     * @return $this
     */
    public function setMessage(
        string $message
    ): static {
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
     * Set message input
     *
     * @return $this
     */
    public function setMessageInput(
        ?string $input
    ): static {
        $this->input = $input;
        return $this;
    }

    /**
     * Get message input
     */
    public function getMessageInput(): ?string
    {
        return $this->input;
    }


    /**
     * Should options be shown?
     *
     * @return $this
     */
    public function setShowOptions(
        bool $show
    ): static {
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
     *
     * @param bool|(callable():?bool)|null $default
     * @return $this
     */
    public function setDefaultValue(
        bool|callable|null $default
    ): static {
        if (is_callable($default)) {
            $default = Coercion::toBoolOrNull($default());
        }

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

                if (
                    $answer === "\n" &&
                    $this->default !== null
                ) {
                    $answer = $this->default ? 'y' : 'n';
                }

                $answer = rtrim((string)$answer, "\n");
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

        return $done;
    }

    /**
     * Render question
     */
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

    /**
     * Check answer
     */
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
            $answer = Session::stringToBoolean(Coercion::forceString($answer));
        }

        if ($answer === null) {
            $this->session->style('.brightRed|bold', 'Sorry, try again..');
            return false;
        }

        return true;
    }
}
