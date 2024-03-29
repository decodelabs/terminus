<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Command;

use DecodeLabs\Coercion;
use DecodeLabs\Exceptional;
use DecodeLabs\Terminus\Session;

class Argument
{
    protected string $name;
    protected string $description;
    protected ?string $shortcut = null;

    protected bool $named = false;
    protected bool $boolean = false;
    protected bool $optional = false;
    protected bool $list = false;

    protected ?string $defaultValue = null;
    protected ?string $pattern = null;


    /**
     * Init with name
     */
    public function __construct(
        string $name,
        string $description
    ) {
        if (substr($name, 0, 1) == '?') {
            $this->setOptional(true);
            $name = ltrim($name, '?');
        }

        if (substr($name, 0, 1) == '-') {
            $this->setBoolean(true);
            $name = ltrim($name, '-');
        }

        if (false !== strpos($name, '=')) {
            $parts = explode('=', $name);
            $name = (string)array_shift($parts);
            $this->setDefaultValue(array_shift($parts));
            $this->setBoolean(false);
        }

        if (substr($name, -1) == '*') {
            $this->setList(true);
            $name = substr($name, 0, -1);
        }

        if (false !== strpos($name, '|')) {
            $parts = explode('|', $name);
            $name = array_shift($parts);
            $this->setShortcut(array_shift($parts));
        }

        $this->name = $name;
        $this->setDescription($description);
    }

    /**
     * Get the name of the argument
     */
    public function getName(): string
    {
        return $this->name;
    }




    /**
     * Set description of argument
     *
     * @return $this
     */
    public function setDescription(
        string $description
    ): static {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description of argument
     */
    public function getDescription(): string
    {
        return $this->description;
    }


    /**
     * Set whether argument is named option
     *
     * @return $this
     */
    public function setNamed(
        bool $named
    ): static {
        $this->named = $named;
        return $this;
    }

    /**
     * Is this argument named?
     */
    public function isNamed(): bool
    {
        return $this->named;
    }



    /**
     * Set a single char shortcut
     *
     * @return $this
     */
    public function setShortcut(
        ?string $shortcut
    ): static {
        if ($shortcut !== null) {
            $shortcut = substr($shortcut, 0, 1);
        }

        $this->shortcut = $shortcut;
        return $this;
    }

    /**
     * Get the single char shortcut
     */
    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }



    /**
     * Is this argument a boolean value?
     *
     * @return $this
     */
    public function setBoolean(
        bool $boolean
    ): static {
        if ($boolean) {
            $this->defaultValue = null;
            $this->optional = true;
            $this->named = true;
            $this->pattern = null;
        }

        $this->boolean = $boolean;
        return $this;
    }

    /**
     * Is this a boolean option?
     */
    public function isBoolean(): bool
    {
        return $this->boolean;
    }


    /**
     * Set whether argument is optional
     *
     * @return $this
     */
    public function setOptional(
        bool $optional
    ): static {
        $this->optional = $optional;

        if (!$optional) {
            $this->defaultValue = null;
        }

        return $this;
    }

    /**
     * Is argument optional?
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }


    /**
     * Set this as a list argument
     *
     * @return $this
     */
    public function setList(
        bool $list
    ): static {
        if ($list) {
            $this->setBoolean(false);
        }

        $this->list = $list;
        return $this;
    }

    /**
     * Is this a list argument?
     */
    public function isList(): bool
    {
        return $this->list;
    }


    /**
     * Set a default value
     *
     * @return $this
     */
    public function setDefaultValue(
        ?string $value
    ): static {
        if (empty($value)) {
            $value = null;
        }

        $this->optional = true;
        $this->defaultValue = $value;
        return $this;
    }

    /**
     * Get default value
     */
    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }


    /**
     * Set a test reg pattern
     *
     * @return $this
     */
    public function setPattern(
        ?string $pattern
    ): static {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * Get the test reg pattern
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }


    /**
     * Check and normalize input value
     */
    public function validate(
        mixed $value
    ): bool|string|null {
        if ($this->boolean) {
            if (is_string($value)) {
                $value = Session::stringToBoolean($value);

                if ($value === null) {
                    throw Exceptional::UnexpectedValue(
                        'Invalid boolean value found for argument: ' . $this->name
                    );
                }
            }

            if ($value === true) {
                return true;
            } elseif (
                $value === false ||
                $value === null
            ) {
                return false;
            }

            return false;
        } else {
            if ($value === null) {
                if (!$this->optional) {
                    throw Exceptional::UnexpectedValue(
                        'No value found for argument: ' . $this->name
                    );
                } else {
                    return $this->defaultValue;
                }
            }

            $value = Coercion::toString($value);

            if (
                $this->pattern !== null &&
                !mb_ereg($this->pattern, $value)
            ) {
                throw Exceptional::UnexpectedValue(
                    'Value does not match pattern for argument: ' . $this->name
                );
            }

            return $value;
        }
    }
}
