<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Command;

use DecodeLabs\Glitch\Dumpable;

class Request implements Dumpable
{
    /**
     * @var string|null
     */
    protected $script;

    /**
     * @var array<string, string>
     */
    protected $args = [];

    /**
     * @var array<string, string>
     */
    protected $server = [];

    /**
     * Init
     *
     * @param array<string, string> $server
     * @param array<string, string> $args
     * @param string|null $script
     */
    public function __construct(
        array $server = [],
        array $args = [],
        string $script = null
    ) {
        $this->server = $server;
        $this->args = $args;
        $this->script = $script;
    }


    /**
     * Alias withScript()
     */
    public function setScript(string $script): Request
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
            if (false !== ($output = realpath($this->script))) {
                return $output;
            }

            return null;
        }

        return $this->script;
    }

    /**
     * New instance with script set
     */
    public function withScript(string $script): Request
    {
        $output = clone $this;
        $output->script = $script;

        return $output;
    }


    /**
     * Alias withCommandParams()
     *
     * @param array<string, string> $params
     */
    public function setCommandParams(array $params): Request
    {
        return $this->withCommandParams($params);
    }

    /**
     * Get list of command args
     *
     * @return array<string, string>
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
     *
     * @param array<string, string> $params
     */
    public function withCommandParams(array $params): Request
    {
        $output = clone $this;
        $output->args = $params;

        return $output;
    }


    /**
     * Get $_SERVER equiv
     *
     * @return array<string, string>
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
        $output = $this->script;

        if (!empty($output) && !empty($this->args)) {
            $output .= ' ';
            $output .= implode(' ', $this->args);
        }

        return (string)$output;
    }

    /**
     * Export for dump inspection
     */
    public function glitchDump(): iterable
    {
        yield 'definition' => $this->__toString();
    }
}
