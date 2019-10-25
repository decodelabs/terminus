<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus;

use DecodeLabs\Terminus\Command\Request;
use DecodeLabs\Terminus\Command\Definition;

use DecodeLabs\Atlas\Broker;
use ArrayAccess;

class Session implements ArrayAccess
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
}
