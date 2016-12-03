<?php
/**
 * Client for Freepaid's SOAP interface Tests
 *
 * @author    Jacques Marneweck <jacques@powertrip.co.za>
 * @copyright 2013-2016 Jacques Marneweck.  All rights strictly reserved.
 * @package   Freepaid
 * @license   MIT
 */

require_once __DIR__.'/../vendor/autoload.php';
\VCR\VCR::configure()
    ->enableRequestMatchers(array('method', 'url', 'host'))
    ->setStorage('json');
\VCR\VCR::turnOn();
