<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Widget;

use DecodeLabs\Terminus\Session;

class Question
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var array<string>
     */
    protected $options = [];

    /**
     * @var bool
     */
    protected $showOptions = true;

    /**
     * @var bool
     */
    protected $strict = false;

    /**
     * @var bool
     */
    protected $required = true;

    /**
     * @var bool
     */
    protected $confirm = false;

    /**
     * @var string|null
     */
    protected $default;

    /**
     * @var callable|null
     */
    protected $validator;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Init with message
     */
    public function __construct(Session $session, string $message, string $default = null, ?callable $validator = null)
    {
        $this->session = $session;
        $this->setMessage($message);
        $this->setDefaultValue($default);
        $this->setValidator($validator);
    }

    /**
     * Set message body
     *
     * @return $this
     */
    public function setMessage(string $message): Question
    {
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
     * Set available options
     *
     * @return $this
     */
    public function setOptions(string ...$options): Question
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get available options
     *
     * @return array<string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Should options be shown?
     *
     * @return $this
     */
    public function setShowOptions(bool $show): Question
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
     * Set strict
     *
     * @return $this
     */
    public function setStrict(bool $strict): Question
    {
        $this->strict = $strict;
        return $this;
    }

    /**
     * Is strict?
     */
    public function isStrict(): bool
    {
        return $this->strict;
    }

    /**
     * Set required
     *
     * @return $this
     */
    public function setRequired(bool $flag): Question
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
     * Set to confirm
     *
     * @return $this
     */
    public function setConfirm(bool $flag): Question
    {
        $this->confirm = $flag;
        return $this;
    }

    /**
     * Should confirm answer?
     */
    public function shouldConfirm(): bool
    {
        return $this->confirm;
    }


    /**
     * Set default value
     *
     * @return $this
     */
    public function setDefaultValue(?string $default): Question
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Get default value
     */
    public function getDefaultValue(): ?string
    {
        return $this->default;
    }

    /**
     * Set validator callback
     *
     * @return $this
     */
    public function setValidator(?callable $validator): Question
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * Get validator callback
     */
    public function getValidator(): ?callable
    {
        return $this->validator;
    }


    /**
     * Ask the question
     */
    public function prompt(): ?string
    {
        while (true) {
            $this->renderQuestion();
            $answer = $this->session->readLine();

            if ($this->validate($answer)) {
                if ($this->confirm) {
                    $confirmation = $this->session->newConfirmation('Is this correct?', true)
                        ->setMessageInput($answer);

                    if (!$confirmation->prompt()) {
                        continue;
                    }
                }

                break;
            }
        }

        return $answer;
    }

    /**
     * Render question
     */
    protected function renderQuestion(): void
    {
        $this->session->style('cyan', $this->message);

        if (!empty($this->options) && $this->showOptions) {
            $this->session->style('white', ' [');
            $fDefault = $this->strict ? $this->default : trim(strtolower((string)$this->default));
            $first = true;
            $defaultFound = false;

            foreach ($this->options as $option) {
                if (!$first) {
                    $this->session->style('white', '/');
                }

                $first = false;
                $fOption = $this->strict ? $option : trim(strtolower($option));
                $style = 'white';

                if ($fDefault === $fOption) {
                    $style .= '|bold|underline';
                    $defaultFound = true;
                }

                $this->session->style($style, $option);
            }

            if (!$defaultFound && $this->default !== null) {
                $this->session->style('white', ' : ');
                $this->session->style('brightWhite|bold|underline', $this->default);
            }

            $this->session->style('white', '] ');
        } elseif ($this->default !== null) {
            $this->session->style('white', ' [');
            $this->session->style('white|bold|underline', $this->default);
            $this->session->style('white', '] ');
        } else {
            if (preg_match('/[^a-zA-Z0-0-_ ]$/', $this->message)) {
                $this->session->write(' ');
            } else {
                $this->session->style('cyan', ': ');
            }
        }
    }

    /**
     * Check answer
     */
    protected function validate(string &$answer): bool
    {
        if (!strlen($answer) && $this->default !== null) {
            $answer = $this->default;
        }

        if (!strlen($answer)) {
            $answer = null;
        }

        if ($answer === null) {
            if ($this->required) {
                $this->session->style('.brightRed|bold', 'Sorry, try again..');
                return false;
            }
        } else {
            $testAnswer = $this->strict ? $answer : trim(strtolower($answer));

            if (!empty($this->options)) {
                $testOptions = [];

                foreach ($this->options as $option) {
                    $fOption = $this->strict ? $option : trim(strtolower($option));
                    $testOptions[$fOption] = $option;
                }

                if (!isset($testOptions[$testAnswer])) {
                    if ($answer !== $this->default) {
                        $this->session->style('.brightRed|bold', 'Sorry, try again..');
                        return false;
                    }
                } else {
                    $answer = $testOptions[$testAnswer];
                }
            }
        }

        if ($this->validator !== null && (false === ($this->validator)($answer, $this->session))) {
            return false;
        }

        return true;
    }
}
