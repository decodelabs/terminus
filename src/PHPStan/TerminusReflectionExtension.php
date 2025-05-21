<?php

/**
 * @package PHPStanDecodeLabs
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\PHPStan;

use DecodeLabs\PHPStan\MethodReflection;
use DecodeLabs\PHPStan\StaticMethodReflection;
use DecodeLabs\Terminus\Io\Style;
use DecodeLabs\Terminus\Session;
use Exception;
use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
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
        return
            $classReflection->getName() === Session::class;
    }

    public function getMethod(
        ClassReflection $classReflection,
        string $methodName
    ): MethodReflectionInterface {
        if (
            !preg_match('/[^a-zA-Z0-9_]/', $methodName) &&
            !Style::isKeyword($methodName) &&
            $this->reflectionProvider->getClass(Session::class)->hasMethod($methodName)
        ) {
            $method = $this->reflectionProvider->getClass(Session::class)->getMethod($methodName, new OutOfClassScope());

            if ($classReflection->getName() === Session::class) {
                return $method;
            }

            return new StaticMethodReflection($method);
        }

        $method = $this->reflectionProvider->getClass(Session::class)->getNativeMethod('style');

        /** @var FunctionVariant $variant */
        $variant = $method->getVariants()[0];
        $params = array_slice($variant->getParameters(), 1);

        $newVariant = MethodReflection::alterVariant($variant, $params);
        return new MethodReflection($classReflection, $methodName, [$newVariant]);
    }
}
