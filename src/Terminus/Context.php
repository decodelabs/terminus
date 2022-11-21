<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus;

use DecodeLabs\Deliverance;
use DecodeLabs\Deliverance\Broker;

use DecodeLabs\Systemic;
use DecodeLabs\Terminus\Command\Definition;
use DecodeLabs\Terminus\Command\Request;

use Stringable;

/**
 * @mixin Session
 */
class Context
{
    protected ?Session $session = null;

    /**
     * Is CLI sapi?
     */
    public function isActiveSapi(): bool
    {
        return \PHP_SAPI === 'cli';
    }

    /**
     * Set active session
     */
    public function setSession(Session $session): Context
    {
        $this->session = $session;
        return $this;
    }

    /**
     * Replace active session with new session based on args
     */
    public function replaceSession(
        ?Request $request = null,
        ?Broker $broker = null
    ): ?Session {
        $output = $this->session;
        $this->setSession($this->newSession($request, $broker));
        return $output;
    }

    /**
     * Get active session, create default if needed
     */
    public function getSession(): Session
    {
        if ($this->session === null) {
            $this->session = $this->newSession();
        }

        return $this->session;
    }

    /**
     * Create a new session from defaults
     */
    public function newSession(
        ?Request $request = null,
        ?Broker $broker = null
    ): Session {
        if ($request === null) {
            $request = $this->newRequest();
        }

        if (null === ($name = $request->getScript())) {
            $name = $_SERVER['PHP_SELF'];
        }

        $name = pathinfo($name, \PATHINFO_FILENAME);

        if ($broker === null) {
            $broker = defined('STDOUT') ?
                Deliverance::newCliBroker() :
                Deliverance::newHttpBroker();
        }

        return new Session(
            $broker,
            $request,
            $this->newCommandDefinition($name)
        );
    }

    /**
     * Create request from environment
     *
     * @param array<string>|null $argv
     * @param array<string, string>|null $server
     */
    public function newRequest(
        array $argv = null,
        array $server = null
    ): Request {
        $server = $server ?? $_SERVER;
        $args = $argv ?? $_SERVER['argv'] ?? [];
        $script = array_shift($args);

        return new Request($server, $args, $script);
    }

    /**
     * Create new command definition
     */
    public function newCommandDefinition(?string $name = null): Definition
    {
        if ($name === null) {
            if (null === ($name = $this->getSession()->getRequest()->getScript())) {
                $name = $_SERVER['PHP_SELF'];
            }

            $name = pathinfo($name, \PATHINFO_FILENAME);
        }

        return new Definition($name);
    }



    /**
     * Prepare command in session and generate args
     */
    public function prepareCommand(callable $builder): Session
    {
        $session = $this->getSession();
        $builder($session->getCommandDefinition());
        $session->prepareArguments();
        return $session;
    }



    /**
     * Get TTY width
     */
    public function getShellWidth(): int
    {
        return Systemic::$os->getShellWidth();
    }

    /**
     * Get TTY height
     */
    public function getShellHeight(): int
    {
        return Systemic::$os->getShellHeight();
    }

    /**
     * Can color output?
     */
    public function canColor(): bool
    {
        return Systemic::$os->canColorShell();
    }


    /**
     * Pass method calls through to active session
     *
     * @param array<mixed> $args
     * @return mixed
     */
    public function __call(
        string $method,
        array $args
    ): mixed {
        return $this->getSession()->{$method}(...$args);
    }

    /**
     * Render info line
     *
     * @param array<string, string> $context
     */
    public function info(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->info((string)$message, $context);
    }

    /**
     * Render notice line
     *
     * @param array<string, string> $context
     */
    public function notice(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->notice((string)$message, $context);
    }

    /**
     * Render comment line
     *
     * @param array<string, string> $context
     */
    public function comment(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->comment((string)$message, $context);
    }

    /**
     * Render success line
     *
     * @param array<string, string> $context
     */
    public function success(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->success((string)$message, $context);
    }

    /**
     * Render operative line
     *
     * @param array<string, string> $context
     */
    public function operative(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->operative((string)$message, $context);
    }

    /**
     * Render deleteSuccess line
     *
     * @param array<string, string> $context
     */
    public function deleteSuccess(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->deleteSuccess((string)$message, $context);
    }

    /**
     * Render warning line
     *
     * @param array<string, string> $context
     */
    public function warning(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->warning((string)$message, $context);
    }

    /**
     * Render critical line
     *
     * @param array<string, string> $context
     */
    public function critical(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->critical((string)$message, $context);
    }

    /**
     * Render alert line
     *
     * @param array<string, string> $context
     */
    public function alert(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->alert((string)$message, $context);
    }

    /**
     * Render emergency line
     *
     * @param array<string, string> $context
     */
    public function emergency(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->emergency((string)$message, $context);
    }

    /**
     * Render log line
     *
     * @param array<string, string> $context
     */
    public function log(
        string $level,
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->log($level, (string)$message, $context);
    }

    /**
     * Render inlineDebug line
     *
     * @param array<string, string> $context
     */
    public function inlineDebug(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineDebug((string)$message, $context);
    }

    /**
     * Render inlineInfo line
     *
     * @param array<string, string> $context
     */
    public function inlineInfo(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineInfo((string)$message, $context);
    }

    /**
     * Render inlineNotice line
     *
     * @param array<string, string> $context
     */
    public function inlineNotice(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineNotice((string)$message, $context);
    }

    /**
     * Render inlineComment line
     *
     * @param array<string, string> $context
     */
    public function inlineComment(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineComment((string)$message, $context);
    }

    /**
     * Render inlineSuccess line
     *
     * @param array<string, string> $context
     */
    public function inlineSuccess(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineSuccess((string)$message, $context);
    }

    /**
     * Render inlineOperative line
     *
     * @param array<string, string> $context
     */
    public function inlineOperative(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineOperative((string)$message, $context);
    }

    /**
     * Render inlineDeleteSuccess line
     *
     * @param array<string, string> $context
     */
    public function inlineDeleteSuccess(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineDeleteSuccess((string)$message, $context);
    }

    /**
     * Render inlineWarning line
     *
     * @param array<string, string> $context
     */
    public function inlineWarning(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineWarning((string)$message, $context);
    }

    /**
     * Render inlineError line
     *
     * @param array<string, string> $context
     */
    public function inlineError(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineError((string)$message, $context);
    }

    /**
     * Render inlineCritical line
     *
     * @param array<string, string> $context
     */
    public function inlineCritical(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineCritical((string)$message, $context);
    }

    /**
     * Render inlineAlert line
     *
     * @param array<string, string> $context
     */
    public function inlineAlert(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineAlert((string)$message, $context);
    }

    /**
     * Render inlineEmergency line
     *
     * @param array<string, string> $context
     */
    public function inlineEmergency(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineEmergency((string)$message, $context);
    }

    /**
     * Render inlineLog line
     *
     * @param array<string, string> $context
     */
    public function inlineLog(
        string $level,
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineLog($level, (string)$message, $context);
    }
}
