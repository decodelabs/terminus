<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Terminus\Context as Inst;
use DecodeLabs\Terminus\Adapter as Ref0;
use DecodeLabs\Terminus\Session as Ref1;
use DecodeLabs\Deliverance\Broker as Ref2;
use Stringable as Ref3;

class Terminus implements Proxy
{
    use ProxyTrait;

    public const Veneer = 'DecodeLabs\\Terminus';
    public const VeneerTarget = Inst::class;

    protected static Inst $_veneerInstance;

    public static function isActiveSapi(): bool {
        return static::$_veneerInstance->isActiveSapi();
    }
    public static function getAdapter(): Ref0 {
        return static::$_veneerInstance->getAdapter();
    }
    public static function setSession(Ref1 $session): Inst {
        return static::$_veneerInstance->setSession(...func_get_args());
    }
    public static function replaceSession(?Ref2 $broker = NULL): ?Ref1 {
        return static::$_veneerInstance->replaceSession(...func_get_args());
    }
    public static function getSession(): Ref1 {
        return static::$_veneerInstance->getSession();
    }
    public static function newSession(?Ref2 $broker = NULL): Ref1 {
        return static::$_veneerInstance->newSession(...func_get_args());
    }
    public static function canColor(): bool {
        return static::$_veneerInstance->canColor();
    }
    public static function info(Ref3|string|int|float $message, array $context = []): void {}
    public static function notice(Ref3|string|int|float $message, array $context = []): void {}
    public static function comment(Ref3|string|int|float $message, array $context = []): void {}
    public static function success(Ref3|string|int|float $message, array $context = []): void {}
    public static function operative(Ref3|string|int|float $message, array $context = []): void {}
    public static function deleteSuccess(Ref3|string|int|float $message, array $context = []): void {}
    public static function warning(Ref3|string|int|float $message, array $context = []): void {}
    public static function critical(Ref3|string|int|float $message, array $context = []): void {}
    public static function alert(Ref3|string|int|float $message, array $context = []): void {}
    public static function emergency(Ref3|string|int|float $message, array $context = []): void {}
    public static function log(string $level, Ref3|string|int|float $message, array $context = []): void {}
    public static function inlineDebug(Ref3|string|int|float $message, array $context = []): void {}
    public static function inlineInfo(Ref3|string|int|float $message, array $context = []): void {}
    public static function inlineNotice(Ref3|string|int|float $message, array $context = []): void {}
    public static function inlineComment(Ref3|string|int|float $message, array $context = []): void {}
    public static function inlineSuccess(Ref3|string|int|float $message, array $context = []): void {}
    public static function inlineOperative(Ref3|string|int|float $message, array $context = []): void {}
    public static function inlineDeleteSuccess(Ref3|string|int|float $message, array $context = []): void {}
    public static function inlineWarning(Ref3|string|int|float $message, array $context = []): void {}
    public static function inlineError(Ref3|string|int|float $message, array $context = []): void {}
    public static function inlineCritical(Ref3|string|int|float $message, array $context = []): void {}
    public static function inlineAlert(Ref3|string|int|float $message, array $context = []): void {}
    public static function inlineEmergency(Ref3|string|int|float $message, array $context = []): void {}
    public static function inlineLog(string $level, Ref3|string|int|float $message, array $context = []): void {}
};
