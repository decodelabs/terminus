<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus;

use DecodeLabs\Terminus\Command\Request;
use DecodeLabs\Terminus\Command\Definition;
use DecodeLabs\Terminus\Io\Style;

use DecodeLabs\Atlas\Broker;
use DecodeLabs\Atlas\DataProvider;
use DecodeLabs\Atlas\DataReceiver;
use DecodeLabs\Atlas\ErrorDataReceiver;
use DecodeLabs\Atlas\Channel\Buffer;
use ArrayAccess;

class Session implements ArrayAccess, DataReceiver, DataProvider, ErrorDataReceiver
{
    protected $arguments = [];
    protected $request;
    protected $definition;
    protected $broker;

    /**
     * Init with IO broker and command info
     */
    public function __construct(Broker $broker, Request $request, Definition $definition)
    {
        $this->request = $request;
        $this->definition = $definition;
        $this->broker = $broker;
    }

    /**
     * Get request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get command definition
     */
    public function getCommandDefinition(): Definition
    {
        return $this->definition;
    }

    /**
     * Get broker
     */
    public function getBroker(): Broker
    {
        return $this->broker;
    }

    /**
     * Prepare arguments from command definition
     */
    public function prepareArguments(): array
    {
        return $this->arguments = $this->definition->apply($this->request);
    }

    /**
     * Get argument
     */
    public function getArgument(string $name)
    {
        return $this->arguments[$name] ?? null;
    }

    /**
     * Has argument
     */
    public function hasArgument(string $name): bool
    {
        return array_key_exists($name, $this->arguments);
    }



    /**
     * Manually override argument
     */
    public function offsetSet($name, $value): void
    {
        $this->arguments[$name] = $value;
    }

    /**
     * Get argument shortcut
     */
    public function offsetGet($name)
    {
        return $this->arguments[$name] ?? null;
    }

    /**
     * Has argument
     */
    public function offsetExists($name): bool
    {
        return array_key_exists($name, $this->arguments);
    }

    /**
     * Remove argument
     */
    public function offsetUnset($name): void
    {
        unset($this->arguments[$name]);
    }



    /**
     * Request read blocking on broker
     */
    public function setReadBlocking(bool $flag): DataProvider
    {
        $this->broker->setReadBlocking($flag);
        return $this;
    }

    /**
     * Is broker blocking for reads?
     */
    public function isReadBlocking(): bool
    {
        return $this->broker->isReadBlocking();
    }

    /**
     * Can read from broker?
     */
    public function isReadable(): bool
    {
        return $this->broker->isReadable();
    }


    /**
     * Read chunk from broker
     */
    public function read(int $length): ?string
    {
        return $this->broker->read($length);
    }

    /**
     * Read all available data from broker
     */
    public function readAll(): ?string
    {
        return $this->broker->readAll();
    }

    /**
     * Read single ascii char from broker
     */
    public function readChar(): ?string
    {
        return $this->broker->readChar();
    }

    /**
     * Read line from broker
     */
    public function readLine(): ?string
    {
        return $this->broker->readLine();
    }

    /**
     * Read data from broker to receiver
     */
    public function readTo(DataReceiver $writer): DataProvider
    {
        $this->broker->readTo($writer);
        return $this;
    }

    /**
     * Is broker at end of input?
     */
    public function isAtEnd(): bool
    {
        return $this->broker->isAtEnd();
    }


    /**
     * Is broker writable?
     */
    public function isWritable(): bool
    {
        return $this->broker->isWritable();
    }

    /**
     * Write chunk to broker
     */
    public function write(?string $data, int $length=null): int
    {
        return $this->broker->write($data, $length);
    }

    /**
     * Write line to broker
     */
    public function writeLine(?string $data=''): int
    {
        return $this->broker->writeLine($data);
    }

    /**
     * Write buffer to broker
     */
    public function writeBuffer(Buffer $buffer, int $length): int
    {
        return $this->broker->writeBuffer($buffer, $length);
    }



    /**
     * Is broker error writable?
     */
    public function isErrorWritable(): bool
    {
        return $this->broker->isErrorWritable();
    }

    /**
     * Write error chunk to broker
     */
    public function writeError(?string $data, int $length=null): int
    {
        return $this->broker->writeError($data, $length);
    }

    /**
     * Write error line to broker
     */
    public function writeErrorLine(?string $data=''): int
    {
        return $this->broker->writeErrorLine($data);
    }

    /**
     * Write error buffer to broker
     */
    public function writeErrorBuffer(Buffer $buffer, int $length): int
    {
        return $this->broker->writeErrorBuffer($buffer, $length);
    }




    /**
     * New line
     */
    public function newLine(int $times=1): Session
    {
        for ($i = 0; $i < $times; $i++) {
            $this->broker->writeLine('');
        }

        return $this;
    }

    /**
     * New error line
     */
    public function newErrorLine(int $times=1): Session
    {
        for ($i = 0; $i < $times; $i++) {
            $this->broker->writeErrorLine('');
        }

        return $this;
    }

    /**
     * Delete n previous lines
     */
    public function deleteLine(int $times=1): Session
    {
        $this->broker->write(str_repeat("\e[1A\e[K", $times));
        return $this;
    }

    /**
     * Delete n previous error lines
     */
    public function deleteErrorLine(int $times=1): Session
    {
        $this->broker->writeError(str_repeat("\e[1A\e[K", $times));
        return $this;
    }

    /**
     * Clear current line
     */
    public function clearLine(): Session
    {
        $this->broker->write("\e[2K\e[0G");
        return $this;
    }

    /**
     * Clear current error line
     */
    public function clearErrorLine(): Session
    {
        $this->broker->writeError("\e[2K\e[0G");
        return $this;
    }

    /**
     * Clear single char
     */
    public function backspace(int $times=1): Session
    {
        $this->broker->write(str_repeat(chr(8), $times));
        return $this;
    }

    /**
     * Clear single error char
     */
    public function backspaceError(int $times=1): Session
    {
        $this->broker->writeError(str_repeat(chr(8), $times));
        return $this;
    }

    /**
     * Write tabs to line
     */
    public function tab(int $times=1): Session
    {
        $this->broker->write(str_repeat("\t", $times));
        return $this;
    }

    /**
     * Write tabs to error line
     */
    public function tabError(int $times=1): Session
    {
        $this->broker->writeError(str_repeat("\t", $times));
        return $this;
    }




    /**
     * Shortcut style generation
     */
    public function __call(string $method, array $args): Session
    {
        return $this->style($method, ...$args);
    }

    /**
     * Style an output line
     */
    public function style(string $style, ?string $message=null): Session
    {
        $style = Style::parse($style);
        $style->apply($message, $this);
        return $this;
    }
}
