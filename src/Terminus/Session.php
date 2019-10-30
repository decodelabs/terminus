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

use DecodeLabs\Terminus\Widget\Question;
use DecodeLabs\Terminus\Widget\Password;
use DecodeLabs\Terminus\Widget\Confirmation;
use DecodeLabs\Terminus\Widget\Spinner;
use DecodeLabs\Terminus\Widget\ProgressBar;

use DecodeLabs\Atlas\Broker;
use DecodeLabs\Atlas\DataProvider;
use DecodeLabs\Atlas\DataReceiver;
use DecodeLabs\Atlas\ErrorDataReceiver;
use DecodeLabs\Atlas\Channel\Buffer;

use DecodeLabs\Systemic;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use ArrayAccess;

class Session implements ArrayAccess, Controller
{
    use LoggerTrait;

    protected $arguments = [];
    protected $request;
    protected $definition;
    protected $broker;
    protected $isAnsi = true;
    protected $hasStty = false;
    protected $sttyReset;

    /**
     * Init with IO broker and command info
     */
    public function __construct(Broker $broker, Request $request, Definition $definition)
    {
        $this->request = $request;
        $this->definition = $definition;
        $this->broker = $broker;
        $this->isAnsi = Systemic::$os->canColorShell();

        if ($this->isAnsi) {
            $this->hasStty = Systemic::$os->which('stty') !== 'stty';
            $this->sttyReset = $this->snapshotStty();
        }
    }

    /**
     * Ensure stty is reset at end of run
     */
    public function __destruct()
    {
        $this->resetStty();
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
     * Replace IO broker
     */
    public function setBroker(Broker $broker): Session
    {
        $this->broker = $broker;
        return $this;
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
     * Is stty available?
     */
    public function hasStty(): bool
    {
        return $this->hasStty;
    }

    /**
     * Get current snapshot of stty state
     */
    public function snapshotStty(): ?string
    {
        if (!$this->hasStty) {
            return null;
        }

        return trim(`stty -g`);
    }

    /**
     * Reset stty back to value at script start
     */
    public function restoreStty(?string $snapshot): bool
    {
        if (!$this->hasStty) {
            return false;
        } elseif ($snapshot === null) {
            return true;
        }

        system('stty \''.$snapshot.'\'');
        return true;
    }

    /**
     * Reset stty back to value at script start
     */
    public function resetStty(): bool
    {
        if (!$this->hasStty) {
            return false;
        }

        system('stty \''.$this->sttyReset.'\'');
        return true;
    }


    /**
     * Get TTY width
     */
    public function getWidth(): int
    {
        return Systemic::$os->getShellWidth();
    }

    /**
     * Get TTY height
     */
    public function getHeight(): int
    {
        return Systemic::$os->getShellHeight();
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
    public function newLine(int $times=1): bool
    {
        for ($i = 0; $i < $times; $i++) {
            $this->broker->writeLine('');
        }

        return true;
    }

    /**
     * New error line
     */
    public function newErrorLine(int $times=1): bool
    {
        for ($i = 0; $i < $times; $i++) {
            $this->broker->writeErrorLine('');
        }

        return true;
    }

    /**
     * Delete n previous lines
     */
    public function deleteLine(int $times=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->write(str_repeat("\e[1A\e[K", $times));
        return true;
    }

    /**
     * Delete n previous error lines
     */
    public function deleteErrorLine(int $times=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->writeError(str_repeat("\e[1A\e[K", $times));
        return true;
    }

    /**
     * Clear current line
     */
    public function clearLine(): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->write("\e[2K\e[0G");
        return true;
    }

    /**
     * Clear current error line
     */
    public function clearErrorLine(): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->writeError("\e[2K\e[0G");
        return true;
    }

    /**
     * Clear current line before cursor
     */
    public function clearLineBefore(): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->write("\e[1K\e[0G");
        return true;
    }

