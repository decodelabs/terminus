<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus;

use DecodeLabs\Veneer\FacadeTarget;
use DecodeLabs\Veneer\FacadeTargetTrait;
use DecodeLabs\Veneer\FacadePlugin;

use DecodeLabs\Terminus\Session;
use DecodeLabs\Terminus\Command\Request;
use DecodeLabs\Terminus\Command\Definition;

use DecodeLabs\Glitch;

class Context implements FacadeTarget
{
    use FacadeTargetTrait;

    const FACADE = 'Cli';

    protected $session;

    /**
     * Set active session
     */
    public function setSession(Session $session): Context
    {
        $ths->session = $session;
        return $this;
    }

    /**
     * Get active session, create default if needed
     */
    public function getSession(): Session
    {
        if (!$this->session) {
            $this->session = $this->newSession();
        }

        return $this->session;
    }

    /**
     * Create a new session from defaults
     */
    public function newSession(?Request $request=null, ?Broker $broker=null): Session
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
     */
    public function newRequest(
        array $argv=null,
        array $server=null
    ): Request {
        $server = $server ?? $_SERVER;
        $args = $argv ?? $_SERVER['argv'];
        $script = array_shift($args);

        return new Request($server, $args, $script);
    }

    /**
     * Create new command definition
     */
    public function newCommandDefinition(?string $name=null): Definition
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
     */
    public function __call(string $method, array $args)
    {
        $session = $this->getSession();
        return $session->{$method}(...$args);
    }
}
