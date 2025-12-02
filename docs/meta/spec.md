# Terminus — Package Specification

> **Cluster:** `cli`
> **Language:** `php`
> **Milestone:** `m2`
> **Repo:** `https://github.com/decodelabs/terminus`
> **Role:** CLI IO

## Overview

### Purpose

Terminus provides everything needed to build highly interactive, beautiful CLI processes. It offers a comprehensive set of tools for terminal I/O operations, including text output, input handling, ANSI styling, cursor control, and interactive widgets.

Key features:
- **Terminal I/O**: Reading from and writing to standard input, output, and error streams
- **ANSI styling**: Rich text formatting with colors, backgrounds, and text options
- **Cursor control**: Precise cursor positioning and movement
- **Line control**: Clearing, deleting, and manipulating lines
- **stty integration**: Extended input control (echo, buffering) on Unix systems
- **Interactive widgets**: Question prompts, password input, confirmations, spinners, and progress bars
- **PSR-3 logging**: Full PSR-3 logger interface implementation
- **Output capture**: Capture output from operations for testing or processing
- **Style parsing**: Flexible style string parsing with modifiers and color codes

### Non-Goals

- Terminus does not provide command-line argument parsing (see Commandment).
- It does not handle process execution or system commands (see Systemic).
- It does not provide terminal multiplexing or screen management.
- It does not handle Windows terminal support (currently Unix-only).
- It does not provide file system operations or path manipulation.

## Role in the Ecosystem

### Cluster & Positioning

Terminus belongs to the **cli** cluster, providing foundational CLI I/O capabilities. It serves as the base layer for building interactive command-line applications and complements other CLI packages like Commandment.

### Usage Contexts

- **Interactive CLI applications**: Building user-friendly command-line interfaces
- **Progress indicators**: Displaying progress bars and spinners for long-running operations
- **User input**: Prompting users for input, passwords, and confirmations
- **Colored output**: Styling terminal output with colors and formatting
- **Terminal manipulation**: Controlling cursor position and clearing lines
- **Logging**: Using PSR-3 logging interface for CLI output

## Public Surface

### Key Types

- **`Session`** (class): Main session class providing all CLI I/O operations. Implements `Controller` interface and `Service` for Kingdom integration. Provides methods for reading, writing, styling, cursor control, and widgets.

- **`Adapter`** (interface): Adapter interface for platform-specific terminal operations. Defines methods for stty support, shell dimensions, and color detection.

- **`Adapter\Unix`** (class): Unix adapter implementation. Provides stty support, shell dimension detection, and color capability detection.

- **`Io\Controller`** (interface): Controller interface extending `DataProvider`, `DataReceiver`, `ErrorDataReceiver`, and `LoggerInterface`. Defines all CLI I/O operations.

- **`Io\Style`** (class): Style class for parsing and applying ANSI styles. Handles foreground/background colors, options, and modifiers.

- **`Widget\Question`** (class): Question widget for prompting users with options and validation.

- **`Widget\Password`** (class): Password widget for secure password input with optional repeat confirmation.

- **`Widget\Confirmation`** (class): Confirmation widget for yes/no questions.

- **`Widget\Spinner`** (class): Spinner widget for indicating progress with animated spinner.

- **`Widget\ProgressBar`** (class): Progress bar widget for displaying progress with percentage and completion values.

- **`Capture`** (class): Capture class for capturing output from operations. Generic template class.

- **`PHPStan\TerminusReflectionExtension`** (class): PHPStan reflection extension for static analysis.

### Main Entry Points

**Session:**
- `Session::getDefault(): Session` — Get default session instance
- `Session::provideService(ContainerAdapter $container): static` — Service provider method
- `new Session(Broker $broker)` — Constructor with custom broker
- `$session->broker` — Deliverance broker instance (public property)
- `$session->adapter` — Terminal adapter instance (public property)
- `$session->width` — Terminal width (readonly property)
- `$session->height` — Terminal height (readonly property)
- `$session->readBlocking` — Read blocking mode (public property)

**Input Operations:**
- `$session->isReadable(): bool` — Check if readable
- `$session->read(int $length): ?string` — Read bytes
- `$session->readAll(): ?string` — Read all available data
- `$session->readChar(): ?string` — Read single character
- `$session->readLine(): ?string` — Read line
- `$session->readTo(DataReceiver $writer): static` — Read to receiver
- `$session->isAtEnd(): bool` — Check if at end of input

