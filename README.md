# Terminus

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/terminus?style=flat)](https://packagist.org/packages/decodelabs/terminus)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/terminus.svg?style=flat)](https://packagist.org/packages/decodelabs/terminus)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/terminus.svg?style=flat)](https://packagist.org/packages/decodelabs/terminus)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/decodelabs/terminus/PHP%20Composer)](https://github.com/decodelabs/terminus/actions/workflows/php.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/terminus?style=flat)](https://packagist.org/packages/decodelabs/terminus)


Simple CLI interactions for PHP

## Installation
```bash
composer require decodelabs/terminus
```

## Usage

Terminus uses [Veneer](https://github.com/decodelabs/veneer) to provide a unified frontage under <code>DecodeLabs\Terminus</code>.
You can access all the primary functionality via this static frontage without compromising testing and dependency injection.

### Session

Terminus will by default create a standard session communicating via PHP's <code>STDIN</code>, <code>STDOUT</code> and <code>STDERR</code> streams, with arguments from <code>$\_SERVER['argv']</code>.

You can however customise the session by creating your own and setting it via the main <code>Terminus</code> frontage.
See [Deliverance Broker](https://github.com/decodelabs/atlas) for more information about controlling IO streams.

```php
use DecodeLabs\Deliverance;
use DecodeLabs\Terminus as Cli;

$session = Cli::newSession(
    Cli::newRequest(['list', 'of', 'argv', 'params']),

    // The Io Broker is optional, defaults to best fit
    Deliverance::newIoBroker()
        ->addInputProvider($inputStream)
        ->addOutputReceiver($outputStream)
        ->addErrorReceiver($errorStream)
);

Cli::setSession($session);
```

### Writing output

Write standard text to output:

```php
use DecodeLabs\Terminus as Cli;

Cli::write('Normal text'); // no newline
Cli::writeLine(' - end of line'); // with newline
```

Error output works the same way, with <code>Error</code> in the method name:

```php
use DecodeLabs\Terminus as Cli;

Cli::writeError('Error text'); // no newline
Cli::writeErrorLine(' - end of line'); // with newline
```

### Reading input

Read input from the user:
Note, PHP by default buffers the input stream so input requires return to be pressed before it can be read.

```php
use DecodeLabs\Terminus as Cli;

$data = Cli::read(3); // Read 3 bytes
$line = Cli::readLine();
```

If the connected terminal supports <code>stty</code> (most Unix emulators), buffering can be turned off for instant input:

```php
use DecodeLabs\Terminus as Cli;

Cli::toggleInputBuffer(false);
Cli::writeLine('Yes or no?')
$char = Cli::read(1); // y or n
Cli::toggleInputBuffer(true);
```

More on extended <code>ANSI</code> and <code>stty</code> support below.


### Colors and styles
If the connected terminal can support <code>ANSI</code> codes can be styled easily using a handy shortcut on the facade:

```php
use DecodeLabs\Terminus as Cli;

Cli::{'blue'}('This is blue ');
Cli::{'yellow'}('This is yellow ');
Cli::{'red|green|underline'}(' This is red on green, underlined');
Cli::{'+'}('This starts on a new line');
Cli::{'.'}('- this ends on a new line');
Cli::{'>>'}('This is tabbed, twice!');
Cli::{'<'}(' - this backspaces the last character');
Cli::writeLine();
Cli::{'++>..:146|#CCC|bold|underline'}
```

Support for <code>ANSI</code> codes can be checked with:

```php
use DecodeLabs\Terminus as Cli;

if(Cli::isAnsi()) {
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
use DecodeLabs\Terminus as Cli;

Cli::newLine(); // Write to a new line
Cli::newLine(5); // Write 5 new lines
Cli::deleteLine(); // Delete the previous line
Cli::clearLine(); // Clear the current line
Cli::clearLineBefore(); // Clear the current line from cursor to start
Cli::clearLineAfter(); // Clear the current line from cursor to end
Cli::backspace(); // Clear the previous character
Cli::tab(); // Write \t to output

Cli::cursorUp(); // Move cursor up vertically
Cli::cursorLineUp(); // Move cursor up to start of previous line
Cli::cursorDown(); // Move cursor down vertically
Cli::cursorLineDown(); // Move cursor down to start of next line
Cli::cursorLeft(); // Move cursor left
Cli::cursorRight(); // Move cursor right

Cli::setCursor(5); // Set cursor horizontally to index 5
Cli::setCursorLine(30, 10); // Set absolute cursor position

[$line, $pos] = Cli::getCursor(); // Attempt to get absolute cursor position
$pos = Cli::getCursorH(); // Attempt to get horizontal cursor position
$line = Cli::getCursorV(); // Attempt to get vertical cursor position

Cli::saveCursor(); // Store cursor position in terminal memory
Cli::restoreCursor(); // Attempt to restore cursor position from terminal memory

$width = Cli::getWidth(); // Get line width of terminal
$height = Cli::getHeight(); // Get line height of terminal
```

### stty
Some extended functionality is dependent on <code>stty</code> being available (most Unix emulators).

```php
use DecodeLabs\Terminus as Cli;

Cli::toggleInputEcho(false); // Hide input characters
Cli::toggleInputBuffer(false); // Don't wait on return key for input
```

<code>stty</code> can be controlled with the following methods:

```php
use DecodeLabs\Terminus as Cli;

if(Cli::hasStty()) {
    $snapshot = Cli::snapshotStty(); // Take a snapshot of current settings
    Cli::toggleInputEcho(false);
    // do some stuff

    Cli::restoreStty($snapshot); // Restore settings
    // or
    Cli::resetStty(); // Reset to original settings at the start of execution
}
```


### Widgets

Simplify common use cases with built in widgets:

#### Question
```php
use DecodeLabs\Terminus as Cli;

$answer = Cli::newQuestion('How are you?')
    ->setOptions('Great', 'Fine', 'OK')
    ->setDefaultValue('great')
    ->prompt();


// Or direct..
$answer = Cli::ask('How are you?', 'great');

Cli::{'..green'}('You are: '.$answer);
```

#### Password
```php
$password = Cli::newPasswordQuestion('Now enter a password...')
    ->setRequired(true)
    ->setRepeat(true)
    ->prompt();

// Or direct
$password = Cli::askPassword('Now enter a password...', true, true);

Cli::{'..green'}('Your password is: '.$password);
```

#### Confirmation
```php
use DecodeLabs\Terminus as Cli;

if (Cli::confirm('Do you like green?', true)) {
    Cli::{'..brightGreen'}('Awesome!');
} else {
    Cli::{'..brightRed'}('Boo!');
}
```

#### Spinner
```php
use DecodeLabs\Terminus as Cli;

Cli::{'.'}('Progress spinner: ');
$spinner = Cli::newSpinner();

for ($i = 0; $i < 60; $i++) {
    usleep(20000);
    $spinner->advance();
}

$spinner->complete('Done!');
```

#### Progress bar
```php
use DecodeLabs\Terminus as Cli;

Cli::{'.'}('Progress bar: ');
$spinner = Cli::newProgressBar(10, 50);

for ($i = 0; $i < 80; $i++) {
    usleep(20000);
    $spinner->advance(($i / 2) + 11);
}

$spinner->complete();
```


### Use Terminus as a PSR Logger
```php
use DecodeLabs\Terminus as Cli;

Cli::debug('This is a debug');
Cli::info('This is an info message');
Cli::notice('This is a notice');
Cli::success('You\'ve done a success, well done!');
Cli::warning('This is a warning');
Cli::error('Hold tight, we have an error');
Cli::critical('This is CRITICAL');
Cli::alert('alert alert alert');
Cli::emergency('Oh no this is an emergency!');
```


### Argument parsing
Quickly parse input arguments from the request into the session:

```php
use DecodeLabs\Terminus as Cli;

$session = Cli::prepareCommand(function ($command) {
    $command
        ->setHelp('Test out Terminus functionality')
        ->addArgument('action', 'Unnamed action argument')
        ->addArgument('?-test|t=Test arg', 'Named test argument with default value');
});

$action = $session['action'];
$test = $session['test'];
```


## Licensing
Terminus is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
