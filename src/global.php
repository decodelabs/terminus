<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);

/**
 * global helpers
 */
namespace DecodeLabs\Terminus
{
    use DecodeLabs\Terminus as Facade;
    use DecodeLabs\Terminus\Context;

    Context::registerFacade(Facade::class);
}
