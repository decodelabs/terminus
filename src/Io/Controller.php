<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus\Io;

use DecodeLabs\Deliverance\DataProvider;
use DecodeLabs\Deliverance\DataReceiver;
use DecodeLabs\Deliverance\ErrorDataReceiver;

use DecodeLabs\Terminus\Widget\Confirmation;
use DecodeLabs\Terminus\Widget\Password;
use DecodeLabs\Terminus\Widget\ProgressBar;
use DecodeLabs\Terminus\Widget\Question;
use DecodeLabs\Terminus\Widget\Spinner;

use Psr\Log\LoggerInterface;

interface Controller extends
    DataProvider,
    DataReceiver,
    ErrorDataReceiver,
    LoggerInterface
{
    public function isAnsi(): bool;
    public function hasStty(): bool;
    public function snapshotStty(): ?string;

    public function restoreStty(
        ?string $snapshot
    ): bool;

    public function resetStty(): bool;

    public function getWidth(): int;
    public function getHeight(): int;

    public function newLine(
        int $times = 1
    ): bool;

    public function newErrorLine(
        int $times = 1
    ): bool;


    public function deleteLine(
        int $times = 1
    ): bool;

    public function deleteErrorLine(
        int $times = 1
    ): bool;


    public function clearLine(): bool;
    public function clearErrorLine(): bool;
    public function clearLineBefore(): bool;
    public function clearErrorLineBefore(): bool;
    public function clearLineAfter(): bool;
    public function clearErrorLineAfter(): bool;

    public function backspace(
        int $times = 1
    ): bool;

    public function backspaceError(
        int $times = 1
    ): bool;


    public function tab(
        int $times = 1
    ): bool;

    public function tabError(
        int $times = 1
    ): bool;


    public function cursorUp(
        int $times = 1
    ): bool;

    public function cursorLineUp(
        int $times = 1
    ): bool;

    public function cursorDown(
        int $times = 1
    ): bool;

    public function cursorLineDown(
        int $times = 1
    ): bool;

    public function errorCursorUp(
        int $times = 1
    ): bool;

    public function errorCursorLineUp(
        int $times = 1
    ): bool;

    public function errorCursorDown(
        int $times = 1
    ): bool;

    public function errorCursorLineDown(
        int $times = 1
    ): bool;


    public function cursorLeft(
        int $times = 1
    ): bool;

    public function cursorRight(
        int $times = 1
    ): bool;

    public function errorCursorLeft(
        int $times = 1
    ): bool;

    public function errorCursorRight(
        int $times = 1
    ): bool;


    public function setCursor(
        int $pos
    ): bool;

    public function setErrorCursor(
        int $pos
    ): bool;


    public function setCursorLine(
        int $line,
        int $pos = 1
    ): bool;

    public function setErrorCursorLine(
        int $line,
        int $pos = 1
    ): bool;

    /**
     * @return array<int>
     */
    public function getCursor(): array;

    /**
     * @return array<int>
     */
    public function getErrorCursor(): array;

    public function getCursorH(): int;
    public function getErrorCursorH(): int;
    public function getCursorV(): int;
    public function getErrorCursorV(): int;

    public function saveCursor(): bool;
    public function saveErrorCursor(): bool;
    public function restoreCursor(): bool;
    public function restoreErrorCursor(): bool;

    //public function getDefaultBackgroundColor(): ?string;

    public function toggleInputEcho(
        bool $flag
    ): bool;

    public function toggleInputBuffer(
        bool $flag
    ): bool;


    /**
     * @param array{0?: string|null} $args
     * @return $this
     */
    public function __call(
        string $method,
        array $args
    ): static;

    /**
     * @return $this
     */
    public function style(
        string $style,
        ?string $message = null
    ): static;



    public function ask(
        string $message,
        string $default = null,
        ?callable $validator = null
    ): ?string;

    public function newQuestion(
        string $message,
        string $default = null,
        ?callable $validator = null
    ): Question;

    public function askPassword(
        ?string $message = null,
        bool $repeat = false,
        bool $required = true
    ): ?string;

    public function newPasswordQuestion(
        ?string $message = null,
        bool $repeat = false,
        bool $required = true
    ): Password;

    public function confirm(
        string $message,
        bool $default = null
    ): bool;

    public function newConfirmation(
        string $message,
        bool $default = null
    ): Confirmation;

    public function newSpinner(
        string $style = null
    ): Spinner;

    public function newProgressBar(
        float $min = 0.0,
        float $max = 100.0,
        ?int $precision = null
    ): ProgressBar;


    /**
     * @param array<string, mixed> $context
     */
    public function comment(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function success(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function operative(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function deleteSuccess(
        string $message,
        array $context = []
    ): void;


    /**
     * @param array<string, mixed> $context
     */
    public function inlineDebug(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function inlineInfo(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function inlineNotice(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function inlineComment(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function inlineSuccess(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function inlineOperative(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function inlineDeleteSuccess(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function inlineWarning(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function inlineError(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function inlineCritical(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function inlineAlert(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function inlineEmergency(
        string $message,
        array $context = []
    ): void;

    /**
     * @param array<string, mixed> $context
     */
    public function inlineLog(
        string $level,
        string $message,
        array $context = []
    ): void;
}
