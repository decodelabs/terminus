<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus;

use DecodeLabs\Coercion;
use DecodeLabs\Deliverance;
use DecodeLabs\Deliverance\Broker;
use DecodeLabs\Deliverance\Channel\Buffer;
use DecodeLabs\Deliverance\DataReceiver;
use DecodeLabs\Exceptional;
use DecodeLabs\Kingdom\ContainerAdapter;
use DecodeLabs\Kingdom\Service;
use DecodeLabs\Terminus\Io\Controller;
use DecodeLabs\Terminus\Io\Style;
use DecodeLabs\Terminus\Widget\Confirmation;
use DecodeLabs\Terminus\Widget\Password;
use DecodeLabs\Terminus\Widget\ProgressBar;
use DecodeLabs\Terminus\Widget\Question;
use DecodeLabs\Terminus\Widget\Spinner;
use Stringable;

class Session implements Controller, Service
{
    public int $width {
        get => $this->adapter->getShellWidth();
    }

    public int $height {
        get => $this->adapter->getShellHeight();
    }

    public bool $readBlocking {
        get => $this->broker->readBlocking;
        set {
            $this->broker->readBlocking = $value;
        }
    }

    public Broker $broker;

    protected bool $ansi = true;
    protected bool $stty = false;
    protected ?string $sttyReset = null;

    public protected(set) Adapter $adapter;

    private static ?Session $default = null;

    public static function provideService(
        ContainerAdapter $container
    ): static {
        // @phpstan-ignore-next-line
        return static::getDefault();
    }


    public static function getDefault(): Session
    {
        if (self::$default === null) {
            self::$default = new self(
                broker: defined('STDOUT') ?
                    Deliverance::newCliBroker() :
                    Deliverance::newHttpBroker()
            );
        }

        return self::$default;
    }

    public function __construct(
        Broker $broker
    ) {
        $this->broker = $broker;
        $this->adapter = $this->loadAdapter();
        $this->ansi = $this->adapter->canColorShell();

        if ($this->ansi) {
            $this->stty = $this->adapter->hasStty();
            $this->sttyReset = $this->snapshotStty();
        }
    }

    protected function loadAdapter(): Adapter
    {
        $name = php_uname('s');

        if (substr(strtolower($name), 0, 3) == 'win') {
            $name = 'Windows';
        }

        if ($name === 'Windows') {
            throw Exceptional::ComponentUnavailable(
                message: 'Windows is not supported yet'
            );
        }

        $class = match ($name) {
            //'Windows' => Adapter\Windows::class,
            default => Adapter\Unix::class,
        };

        return new $class();
    }

    public function __destruct()
    {
        $this->resetStty();
    }


    /**
     * @return $this
     */
    public function setBroker(
        Broker $broker
    ): static {
        $this->broker = $broker;
        return $this;
    }

    public function getBroker(): Broker
    {
        return $this->broker;
    }

    public function disableAnsi(): void
    {
        $this->ansi = false;
        $this->stty = false;
    }

    public function enableAnsi(): void
    {
        $this->ansi = $this->adapter->canColorShell();

        if ($this->ansi) {
            $this->stty = $this->adapter->hasStty();
        }
    }


    public function isAnsi(): bool
    {
        return $this->ansi;
    }

    public function canColor(): bool
    {
        return $this->ansi;
    }

    public function hasStty(): bool
    {
        return $this->stty;
    }

    public function snapshotStty(): ?string
    {
        if (!$this->stty) {
            return null;
        }

        $output = Coercion::tryString(`stty -g`);

        if ($output !== null) {
            $output = trim($output);
        }

        return $output;
    }

    public function restoreStty(
        ?string $snapshot
    ): bool {
        if (!$this->stty) {
            return false;
        } elseif ($snapshot === null) {
            return true;
        }

        $this->adapter->setStty($snapshot);
        return true;
    }

    public function resetStty(): bool
    {
        if (!$this->stty) {
            return false;
        }

        $this->adapter->setStty((string)$this->sttyReset);
        return true;
    }



    public function isReadable(): bool
    {
        return $this->broker->isReadable();
    }


    public function read(
        int $length
    ): ?string {
        return $this->broker->read($length);
    }

    public function readAll(): ?string
    {
        return $this->broker->readAll();
    }

    public function readChar(): ?string
    {
        return $this->broker->readChar();
    }

    public function readLine(): ?string
    {
        return $this->broker->readLine();
    }

    /**
     * @return $this
     */
    public function readTo(
        DataReceiver $writer
    ): static {
        $this->broker->readTo($writer);
        return $this;
    }

    public function isAtEnd(): bool
    {
        return $this->broker->isAtEnd();
    }


    public function isWritable(): bool
    {
        return $this->broker->isWritable();
    }

    public function write(
        ?string $data,
        ?int $length = null
    ): int {
        return $this->broker->write($data, $length);
    }

