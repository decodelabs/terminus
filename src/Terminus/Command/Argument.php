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
    public string $name {
        set {
            if(str_starts_with($value, '?')) {
                $this->optional = true;
                $value = ltrim($value, '?');
            }

            if(str_starts_with($value, '-')) {
                $this->boolean = true;
                $value = ltrim($value, '-');
            }

            if(str_contains($value, '=')) {
                $parts = explode('=', $value);
                $value = (string)array_shift($parts);
                $this->defaultValue = array_shift($parts);
                $this->boolean = false;
            }

            if (str_ends_with($value, '[]')) {
                $this->many = true;
                $value = substr($value, 0, -1);
            }

            if (str_contains($value, '|')) {
                $parts = explode('|', $value);
                $value = array_shift($parts);
                $this->shortcut = array_shift($parts);
            }

            $this->name = $value;
        }
    }

    public string $description;

    public ?string $shortcut = null {
        set {
            if ($value !== null) {
                $value = substr($value, 0, 1);
            }

            $this->shortcut = $value;
        }
    }

    public bool $named = false;

    public bool $boolean = false {
        set {
            if ($value) {
                $this->defaultValue = null;
                $this->optional = true;
                $this->named = true;
                $this->pattern = null;
            }

            $this->boolean = $value;
        }
    }

    public bool $optional = false {
        set {
            if(!$value) {
                $this->defaultValue = null;
            }

            $this->optional = $value;
        }
    }

    public bool $many = false {
        set {
            if ($value) {
                $this->boolean = false;
            }

            $this->many = $value;
        }
    }

    public ?string $defaultValue = null {
        set {
            if (empty($value)) {
                $value = null;
            }

            $this->optional = true;
            $this->defaultValue = $value;
        }
    }

    public ?string $pattern = null;


    /**
     * Init with name
     */
    public function __construct(
        string $name,
        string $description
    ) {
        $this->name = $name;
        $this->description = $description;
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
                        message: 'Invalid boolean value found for argument: ' . $this->name
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
                        message: 'No value found for argument: ' . $this->name
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
                    message: 'Value does not match pattern for argument: ' . $this->name
                );
            }

            return $value;
        }
    }
}
