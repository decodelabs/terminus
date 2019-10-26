<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus\Command;

use DecodeLabs\Terminus\Session;
use DecodeLabs\Terminus\Command\Argument;
use DecodeLabs\Terminus\Command\Request;
use DecodeLabs\Glitch;

class Definition
{
    protected $name;
    protected $help;

    protected $arguments = [];

    /**
     * Init with name
     */
    public function __construct(string $name)
    {
        $this->setName($name);
    }

    /**
     * Set task name
     */
    public function setName(string $name): Definition
    {
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
     */
    public function setHelp(?string $help): Definition
    {
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
    public function addArgument(string $name, string $description, callable $setup=null): Definition
    {
        if (isset($this->arguments[$name])) {
            throw Glitch::ELogic(
                'Named argument "'.$name.'" has already been defined'
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
     */
    public function setArgument(Argument $arg): Definition
    {
        $this->arguments[$arg->getName()] = $arg;
        return $this;
    }

    /**
     * Lookup a named argument
     */
    public function getArgument(string $name): ?Argument
    {
        return $this->arguments[$name] ?? null;
    }

    /**
     * Get list of arguments
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Remove an argument from the queue
     */
    public function removeArgument(string $name): Definition
    {
        unset($this->arguments[$name]);
        return $this;
    }

    /**
     * Remove all arguments from queue
     */
    public function clearArguments(): Definition
    {
        $this->arguments = [];
        return $this;
    }


    /**
     * Convert request params to list of args
     */
    public function apply(Request $request): array
    {
        // Sort arguments
        $args = $opts = $output = [];
        $lastIsList = $lastIsOptional = false;

        foreach ($this->arguments as $arg) {
            if ($arg->isNamed()) {
                $opts[$arg->getName()] = $arg;

                if (null !== ($shortcut = $arg->getShortcut())) {
                    $opts[$shortcut] = $arg;
                }
            } else {
                if ($lastIsList) {
                    throw Glitch::ELogic(
                        'List arguments must come last in the command definition'
                    );
                }

                $args[$arg->getName()] = $arg;

                if ($arg->isList()) {
                    $lastIsList = true;
                }

                if (!$arg->isOptional()) {
                    if ($lastIsOptional) {
                        throw Glitch::ELogic(
                            'Optional arguments cannot appear before required arguments'
                        );
                    }

                    $lastIsOptional = false;
                } else {
                    $lastIsOptional = true;
                }
            }
        }

        $params = $request->getCommandParams();

        while (!empty($params)) {
            $param = array_shift($params);

            if (substr($param, 0, 1) == '-') {
                $isShortcut = substr($param, 0, 2) !== '--';
                $parts = explode('=', ltrim($param, '-'));
                $name = array_shift($parts);

                if (!$arg = ($opts[$name] ?? null)) {
                    throw Glitch::EUnexpectedValue(
                        'Unexpected option: '.$name
                    );
                }

                if ($isShortcut) {
                    if ($arg->isBoolean()) {
                        $param = true;
                    } else {
                        $param = array_shift($params);
                    }
                } else {
                    $param = array_shift($parts);

                    if ($param === null) {
                        $param = true;
                    }
                }


                if (!$arg->isList()) {
                    unset($opts[$arg->getName()]);

                    if (null !== ($shortcut = $arg->getShortcut())) {
                        unset($opts[$shortcut]);
                    }
                }
            } else {
                if (!$arg = array_shift($args)) {
                    throw Glitch::EUnexpectedValue(
                        'Unexpected argument: '.$param
                    );
                }

                if ($arg->isList()) {
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

    private function validate(Argument $arg, $param, array &$output)
    {
        $name = $arg->getName();

        if ($arg->isList()) {
            if ($param === null) {
                if (!isset($output[$name])) {
                    if ($arg->isOptional()) {
                        if (null !== ($default = $arg->getDefaultValue())) {
                            $output[$name] = [$default];
                        } else {
                            $output[$name] = null;
                        }
                    } else {
                        throw Glitch::EUnexpectedValue(
                            'No list values defined for argument: '.$name
                        );
                    }
                }
            } else {
                $output[$name][] = $arg->validate($param);
            }
        } else {
            $output[$name] = $arg->validate($param);
        }
    }


    /**
     * Render help text
     */
    public function renderHelp(Session $session): void
    {
        $session->writeLine();
        $session->style('yellow|bold', $this->name);
        $session->write(' - ');
        $session->style('.bold', $this->help);

        $session->writeLine();

        foreach ($this->arguments as $arg) {
            if ($arg->isNamed()) {
                continue;
            }

            $this->renderArg($session, $arg);
        }

        foreach ($this->arguments as $arg) {
            if (!$arg->isNamed()) {
                continue;
            }

            $this->renderArg($session, $arg);
        }
    }

    private function renderArg(Session $session, Argument $arg)
    {
        if (!$arg->isNamed()) {
            $session->style('cyan|bold', $arg->getName());

            if ($default = $arg->getDefaultValue()) {
                $session->write(' [=');
                $session->style('green', $default);
                $session->write(']');
            }

            $session->newLine();
        } else {
            $name = '--'.$arg->getName();

            if (null !== ($shortcut = $arg->getShortcut())) {
                $name .= ' | -'.$shortcut;
            }

            $session->style('magenta|bold', $name);

            if (!$arg->isBoolean()) {
                if ($pattern = $arg->getPattern()) {
                    $session->write(' <');
                    $session->style('yellow', $pattern);
                    $session->write('>');
                } elseif ($default = $arg->getDefaultValue()) {
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

        $session->style('>.white|bold', $arg->getDescription());
        $session->newLine();
    }
}