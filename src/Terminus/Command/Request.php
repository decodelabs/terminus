<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus\Command;

class Request
{
    protected $script;
    protected $args = [];
    protected $server = [];

    /**
     * Init
     */
    public function __construct(
        array $server=[],
        array $args=[],
        string $script=null
    ) {
        $this->server = $server;
        $this->args = $args;
        $this->script = $script;
    }


    /**
     * Alias withScript()
     */
    public function setScript(string $script): IRequest
    {
        return $this->withScript($script);
    }

    /**
     * Get launch script
     */
    public function getScript(): ?string
    {
        return $this->script;
    }

    /**
     * Get launch script path
     */
    public function getScriptPath(): ?string
    {
        if ($this->script === null) {
            return null;
        }

        if (false === strpos(str_replace('\\', '/', $this->script), '/')) {
            return realpath($this->script);
        }

        return $this->script;
    }

    /**
     * New instance with script set
     */
    public function withScript(string $script): IRequest
    {
        $output = clone $this;
        $output->script = $script;

        return $output;
    }


    /**
     * Alias withCommandParams()
     */
    public function setCommandParams(array $params): IRequest
    {
        return $this->withCommandParams($params);
    }

    /**
     * Get list of command args
     */
    public function getCommandParams(): array
    {
        return $this->args;
    }

    /**
     * Lookup single command arg
     */
    public function getCommandParam(string $key): ?string
    {
        if (!isset($this->args[$key])) {
            return null;
        }

        return (string)$this->args[$key];
    }

    /**
     * Is command arg set?
     */
    public function hasCommandParam(string $key): bool
    {
        return isset($this->args[$key]);
    }

    /**
     * New instance with params set
     */
    public function withCommandParams(array $params): IRequest
    {
        $output = clone $this;
        $output->args = $params;

        return $output;
    }


    /**
     * Get $_SERVER equiv
     */
    public function getServerParams(): array
    {
        return $this->server;
    }

    /**
     * Get single server param
     */
    public function getServerParam(string $key): ?string
    {
        if (!isset($this->server[$key])) {
            return null;
        }

        return (string)$this->server[$key];
    }

    /**
     * Is $key in $server?
     */
    public function hasServerParam(string $key): bool
    {
        return isset($this->server[$key]);
    }


    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return implode(' ', $this->args);
    }
}
