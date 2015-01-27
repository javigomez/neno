<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

if (!class_exists('PHPUnit_Extensions_Database_TestCase'))
{
	require_once 'PHPUnit/Extensions/Database/TestCase.php';
	require_once 'PHPUnit/Extensions/Database/DataSet/XmlDataSet.php';
	require_once 'PHPUnit/Extensions/Database/DataSet/QueryDataSet.php';
	require_once 'PHPUnit/Extensions/Database/DataSet/MysqlXmlDataSet.php';
}

/**
 * Abstract test case class for database testing.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
abstract class TestCaseDatabase extends PHPUnit_Extensions_Database_TestCase
{
	/**
	 * @var    JDatabaseDriver  The active database driver being used for the tests.
	 * @since  12.1
	 */
	protected static $driver;

	/**
	 * @var    JDatabaseDriver  The saved database driver to be restored after these tests.
	 * @since  12.1
	 */
	private static $stash;

	/**
	 * @var    array  Various JFactory static instances stashed away to be restored later.
	 * @since  12.1
	 */
	private $stashedFactoryState = array(
		'application' => null,
		'config'      => null,
		'dates'       => null,
		'session'     => null,
		'language'    => null,
		'document'    => null,
		'acl'         => null,
		'mailer'      => null
	);

	/**
	 * This method is called after the last test of this test class is run.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public static function tearDownAfterClass()
	{
		JFactory::$database = self::$stash;
		self::$driver       = null;
	}

	/**
	 * Assigns mock callbacks to methods.
	 *
	 * @param   PHPUnit_Framework_MockObject_MockObject $mockObject The mock object.
	 * @param   array                                   $array      An array of methods names to mock with callbacks.
	 *                                                              This method assumes that the mock callback is named {mock}{method name}.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function assignMockCallbacks($mockObject, $array)
	{
		foreach ($array as $index => $method)
		{
			if (is_array($method))
			{
				$methodName = $index;
				$callback   = $method;
			}
			else
			{
				$methodName = $method;
				$callback   = array(get_called_class(), 'mock' . $method);
			}

			$mockObject->expects($this->any())
				->method($methodName)
				->willReturnCallback($callback);
		}
	}

	/**
	 * Assigns mock values to methods.
	 *
	 * @param   PHPUnit_Framework_MockObject_MockObject $mockObject The mock object.
	 * @param   array                                   $array      An associative array of methods to mock with return values:<br />
	 *                                                              string (method name) => mixed (return value)
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function assignMockReturns($mockObject, $array)
	{
		foreach ($array as $method => $return)
		{
			$mockObject->expects($this->any())
				->method($method)
				->willReturn($return);
		}
	}

	/**
	 * Gets a mock configuration object.
	 *
	 * @return  JConfig
	 *
	 * @since   12.1
	 */
	public function getMockConfig()
	{
		return TestMockConfig::create($this);
	}

	/**
	 * Gets a mock database object.
	 *
	 * @param   string $driver       Optional driver to create a sub-class of JDatabaseDriver
	 * @param   array  $extraMethods An array of additional methods to add to the mock
	 * @param   string $nullDate     A null date string for the driver.
	 * @param   string $dateFormat   A date format for the driver.
	 *
	 * @return  JDatabaseDriver
	 *
	 * @since   12.1
	 */
	public function getMockDatabase($driver = '', array $extraMethods = array(), $nullDate = '0000-00-00 00:00:00', $dateFormat = 'Y-m-d H:i:s')
	{
		// Attempt to load the real class first.
		class_exists('NenoDatabaseDriverMysqlx');

		return TestMockDatabaseDriver::create($this, $driver, $extraMethods, $nullDate, $dateFormat);
	}

	protected function getDataSet()
	{
		return new PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
	}

	/**
	 * Sets the Factory pointers
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function restoreFactoryState()
	{
		JFactory::$application = $this->stashedFactoryState['application'];
		JFactory::$config      = $this->stashedFactoryState['config'];
		JFactory::$dates       = $this->stashedFactoryState['dates'];
		JFactory::$session     = $this->stashedFactoryState['session'];
		JFactory::$language    = $this->stashedFactoryState['language'];
		JFactory::$document    = $this->stashedFactoryState['document'];
		JFactory::$mailer      = $this->stashedFactoryState['mailer'];
	}

	/**
	 * Saves the Factory pointers
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function saveFactoryState()
	{
		$this->stashedFactoryState['application'] = JFactory::$application;
		$this->stashedFactoryState['config']      = JFactory::$config;
		$this->stashedFactoryState['dates']       = JFactory::$dates;
		$this->stashedFactoryState['session']     = JFactory::$session;
		$this->stashedFactoryState['language']    = JFactory::$language;
		$this->stashedFactoryState['document']    = JFactory::$document;
		$this->stashedFactoryState['acl']         = JFactory::$acl;
		$this->stashedFactoryState['mailer']      = JFactory::$mailer;
	}

	/**
	 * Returns the default database connection for running the tests.
	 *
	 * @return  PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
	 *
	 * @since   12.1
	 */
	protected function getConnection()
	{
		if (!is_null(self::$driver))
		{
			return $this->createDefaultDBConnection(self::$driver->getConnection(), ':memory:');
		}
		else
		{
			return null;
		}
	}
}