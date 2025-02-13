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
    protected ?string $script = null;

    /**
     * @var array<int|string,string>
     */
    protected array $arguments = [];

    /**
     * @var array<string,string>
     */
    protected array $server = [];

    /**
     * Init
     *
     * @param array<string,string> $server
     * @param array<int|string,string> $arguments
     */
    public function __construct(
        array $server = [],
        array $arguments = [],
        ?string $script = null
    ) {
        $this->server = $server;
        $this->arguments = $arguments;
        $this->script = $script;
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
    public function withScript(
        string $script
    ): static {
        $output = clone $this;
        $output->script = $script;

        return $output;
    }


    /**
     * Get list of command arguments
     *
     * @return array<int|string,string>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Lookup single command arg
     */
    public function getArgument(
        int|string $key
    ): ?string {
        if (!isset($this->arguments[$key])) {
            return null;
        }

        return (string)$this->arguments[$key];
    }

    /**
     * Is command arg set?
     */
    public function hasArgument(
        int|string $key
    ): bool {
        return isset($this->arguments[$key]);
    }

    /**
     * New instance with params set
     *
     * @param array<int|string,string> $arguments
     */
    public function withArguments(
        array $arguments
    ): static {
        $output = clone $this;
        $output->arguments = $arguments;

        return $output;
    }


    /**
     * Get $_SERVER equiv
     *
     * @return array<string,string>
     */
    public function getServerParameters(): array
    {
        return $this->server;
    }

    /**
     * Get single server param
     */
    public function getServerParameter(
        string $key
    ): ?string {
        if (!isset($this->server[$key])) {
            return null;
        }

        return (string)$this->server[$key];
    }

    /**
     * Is $key in $server?
     */
    public function hasServerParameter(
        string $key
    ): bool {
        return isset($this->server[$key]);
    }


    /**
     * Convert to string
     */
    public function __toString(): string
    {
        $output = $this->script;

        if (!empty($output) && !empty($this->arguments)) {
            $output .= ' ';
            $output .= implode(' ', $this->arguments);
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
