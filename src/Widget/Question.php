<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Widget;

use DecodeLabs\Coercion;
use DecodeLabs\Terminus\Session;
use DecodeLabs\Tightrope\Manifest\Requirable;
use DecodeLabs\Tightrope\Manifest\RequirableTrait;

class Question implements Requirable
{
    use RequirableTrait;

    protected string $message;

    /**
     * @var array<string>
     */
    protected array $options = [];

    protected bool $showOptions = true;
    protected bool $strict = false;
    protected bool $confirm = false;

    protected ?string $default = null;

    /**
     * @var callable|null
     */
    protected $validator;
    protected Session $session;

    /**
     * Init with message
     *
     * @param string|(callable():?string)|null $default
     */
    public function __construct(
        Session $session,
        string $message,
        string|callable|null $default = null,
        ?callable $validator = null
    ) {
        $this->session = $session;
        $this->setMessage($message);
        $this->setDefaultValue($default);
        $this->setValidator($validator);
        $this->setRequired(true);
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
     * Set available options
     *
     * @return $this
     */
    public function setOptions(
        string ...$options
    ): static {
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
     * Set strict
     *
     * @return $this
     */
    public function setStrict(
        bool $strict
    ): static {
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
     * Set to confirm
     *
     * @return $this
     */
    public function setConfirm(
        bool $flag
    ): static {
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
     * @param string|(callable():?string)|null $default
     * @return $this
     */
    public function setDefaultValue(
        string|callable|null $default
    ): static {
        if (is_callable($default)) {
            $default = Coercion::toStringOrNull($default());
        }

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
    public function setValidator(
        ?callable $validator
    ): static {
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
    protected function validate(
        string &$answer
    ): bool {
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