**Output Operations:**
- `$session->isWritable(): bool` — Check if writable
- `$session->write(?string $data, ?int $length = null): int` — Write data
- `$session->writeLine(?string $data = ''): int` — Write line
- `$session->writeBuffer(Buffer $buffer, int $length): int` — Write buffer

**Error Output Operations:**
- `$session->isErrorWritable(): bool` — Check if error writable
- `$session->writeError(?string $data, ?int $length = null): int` — Write error
- `$session->writeErrorLine(?string $data = ''): int` — Write error line
- `$session->writeErrorBuffer(Buffer $buffer, int $length): int` — Write error buffer

**Line Control:**
- `$session->newLine(int $times = 1): bool` — New line(s)
- `$session->newErrorLine(int $times = 1): bool` — New error line(s)
- `$session->deleteLine(int $times = 1): bool` — Delete line(s)
- `$session->deleteErrorLine(int $times = 1): bool` — Delete error line(s)
- `$session->clearLine(): bool` — Clear current line
- `$session->clearErrorLine(): bool` — Clear current error line
- `$session->clearLineBefore(): bool` — Clear line before cursor
- `$session->clearErrorLineBefore(): bool` — Clear error line before cursor
- `$session->clearLineAfter(): bool` — Clear line after cursor
- `$session->clearErrorLineAfter(): bool` — Clear error line after cursor
- `$session->backspace(int $times = 1): bool` — Backspace character(s)
- `$session->backspaceError(int $times = 1): bool` — Backspace error character(s)
- `$session->tab(int $times = 1): bool` — Tab character(s)
- `$session->tabError(int $times = 1): bool` — Tab error character(s)

**Cursor Control:**
- `$session->cursorUp(int $times = 1): bool` — Move cursor up
- `$session->cursorLineUp(int $times = 1): bool` — Move cursor to start of previous line
- `$session->cursorDown(int $times = 1): bool` — Move cursor down
- `$session->cursorLineDown(int $times = 1): bool` — Move cursor to start of next line
- `$session->cursorLeft(int $times = 1): bool` — Move cursor left
- `$session->cursorRight(int $times = 1): bool` — Move cursor right
- `$session->errorCursorUp(int $times = 1): bool` — Move error cursor up
- `$session->errorCursorLineUp(int $times = 1): bool` — Move error cursor to start of previous line
- `$session->errorCursorDown(int $times = 1): bool` — Move error cursor down
- `$session->errorCursorLineDown(int $times = 1): bool` — Move error cursor to start of next line
- `$session->errorCursorLeft(int $times = 1): bool` — Move error cursor left
- `$session->errorCursorRight(int $times = 1): bool` — Move error cursor right
- `$session->setCursor(int $pos): bool` — Set cursor horizontal position
- `$session->setErrorCursor(int $pos): bool` — Set error cursor horizontal position
- `$session->setCursorLine(int $line, int $pos = 1): bool` — Set absolute cursor position
- `$session->setErrorCursorLine(int $line, int $pos = 1): bool` — Set absolute error cursor position
- `$session->getCursor(): array` — Get cursor position [line, column]
- `$session->getErrorCursor(): array` — Get error cursor position [line, column]
- `$session->getCursorH(): int` — Get cursor horizontal position
- `$session->getErrorCursorH(): int` — Get error cursor horizontal position
- `$session->getCursorV(): int` — Get cursor vertical position
- `$session->getErrorCursorV(): int` — Get error cursor vertical position
- `$session->saveCursor(): bool` — Save cursor position
- `$session->saveErrorCursor(): bool` — Save error cursor position
- `$session->restoreCursor(): bool` — Restore cursor position
- `$session->restoreErrorCursor(): bool` — Restore error cursor position

**ANSI Support:**
- `$session->isAnsi(): bool` — Check if ANSI supported
- `$session->canColor(): bool` — Check if colors supported
- `$session->disableAnsi(): void` — Disable ANSI support
- `$session->enableAnsi(): void` — Enable ANSI support

**stty Support:**
- `$session->hasStty(): bool` — Check if stty available
- `$session->snapshotStty(): ?string` — Snapshot stty settings
- `$session->restoreStty(?string $snapshot): bool` — Restore stty settings
- `$session->resetStty(): bool` — Reset stty to original settings
- `$session->toggleInputEcho(bool $flag): bool` — Toggle input echo
- `$session->toggleInputBuffer(bool $flag): bool` — Toggle input buffering

