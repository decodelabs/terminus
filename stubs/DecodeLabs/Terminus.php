<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Terminus\Context as Inst;
use DecodeLabs\Terminus\Session as Ref0;
use DecodeLabs\Terminus\Command\Request as Ref1;
use DecodeLabs\Deliverance\Broker as Ref2;
use DecodeLabs\Terminus\Command\Definition as Ref3;
use Stringable as Ref4;

class Terminus implements Proxy
{
    use ProxyTrait;

    const VENEER = 'DecodeLabs\Terminus';
    const VENEER_TARGET = Inst::class;

    public static Inst $instance;

    public static function isActiveSapi(): bool {
        return static::$instance->isActiveSapi();
    }
    public static function setSession(Ref0 $session): Inst {
        return static::$instance->setSession(...func_get_args());
    }
    public static function replaceSession(?Ref1 $request = NULL, ?Ref2 $broker = NULL): ?Ref0 {
        return static::$instance->replaceSession(...func_get_args());
    }
    public static function getSession(): Ref0 {
        return static::$instance->getSession();
    }
    public static function newSession(?Ref1 $request = NULL, ?Ref2 $broker = NULL): Ref0 {
        return static::$instance->newSession(...func_get_args());
    }
    public static function newRequest(?array $argv = NULL, ?array $server = NULL): Ref1 {
        return static::$instance->newRequest(...func_get_args());
    }
    public static function newCommandDefinition(?string $name = NULL): Ref3 {
        return static::$instance->newCommandDefinition(...func_get_args());
    }
    public static function prepareCommand(callable $builder): Ref0 {
        return static::$instance->prepareCommand(...func_get_args());
    }
    public static function getShellWidth(): int {
        return static::$instance->getShellWidth();
    }
    public static function getShellHeight(): int {
        return static::$instance->getShellHeight();
    }
    public static function canColor(): bool {
        return static::$instance->canColor();
    }
    public static function info(Ref4|string|int|float $message, array $context = []): void {}
    public static function notice(Ref4|string|int|float $message, array $context = []): void {}
    public static function comment(Ref4|string|int|float $message, array $context = []): void {}
    public static function success(Ref4|string|int|float $message, array $context = []): void {}
    public static function operative(Ref4|string|int|float $message, array $context = []): void {}
    public static function deleteSuccess(Ref4|string|int|float $message, array $context = []): void {}
    public static function warning(Ref4|string|int|float $message, array $context = []): void {}
    public static function critical(Ref4|string|int|float $message, array $context = []): void {}
    public static function alert(Ref4|string|int|float $message, array $context = []): void {}
    public static function emergency(Ref4|string|int|float $message, array $context = []): void {}
    public static function log(string $level, Ref4|string|int|float $message, array $context = []): void {}
    public static function inlineDebug(Ref4|string|int|float $message, array $context = []): void {}
    public static function inlineInfo(Ref4|string|int|float $message, array $context = []): void {}
    public static function inlineNotice(Ref4|string|int|float $message, array $context = []): void {}
    public static function inlineComment(Ref4|string|int|float $message, array $context = []): void {}
    public static function inlineSuccess(Ref4|string|int|float $message, array $context = []): void {}
    public static function inlineOperative(Ref4|string|int|float $message, array $context = []): void {}
    public static function inlineDeleteSuccess(Ref4|string|int|float $message, array $context = []): void {}
    public static function inlineWarning(Ref4|string|int|float $message, array $context = []): void {}
    public static function inlineError(Ref4|string|int|float $message, array $context = []): void {}
    public static function inlineCritical(Ref4|string|int|float $message, array $context = []): void {}
    public static function inlineAlert(Ref4|string|int|float $message, array $context = []): void {}
    public static function inlineEmergency(Ref4|string|int|float $message, array $context = []): void {}
    public static function inlineLog(string $level, Ref4|string|int|float $message, array $context = []): void {}
};
