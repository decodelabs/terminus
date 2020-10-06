<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

/**
 * global helpers
 */
namespace DecodeLabs\Terminus
{
    use DecodeLabs\Terminus;
    use DecodeLabs\Veneer;

    Veneer::register(Context::class, Terminus::class);
}