**Styling:**
- `$session->__call(string $method, array $args): static` — Dynamic style method calls
- `$session->style(string $style, ?string $message = null): static` — Apply style

**Widgets:**
- `$session->ask(string $message, string|callable|null $default = null, array $options = [], ?callable $validator = null, bool $showOptions = true, bool $strict = false, bool $confirm = false): ?string` — Ask question
- `$session->newQuestion(string $message, string|callable|null $default = null, array $options = [], ?callable $validator = null, bool $showOptions = true, bool $strict = false, bool $confirm = false): Question` — Create question widget
- `$session->askPassword(?string $message = null, bool $repeat = false, bool $required = true): ?string` — Ask password
- `$session->newPasswordQuestion(?string $message = null, bool $repeat = false, bool $required = true): Password` — Create password widget
- `$session->confirm(string $message, bool|callable|null $default = null): bool` — Confirm yes/no
- `$session->newConfirmation(string $message, bool|callable|null $default = null): Confirmation` — Create confirmation widget
- `$session->newSpinner(?string $style = null): Spinner` — Create spinner widget
- `$session->newProgressBar(float $min = 0.0, float $max = 100.0, ?int $precision = null, bool $showPercent = true, bool $showCompleted = true): ProgressBar` — Create progress bar widget

**Logging (PSR-3):**
- `$session->debug(string|Stringable|int|float $message, array $context = []): void` — Debug log
- `$session->info(string|Stringable|int|float $message, array $context = []): void` — Info log
- `$session->notice(string|Stringable|int|float $message, array $context = []): void` — Notice log
- `$session->comment(string|Stringable|int|float $message, array $context = []): void` — Comment log
- `$session->success(string|Stringable|int|float $message, array $context = []): void` — Success log
- `$session->operative(string|Stringable|int|float $message, array $context = []): void` — Operative log
- `$session->deleteSuccess(string|Stringable|int|float $message, array $context = []): void` — Delete success log
- `$session->warning(string|Stringable|int|float $message, array $context = []): void` — Warning log
- `$session->error(string|Stringable|int|float $message, array $context = []): void` — Error log
- `$session->critical(string|Stringable|int|float $message, array $context = []): void` — Critical log
- `$session->alert(string|Stringable|int|float $message, array $context = []): void` — Alert log
- `$session->emergency(string|Stringable|int|float $message, array $context = []): void` — Emergency log
- `$session->inlineDebug(string|Stringable|int|float $message, array $context = []): void` — Inline debug log
- `$session->inlineInfo(string|Stringable|int|float $message, array $context = []): void` — Inline info log
- `$session->inlineNotice(string|Stringable|int|float $message, array $context = []): void` — Inline notice log
- `$session->inlineComment(string|Stringable|int|float $message, array $context = []): void` — Inline comment log
- `$session->inlineSuccess(string|Stringable|int|float $message, array $context = []): void` — Inline success log
- `$session->inlineOperative(string|Stringable|int|float $message, array $context = []): void` — Inline operative log
- `$session->inlineDeleteSuccess(string|Stringable|int|float $message, array $context = []): void` — Inline delete success log
- `$session->inlineWarning(string|Stringable|int|float $message, array $context = []): void` — Inline warning log
- `$session->inlineError(string|Stringable|int|float $message, array $context = []): void` — Inline error log
- `$session->inlineCritical(string|Stringable|int|float $message, array $context = []): void` — Inline critical log
- `$session->inlineAlert(string|Stringable|int|float $message, array $context = []): void` — Inline alert log
- `$session->inlineEmergency(string|Stringable|int|float $message, array $context = []): void` — Inline emergency log
- `$session->log(mixed $level, string|Stringable|int|float $message, array $context = []): void` — Generic log
- `$session->inlineLog(string $level, string|Stringable|int|float $message, array $context = []): void` — Generic inline log

**Output Capture:**
- `$session->capture(Closure $executor): Capture` — Capture output from executor

**Utility:**
- `Session::stringToBoolean(string $string, ?bool $default = null): ?bool` — Convert string to boolean

