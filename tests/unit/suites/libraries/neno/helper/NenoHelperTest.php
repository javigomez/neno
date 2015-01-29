<?php

/**
 * @package     Neno.Test
 * @subpackage  Helper
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class NenoHelperTest extends TestCase
{
	/**
	 * @dataProvider filesProvider
	 */
	public function testIsJoomlaCoreLanguageFile($file, $expectedResult)
	{
		$this->assertEquals($expectedResult, NenoHelper::isJoomlaCoreLanguageFile($file), 'Error detecting language file');
	}

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