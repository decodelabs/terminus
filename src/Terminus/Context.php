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
    /**
     * @var Session|null
     */
    protected $session;

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
    public function replaceSession(?Request $request = null, ?Broker $broker = null): ?Session
    {
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
    public function newSession(?Request $request = null, ?Broker $broker = null): Session
    {
        if ($request === null) {
            $request = $this->newRequest();
        }

        if (null === ($name = $request->getScript())) {
            $name = $_SERVER['PHP_SELF'];
        }

        $name = pathinfo($name, \PATHINFO_FILENAME) ?? $name;

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
     * @param array<string, string>|null $argv
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

            $name = pathinfo($name, \PATHINFO_FILENAME) ?? $name;
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
    public function __call(string $method, array $args)
    {
        return $this->getSession()->{$method}(...$args);
    }

    /**
     * Render info line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function info($message, array $context = []): void
    {
        $this->getSession()->info((string)$message, $context);
    }

    /**
     * Render notice line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function notice($message, array $context = []): void
    {
        $this->getSession()->notice((string)$message, $context);
    }

    /**
     * Render comment line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function comment($message, array $context = []): void
    {
        $this->getSession()->comment((string)$message, $context);
    }

    /**
     * Render success line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function success($message, array $context = []): void
    {
        $this->getSession()->success((string)$message, $context);
    }

    /**
     * Render operative line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function operative($message, array $context = []): void
    {
        $this->getSession()->operative((string)$message, $context);
    }

    /**
     * Render deleteSuccess line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function deleteSuccess($message, array $context = []): void
    {
        $this->getSession()->deleteSuccess((string)$message, $context);
    }

    /**
     * Render warning line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function warning($message, array $context = []): void
    {
        $this->getSession()->warning((string)$message, $context);
    }

    /**
     * Render critical line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function critical($message, array $context = []): void
    {
        $this->getSession()->critical((string)$message, $context);
    }

    /**
     * Render alert line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function alert($message, array $context = []): void
    {
        $this->getSession()->alert((string)$message, $context);
    }

    /**
     * Render emergency line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function emergency($message, array $context = []): void
    {
        $this->getSession()->emergency((string)$message, $context);
    }

    /**
     * Render log line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function log(string $level, $message, array $context = []): void
    {
        $this->getSession()->log($level, (string)$message, $context);
    }

    /**
     * Render inlineDebug line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function inlineDebug($message, array $context = []): void
    {
        $this->getSession()->inlineDebug((string)$message, $context);
    }

    /**
     * Render inlineInfo line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function inlineInfo($message, array $context = []): void
    {
        $this->getSession()->inlineInfo((string)$message, $context);
    }

    /**
     * Render inlineNotice line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function inlineNotice($message, array $context = []): void
    {
        $this->getSession()->inlineNotice((string)$message, $context);
    }

    /**
     * Render inlineComment line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function inlineComment($message, array $context = []): void
    {
        $this->getSession()->inlineComment((string)$message, $context);
    }

    /**
     * Render inlineSuccess line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function inlineSuccess($message, array $context = []): void
    {
        $this->getSession()->inlineSuccess((string)$message, $context);
    }

    /**
     * Render inlineOperative line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function inlineOperative($message, array $context = []): void
    {
        $this->getSession()->inlineOperative((string)$message, $context);
    }

    /**
     * Render inlineDeleteSuccess line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function inlineDeleteSuccess($message, array $context = []): void
    {
        $this->getSession()->inlineDeleteSuccess((string)$message, $context);
    }

    /**
     * Render inlineWarning line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function inlineWarning($message, array $context = []): void
    {
        $this->getSession()->inlineWarning((string)$message, $context);
    }

    /**
     * Render inlineError line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function inlineError($message, array $context = []): void
    {
        $this->getSession()->inlineError((string)$message, $context);
    }

    /**
     * Render inlineCritical line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function inlineCritical($message, array $context = []): void
    {
        $this->getSession()->inlineCritical((string)$message, $context);
    }

    /**
     * Render inlineAlert line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function inlineAlert($message, array $context = []): void
    {
        $this->getSession()->inlineAlert((string)$message, $context);
    }

    /**
     * Render inlineEmergency line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function inlineEmergency($message, array $context = []): void
    {
        $this->getSession()->inlineEmergency((string)$message, $context);
    }

    /**
     * Render inlineLog line
     *
     * @param string|Stringable|int|float $message
     * @param array<string, string> $context
     */
    public function inlineLog(string $level, $message, array $context = []): void
    {
        $this->getSession()->inlineLog($level, (string)$message, $context);
    }
}