**Question Widget:**
- `new Question(Session $io, string $message, string|callable|null $default = null, array $options = [], ?callable $validator = null, bool $showOptions = true, bool $strict = false, bool $confirm = false)` — Constructor
- `$question->message` — Question message (public property)
- `$question->options` — Answer options (public property)
- `$question->default` — Default answer (public property)
- `$question->validator` — Validation callback (public property)
- `$question->showOptions` — Show options flag (public property)
- `$question->strict` — Strict matching flag (public property)
- `$question->confirm` — Require confirmation flag (public property)
- `$question->required` — Required flag (public property)
- `$question->prompt(): ?string` — Prompt for answer

**Password Widget:**
- `new Password(Session $io, ?string $message = null, bool $repeat = false, bool $required = true)` — Constructor
- `$password->message` — Password prompt message (public property)
- `$password->repeatMessage` — Repeat password prompt message (public property)
- `$password->repeat` — Require repeat flag (public property)
- `$password->required` — Required flag (public property)
- `$password->prompt(): ?string` — Prompt for password

**Confirmation Widget:**
- `new Confirmation(Session $io, string $message, bool|callable|null $default = null)` — Constructor
- `$confirmation->message` — Confirmation message (public property)
- `$confirmation->default` — Default answer (public property)
- `$confirmation->showOptions` — Show options flag (public property)
- `$confirmation->input` — Pre-filled input (public property)
- `$confirmation->prompt(): bool` — Prompt for confirmation

**Spinner Widget:**
- `new Spinner(Session $io, ?string $style = null)` — Constructor
- `$spinner->style` — Spinner style (public property)
- `$spinner->advance(): static` — Advance spinner animation
- `$spinner->waitFor(float $seconds): static` — Wait with spinner
- `$spinner->complete(?string $message = null, ?string $style = null): static` — Complete spinner

**ProgressBar Widget:**
- `new ProgressBar(Session $io, float $min = 0.0, float $max = 100.0, ?int $precision = null, bool $showPercent = true, bool $showCompleted = true)` — Constructor
- `$progressBar->min` — Minimum value (public property)
- `$progressBar->max` — Maximum value (public property)
- `$progressBar->precision` — Decimal precision (public property)
- `$progressBar->showPercent` — Show percent flag (public property)
- `$progressBar->showCompleted` — Show completed flag (public property)
- `$progressBar->setRange(float $min, float $max): static` — Set value range
- `$progressBar->advance(float $value): static` — Advance to value
- `$progressBar->complete(): static` — Complete progress bar

**Capture:**
- `new Capture()` — Constructor
- `$capture->buffer` — Output buffer (public property)
- `$capture->result` — Captured result (public property)
- `$capture->error` — Captured error (public property)
- `$capture->resolve(): mixed` — Resolve result or throw error
- `$capture->__toString(): string` — String representation

**Style:**
- `Style::parse(string $modifier): Style` — Parse style string
- `Style::isKeyword(string $string): bool` — Check if string is style keyword
- `new Style(?string $foreground, ?string $background = null, string ...$options)` — Constructor
- `$style->foreground` — Foreground color (public property)
- `$style->background` — Background color (public property)
- `$style->options` — Style options (public property)
- `$style->error` — Error output flag (public property)
- `$style->linesBefore` — Lines before (public property)
- `$style->linesAfter` — Lines after (public property)
- `$style->tabs` — Tab count (public property)
- `$style->backspaces` — Backspace count (public property)
- `$style->apply(?string $message, Session $session): void` — Apply style to message

**Adapter:**
- `Adapter::hasStty(): bool` — Check if stty available
- `Adapter::setStty(string $config): void` — Set stty configuration
- `Adapter::getShellWidth(): int` — Get shell width
- `Adapter::getShellHeight(): int` — Get shell height
- `Adapter::canColorShell(): bool` — Check if shell supports colors

## Dependencies

### Decode Labs

- **`decodelabs/coercion`**: Required. Used for type coercion in data handling.
- **`decodelabs/deliverance`**: Required. Provides `Broker` interface for I/O stream management.
- **`decodelabs/exceptional`**: Required. Used for exception handling throughout the package.
- **`decodelabs/kingdom`**: Required. Used for service container integration (`Service` interface).

### External

- **PHP**: See `composer.json` for supported PHP versions.
- **`psr/log`**: Required. PSR-3 logging interface (^3.0.2).

## Behaviour & Contracts

### Invariants

- Default session created lazily on first access.
- ANSI support detected automatically based on terminal capabilities.
- stty support detected automatically on Unix systems.
- stty settings reset to original values on session destruction.
- Windows is not supported (throws exception).
- Style parsing follows specific format: `<modifiers>foreground?|background?|option1?|option2?...`
- Widgets require ANSI support for advanced features (fallback to basic output otherwise).
- Cursor operations require ANSI support (return `false` if not available).
- Progress bar precision calculated automatically if not specified.