    public function writeLine(
        ?string $data = ''
    ): int {
        return $this->broker->writeLine($data);
    }

    public function writeBuffer(
        Buffer $buffer,
        int $length
    ): int {
        return $this->broker->writeBuffer($buffer, $length);
    }



    public function isErrorWritable(): bool
    {
        return $this->broker->isErrorWritable();
    }

    public function writeError(
        ?string $data,
        ?int $length = null
    ): int {
        return $this->broker->writeError($data, $length);
    }

    public function writeErrorLine(
        ?string $data = ''
    ): int {
        return $this->broker->writeErrorLine($data);
    }

    public function writeErrorBuffer(
        Buffer $buffer,
        int $length
    ): int {
        return $this->broker->writeErrorBuffer($buffer, $length);
    }




    public function newLine(
        int $times = 1
    ): bool {
        for ($i = 0; $i < $times; $i++) {
            $this->broker->writeLine('');
        }

        return true;
    }

    public function newErrorLine(
        int $times = 1
    ): bool {
        for ($i = 0; $i < $times; $i++) {
            $this->broker->writeErrorLine('');
        }

        return true;
    }

    public function deleteLine(
        int $times = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->write(str_repeat("\e[1A\e[K", $times));
        return true;
    }

    public function deleteErrorLine(
        int $times = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->writeError(str_repeat("\e[1A\e[K", $times));
        return true;
    }

    public function clearLine(): bool
    {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->write("\e[2K\e[0G");
        return true;
    }

    public function clearErrorLine(): bool
    {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->writeError("\e[2K\e[0G");
        return true;
    }

    public function clearLineBefore(): bool
    {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->write("\e[1K\e[0G");
        return true;
    }

    public function clearErrorLineBefore(): bool
    {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->writeError("\e[1K\e[0G");
        return true;
    }

    public function clearLineAfter(): bool
    {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->write("\e[0K");
        return true;
    }

    public function clearErrorLineAfter(): bool
    {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->writeError("\e[0K");
        return true;
    }

    public function backspace(
        int $times = 1
    ): bool {
        $this->broker->write(str_repeat(chr(8), $times));
        return true;
    }

    public function backspaceError(
        int $times = 1
    ): bool {
        $this->broker->writeError(str_repeat(chr(8), $times));
        return true;
    }

    public function tab(
        int $times = 1
    ): bool {
        $this->broker->write(str_repeat("\t", $times));
        return true;
    }

    public function tabError(
        int $times = 1
    ): bool {
        $this->broker->writeError(str_repeat("\t", $times));
        return true;
    }



    public function cursorUp(
        int $times = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->write("\e[{$times}A");
        return true;
    }

    public function cursorLineUp(
        int $times = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->write("\e[{$times}F");
        return true;
    }

    public function cursorDown(
        int $times = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->write("\e[{$times}B");
        return true;
    }

    public function cursorLineDown(
        int $times = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->write("\e[{$times}E");
        return true;
    }

    public function cursorLeft(
        int $times = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->write("\e[{$times}D");
        return true;
    }

    public function cursorRight(
        int $times = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->write("\e[{$times}C");
        return true;
    }

    public function errorCursorUp(
        int $times = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->writeError("\e[{$times}A");
        return true;
    }

    public function errorCursorLineUp(
        int $times = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->writeError("\e[{$times}F");
        return true;
    }

    public function errorCursorDown(
        int $times = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->writeError("\e[{$times}B");
        return true;
    }

    public function errorCursorLineDown(
        int $times = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->writeError("\e[{$times}E");
        return true;
    }

    public function errorCursorLeft(
        int $times = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->writeError("\e[{$times}D");
        return true;
    }

    public function errorCursorRight(
        int $times = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->writeError("\e[{$times}C");
        return true;
    }


    public function setCursor(
        int $pos
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->write("\e[{$pos}G");
        return true;
    }

    public function setErrorCursor(
        int $pos
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->writeError("\e[{$pos}G");
        return true;
    }

    public function setCursorLine(
        int $line,
        int $pos = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->write("\e[{$line};{$pos}H");
        return true;
    }

    public function setErrorCursorLine(
        int $line,
        int $pos = 1
    ): bool {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->writeError("\e[{$line};{$pos}H");
        return true;
    }



    public function getCursor(): array
    {
        if (null === ($response = $this->captureAnsi("\e[6n"))) {
            throw Exceptional::Runtime(
                message: 'Unable to detect cursor position'
            );
        }

        if (!preg_match('/^\e\[(\d+);(\d+)R$/', $response, $matches)) {
            throw Exceptional::InvalidArgument(
                message: 'Invalid cursor response from terminal: ' . $response
            );
        }

        return [(int)$matches[1], (int)$matches[2]];
    }

