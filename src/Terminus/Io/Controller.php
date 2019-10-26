<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus\Io;

use DecodeLabs\Terminus\Session;
use DecodeLabs\Atlas\DataProvider;
use DecodeLabs\Atlas\DataReceiver;
use DecodeLabs\Atlas\ErrorDataReceiver;

use DecodeLabs\Glitch;

interface Controller extends DataProvider, DataReceiver, ErrorDataReceiver
{
    public function newLine(int $times=1): Controller;
    public function newErrorLine(int $times=1): Controller;

    public function deleteLine(int $times=1): Controller;
    public function deleteErrorLine(int $times=1): Controller;

    public function clearLine(): Controller;
    public function clearErrorLine(): Controller;
    public function clearLineBefore(): Controller;
    public function clearErrorLineBefore(): Controller;
    public function clearLineAfter(): Controller;
    public function clearErrorLineAfter(): Controller;

    public function backspace(int $times=1): Controller;
    public function backspaceError(int $times=1): Controller;

    public function tab(int $times=1): Controller;
    public function tabError(int $times=1): Controller;

    public function cursorUp(int $times=1): Controller;
    public function cursorLineUp(int $times=1): Controller;
    public function cursorDown(int $times=1): Controller;
    public function cursorLineDown(int $times=1): Controller;
    public function errorCursorUp(int $times=1): Controller;
    public function errorCursorLineUp(int $times=1): Controller;
    public function errorCursorDown(int $times=1): Controller;
    public function errorCursorLineDown(int $times=1): Controller;

    public function cursorLeft(int $times=1): Controller;
    public function cursorRight(int $times=1): Controller;
    public function errorCursorLeft(int $times=1): Controller;
    public function errorCursorRight(int $times=1): Controller;

    public function setCursor(int $pos): Controller;
    public function setErrorCursor(int $pos): Controller;
    public function setCursorLine(int $line, int $pos=1): Controller;
    public function setErrorCursorLine(int $line, int $pos=1): Controller;

    /*
    public function getCursor(): int;
    public function getErrorCursor(): int;
    public function getCursorLine(): int;
    public function getErrorCursorLine(): int;
    */

    public function saveCursor(): Controller;
    public function saveErrorCursor(): Controller;
    public function restoreCursor(): Controller;
    public function restoreErrorCursor(): Controller;

    public function __call(string $method, array $args): Controller;
    public function style(string $style, ?string $message=null): Controller;
}
