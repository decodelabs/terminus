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
use DecodeLabs\Terminus\Io\Controller;

use DecodeLabs\Atlas\Broker;
use DecodeLabs\Atlas\DataProvider;
use DecodeLabs\Atlas\DataReceiver;
use DecodeLabs\Atlas\ErrorDataReceiver;
use DecodeLabs\Atlas\Channel\Buffer;

use DecodeLabs\Systemic;
use ArrayAccess;

class Session implements ArrayAccess, Controller
{
    protected $arguments = [];
    protected $request;
    protected $definition;
    protected $broker;
    protected $isAnsi = true;

    /**
     * Init with IO broker and command info
     */
    public function __construct(Broker $broker, Request $request, Definition $definition)
    {
        $this->request = $request;
        $this->definition = $definition;
        $this->broker = $broker;
        $this->isAnsi = Systemic::$os->canColorShell();
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
     * Is this an ANSI supporting TTY?
     */
    public function isAnsi(): bool
    {
        return $this->isAnsi;
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
    public function newLine(int $times=1): Controller
    {
        for ($i = 0; $i < $times; $i++) {
            $this->broker->writeLine('');
        }

        return $this;
    }

    /**
     * New error line
     */
    public function newErrorLine(int $times=1): Controller
    {
        for ($i = 0; $i < $times; $i++) {
            $this->broker->writeErrorLine('');
        }

        return $this;
    }

    /**
     * Delete n previous lines
     */
    public function deleteLine(int $times=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->write(str_repeat("\e[1A\e[K", $times));
        return $this;
    }

    /**
     * Delete n previous error lines
     */
    public function deleteErrorLine(int $times=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->writeError(str_repeat("\e[1A\e[K", $times));
        return $this;
    }

    /**
     * Clear current line
     */
    public function clearLine(): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->write("\e[2K\e[0G");
        return $this;
    }

    /**
     * Clear current error line
     */
    public function clearErrorLine(): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->writeError("\e[2K\e[0G");
        return $this;
    }

    /**
     * Clear current line before cursor
     */
    public function clearLineBefore(): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->write("\e[1K\e[0G");
        return $this;
    }

    /**
     * Clear current error line before cursor
     */
    public function clearErrorLineBefore(): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->writeError("\e[1K\e[0G");
        return $this;
    }

    /**
     * Clear current line after cursor
     */
    public function clearLineAfter(): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->write("\e[0K");
        return $this;
    }

    /**
     * Clear current error line after cursor
     */
    public function clearErrorLineAfter(): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->writeError("\e[0K");
        return $this;
    }

    /**
     * Clear single char
     */
    public function backspace(int $times=1): Controller
    {
        $this->broker->write(str_repeat(chr(8), $times));
        return $this;
    }

    /**
     * Clear single error char
     */
    public function backspaceError(int $times=1): Controller
    {
        $this->broker->writeError(str_repeat(chr(8), $times));
        return $this;
    }

    /**
     * Write tabs to line
     */
    public function tab(int $times=1): Controller
    {
        $this->broker->write(str_repeat("\t", $times));
        return $this;
    }

    /**
     * Write tabs to error line
     */
    public function tabError(int $times=1): Controller
    {
        $this->broker->writeError(str_repeat("\t", $times));
        return $this;
    }



    /**
     * Move cursor up a line
     */
    public function cursorUp(int $times=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->write("\e[${times}A");
        return $this;
    }

    /**
     * Move cursor up a line pos 0
     */
    public function cursorLineUp(int $times=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->write("\e[${times}F");
        return $this;
    }

    /**
     * Move cursor down a line
     */
    public function cursorDown(int $times=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->write("\e[${times}B");
        return $this;
    }

    /**
     * Move cursor down a line pos 0
     */
    public function cursorLineDown(int $times=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->write("\e[${times}E");
        return $this;
    }

    /**
     * Move cursor left
     */
    public function cursorLeft(int $times=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->write("\e[${times}D");
        return $this;
    }

    /**
     * Move cursor right
     */
    public function cursorRight(int $times=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->write("\e[${times}C");
        return $this;
    }

    /**
     * Move error cursor up a line
     */
    public function errorCursorUp(int $times=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->writeError("\e[${times}A");
        return $this;
    }

    /**
     * Move error cursor up a line pos 0
     */
    public function errorCursorLineUp(int $times=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->writeError("\e[${times}F");
        return $this;
    }

    /**
     * Move error cursor down a line
     */
    public function errorCursorDown(int $times=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->writeError("\e[${times}B");
        return $this;
    }

    /**
     * Move error cursor down a line
     */
    public function errorCursorLineDown(int $times=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->writeError("\e[${times}E");
        return $this;
    }

    /**
     * Move error cursor left
     */
    public function errorCursorLeft(int $times=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->writeError("\e[${times}D");
        return $this;
    }

    /**
     * Move error cursor right
     */
    public function errorCursorRight(int $times=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->writeError("\e[${times}C");
        return $this;
    }


    /**
     * Set cursor line position
     */
    public function setCursor(int $pos): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->write("\e[${pos}G");
        return $this;
    }

    /**
     * Set error cursor line position
     */
    public function setErrorCursor(int $pos): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->writeError("\e[${pos}G");
        return $this;
    }

    /**
     * Set cursor absolute position
     */
    public function setCursorLine(int $line, int $pos=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->write("\e[${line};${pos}H");
        return $this;
    }

    /**
     * Set cursor absolute position
     */
    public function setErrorCursorLine(int $line, int $pos=1): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->writeError("\e[${line};${pos}H");
        return $this;
    }



    /**
     * Get cursor horizontal position
     */
    /*
    public function getCursor(): int
    {
       if (!$this->isAnsi) {
           throw Glitch::ERuntime('Unable to detect cursor position');
       }

       $this->broker->write("\e[6n");
       dd($this->broker->readLine());
    }
    */

    /**
     * Get error cursor horizontal position
     */
    /*
    public function getErrorCursor(): int
    {
       if (!$this->isAnsi) {
           throw Glitch::ERuntime('Unable to detect cursor position');
       }
    }
    */

    /**
     * Get cursor vertical position
     */
    /*
    public function getCursorLine(): int
    {
       if (!$this->isAnsi) {
           throw Glitch::ERuntime('Unable to detect cursor position');
       }
    }
    */

    /**
     * Get error cursor vertical position
     */
    /*
    public function getErrorCursorLine(): int
    {
       if (!$this->isAnsi) {
           throw Glitch::ERuntime('Unable to detect cursor position');
       }
    }
    */





    /**
     * Store cursor position
     */
    public function saveCursor(): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->write("\e[s");
        return $this;
    }

    /**
     * Store error cursor position
     */
    public function saveErrorCursor(): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->writeError("\e[s");
        return $this;
    }

    /**
     * Restore cursor position
     */
    public function restoreCursor(): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->write("\e[u");
        return $this;
    }

    /**
     * Restore error cursor position
     */
    public function restoreErrorCursor(): Controller
    {
        if (!$this->isAnsi) {
            return $this;
        }

        $this->broker->writeError("\e[u");
        return $this;
    }


    /**
     * Shortcut style generation
     */
    public function __call(string $method, array $args): Controller
    {
        if (preg_match('/^[a-z][a-zA-Z0-9]+$/', $method) && !Style::isKeyword($method)) {
            throw Glitch::EBadMethodCall('CLI method not found: '.$method);
        }

        return $this->style($method, ...$args);
    }

    /**
     * Style an output line
     */
    public function style(string $style, ?string $message=null): Controller
    {
        $style = Style::parse($style);
        $style->apply($message, $this);
        return $this;
    }
}