    public function getErrorCursor(): array
    {
        if (null === ($response = $this->captureAnsi("\e[6n", true))) {
            throw Exceptional::Runtime(
                message: 'Unable to detect cursor position'
            );
        }

        if (!preg_match('/^\e\[(\d+);(\d+)R$/', $response, $matches)) {
            throw Exceptional::InvalidArgument(
                message: 'Invalid cursor response from terminal: ' . $response
            );
        }

        return [(int)$matches[1], (int)$matches[2]];
    }

    public function getCursorH(): int
    {
        return $this->getCursor()[1];
    }

    public function getErrorCursorH(): int
    {
        if ($this->captureAnsi("\e[6n", true) === null) {
            throw Exceptional::Runtime(
                message: 'Unable to detect cursor position'
            );
        }

        return $this->getErrorCursor()[1];
    }

    public function getCursorV(): int
    {
        return $this->getCursor()[0];
    }

    public function getErrorCursorV(): int
    {
        return $this->getErrorCursor()[0];
    }





    public function saveCursor(): bool
    {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->write("\e[s");
        return true;
    }

    public function saveErrorCursor(): bool
    {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->writeError("\e[s");
        return true;
    }

    public function restoreCursor(): bool
    {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->write("\e[u");
        return true;
    }

    public function restoreErrorCursor(): bool
    {
        if (!$this->ansi) {
            return false;
        }

        $this->broker->writeError("\e[u");
        return true;
    }



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


    protected function captureAnsi(
        string $command,
        bool $error = false
    ): ?string {
        if (
            !$this->ansi ||
            !$this->stty
        ) {
            return null;
        }

        $this->snapshotStty();
        $this->toggleInputEcho(false);
        $this->toggleInputBuffer(false);

        $error ?
            $this->broker->write($command) :
            $this->broker->writeError($command);

        $blocking = $this->broker->readBlocking;

        if ($blocking) {
            $this->broker->readBlocking = false;
        }

        $count = 0;

        do {
            usleep(3000);
            $data = $this->broker->read(16);
        } while ($data === null && ++$count < 5);


        if ($blocking) {
            $this->broker->readBlocking = true;
        }

        $this->toggleInputEcho(true);
        $this->toggleInputBuffer(true);

        return $data;
    }


    public function toggleInputEcho(
        bool $flag
    ): bool {
        if (!$this->stty) {
            return false;
        }

        $this->adapter->setStty(($flag ? '' : '-') . 'echo');
        return true;
    }

    public function toggleInputBuffer(
        bool $flag
    ): bool {
        if (!$this->stty) {
            return false;
        }

        $this->adapter->setStty(($flag ? '' : '-') . 'icanon');
        return true;
    }


    public function __call(
        string $method,
        array $args
    ): static {
        if (preg_match('/^[a-z][a-zA-Z0-9]+$/', $method) && !Style::isKeyword($method)) {
            throw Exceptional::BadMethodCall(
                message: 'CLI method not found: ' . $method
            );
        }

        return $this->style($method, ...$args);
    }

    /**
     * @return $this
     */
    public function style(
        string $style,
        ?string $message = null
    ): static {
        if ($message === null) {
            return $this;
        }

        $style = Style::parse($style);
        $style->apply($message, $this);
        return $this;
    }



    /**
     * @param string|(callable():?string)|null $default
     * @param array<string> $options
     */
    public function ask(
        string $message,
        string|callable|null $default = null,
        array $options = [],
        ?callable $validator = null,
        bool $showOptions = true,
        bool $strict = false,
        bool $confirm = false
    ): ?string {
        return $this->newQuestion(
            message: $message,
            default: $default,
            options: $options,
            validator: $validator,
            showOptions: $showOptions,
            strict: $strict,
            confirm: $confirm
        )->prompt();
    }

    /**
     * @param string|(callable():?string)|null $default
     * @param array<string> $options
     */
    public function newQuestion(
        string $message,
        string|callable|null $default = null,
        array $options = [],
        ?callable $validator = null,
        bool $showOptions = true,
        bool $strict = false,
        bool $confirm = false
    ): Question {
        return new Question(
            io: $this,
            message: $message,
            default: $default,
            options: $options,
            validator: $validator,
            showOptions: $showOptions,
            strict: $strict,
            confirm: $confirm
        );
    }

    public function askPassword(
        ?string $message = null,
        bool $repeat = false,
        bool $required = true
    ): ?string {
        return $this->newPasswordQuestion(
            message: $message,
            repeat: $repeat,
            required: $required
        )->prompt();
    }

    public function newPasswordQuestion(
        ?string $message = null,
        bool $repeat = false,
        bool $required = true
    ): Password {
        return new Password(
            io: $this,
            message: $message,
            repeat: $repeat,
            required: $required
        );
    }

