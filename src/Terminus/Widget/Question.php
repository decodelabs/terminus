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

class Question
{
    public string $message;

    /**
     * @var array<string>
     */
    public array $options = [];

    public bool $showOptions = true;
    public bool $strict = false;
    public bool $confirm = false;
    public bool $required = true;

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

    protected Session $io;

    /**
     * Init with message
     *
     * @param string|(callable():?string)|null $default
     * @param array<string> $options
     */
    public function __construct(
        Session $io,
        string $message,
        string|callable|null $default = null,
        array $options = [],
        ?callable $validator = null,
        bool $showOptions = true,
        bool $strict = false,
        bool $confirm = false
    ) {
        $this->io = $io;
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
            $answer = $this->io->readLine();

            if ($this->validate($answer)) {
                if ($this->confirm) {
                    $confirmation = $this->io->newConfirmation('Is this correct?', true);
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
        $this->io->style('cyan', $this->message);

        if (
            !empty($this->options) &&
            $this->showOptions
        ) {
            $this->io->style('white', ' [');
            $fDefault = $this->strict ? $this->default : trim(strtolower((string)$this->default));
            $first = true;
            $defaultFound = false;

            foreach ($this->options as $option) {
                if (!$first) {
                    $this->io->style('white', '/');
                }

                $first = false;
                $fOption = $this->strict ? $option : trim(strtolower($option));
                $style = 'white';

                if ($fDefault === $fOption) {
                    $style .= '|bold|underline';
                    $defaultFound = true;
                }

                $this->io->style($style, $option);
            }

            if (
                !$defaultFound &&
                $this->default !== null
            ) {
                $this->io->style('white', ' : ');
                $this->io->style('brightWhite|bold|underline', $this->default);
            }

            $this->io->style('white', '] ');
        } elseif ($this->default !== null) {
            $this->io->style('white', ' [');
            $this->io->style('white|bold|underline', $this->default);
            $this->io->style('white', '] ');
        } else {
            if (preg_match('/[^a-zA-Z0-0-_ ]$/', $this->message)) {
                $this->io->write(' ');
            } else {
                $this->io->style('cyan', ': ');
            }
        }
    }

    protected function validate(
        ?string &$answer
    ): bool {
        if ($answer === null) {
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
                $this->io->style('.brightRed|bold', 'Sorry, try again..');
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
                        $this->io->style('.brightRed|bold', 'Sorry, try again..');
                        return false;
                    }
                } else {
                    $answer = $testOptions[$testAnswer];
                }
            }
        }

        if (
            $this->validator !== null &&
            (false === ($this->validator)($answer, $this->io))
        ) {
            return false;
        }

        return true;
    }
}
