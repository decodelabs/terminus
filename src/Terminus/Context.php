<?php
/**
 * This file is part of the Terminus package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Terminus;

use DecodeLabs\Veneer\FacadeTarget;
use DecodeLabs\Veneer\FacadeTargetTrait;
use DecodeLabs\Veneer\FacadePlugin;

use DecodeLabs\Glitch;

class Context implements FacadeTarget
{
    use FacadeTargetTrait;

    const FACADE = 'Terminus';
}