### Input & Output Contracts

**Session Creation:**
- Default session uses CLI broker if `STDOUT` defined, otherwise HTTP broker.
- Custom session can be created with custom broker.
- Adapter loaded based on OS (Unix only, Windows throws exception).
- ANSI support detected via adapter.
- stty support detected via adapter if ANSI available.

**Input Operations:**
- Read operations use Deliverance broker.
- `read()` reads specified number of bytes.
- `readLine()` reads until newline.
- `readChar()` reads single character.
- `readAll()` reads all available data.
- Input blocking controlled via `readBlocking` property.

**Output Operations:**
- Write operations use Deliverance broker.
- `write()` writes data without newline.
- `writeLine()` writes data with newline.
- Error output operations write to error stream.
- Buffer operations write buffer contents.

**ANSI Styling:**
- Style format: `<modifiers>foreground?|background?|option1?|option2?...`
- Modifiers:
  - `^` — Clear line(s) above
  - `+` — Add lines before
  - `.` — Add lines after
  - `>` — Add tabs before
  - `<` — Backspace previous output
  - `!` — Consider error output
  - `!!` — Not error output
- Foreground/background colors:
  - Named colors: `black`, `red`, `green`, `yellow`, `blue`, `magenta`, `cyan`, `white`, `reset`
  - Bright colors: `brightBlack`, `brightRed`, `brightGreen`, `brightYellow`, `brightBlue`, `brightMagenta`, `brightCyan`, `brightWhite`
  - 8-bit: `:0` to `:255`
  - 24-bit hex: `#000000` to `#FFFFFF`
- Options: `bold`, `dim`, `italic`, `underline`, `blink`, `strobe`, `reverse`, `private`, `strike`
- Dynamic method calls: `$io->{'color'}(message)` applies color style.
- Style parsing throws exception on invalid format.

**Cursor Control:**
- Cursor operations require ANSI support.
- Cursor position queries use ANSI escape sequences.
- Cursor position format: `[line, column]`.
- Cursor save/restore uses terminal memory.

**Line Control:**
- Line operations require ANSI support (except `newLine`, `backspace`, `tab`).
- Delete line: moves cursor up and clears line.
- Clear line: clears entire line or portion.
- Backspace: moves cursor back and clears character.

**stty Operations:**
- stty operations require stty availability.
- Snapshot captures current stty settings.
- Restore restores from snapshot.
- Reset restores to original settings at session start.
- Input echo toggle: `-echo` / `echo`.
- Input buffer toggle: `-icanon` / `icanon`.

**Widgets:**
- Question: prompts with message, options, default, and validation.
- Password: prompts with hidden input (stty) or visible input (fallback).
- Confirmation: prompts yes/no with default.
- Spinner: animated spinner with configurable style.
- Progress bar: progress bar with min/max, percent, and completion display.

**Logging:**
- PSR-3 logging interface implementation.
- Log levels: `debug`, `info`, `notice`, `comment`, `success`, `operative`, `deleteSuccess`, `warning`, `error`, `critical`, `alert`, `emergency`.
- Each level has styled prefix and formatting.
- Inline variants write without newline.
- Context interpolation: `{key}` replaced with context values.

**Output Capture:**
- Capture wraps executor and captures output.
- ANSI disabled during capture.
- Buffer added to broker during capture.
- Result and error stored in capture object.
- Buffer removed after capture.

**String to Boolean:**
- Converts string to boolean with common values.
- `true`: `'true'`, `'1'`, `'yes'`, `'y'`, `'on'`, `'enabled'`.
- `false`: `'false'`, `'0'`, `'no'`, `'n'`, `'off'`, `'disabled'`.
- Returns `null` if not recognized (unless default provided).

## Error Handling

- **Windows support**: Constructor throws `ComponentUnavailable` exception if Windows detected.
- **Invalid style**: Style parsing throws `InvalidArgument` exception on invalid format.
- **Invalid color**: Style throws `InvalidArgument` exception on invalid color name.
- **Invalid option**: Style throws `InvalidArgument` exception on invalid option name.
- **Cursor detection failure**: Cursor position queries throw `Runtime` exception if unable to detect.
- **Invalid cursor response**: Cursor position queries throw `InvalidArgument` exception on invalid response format.
- **Invalid wait time**: Spinner `waitFor()` throws `InvalidArgument` exception if time <= 0.
- **Capture error**: Capture stores error in `error` property, `resolve()` throws error.

