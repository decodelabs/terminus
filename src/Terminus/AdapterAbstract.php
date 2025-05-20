<?php

/**
 * @package Terminus
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Terminus;

use DecodeLabs\Archetype;
use DecodeLabs\Terminus\Adapter\Unix;

abstract class AdapterAbstract implements Adapter
{
    public static function load(
        ?string $name = null
    ): Adapter {
        if ($name === null) {
            $name = php_uname('s');

            if (substr(strtolower($name), 0, 3) == 'win') {
                $name = 'Windows';
            }
        }

        $class = Archetype::resolve(
            Adapter::class,
            $name,
            Unix::class
        );

        return new $class($name);
    }
}
