<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus;

use DecodeLabs\Deliverance\Channel\Buffer;
use Stringable;
use Throwable;

/**
 * @template T
 */
class Capture implements Stringable
{
    public protected(set) Buffer $buffer;

    /**
     * @var ?T
     */
    public mixed $result = null;
    public ?Throwable $error = null;

    public function __construct()
    {
        $this->buffer = new Buffer();
    }

    /**
     * @return T
     */
    public function resolve(): mixed
    {
        if ($this->error) {
            throw $this->error;
        }

        /** @var T */
        return $this->result;
    }

    public function __toString(): string
    {
        $output = (string)$this->buffer;

        if ($this->error) {
            $output .= $this->error->getMessage() . "\n";
            $output .= $this->error->getFile() . ":" . $this->error->getLine() . "\n";
        }

        return $output;
    }
}