## Configuration & Extensibility

### Custom Broker

Create custom session with custom broker:

```php
use DecodeLabs\Deliverance;
use DecodeLabs\Terminus\Session;

$io = new Session(
    Deliverance::newIoBroker()
        ->addInputProvider($inputStream)
        ->addOutputReceiver($outputStream)
        ->addErrorReceiver($errorStream)
);
```

### Custom Adapter

Implement `Adapter` interface:

```php
use DecodeLabs\Terminus\Adapter;

class MyAdapter implements Adapter
{
    public function hasStty(): bool
    {
        // Check if stty available
    }

    public function setStty(string $config): void
    {
        // Set stty configuration
    }

    public function getShellWidth(): int
    {
        // Get shell width
    }

    public function getShellHeight(): int
    {
        // Get shell height
    }

    public function canColorShell(): bool
    {
        // Check if colors supported
    }
}
```

Adapter loaded automatically based on OS detection (Unix only currently).

### Custom Widgets

Create custom widgets by implementing similar patterns to existing widgets:

```php
use DecodeLabs\Terminus\Session;

class MyWidget
{
    protected Session $io;

    public function __construct(Session $io)
    {
        $this->io = $io;
    }

    public function prompt(): mixed
    {
        // Widget implementation
    }
}
```

## Interactions with Other Packages

- **Deliverance**: Used for I/O stream management via `Broker` interface. Provides abstraction over STDIN, STDOUT, STDERR.
- **Kingdom**: Used for service container integration. Session implements `Service` interface.
- **Coercion**: Used for type coercion in data handling.
- **Exceptional**: Used for exception handling throughout the package.
- **PSR Log**: Implements PSR-3 `LoggerInterface` for logging operations.

## Usage Examples

### Basic Output

```php
use DecodeLabs\Terminus\Session;

$io = Session::getDefault();

$io->write('Normal text'); // no newline
$io->writeLine(' - end of line'); // with newline
```

### Error Output

```php
$io->writeError('Error text'); // no newline
$io->writeErrorLine(' - end of line'); // with newline
```

### Reading Input

```php
$data = $io->read(3); // Read 3 bytes
$line = $io->readLine(); // Read line
```

### Toggle Input Buffer

```php
$io->toggleInputBuffer(false);
$io->writeLine('Yes or no?');
$char = $io->read(1); // y or n
$io->toggleInputBuffer(true);
```

### Colors and Styles

```php
$io->{'blue'}('This is blue ');
$io->{'yellow'}('This is yellow ');
$io->{'red|green|underline'}(' This is red on green, underlined');
$io->{'+'}('This starts on a new line');
$io->{'.'}('- this ends on a new line');
$io->{'>>'}('This is tabbed, twice!');
$io->{'<'}(' - this backspaces the last character');
$io->writeLine();
$io->{'++>..:146|#CCC|bold|underline'}('A whole mix of parameters');
```

### Check ANSI Support

```php
if ($io->isAnsi()) {
    // ANSI codes supported
}
```

### Line Control

```php
$io->newLine(); // Write to a new line
$io->newLine(5); // Write 5 new lines
$io->deleteLine(); // Delete the previous line
$io->clearLine(); // Clear the current line
$io->clearLineBefore(); // Clear the current line from cursor to start
$io->clearLineAfter(); // Clear the current line from cursor to end
$io->backspace(); // Clear the previous character
$io->tab(); // Write \t to output
```

### Cursor Control

```php
$io->cursorUp(); // Move cursor up vertically
$io->cursorLineUp(); // Move cursor up to start of previous line
$io->cursorDown(); // Move cursor down vertically
$io->cursorLineDown(); // Move cursor down to start of next line
$io->cursorLeft(); // Move cursor left
$io->cursorRight(); // Move cursor right

$io->setCursor(5); // Set cursor horizontally to index 5
$io->setCursorLine(30, 10); // Set absolute cursor position

[$line, $pos] = $io->getCursor(); // Attempt to get absolute cursor position
$pos = $io->getCursorH(); // Attempt to get horizontal cursor position
$line = $io->getCursorV(); // Attempt to get vertical cursor position

$io->saveCursor(); // Store cursor position in terminal memory
$io->restoreCursor(); // Attempt to restore cursor position from terminal memory

$width = $io->getWidth(); // Get line width of terminal
$height = $io->getHeight(); // Get line height of terminal
```

