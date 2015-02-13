<?php

/**
 * @package     Neno.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class NenoDatabaseDriverMysqlxTest
 *
 * @since  1.0
 */
class NenoDatabaseDriverMysqlxTest extends TestCase
{
	/**
	 * This method will check if the driver creates the shadow tables properly.
	 *
	 * @dataProvider testDataProvider
	 *
	 * @param   string $tableName Table Name
	 *
	 * @return void
	 */
	public function testCreateShadowTables($tableName)
	{
		try
		{
			self::$driver->createShadowTables($tableName);
			$result = true;
		}
		catch (Exception $e)
		{
			$result = false;
		}

		$this->assertTrue($result);
	}

	/**
	 * Method to generate data to perform tests
	 *
	 * @return array
	 */
	public function testDataProvider()
	{
		return array(
			array('#__content'),
			array('#__categories'),
			array('#__banners'),
			array('#__extensions'),
			array('#__tags')
		);
	}

	/**
	 * Check if the method to set the Autoincrement property works
	 *
	 * @param   string $tableName Table Name
	 *
	 * @dataProvider testDataProvider
	 *
	 * @return void
	 */
	public function testSetAutoincrementIndex($tableName)
	{
		$defaultLanguage = JFactory::getLanguage()->getDefault();
		$knownLanguages  = NenoHelper::getLanguages();

		foreach ($knownLanguages as $knownLanguage)
		{
			if ($knownLanguage->lang_code !== $defaultLanguage)
			{
				$shadowTableName = NenoDatabaseParser::generateShadowTableName($tableName, $knownLanguage->lang_code);
				$this->assertTrue(self::$driver->setAutoincrementIndex($tableName, $shadowTableName), 'Something ');
			}
		}
	}
}