    /**
     * Clear current error line before cursor
     */
    public function clearErrorLineBefore(): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->writeError("\e[1K\e[0G");
        return true;
    }

    /**
     * Clear current line after cursor
     */
    public function clearLineAfter(): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->write("\e[0K");
        return true;
    }

    /**
     * Clear current error line after cursor
     */
    public function clearErrorLineAfter(): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->writeError("\e[0K");
        return true;
    }

    /**
     * Clear single char
     */
    public function backspace(int $times=1): bool
    {
        $this->broker->write(str_repeat(chr(8), $times));
        return true;
    }

    /**
     * Clear single error char
     */
    public function backspaceError(int $times=1): bool
    {
        $this->broker->writeError(str_repeat(chr(8), $times));
        return true;
    }

    /**
     * Write tabs to line
     */
    public function tab(int $times=1): bool
    {
        $this->broker->write(str_repeat("\t", $times));
        return true;
    }

    /**
     * Write tabs to error line
     */
    public function tabError(int $times=1): bool
    {
        $this->broker->writeError(str_repeat("\t", $times));
        return true;
    }



    /**
     * Move cursor up a line
     */
    public function cursorUp(int $times=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->write("\e[${times}A");
        return true;
    }

    /**
     * Move cursor up a line pos 0
     */
    public function cursorLineUp(int $times=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->write("\e[${times}F");
        return true;
    }

    /**
     * Move cursor down a line
     */
    public function cursorDown(int $times=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->write("\e[${times}B");
        return true;
    }

    /**
     * Move cursor down a line pos 0
     */
    public function cursorLineDown(int $times=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->write("\e[${times}E");
        return true;
    }

    /**
     * Move cursor left
     */
    public function cursorLeft(int $times=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->write("\e[${times}D");
        return true;
    }

    /**
     * Move cursor right
     */
    public function cursorRight(int $times=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->write("\e[${times}C");
        return true;
    }

    /**
     * Move error cursor up a line
     */
    public function errorCursorUp(int $times=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->writeError("\e[${times}A");
        return true;
    }

    /**
     * Move error cursor up a line pos 0
     */
    public function errorCursorLineUp(int $times=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->writeError("\e[${times}F");
        return true;
    }

    /**
     * Move error cursor down a line
     */
    public function errorCursorDown(int $times=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->writeError("\e[${times}B");
        return true;
    }

    /**
     * Move error cursor down a line
     */
    public function errorCursorLineDown(int $times=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->writeError("\e[${times}E");
        return true;
    }

    /**
     * Move error cursor left
     */
    public function errorCursorLeft(int $times=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->writeError("\e[${times}D");
        return true;
    }

    /**
     * Move error cursor right
     */
    public function errorCursorRight(int $times=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->writeError("\e[${times}C");
        return true;
    }


    /**
     * Set cursor line position
     */
    public function setCursor(int $pos): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->write("\e[${pos}G");
        return true;
    }

    /**
     * Set error cursor line position
     */
    public function setErrorCursor(int $pos): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->writeError("\e[${pos}G");
        return true;
    }

    /**
     * Set cursor absolute position
     */
    public function setCursorLine(int $line, int $pos=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->write("\e[${line};${pos}H");
        return true;
    }

    /**
     * Set cursor absolute position
     */
    public function setErrorCursorLine(int $line, int $pos=1): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->writeError("\e[${line};${pos}H");
        return true;
    }



    /**
     * Get cursor position
     */
    public function getCursor(): array
    {
        if (null === ($response = $this->captureAnsi("\e[6n"))) {
            throw Glitch::ERuntime('Unable to detect cursor position');
        }

        if (!preg_match('/^\e\[(\d+);(\d+)R$/', $response, $matches)) {
            throw Glitch::EInvalidArgument('Invalid cursor response from terminal: '.$response);
        }

        return [(int)$matches[1], (int)$matches[2]];
    }

    /**
     * Get error cursor position
     */
    public function getErrorCursor(): array
    {
        if (null === ($response = $this->captureAnsi("\e[6n", true))) {
            throw Glitch::ERuntime('Unable to detect cursor position');
        }

        if (!preg_match('/^\e\[(\d+);(\d+)R$/', $response, $matches)) {
            throw Glitch::EInvalidArgument('Invalid cursor response from terminal: '.$response);
        }

        return [(int)$matches[1], (int)$matches[2]];
    }

    /**
     * Get cursor horizontal position
     */
    public function getCursorH(): int
    {
        return $this->getCursor()[1];
    }

    /**
     * Get error cursor horizontal position
     */
    public function getErrorCursorH(): int
    {
        if (null === ($response = $this->captureAnsi("\e[6n", true))) {
            throw Glitch::ERuntime('Unable to detect cursor position');
        }

        return $this->getErrorCursor()[1];
    }

    /**
     * Get cursor vertical position
     */
    public function getCursorV(): int
    {
        return $this->getCursor()[0];
    }

    /**
     * Get error cursor vertical position
     */
    public function getErrorCursorV(): int
    {
        return $this->getErrorCursor()[0];
    }





    /**
     * Store cursor position
     */
    public function saveCursor(): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->write("\e[s");
        return true;
    }

    /**
     * Store error cursor position
     */
    public function saveErrorCursor(): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->writeError("\e[s");
        return true;
    }

    /**
     * Restore cursor position
     */
    public function restoreCursor(): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->write("\e[u");
        return true;
    }

    /**
     * Restore error cursor position
     */
    public function restoreErrorCursor(): bool
    {
        if (!$this->isAnsi) {
            return false;
        }

        $this->broker->writeError("\e[u");
        return true;
    }



    /**
     * Detect current background color
     */
    /*
    public function getDefaultBackgroundColor(): ?string
    {
       if (null === ($response = $this->captureAnsi("\e]11;?\a"))) {
           return null;
       }

       if (!preg_match('#^rgb\:([a-f0-9]{2,4})/([a-f0-9]{2,4})/([a-f0-9]{2,4})$#', $response, $matches)) {
           return null;
       }

       $r = dechex(255 * (hexdec($matches[1]) / 65535));
       $g = dechex(255 * (hexdec($matches[2]) / 65535));
       $b = dechex(255 * (hexdec($matches[3]) / 65535));

       return '#'.$r.$g.$b;
    }
    */


    /**
     * Capture ansi response call
     */
    protected function captureAnsi(string $command, bool $error=false): ?string
    {
        if (!$this->isAnsi || !$this->hasStty) {
            return null;
        }

        $ttyprops = $this->snapshotStty();
        $this->toggleInputEcho(false);
        $this->toggleInputBuffer(false);

        $error ?
            $this->broker->write($command) :
            $this->broker->writeError($command);

        $blocking = $this->broker->isReadBlocking();

        if ($blocking) {
            $this->broker->setReadBlocking(false);
        }

        $count = 0;

        do {
            usleep(3000);
            $data = $this->broker->read(16);
        } while ($data === null && ++$count < 5);


        if ($blocking) {
            $this->broker->setReadBlocking(true);
        }

        $this->toggleInputEcho(true);
        $this->toggleInputBuffer(true);

        return $data;
    }


    /**
     * Switch echo on and off via stty
     */
    public function toggleInputEcho(bool $flag): bool
    {
        if (!$this->hasStty) {
            return false;
        }

        system('stty '.($flag ? '' : '-').'echo');
        return true;
    }

    /**
     * Switch icanon on and off via stty
     */
    public function toggleInputBuffer(bool $flag): bool
    {
        if (!$this->hasStty) {
            return false;
        }

        system('stty '.($flag ? '' : '-').'icanon');
        return true;
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
        if ($message === null) {
            return $this;
        }

        $style = Style::parse($style);
        $style->apply($message, $this);
        return $this;
    }



    /**
     * Ask a question
     */
    public function ask(string $message, string $default=null): Question
    {
        return new Question($this, $message, $default);
    }

    /**
     * Ask for password
     */
    public function askPassword(string $message): Password
    {
        return new Password($this, $message);
    }

    /**
     * Ask for confirmation
     */
    public function confirm(string $message, bool $default=null): Confirmation
    {
        return new Confirmation($this, $message, $default);
    }



    /**
     * Show progress indicator
     */
    public function newSpinner(string $style=null): Spinner
    {
        return new Spinner($this, $style);
    }

    /**
     * Show progress bar
     */
    public function newProgressBar(float $min=0.0, float $max=100.0, ?int $precision=null): ProgressBar
    {
        return new ProgressBar($this, $min, $max);
    }


    /**
     * String to boolean
     */
    public static function stringToBoolean(string $string, bool $default=null): ?bool
    {
        switch ($string = strtolower(trim($string))) {
            case 'false':
            case '0':
            case 'no':
            case 'n':
            case 'off':
            case 'disabled':
                return false;

            case 'true':
            case '1':
            case 'yes':
            case 'y':
            case 'on':
            case 'enabled':
                return true;

            default:
                return $default;
        }
    }



    const LOG_STYLES = [
        'debug' => ['β ', '#996300'],
        'info' => ['ℹ ', 'cyan'],
        'notice' => ['☛ ', 'cyan|bold'],
        'comment' => ['# ', 'yellow|dim'],
        'success' => ['✓ ', 'green|bold'],
        'operative' => ['⚑ ', '#ffa500|bold'],
        'deleteSuccess' => ['⌦ ', 'brightRed'],
        'warning' => ['⚠ ', '#ffa500|bold'],
        'error' => ['✗ ', '!brightRed'],
        'critical' => ['⚠ ', '!white|red|bold'],
        'alert' => ['☎ ', '!brightRed|bold'],
        'emergency' => ['☎ ', '!white|red|bold|underline'],
    ];


    /**
     * Render comment line
     */
    public function comment($message, array $context=[])
    {
        $this->log('comment', $message, $context);
    }

    /**
     * Render success log
     */
    public function success($message, array $context=[])
    {
        $this->log('success', $message, $context);
    }

    /**
     * Render operative message line
     */
    public function operative($message, array $context=[])
    {
        $this->log('operative', $message, $context);
    }

    /**
     * Render delete success log
     */
    public function deleteSuccess($message, array $context=[])
    {
        $this->log('deleteSuccess', $message, $context);
    }


    /**
     * Render inline debug log
     */
    public function inlineDebug($message, array $context=[])
    {
        $this->inlineLog('debug', $message, $context);
    }

    /**
     * Render inline info log
     */
    public function inlineInfo($message, array $context=[])
    {
        $this->inlineLog('info', $message, $context);
    }

    /**
     * Render inline notice log
     */
    public function inlineNotice($message, array $context=[])
    {
        $this->inlineLog('notice', $message, $context);
    }

    /**
     * Render inline comment line
     */
    public function inlineComment($message, array $context=[])
    {
        $this->inlineLog('comment', $message, $context);
    }

    /**
     * Render inline success log
     */
    public function inlineSuccess($message, array $context=[])
    {
        $this->inlineLog('success', $message, $context);
    }

    /**
     * Render inline operative log
     */
    public function inlineOperative($message, array $context=[])
    {
        $this->inlineLog('operative', $message, $context);
    }

    /**
     * Render inline delete success log
     */
    public function inlineDeleteSuccess($message, array $context=[])
    {
        $this->inlineLog('deleteSuccess', $message, $context);
    }

    /**
     * Render inline warning log
     */
    public function inlineWarning($message, array $context=[])
    {
        $this->inlineLog('warning', $message, $context);
    }

    /**
     * Render inline error log
     */
    public function inlineError($message, array $context=[])
    {
        $this->inlineLog('error', $message, $context);
    }

    /**
     * Render inline critical log
     */
    public function inlineCritical($message, array $context=[])
    {
        $this->inlineLog('critical', $message, $context);
    }

    /**
     * Render inline alert log
     */
    public function inlineAlert($message, array $context=[])
    {
        $this->inlineLog('alert', $message, $context);
    }

    /**
     * Render inline emergency log
     */
    public function inlineEmergency($message, array $context=[])
    {
        $this->inlineLog('emergency', $message, $context);
    }


    /**
     * Render generic log message
     */
    public function log($level, $message, array $context=[])
    {
        $message = $this->interpolate((string)$message, $context);

        if (!isset(self::LOG_STYLES[$level])) {
            $this->writeLine($message);
            return;
        }

        [$prefix, $style] = self::LOG_STYLES[$level];

        $message = $prefix.$message;
        $this->style('.'.$style, $message);
    }

    /**
     * Render inline generic log message
     */
    public function inlineLog($level, $message, array $context=[])
    {
        $message = $this->interpolate((string)$message, $context);

        if (!isset(self::LOG_STYLES[$level])) {
            $this->writeLine($message);
            return;
        }

        [$prefix, $style] = self::LOG_STYLES[$level];

        $message = $prefix.$message;
        $this->style($style, $message);
    }

    /**
     * Interpolate log message with context
     */
    private function interpolate(string $message, array $context=[]): string
    {
        $replace = [];

        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{'.$key.'}'] = $val;
            }
        }

        return strtr($message, $replace);
    }
}