### stty Operations

```php
if ($io->hasStty()) {
    $snapshot = $io->snapshotStty(); // Take a snapshot of current settings
    $io->toggleInputEcho(false);
    // do some stuff

    $io->restoreStty($snapshot); // Restore settings
    // or
    $io->resetStty(); // Reset to original settings at the start of execution
}
```

### Question Widget

```php
$answer = $io->newQuestion(
        message: 'How are you?',
        options: ['Great', 'Fine', 'OK'],
        default: 'great'
    )
    ->prompt();

// Or direct..
$answer = $io->ask(
    message: 'How are you?',
    default: 'great'
);

$io->{'..green'}('You are: '.$answer);
```

### Password Widget

```php
$password = $io->newPasswordQuestion(
        message: 'Now enter a password...',
        repeat: true,
        required: true,
    )
    ->prompt();

// Or direct
$password = $io->askPassword(
    message: 'Now enter a password...',
    repeat: true,
    required: true
);

$io->{'..green'}('Your password is: '.$password);
```

### Confirmation Widget

```php
if ($io->confirm(
    message: 'Do you like green?',
    default: true
)) {
    $io->{'..brightGreen'}('Awesome!');
} else {
    $io->{'..brightRed'}('Boo!');
}
```

### Spinner Widget

```php
$io->{'.'}('Progress spinner: ');
$spinner = $io->newSpinner();

for ($i = 0; $i < 60; $i++) {
    usleep(20000);
    $spinner->advance();
}

$spinner->complete('Done!');
```

### Progress Bar Widget

```php
$io->{'.'}('Progress bar: ');
$spinner = $io->newProgressBar(
    min: 10,
    max: 50
);

for ($i = 0; $i < 80; $i++) {
    usleep(20000);
    $spinner->advance(($i / 2) + 11);
}

$spinner->complete();
```

### PSR-3 Logging

```php
$io->debug('This is a debug');
$io->info('This is an info message');
$io->notice('This is a notice');
$io->success('You\'ve done a success, well done!');
$io->warning('This is a warning');
$io->error('Hold tight, we have an error');
$io->critical('This is CRITICAL');
$io->alert('alert alert alert');
$io->emergency('Oh no this is an emergency!');
```

### Output Capture

```php
$capture = $io->capture(function () {
    $io->writeLine('This will be captured');
    return 'result';
});

echo $capture->buffer; // Output: "This will be captured\n"
$result = $capture->resolve(); // Returns: "result"
```

### Custom Session

```php
use DecodeLabs\Deliverance;
use DecodeLabs\Terminus\Session;

$io = new Session(
    Deliverance::newIoBroker()
        ->addInputProvider($inputStream)
        ->addOutputReceiver($outputStream)
        ->addErrorReceiver($errorStream)
);
```

## Implementation Notes (for Contributors)

### Session Initialization

- Default session created lazily on first `getDefault()` call.
- Session uses CLI broker if `STDOUT` defined, otherwise HTTP broker.
- Adapter loaded based on OS detection (Unix only currently).
- ANSI support detected via adapter `canColorShell()`.
- stty support detected via adapter `hasStty()` if ANSI available.
- Original stty settings captured on session creation.

### Adapter Loading

- Adapter loaded based on `php_uname('s')`.
- Windows detection: checks if OS name starts with 'win'.
- Windows throws `ComponentUnavailable` exception.
- Unix uses `Adapter\Unix` implementation.
- Adapter instance cached in session.

### ANSI Detection

- Color support detected via `stream_isatty()` or `posix_isatty()`.
- Fallback to `TERM` environment variable (`xterm-256color`).
- Fallback to `CLICOLOR` environment variable (`1`).
- Returns `false` if `STDOUT` not defined.

### stty Detection

- stty availability checked via `which stty` command.
- stty settings managed via `system('stty ...')` calls.
- Settings snapshot captured via `stty -g` command.
- Settings restored via `stty` command with snapshot.

### Style Parsing

- Style format: `<modifiers>foreground?|background?|option1?|option2?...`
- Modifiers parsed first (sequential application).
- Colors parsed: named, 8-bit (`:0-255`), 24-bit hex (`#000000-FFFFFF`).
- Options parsed: `bold`, `dim`, `italic`, `underline`, `blink`, `strobe`, `reverse`, `private`, `strike`.
- Invalid format throws `InvalidArgument` exception.

