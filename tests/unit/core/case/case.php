<?php
/**
 * @package    Neno.Test
 *
 * @copyright  Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Abstract test case class for unit testing.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * Set up things before to execute the very first test
	 *
	 * @return void
	 */
	public static function setUpBeforeClass()
	{
		// Check if the Neno library has been already included
		if (!defined('JPATH_NENO'))
		{
			$nenoLoader = JPATH_LIBRARIES . '/neno/loader.php';
			JLoader::register('NenoLoader', $nenoLoader);

			NenoLoader::init();
		}

	}
}
