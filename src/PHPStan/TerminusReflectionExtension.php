<?php

/**
 * @package PHPStanDecodeLabs
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\PHPStan;

use DecodeLabs\PHPStan\StaticMethodReflection;
use DecodeLabs\Terminus\Context;
use DecodeLabs\Terminus\Session;
use Exception;
use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection as MethodReflectionInterface;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;

class TerminusReflectionExtension implements MethodsClassReflectionExtension
{
    protected ReflectionProvider $reflectionProvider;

    public function __construct(
        ReflectionProvider $reflectionProvider
    ) {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function hasMethod(
        ClassReflection $classReflection,
        string $methodName
    ): bool {
        $class = $classReflection->getName();

        if ($class === Context::class) {
            return $this->reflectionProvider->getClass(Session::class)->hasMethod($methodName);
        }

        return false;
    }

    public function getMethod(
        ClassReflection $classReflection,
        string $methodName
    ): MethodReflectionInterface {
        $class = $classReflection->getName();

        if ($class !== Context::class) {
            throw new Exception('Unable to get method');
        }

        return new StaticMethodReflection(
            $this->reflectionProvider->getClass(Session::class)->getMethod($methodName, new OutOfClassScope())
        );
    }
}
