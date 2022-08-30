<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;
use DecodeLabs\Veneer\Proxy;
use DecodeLabs\Veneer\ProxyTrait;
use DecodeLabs\Terminus\Context as Inst;
class Terminus implements Proxy { use ProxyTrait;
const VENEER = 'Terminus';
const VENEER_TARGET = Inst::class;};
