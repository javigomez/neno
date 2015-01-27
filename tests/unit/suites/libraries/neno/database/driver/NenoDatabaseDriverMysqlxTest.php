<?php

/**
 * @package     Neno.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class NenoDatabaseDriverMysqlxTest extends TestCaseDatabaseMysqli
{
	/**
	 * This method will check if the driver creates the shadow tables properly.
	 */
	public function testCreateShadowTables()
	{
		$result = false;

		try
		{
			self::$driver->createShadowTables('#__content');
			$result = true;
		}
		catch (Exception $e)
		{
			$result = false;
		}

		$this->assertTrue($result);
	}

	/**
	 *
	 */
	public function testSetAutoincrementIndex()
	{
		$defaultLanguage = JFactory::getLanguage()->getDefault();
		$knownLanguages  = NenoHelper::getLanguages();

		foreach ($knownLanguages as $knownLanguage)
		{
			if ($knownLanguage->lang_code !== $defaultLanguage)
			{
				$shadowTableName = NenoDatabaseParser::generateShadowTableName('#__content', $knownLanguage->lang_code);
				$this->assertTrue(self::$driver->setAutoincrementIndex('#__content', $shadowTableName), 'Something ');
			}
		}
	}
}
