# Terminus

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/terminus?style=flat)](https://packagist.org/packages/decodelabs/terminus)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/terminus.svg?style=flat)](https://packagist.org/packages/decodelabs/terminus)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/terminus.svg?style=flat)](https://packagist.org/packages/decodelabs/terminus)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/terminus/integrate.yml?branch=develop)](https://github.com/decodelabs/terminus/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/terminus?style=flat)](https://packagist.org/packages/decodelabs/terminus)


### Simple CLI interactions for PHP

Terminus provides everything you need to build highly interactive, beautiful CLI processes.

---


## Installation

This package requires PHP 8.4 or higher.

Install via Composer:

```bash
composer require decodelabs/terminus
```

## Usage

### Writing output

Write standard text to output:

```php
use DecodeLabs\Terminus\Session;

$io = Session::getDefault();

$io->$write('Normal text'); // no newline
$io->writeLine(' - end of line'); // with newline
```

Error output works the same way, with <code>Error</code> in the method name:

```php
$io->writeError('Error text'); // no newline
$io->writeErrorLine(' - end of line'); // with newline
```

### Reading input

Read input from the user:
Note, PHP by default buffers the input stream so input requires return to be pressed before it can be read.

```php
$data = $io->read(3); // Read 3 bytes
$line = $io->readLine();
```

If the connected terminal supports <code>stty</code> (most Unix emulators), buffering can be turned off for instant input:

```php
$io->toggleInputBuffer(false);
$io->writeLine('Yes or no?')
$char = $io->read(1); // y or n
$io->toggleInputBuffer(true);
```

More on extended <code>ANSI</code> and <code>stty</code> support below.


### Colors and styles
If the connected terminal can support <code>ANSI</code> codes can be styled easily using a handy shortcut on the facade:

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

Support for <code>ANSI</code> codes can be checked with:

```php
if($io->isAnsi()) {
    // do stuff
}
```

The format of the style prefix is as follows:

<code>\<modifiers\>foreground?|background?|option1?|option2?...</code>

Modifiers are applied as many times as they appear sequentially.

- Modifiers:
    - <code>^</code> Clear line(s) above
    - <code>+</code> Add lines before
    - <code>.</code> Add lines after
    - <code>\></code> Add tabs before
    - <code>\<</code> Backspace previous output
    - <code>!</code> Considered an error
    - <code>!!</code> Not considered an error
- Foreground / background
    - <code>black</code> (ANSI)
    - <code>red</code> (ANSI)
    - <code>green</code> (ANSI)
    - <code>yellow</code> (ANSI)
    - <code>blue</code> (ANSI)
    - <code>magenta</code> (ANSI)
    - <code>cyan</code> (ANSI)
    - <code>white</code> (ANSI)
    - <code>reset</code> (ANSI)
    - <code>brightBlack</code> (ANSI)
    - <code>brightRed</code> (ANSI)
    - <code>brightGreen</code> (ANSI)
    - <code>brightYellow</code> (ANSI)
    - <code>brightBlue</code> (ANSI)
    - <code>brightMagenta</code> (ANSI)
    - <code>brightCyan</code> (ANSI)
    - <code>brightWhite</code> (ANSI)
    - <code>:0</code> to <code>:255</code> [8bit color code](https://en.wikipedia.org/wiki/ANSI_escape_code#8-bit)
    - <code>#000000</code> to <code>#FFFFFF</code> 24bit hex color
- Options
    - <code>bold</code>
    - <code>dim</code>
    - <code>italic</code>
    - <code>underline</code>
    - <code>blink</code>
    - <code>strobe</code>
    - <code>reverse</code>
    - <code>private</code>
    - <code>strike</code>

Note, some options are not or only partially supported on many terminal emulators.

### Line control

Directly control lines and the cursor:
All of the below methods allow passing a numeric value to control the number of times it should be applied.

```php
$io->newLine(); // Write to a new line
$io->newLine(5); // Write 5 new lines
$io->deleteLine(); // Delete the previous line
$io->clearLine(); // Clear the current line
$io->clearLineBefore(); // Clear the current line from cursor to start
$io->clearLineAfter(); // Clear the current line from cursor to end
$io->backspace(); // Clear the previous character
$io->tab(); // Write \t to output

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

### stty
Some extended functionality is dependent on <code>stty</code> being available (most Unix emulators).

```php
$io->toggleInputEcho(false); // Hide input characters
$io->toggleInputBuffer(false); // Don't wait on return key for input
```

<code>stty</code> can be controlled with the following methods:

```php
if($io->hasStty()) {
    $snapshot = $io->snapshotStty(); // Take a snapshot of current settings
    $io->toggleInputEcho(false);
    // do some stuff

    $io->restoreStty($snapshot); // Restore settings
    // or
    $io->resetStty(); // Reset to original settings at the start of execution
}
```


### Widgets

Simplify common use cases with built in widgets:

#### Question
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

#### Password
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

#### Confirmation
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

#### Spinner
```php
$io->{'.'}('Progress spinner: ');
$spinner = $io->newSpinner();

for ($i = 0; $i < 60; $i++) {
    usleep(20000);
    $spinner->advance();
}

$spinner->complete('Done!');
```

#### Progress bar
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


### Use Terminus as a PSR Logger
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


### Session

Terminus will by default create a standard session communicating via PHP's <code>STDIN</code>, <code>STDOUT</code> and <code>STDERR</code> streams.

You can however customise the session by creating your own and setting it via the main <code>Terminus</code> frontage.
See [Deliverance Broker](https://github.com/decodelabs/atlas) for more information about controlling IO streams.

```php
use DecodeLabs\Deliverance;
use DecodeLabs\Terminus\Session;

$io = new Session(
    // The Io Broker is optional, defaults to best fit
    Deliverance::newIoBroker()
        ->addInputProvider($inputStream)
        ->addOutputReceiver($outputStream)
        ->addErrorReceiver($errorStream)
);
```

## Licensing
Terminus is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
