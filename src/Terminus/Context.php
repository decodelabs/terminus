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
            $request = $this->createRequest();
            $name = $request->getScript();
            $name = pathinfo($name, \PATHINFO_FILENAME) ?? $name;

            $this->session = new Session(
                defined('STDOUT') ?
                    Atlas::newCliBroker() :
                    Atlas::newHttpBroker(),
                $request,
                $this->newCommandDefinition($name)
            );
        }

        return $this->session;
    }

    /**
     * Create request from environment
     */
    public function createRequest(
        array $server=null,
        array $argv=null
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
            $name = $this->getSession()->getRequest()->getScript();
            $name = pathinfo($name, \PATHINFO_FILENAME) ?? $name;
        }

        return new Definition($name);
    }
}
