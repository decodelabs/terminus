<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus\Io;

use DecodeLabs\Terminus\Session;

use DecodeLabs\Terminus\Widget\Question;
use DecodeLabs\Terminus\Widget\Password;
use DecodeLabs\Terminus\Widget\Confirmation;
use DecodeLabs\Terminus\Widget\Spinner;
use DecodeLabs\Terminus\Widget\ProgressBar;

use DecodeLabs\Atlas\DataProvider;
use DecodeLabs\Atlas\DataReceiver;
use DecodeLabs\Atlas\ErrorDataReceiver;

use DecodeLabs\Glitch;

interface Controller extends DataProvider, DataReceiver, ErrorDataReceiver
{
    public function isAnsi(): bool;
    public function hasStty(): bool;
    public function snapshotStty(): ?string;
    public function restoreStty(?string $snapshot): bool;
    public function resetStty(): bool;

    public function getWidth(): int;
    public function getHeight(): int;

    public function newLine(int $times=1): bool;
    public function newErrorLine(int $times=1): bool;

    public function deleteLine(int $times=1): bool;
    public function deleteErrorLine(int $times=1): bool;

    public function clearLine(): bool;
    public function clearErrorLine(): bool;
    public function clearLineBefore(): bool;
    public function clearErrorLineBefore(): bool;
    public function clearLineAfter(): bool;
    public function clearErrorLineAfter(): bool;

    public function backspace(int $times=1): bool;
    public function backspaceError(int $times=1): bool;

    public function tab(int $times=1): bool;
    public function tabError(int $times=1): bool;

    public function cursorUp(int $times=1): bool;
    public function cursorLineUp(int $times=1): bool;
    public function cursorDown(int $times=1): bool;
    public function cursorLineDown(int $times=1): bool;
    public function errorCursorUp(int $times=1): bool;
    public function errorCursorLineUp(int $times=1): bool;
    public function errorCursorDown(int $times=1): bool;
    public function errorCursorLineDown(int $times=1): bool;

    public function cursorLeft(int $times=1): bool;
    public function cursorRight(int $times=1): bool;
    public function errorCursorLeft(int $times=1): bool;
    public function errorCursorRight(int $times=1): bool;

    public function setCursor(int $pos): bool;
    public function setErrorCursor(int $pos): bool;
    public function setCursorLine(int $line, int $pos=1): bool;
    public function setErrorCursorLine(int $line, int $pos=1): bool;

    public function getCursor(): array;
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

    public function toggleInputEcho(bool $flag): bool;
    public function toggleInputBuffer(bool $flag): bool;

    public function __call(string $method, array $args): Controller;
    public function style(string $style, ?string $message=null): Controller;



    public function ask(string $message, string $default=null): Question;
    public function askPassword(string $message): Password;
    public function confirm(string $message, bool $default=null): Confirmation;
    public function newSpinner(string $style=null): Spinner;
    public function newProgressBar(float $min=0.0, float $max=100.0, ?int $precision=null): ProgressBar;
}
