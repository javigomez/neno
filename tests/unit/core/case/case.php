<?php
/**
 * @package    Neno.Testing
 *
 * @copyright  Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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
abstract class TestCase extends PHPUnit_Extensions_Database_TestCase
{
	/**
	 * @var    NenoDatabaseDriverMysqlx  The active database driver being used for the tests.
	 * @since  12.1
	 */
	protected static $driver;

	/**
	 * @var    array  The JDatabaseDriver options for the connection.
	 * @since  12.1
	 */
	private static $options = array('driver' => 'mysqli');

	/**
	 * @var    JDatabaseDriverMysqli  The saved database driver to be restored after these tests.
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
	 * This method is called before the first test of this test class is run.
	 *
	 * An example DSN would be: host=localhost;dbname=joomla_ut;user=utuser;pass=ut1234
	 *
	 * @return  void
	 *
	 * @since   12.1
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

		// Set up parent stuff
		parent::setUpBeforeClass();

		// First let's look to see if we have a DSN defined or in the environment variables.
		if (defined('JTEST_DATABASE_MYSQLI_DSN') || getenv('JTEST_DATABASE_MYSQLI_DSN'))
		{
			$dsn = defined('JTEST_DATABASE_MYSQLI_DSN') ? JTEST_DATABASE_MYSQLI_DSN : getenv('JTEST_DATABASE_MYSQLI_DSN');
		}
		else
		{
			return;
		}

		// First let's trim the mysql: part off the front of the DSN if it exists.
		if (strpos($dsn, 'mysql:') === 0)
		{
			$dsn = substr($dsn, 6);
		}

		// Split the DSN into its parts over semicolons.
		$parts = explode(';', $dsn);

		// Parse each part and populate the options array.
		foreach ($parts as $part)
		{
			list ($k, $v) = explode('=', $part, 2);

			switch ($k)
			{
				case 'host':
					self::$options['host'] = $v;
					break;
				case 'dbname':
					self::$options['database'] = $v;
					break;
				case 'user':
					self::$options['user'] = $v;
					break;
				case 'pass':
					self::$options['password'] = $v;
					break;
			}
		}

		try
		{
			// Attempt to instantiate the driver.
			self::$driver = NenoDatabaseDriver::getInstance(self::$options);
		}
		catch (RuntimeException $e)
		{
			self::$driver = null;
		}

		// If for some reason an exception object was returned set our database object to null.
		if (self::$driver instanceof Exception)
		{
			self::$driver = null;
		}

		// Setup the factory pointer for the driver and stash the old one.
		self::$stash        = JFactory::$database;
		JFactory::$database = self::$driver;
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

	protected function getDataSet()
	{
		return new PHPUnit_Extensions_Database_DataSet_DefaultDataSet;
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
		// Compile the connection DSN.
		$dsn = 'mysql:host=' . self::$options['host'] . ';dbname=' . self::$options['database'];

		// Create the PDO object from the DSN and options.
		$pdo = new PDO($dsn, self::$options['user'], self::$options['password']);

		return $this->createDefaultDBConnection($pdo, self::$options['database']);
	}
}
