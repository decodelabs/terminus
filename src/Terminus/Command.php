<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus;

use ArrayAccess;
use ArrayIterator;
use DecodeLabs\Coercion;
use DecodeLabs\Glitch\Dumpable;
use DecodeLabs\Terminus\Command\Argument;
use DecodeLabs\Terminus\Command\Definition;
use DecodeLabs\Terminus\Command\Request;
use IteratorAggregate;
use Traversable;

/**
 * @implements ArrayAccess<string,bool|string|list<string>|null>
 * @implements IteratorAggregate<string,bool|string|list<string>|null>
 */
class Command extends Definition implements
    ArrayAccess,
    IteratorAggregate,
    Dumpable
{
    public Request $request;

    /**
     * @var array<string, bool|string|list<string>|null>|null
     */
    protected ?array $values = null;

    /**
     * Init with Request
     */
    public function __construct(
        Request $request
    ) {
        $this->setRequest($request);
    }


    /**
     * Set request - must be done early in process
     *
     * @return $this
     */
    public function setRequest(
        Request $request
    ): static {
        $this->request = $request;

        if (null === ($name = $request->getScript())) {
            $name = $request->getServerParameter('PHP_SELF');
        }

        $name = pathinfo((string)$name, \PATHINFO_FILENAME);
        $this->setName($name);
        $this->values = null;

        return $this;
    }

    /**
     * Get request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }



    /**
     * Add a single argument to the queue
     */
    public function addArgument(
        string $name,
        string $description,
        ?callable $setup = null
    ): static {
        $this->values = null;
        return parent::addArgument($name, $description, $setup);
    }


    /**
     * Push an argument to the queue
     *
     * @return $this
     */
    public function setArgument(
        Argument $arg
    ): static {
        $this->values = null;
        return parent::setArgument($arg);
    }

    /**
     * Remove an argument from the queue
     *
     * @return $this
     */
    public function removeArgument(
        string $name
    ): static {
        $this->values = null;
        return parent::removeArgument($name);
    }

    /**
     * Remove all arguments from queue
     *
     * @return $this
     */
    public function clearArguments(): static
    {
        $this->values = null;
        return parent::clearArguments();
    }







    /**
     * Prepare arguments from command definition
     *
     * @return array<string,bool|string|list<string>|null>
     */
    public function prepare(): array
    {
        return $this->values = $this->apply($this->request);
    }

    /**
     * Get argument
     *
     * @return bool|string|list<string>|null
     */
    public function get(
        string $name
    ): bool|string|array|null {
        if ($this->values === null) {
            $this->prepare();
        }

        return $this->values[$name] ?? null;
    }

    /**
     * Get argument as string
     */
    public function getString(
        string $name
    ): string {
        return Coercion::forceString($this->get($name));
    }

    /**
     * Get argument as bool
     */
    public function getBool(
        string $name
    ): bool {
        return Coercion::toBool($this->get($name));
    }

    /**
     * Get argument as list
     *
     * @return list<string>
     */
    public function getList(
        string $name
    ): array {
        $value = $this->get($name);

        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if(!is_string($value)) {
            return [];
        }

        return [$value];
    }

    /**
     * Has argument
     */
    public function has(
        string $name
    ): bool {
        if ($this->values === null) {
            $this->prepare();
        }

        return array_key_exists($name, $this->values ?? []);
    }

    /**
     * Get unnamed arguments
     *
     * @return array<string>
     */
    public function getUnnamed(): array
    {
        if ($this->values === null) {
            $this->prepare();
        }

        $output = [];

        foreach ($this->values ?? [] as $name => $value) {
            if (substr($name, 0, 7) === 'unnamed') {
                $output[] = Coercion::forceString($value);
            }
        }

        return $output;
    }

    /**
     * Get passthrough arguments
     *
     * @return array<string>
     */
    public function passthrough(
        string ...$remove
    ): array {
        if ($this->values === null) {
            $this->prepare();
        }

        $output = [];

        foreach ($this->values ?? [] as $name => $value) {
            if (in_array($name, $remove)) {
                continue;
            }

            if (substr($name, 0, 7) === 'unnamed') {
                $output[] = Coercion::toString($value);
            } elseif (is_string($value)) {
                $output[] = '--' . $name . '="' . $value . '"';
            } elseif ($value) {
                $output[] = '--' . $name;
            }
        }

        return $output;
    }


    /**
     * Manually override argument
     *
     * @param string $name
     */
    public function offsetSet(
        mixed $name,
        mixed $value
    ): void {
        if ($this->values === null) {
            $this->prepare();
        }

        $this->values[$name] = $value;
    }

    /**
     * Get argument shortcut
     *
     * @param string $name
     * @return bool|string|array<bool|string>|null
     */
    public function offsetGet(
        mixed $name
    ): bool|string|array|null {
        if ($this->values === null) {
            $this->prepare();
        }

        return $this->values[$name] ?? null;
    }

    /**
     * Has argument
     *
     * @param string $name
     */
    public function offsetExists(
        mixed $name
    ): bool {
        if ($this->values === null) {
            $this->prepare();
        }

        return array_key_exists($name, $this->values ?? []);
    }

    /**
     * Remove argument
     *
     * @param string $name
     */
    public function offsetUnset(
        mixed $name
    ): void {
        if ($this->values === null) {
            $this->prepare();
        }

        unset($this->values[$name]);
    }

    /**
     * @return array<string, bool|string|array<bool|string>|null>
     */
    public function toArray(): array
    {
        if ($this->values === null) {
            $this->prepare();
        }

        return $this->values ?? [];
    }

    /**
     * Get iterator
     */
    public function getIterator(): Traversable
    {
        if ($this->values === null) {
            $this->prepare();
        }

        return new ArrayIterator($this->values ?? []);
    }


    /**
     * Export for dump inspection
     */
    public function glitchDump(): iterable
    {
        yield 'text' => $this->request->getScript();
        yield 'values' => $this->toArray();
    }
}
