<?php
/**
 * @package     Neno.Test
 * @subpackage  Helper
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class NenoHelperTest
 *
 * @since  1.0
 */
class NenoHelperTest extends TestCase
{
	/**
	 * Test method that checks if a file a Joomla Core language file
	 *
	 * @param   string $file           Filename
	 * @param   mixed  $expectedResult The result expected from the test
	 *
	 * @dataProvider filesProvider
	 *
	 * @return void
	 */
	public function testIsJoomlaCoreLanguageFile($file, $expectedResult)
	{
		$this->assertEquals($expectedResult, NenoHelper::isJoomlaCoreLanguageFile($file), 'Error detecting language file');
	}

	/**
	 * This method provides data to test a method
	 *
	 * @return array
	 */
	public function filesProvider()
	{
		return array(
			array(
				'en-GB.com_content.ini',
				true
			)
		);
	}
}