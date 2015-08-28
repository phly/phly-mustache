<?php
/**
 * phly-mustache
 *
 * @category   PhlyTest
 * @package    phly-mustache
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2010 Matthew Weier O'Phinney <mweierophinney@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/** @namespace */
namespace PhlyTest\Mustache\TestAsset;

/**
 * View containing a "higher order" section
 *
 * @category   Phly
 * @package    phly-mustache
 * @subpackage UnitTests
 */
class ViewWithHigherOrderSection
{
    public $name = 'Tater';

    public function bolder()
    {
        return function ($text, $renderer) {
            return '<b>' . call_user_func($renderer, $text) . '</b>';
        };
    }
}
