<?php
/**
 * @copyright  Copyright (c) 2010-2015 Matthew Weier O'Phinney <matthew@weierophinney.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace Phly\Mustache\Exception;

use RuntimeException;

/**
 * Exception raised when attempting to fetch a resolver by type, and one is not found.
 */
class ResolverTypeNotFoundException extends RuntimeException implements ExceptionInterface
{
}
