<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Widget;

use Closure;
use DecodeLabs\Coercion;
use DecodeLabs\Terminus\Session;
use DecodeLabs\Tightrope\RequiredSet;
use DecodeLabs\Tightrope\RequiredSetTrait;

class Question implements RequiredSet
{
    use RequiredSetTrait;

    public string $message;

    /**
     * @var array<string>
     */
    public array $options = [];

    public bool $showOptions = true;
    public bool $strict = false;
    public bool $confirm = false;

    public ?string $default = null {
        set(
            string|callable|null $default
        ) {
            if (is_callable($default)) {
                $default = Coercion::tryString($default());
            }

            $this->default = $default;
        }
    }

    public ?Closure $validator {
        set(
            callable|Closure|null $validator
        ) {
            if (is_callable($validator)) {
                $this->validator = Closure::fromCallable($validator);
            } else {
                $this->validator = null;
            }
        }
    }

    protected Session $session;

    /**
     * Init with message
     *
     * @param string|(callable():?string)|null $default
     * @param array<string> $options
     */
    public function __construct(
        Session $session,
        string $message,
        string|callable|null $default = null,
        array $options = [],
        ?callable $validator = null,
        bool $showOptions = true,
        bool $strict = false,
        bool $confirm = false
    ) {
        $this->session = $session;
        $this->message = $message;
        $this->default = $default;
        $this->options = $options;
        $this->validator = $validator;
        $this->required = true;
        $this->showOptions = $showOptions;
        $this->strict = $strict;
        $this->confirm = $confirm;
    }

    public function prompt(): ?string
    {
        while (true) {
            $this->renderQuestion();
            $answer = $this->session->readLine();

            if ($this->validate($answer)) {
                if ($this->confirm) {
                    $confirmation = $this->session->newConfirmation('Is this correct?', true);
                    $confirmation->input = $answer;

                    if (!$confirmation->prompt()) {
                        continue;
                    }
                }

                break;
            }
        }

        return $answer;
    }

    protected function renderQuestion(): void
    {
        $this->session->style('cyan', $this->message);

        if (
            !empty($this->options) &&
            $this->showOptions
        ) {
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

            if (
                !$defaultFound &&
                $this->default !== null
            ) {
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

    protected function validate(
        ?string &$answer
    ): bool {
        if($answer === null) {
            $answer = '';
        }

        if (
            !strlen($answer) &&
            $this->default !== null
        ) {
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
