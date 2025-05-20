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
use DecodeLabs\Terminus;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\Plugin;
use Stringable;

/**
 * @mixin Session
 */
class Context
{
    protected ?Session $session = null;


    public function isActiveSapi(): bool
    {
        return \PHP_SAPI === 'cli';
    }

    public function getAdapter(): Adapter
    {
        return $this->getSession()->adapter;
    }

    public function setSession(
        Session $session
    ): Context {
        $this->session = $session;
        return $this;
    }

    public function replaceSession(
        ?Broker $broker = null
    ): ?Session {
        $output = $this->session;
        $this->setSession($this->newSession($broker));
        return $output;
    }

    public function getSession(): Session
    {
        if ($this->session === null) {
            $this->session = $this->newSession();
        }

        return $this->session;
    }

    public function newSession(
        ?Broker $broker = null
    ): Session {
        if ($broker === null) {
            $broker = defined('STDOUT') ?
                Deliverance::newCliBroker() :
                Deliverance::newHttpBroker();
        }

        return new Session(
            $broker,
        );
    }

    public function canColor(): bool
    {
        return $this->getAdapter()->canColorShell();
    }


    /**
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
     * @param array<string, string> $context
     */
    public function info(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->info((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function notice(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->notice((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function comment(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->comment((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function success(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->success((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function operative(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->operative((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function deleteSuccess(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->deleteSuccess((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function warning(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->warning((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function critical(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->critical((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function alert(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->alert((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function emergency(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->emergency((string)$message, $context);
    }

    /**
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
     * @param array<string, string> $context
     */
    public function inlineDebug(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineDebug((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function inlineInfo(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineInfo((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function inlineNotice(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineNotice((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function inlineComment(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineComment((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function inlineSuccess(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineSuccess((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function inlineOperative(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineOperative((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function inlineDeleteSuccess(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineDeleteSuccess((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function inlineWarning(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineWarning((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function inlineError(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineError((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function inlineCritical(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineCritical((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function inlineAlert(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineAlert((string)$message, $context);
    }

    /**
     * @param array<string, string> $context
     */
    public function inlineEmergency(
        string|Stringable|int|float $message,
        array $context = []
    ): void {
        $this->getSession()->inlineEmergency((string)$message, $context);
    }

    /**
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


// Register Veneer
Veneer\Manager::getGlobalManager()->register(
    Context::class,
    Terminus::class
);