    /**
     * @param bool|(callable():?bool)|null $default
     */
    public function confirm(
        string $message,
        bool|callable|null $default = null
    ): bool {
        return $this->newConfirmation(
            message: $message,
            default: $default
        )->prompt();
    }

    /**
     * @param bool|(callable():?bool)|null $default
     */
    public function newConfirmation(
        string $message,
        bool|callable|null $default = null
    ): Confirmation {
        return new Confirmation(
            io: $this,
            message: $message,
            default: $default
        );
    }



    public function newSpinner(
        ?string $style = null
    ): Spinner {
        return new Spinner(
            io: $this,
            style: $style
        );
    }

    public function newProgressBar(
        float $min = 0.0,
        float $max = 100.0,
        ?int $precision = null,
        bool $showPercent = true,
        bool $showCompleted = true
    ): ProgressBar {
        return new ProgressBar(
            io: $this,
            min: $min,
            max: $max,
            precision: $precision,
            showPercent: $showPercent,
            showCompleted: $showCompleted
        );
    }


    public static function stringToBoolean(
        string $string,
        ?bool $default = null
    ): ?bool {
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



    protected const LogStyles = [
        'debug' => ['β ', '#996300'], // @ignore-non-ascii
        'info' => ['ℹ ', 'cyan'], // @ignore-non-ascii
        'notice' => ['☛ ', 'cyan|bold'], // @ignore-non-ascii
        'comment' => ['# ', 'yellow|dim'], // @ignore-non-ascii
        'success' => ['✓ ', 'green|bold'], // @ignore-non-ascii
        'operative' => ['⚑ ', '#ffa500|bold'], // @ignore-non-ascii
        'deleteSuccess' => ['⌦ ', 'brightRed'], // @ignore-non-ascii
        'warning' => ['⚠ ', '#ffa500|bold'], // @ignore-non-ascii
        'error' => ['✗ ', '!brightRed'], // @ignore-non-ascii
        'critical' => ['⚠ ', '!white|red|bold'], // @ignore-non-ascii
        'alert' => ['☎ ', '!brightRed|bold'], // @ignore-non-ascii
        'emergency' => ['☎ ', '!white|red|bold|underline'], // @ignore-non-ascii
    ];



    /**
     * @param array<string,mixed> $context
     */
    public function debug(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->log('debug', (string)$message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public function info(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->log('info', (string)$message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public function notice(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->log('notice', (string)$message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public function comment(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->log('comment', (string)$message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public function success(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->log('success', (string)$message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public function operative(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->log('operative', (string)$message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public function deleteSuccess(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->log('deleteSuccess', (string)$message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public function warning(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->log('warning', (string)$message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public function error(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->log('error', (string)$message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public function critical(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->log('critical', (string)$message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public function alert(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->log('alert', (string)$message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public function emergency(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->log('emergency', (string)$message, $context);
    }


    public function inlineDebug(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->inlineLog('debug', (string)$message, $context);
    }

    public function inlineInfo(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->inlineLog('info', (string)$message, $context);
    }

    public function inlineNotice(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->inlineLog('notice', (string)$message, $context);
    }

    public function inlineComment(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->inlineLog('comment', (string)$message, $context);
    }

    public function inlineSuccess(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->inlineLog('success', (string)$message, $context);
    }

    public function inlineOperative(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->inlineLog('operative', (string)$message, $context);
    }

    public function inlineDeleteSuccess(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->inlineLog('deleteSuccess', (string)$message, $context);
    }

    public function inlineWarning(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->inlineLog('warning', (string)$message, $context);
    }

    public function inlineError(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->inlineLog('error', (string)$message, $context);
    }

    public function inlineCritical(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->inlineLog('critical', (string)$message, $context);
    }

    public function inlineAlert(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->inlineLog('alert', (string)$message, $context);
    }

    public function inlineEmergency(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->inlineLog('emergency', (string)$message, $context);
    }


    /**
     * @param string $level
     * @param array<string,mixed> $context
     */
    public function log(
        mixed $level,
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $message = $this->interpolate((string)$message, $context);

        if (!isset(self::LogStyles[$level])) {
            $this->writeLine($message);
            return;
        }

        [$prefix, $style] = self::LogStyles[$level];

        $message = $prefix . $message;
        $this->style('.' . $style, $message);
    }

    public function inlineLog(
        string $level,
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $message = $this->interpolate((string)$message, $context);

        if (!isset(self::LogStyles[$level])) {
            $this->writeLine($message);
            return;
        }

        [$prefix, $style] = self::LogStyles[$level];

        $message = $prefix . $message;
        $this->style($style, $message);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function interpolate(
        string $message,
        array $context = []
    ): string {
        $replace = [];

        foreach ($context as $key => $val) {
            if (
                !is_array($val) &&
                (
                    !is_object($val) ||
                    method_exists($val, '__toString')
                )
            ) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }
}