### ANSI Code Generation

- ANSI codes generated based on color bits (4-bit, 8-bit, 24-bit).
- 4-bit: standard ANSI color codes.
- 8-bit: `38;5;{code}` or `48;5;{code}`.
- 24-bit: `38;2;{r};{g};{b}` or `48;2;{r};{g};{b}`.
- Options: set codes and unset codes.
- Format: `\e[{setCodes}m{message}\e[{unsetCodes}m`.

### Cursor Position Detection

- Cursor position queried via ANSI escape sequence `\e[6n`.
- Response format: `\e[{line};{column}R`.
- Requires stty support for input buffer/echo control.
- Response parsed via regex.
- Throws exception if unable to detect or invalid response.

### Widget Implementation

- Question: renders message with options, validates input, supports confirmation.
- Password: hides input via stty echo toggle, supports repeat confirmation.
- Confirmation: single character input (y/n), supports default, handles Ctrl+C.
- Spinner: animated character rotation, throttled by tick interval.
- Progress bar: calculates percentage, renders bar with completion values.

### Logging Implementation

- PSR-3 interface implementation.
- Each log level has prefix icon and style.
- Context interpolation: `{key}` replaced with context values.
- Inline variants write without newline.
- Unknown levels write plain message.

### Output Capture

- Capture wraps executor in try-catch.
- Buffer added to broker before execution.
- ANSI disabled during capture.
- Buffer removed after execution.
- Result and error stored in capture object.

### String to Boolean

- Converts common string values to boolean.
- Case-insensitive matching.
- Returns `null` if not recognized (unless default provided).
- Used by confirmation widget.

## Testing & Quality

**Current Status:**
- Code quality: 4.5/5
- README quality: 4/5
- Documentation: 0/5 (no formal docs yet)
- Tests: 0/5 (no test suite yet)

**Testing Considerations:**
- Session creation should be tested for:
  - Default session creation
  - Custom broker session creation
  - Adapter loading (Unix)
  - Windows detection (should throw exception)

- Input operations should be tested for:
  - Reading bytes
  - Reading lines
  - Reading characters
  - Reading all data
  - Input blocking mode

- Output operations should be tested for:
  - Writing data
  - Writing lines
  - Error output
  - Buffer operations

- ANSI styling should be tested for:
  - Style parsing (valid and invalid)
  - Color application
  - Option application
  - Modifier application
  - Dynamic method calls

- Cursor control should be tested for:
  - Cursor movement
  - Cursor positioning
  - Cursor position detection
  - Cursor save/restore

- Line control should be tested for:
  - Line clearing
  - Line deletion
  - Backspace
  - Tab

- stty operations should be tested for:
  - stty detection
  - stty snapshot/restore
  - Input echo toggle
  - Input buffer toggle

- Widgets should be tested for:
  - Question prompt (with options, default, validation)
  - Password prompt (with repeat)
  - Confirmation prompt (with default)
  - Spinner animation
  - Progress bar display

- Logging should be tested for:
  - All log levels
  - Context interpolation
  - Inline variants
  - Unknown levels

- Output capture should be tested for:
  - Output capture
  - Error capture
  - Result capture

- Edge cases should be tested for:
  - Non-ANSI terminals
  - Missing stty
  - Invalid style strings
  - Invalid cursor responses
  - Empty input
  - Large output

## Roadmap & Future Ideas

- **Windows support**: Add Windows adapter for Windows terminal support
- **More widgets**: Additional interactive widgets (tables, menus, etc.)
- **Terminal multiplexing**: Support for screen/tmux-like features
- **Better Windows compatibility**: Improved Windows terminal detection and support
- **Performance optimization**: Optimization for large output operations
- **Better error messages**: More detailed error messages for debugging
- **Extended ANSI support**: Support for more ANSI features and escape sequences

## References

- Package repository: https://github.com/decodelabs/terminus
- Composer package: https://packagist.org/packages/decodelabs/terminus
- Related packages:
  - `decodelabs/deliverance` — I/O stream management
  - `decodelabs/commandment` — Command-line argument parsing
  - `decodelabs/systemic` — Process execution
- ANSI escape codes: https://en.wikipedia.org/wiki/ANSI_escape_code

