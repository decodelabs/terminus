<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Terminus\Context as Inst;
use DecodeLabs\Terminus\Command as CommandPlugin;
use DecodeLabs\Terminus\Adapter as Ref0;
use DecodeLabs\Terminus\Session as Ref1;
use DecodeLabs\Terminus\Command\Request as Ref2;
use DecodeLabs\Deliverance\Broker as Ref3;
use DecodeLabs\Terminus\Command\Definition as Ref4;
use Stringable as Ref5;

class Terminus implements Proxy
{
    use ProxyTrait;

    public const Veneer = 'DecodeLabs\\Terminus';
    public const VeneerTarget = Inst::class;

    protected static Inst $_veneerInstance;
    public static CommandPlugin $command;

    public static function isActiveSapi(): bool {
        return static::$_veneerInstance->isActiveSapi();
    }
    public static function getAdapter(): Ref0 {
        return static::$_veneerInstance->getAdapter();
    }
    public static function setSession(Ref1 $session): Inst {
        return static::$_veneerInstance->setSession(...func_get_args());
    }
    public static function replaceSession(?Ref2 $request = NULL, ?Ref3 $broker = NULL): ?Ref1 {
        return static::$_veneerInstance->replaceSession(...func_get_args());
    }
    public static function getSession(): Ref1 {
        return static::$_veneerInstance->getSession();
    }
    public static function newSession(?Ref2 $request = NULL, ?Ref3 $broker = NULL): Ref1 {
        return static::$_veneerInstance->newSession(...func_get_args());
    }
    public static function setRequest(Ref2 $request): Inst {
        return static::$_veneerInstance->setRequest(...func_get_args());
    }
    public static function newRequest(?array $argv = NULL, ?array $server = NULL): Ref2 {
        return static::$_veneerInstance->newRequest(...func_get_args());
    }
    public static function newCommandDefinition(?string $name = NULL): Ref4 {
        return static::$_veneerInstance->newCommandDefinition(...func_get_args());
    }
    public static function getCommand(): CommandPlugin {
        return static::$_veneerInstance->getCommand();
    }
    public static function getShellWidth(): int {
        return static::$_veneerInstance->getShellWidth();
    }
    public static function getShellHeight(): int {
        return static::$_veneerInstance->getShellHeight();
    }
    public static function canColor(): bool {
        return static::$_veneerInstance->canColor();
    }
    public static function info(Ref5|string|int|float $message, array $context = []): void {}
    public static function notice(Ref5|string|int|float $message, array $context = []): void {}
    public static function comment(Ref5|string|int|float $message, array $context = []): void {}
    public static function success(Ref5|string|int|float $message, array $context = []): void {}
    public static function operative(Ref5|string|int|float $message, array $context = []): void {}
    public static function deleteSuccess(Ref5|string|int|float $message, array $context = []): void {}
    public static function warning(Ref5|string|int|float $message, array $context = []): void {}
    public static function critical(Ref5|string|int|float $message, array $context = []): void {}
    public static function alert(Ref5|string|int|float $message, array $context = []): void {}
    public static function emergency(Ref5|string|int|float $message, array $context = []): void {}
    public static function log(string $level, Ref5|string|int|float $message, array $context = []): void {}
    public static function inlineDebug(Ref5|string|int|float $message, array $context = []): void {}
    public static function inlineInfo(Ref5|string|int|float $message, array $context = []): void {}
    public static function inlineNotice(Ref5|string|int|float $message, array $context = []): void {}
    public static function inlineComment(Ref5|string|int|float $message, array $context = []): void {}
    public static function inlineSuccess(Ref5|string|int|float $message, array $context = []): void {}
    public static function inlineOperative(Ref5|string|int|float $message, array $context = []): void {}
    public static function inlineDeleteSuccess(Ref5|string|int|float $message, array $context = []): void {}
    public static function inlineWarning(Ref5|string|int|float $message, array $context = []): void {}
    public static function inlineError(Ref5|string|int|float $message, array $context = []): void {}
    public static function inlineCritical(Ref5|string|int|float $message, array $context = []): void {}
    public static function inlineAlert(Ref5|string|int|float $message, array $context = []): void {}
    public static function inlineEmergency(Ref5|string|int|float $message, array $context = []): void {}
    public static function inlineLog(string $level, Ref5|string|int|float $message, array $context = []): void {}
};
