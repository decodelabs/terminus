<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Command;

use DecodeLabs\Exceptional;
use DecodeLabs\Terminus\Session;

class Definition
{
    public string $name;
    public ?string $help = null;

    /**
     * @var array<string, Argument>
     */
    protected array $arguments = [];

    /**
     * Init with name
     */
    public function __construct(
        string $name
    ) {
        $this->setName($name);
    }

    /**
     * Set task name
     *
     * @return $this
     */
    public function setName(
        string $name
    ): static {
        $this->name = $name;
        return $this;
    }

    /**
     * Get task name
     */
    public function getPath(): string
    {
        return $this->name;
    }


    /**
     * Set help info
     *
     * @return $this
     */
    public function setHelp(
        ?string $help
    ): static {
        $this->help = $help;
        return $this;
    }

    /**
     * Get help info
     */
    public function getHelp(): ?string
    {
        return $this->help;
    }



    /**
     * Add a single argument to the queue
     */
    public function addArgument(
        string $name,
        string $description,
        ?callable $setup = null
    ): static {
        if (isset($this->arguments[$name])) {
            throw Exceptional::Logic(
                message: 'Named argument "' . $name . '" has already been defined'
            );
        }

        $argument = new Argument($name, $description);

        if ($setup) {
            $setup($argument, $this);
        }

        return $this->setArgument($argument);
    }

    /**
     * Push an argument to the queue
     *
     * @return $this
     */
    public function setArgument(
        Argument $arg
    ): static {
        $this->arguments[$arg->name] = $arg;
        return $this;
    }

    /**
     * Lookup a named argument
     */
    public function getArgument(
        string $name
    ): ?Argument {
        return $this->arguments[$name] ?? null;
    }

    /**
     * Get list of arguments
     *
     * @return array<string, Argument>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Remove an argument from the queue
     *
     * @return $this
     */
    public function removeArgument(
        string $name
    ): static {
        unset($this->arguments[$name]);
        return $this;
    }

    /**
     * Remove all arguments from queue
     *
     * @return $this
     */
    public function clearArguments(): static
    {
        $this->arguments = [];
        return $this;
    }


    /**
     * Convert request params to list of args
     *
     * @return array<string,bool|string|list<string>|null>
     */
    public function apply(
        Request $request
    ): array {
        // Sort arguments
        $args = $opts = $output = [];
        $lastIsList = $lastIsOptional = false;

        $unnamed = 0;

        foreach ($this->arguments as $arg) {
            if ($arg->named) {
                $opts[$arg->name] = $arg;

                if (null !== ($shortcut = $arg->shortcut)) {
                    $opts[$shortcut] = $arg;
                }
            } else {
                if ($lastIsList) {
                    throw Exceptional::Logic(
                        message: 'List arguments must come last in the command definition'
                    );
                }

                $args[$arg->name] = $arg;

                if ($arg->many) {
                    $lastIsList = true;
                }

                if (!$arg->optional) {
                    if ($lastIsOptional) {
                        throw Exceptional::Logic(
                            message: 'Optional arguments cannot appear before required arguments'
                        );
                    }

                    $lastIsOptional = false;
                } else {
                    $lastIsOptional = true;
                }
            }
        }

        $params = $request->getArguments();

        while (!empty($params)) {
            $param = array_shift($params);

            if (substr($param, 0, 1) == '-') {
                $isShortcut = substr($param, 0, 2) !== '--';
                $parts = explode('=', ltrim($param, '-'));
                $name = array_shift($parts);

                if (!$arg = ($opts[$name] ?? null)) {
                    $arg = new Argument($param, 'Auto-argument');
                }

                if ($isShortcut) {
                    if ($arg->boolean) {
                        $param = true;
                    } else {
                        $param = array_shift($params);
                    }
                } else {
                    $param = array_shift($parts);

                    if (
                        is_string($param) &&
                        preg_match('/^([\'"]).*\1$/', $param)
                    ) {
                        $param = substr($param, 1, -1);
                    }

                    if ($param === null) {
                        $param = true;
                    }
                }


                if (!$arg->many) {
                    unset($opts[$arg->name]);

                    if (null !== ($shortcut = $arg->shortcut)) {
                        unset($opts[$shortcut]);
                    }
                }
            } else {
                if (!$arg = array_shift($args)) {
                    $arg = new Argument(
                        'unnamed' . ++$unnamed,
                        'Unnamed argument ' . $unnamed
                    );
                }

                if ($arg->many) {
                    array_unshift($args, $arg);
                }
            }

            $this->validate($arg, $param, $output);
        }

        foreach ($args as $arg) {
            $this->validate($arg, null, $output);
        }

        foreach ($opts as $arg) {
            $this->validate($arg, null, $output);
        }

        return $output;
    }


    /**
     * @param array<string,bool|string|list<string>|null> $output
     */
    private function validate(
        Argument $arg,
        mixed $param,
        array &$output
    ): void {
        $name = $arg->name;

        if ($arg->many) {
            if ($param === null) {
                if (!isset($output[$name])) {
                    if ($arg->optional) {
                        if (null !== ($default = $arg->defaultValue)) {
                            $output[$name] = [$default];
                        } else {
                            $output[$name] = null;
                        }
                    } else {
                        throw Exceptional::UnexpectedValue(
                            message: 'No list values defined for argument: ' . $name
                        );
                    }
                }
            } else {
                if (!is_array($output[$name] ?? null)) {
                    $output[$name] = [];
                }

                $value = $arg->validate($param);

                if(is_string($value)) {
                    $output[$name][] = $value;
                }
            }
        } else {
            $output[$name] = $arg->validate($param);
        }
    }


    /**
     * Render help text
     */
    public function renderHelp(
        Session $session
    ): void {
        $session->writeLine();
        $session->style('yellow|bold', $this->name);
        $session->write(' - ');
        $session->style('.bold', $this->help);

        $session->writeLine();

        foreach ($this->arguments as $arg) {
            if ($arg->named) {
                continue;
            }

            $this->renderArg($session, $arg);
        }

        foreach ($this->arguments as $arg) {
            if (!$arg->named) {
                continue;
            }

            $this->renderArg($session, $arg);
        }
    }


    /**
     * Render argument to session
     */
    private function renderArg(
        Session $session,
        Argument $arg
    ): void {
        if (!$arg->named) {
            $session->style('cyan|bold', $arg->name);

            if ($default = $arg->defaultValue) {
                $session->write(' [=');
                $session->style('green', $default);
                $session->write(']');
            }

            $session->newLine();
        } else {
            $name = '--' . $arg->name;

            if (null !== ($shortcut = $arg->shortcut)) {
                $name .= ' | -' . $shortcut;
            }

            $session->style('magenta|bold', $name);

            if (!$arg->boolean) {
                if ($pattern = $arg->pattern) {
                    $session->write(' <');
                    $session->style('yellow', $pattern);
                    $session->write('>');
                } elseif ($default = $arg->defaultValue) {
                    $session->write(' [=');
                    $session->style('green', $default);
                    $session->write(']');
                } else {
                    $session->write(' <');
                    $session->style('cyan', 'value');
                    $session->write('>');
                }
            }

            $session->newLine();
        }

        $session->style('>.white|bold', $arg->description);
        $session->newLine();
    }
}
