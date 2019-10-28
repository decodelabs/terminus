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
    protected $default;

    protected $session;

    /**
     * Init with message
     */
    public function __construct(Session $session, string $message, string $default=null)
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
     * Ask the question
     */
    public function prompt(): ?string
    {
        $done = false;

        while (!$done) {
            $this->renderQuestion();
            $answer = $this->session->readLine();

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

        if (!empty($this->options) && $this->showOptions) {
            $this->session->style('white', '[');
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

            $this->session->style('white', ']');
        }

        $this->session->newLine();
        $this->session->write('> ');
    }

    /**
     * Check answer
     */
    protected function validate(string &$answer): bool
    {
        if (empty($this->options)) {
            return true;
        }

        if (!strlen($answer) && $this->default !== null) {
            $answer = $this->default;
        }

        $testAnswer = $this->strict ? $answer : trim(strtolower($answer));
        $testOptions = [];

        foreach ($this->options as $option) {
            $fOption = $this->strict ? $option : trim(strtolower($option));
            $testOptions[$fOption] = $option;
        }

        if (!isset($testOptions[$answer])) {
            if ($answer === $this->default) {
                return true;
            } else {
                $this->session->style('.brightRed|bold', 'Sorry, try again..');
                return false;
            }
        } else {
            $answer = $testOptions[$answer];
        }

        return true;
    }
}
