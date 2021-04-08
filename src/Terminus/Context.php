<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus;

use DecodeLabs\Atlas;
use DecodeLabs\Atlas\Broker;

use DecodeLabs\Systemic;
use DecodeLabs\Terminus\Command\Definition;
use DecodeLabs\Terminus\Command\Request;

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
                Atlas::newCliBroker() :
                Atlas::newHttpBroker();
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
        $args = $this->prepareCallArgs($method, $args);
        return $this->getSession()->{$method}(...$args);
    }

    /**
     * Prepare mixin call args
     *
     * @param array<mixed> $args
     * @return array<mixed>
     */
    protected function prepareCallArgs(string $method, array $args): array
    {
        if (isset(self::CALL_ARGS[$method])) {
            foreach (self::CALL_ARGS[$method] as $i => $type) {
                if (!isset($args[$i])) {
                    switch ($type) {
                        case 'string':
                            $args[$i] = '';
                            break;

                        case 'array':
                            $args[$i] = [];
                            break;
                    }
                }

                switch ($type) {
                    case 'string':
                        $args[$i] = (string)$args[$i];
                        break;

                    case 'array':
                        $args[$i] = (array)$args[$i];
                        break;
                }
            }
        }

        return $args;
    }

    public const CALL_ARGS = [
        'info' => ['string', 'array'],
        'notice' => ['string', 'array'],
        'comment' => ['string', 'array'],
        'success' => ['string', 'array'],
        'operative' => ['string', 'array'],
        'deleteSuccess' => ['string', 'array'],
        'warning' => ['string', 'array'],
        'critical' => ['string', 'array'],
        'alert' => ['string', 'array'],
        'emergency' => ['string', 'array'],
        'log' => ['string', 'array'],
        'inlineDebug' => ['string', 'array'],
        'inlineInfo' => ['string', 'array'],
        'inlineNotice' => ['string', 'array'],
        'inlineComment' => ['string', 'array'],
        'inlineSuccess' => ['string', 'array'],
        'inlineOperative' => ['string', 'array'],
        'inlineDeleteSuccess' => ['string', 'array'],
        'inlineWarning' => ['string', 'array'],
        'inlineError' => ['string', 'array'],
        'inlineCritical' => ['string', 'array'],
        'inlineAlert' => ['string', 'array'],
        'inlineEmergency' => ['string', 'array'],
        'inlineLog' => ['string', 'array']
    ];
}
