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
 * View with an embedded array enumerable for iteration
 *
 * @category   Phly
 * @package    phly-mustache
 * @subpackage UnitTests
 */
class ViewWithArrayEnumerable
{
    public $name = "Joe's shopping card";
    public $items = array(
        array('item' => 'bananas'),
        array('item' => 'apples'),
    );
}
