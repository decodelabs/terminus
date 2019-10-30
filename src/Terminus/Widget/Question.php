<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus\Widget;

use DecodeLabs\Terminus\Session;
use DecodeLabs\Glitch;

class Question
{
    protected $message;
    protected $options = [];
    protected $showOptions = true;
    protected $strict = false;
    protected $required = true;
    protected $confirm = false;
    protected $default;
    protected $validator;
    protected $session;

    /**
     * Init with message
     */
    public function __construct(Session $session, string $message, string $default=null, ?callable $validator=null)
    {
        $this->session = $session;
        $this->setMessage($message);
        $this->setDefaultValue($default);
        $this->setValidator($validator);
    }

    /**
     * Set message body
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
     */
    public function setOptions(string ...$options): Question
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get available options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Should options be shown?
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
            $this->session->style('cyan', ': ');
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
                    $answer = $testOptions[$answer];
                }
            }
        }

        if ($this->validator !== null && (false === ($this->validator)($answer, $this->session))) {
            return false;
        }

        return true;
    }
}
