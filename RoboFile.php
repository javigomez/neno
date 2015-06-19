<?php

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */

require_once 'vendor/autoload.php';

class RoboFile extends \Robo\Tasks
{
	// Load tasks from composer, see composer.json
	use \joomla_projects\robo\loadTasks;

	private $extension = '';

	public function testAcceptance($seleniumPath = null)
	{
		$this->packNeno();

		$this->setExecExtension();
		// Get Joomla Clean Testing sites
		if (is_dir('tests/joomla-cms3'))
		{
			$this->taskDeleteDir('tests/joomla-cms3')->run();
		}
		$this->_exec('git' . $this->extension . ' clone -b staging --single-branch --depth 1 https://github.com/joomla/joomla-cms.git tests/joomla-cms3');
		$this->say('Joomla CMS site created at tests/joomla-cms3');
		if (!$seleniumPath)
		{
			if (!file_exists('selenium-server-standalone.jar'))
			{
				$this->say('Downloading Selenium Server, this may take a while.');
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
				{
					$this->_exec('curl.exe -sS http://selenium-release.storage.googleapis.com/2.45/selenium-server-standalone-2.45.0.jar > selenium-server-standalone.jar');
				}
				else
				{
					$this->taskExec('wget')
						->arg('http://selenium-release.storage.googleapis.com/2.45/selenium-server-standalone-2.45.0.jar')
						->arg('-O selenium-server-standalone.jar')
						->printed(false)
						->run();
				}
			}
			$seleniumPath = 'selenium-server-standalone.jar';
		}
		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
		{
			$seleniumPath = "java -jar $seleniumPath >> selenium.log 2>&1 &";
		}
		else
		{
			$seleniumPath = "START java.exe -jar .\\" . $seleniumPath;
		}

		// Make sure we have Composer
		if (!file_exists('./composer.phar'))
		{
			$this->_exec('curl' . $this->extension . ' -sS https://getcomposer.org/installer | php');
		}
		$this->taskComposerUpdate()->run();
		// Running Selenium server
		$this->_exec($seleniumPath);
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			sleep(10);
		}
		else
		{
			$this->taskWaitForSeleniumStandaloneServer()
				->run()
				->stopOnFail();
		}

		// Loading Symfony Command and running with passed argument
		$this->_exec('php' . $this->extension . ' vendor/bin/codecept build');
		$this->taskCodecept()
			->suite('acceptance')
			->arg('--steps')
			->arg('--debug')
			->run()
			->stopOnFail();
		// Kill selenium server
		// $this->_exec('curl http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer');
		/*
		// Uncomment this lines if you need to debug selenium errors
		$seleniumErrors = file_get_contents('selenium.log');
		if ($seleniumErrors) {
			$this->say('Printing Selenium Log files');
			$this->say('------ selenium.log (start) ---------');
			$this->say($seleniumErrors);
			$this->say('------ selenium.log (end) -----------');
		}
		*/
	}

	public function packNeno()
	{
		$this
			->taskExecStack()
			->exec('php /home/travis/build/Jensen-Technologies/neno/pack.php')
			->run();
	}

	/**
	 * Set the Executeextension
	 *
	 * @return void
	 */
	public function setExecExtension()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			$this->extension = '.exe';
		}
	}

	public function sendEmail()
	{
		$rootPath = realpath('/home/travis/build/Jensen-Technologies/neno/tests/_output');
		$zipPath  = '/home/travis/build/Jensen-Technologies/neno/tests/output.zip';

		// Initialize archive object
		$zip = new ZipArchive();
		$zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($files as $name => $file)
		{
			// Skip directories (they would be added automatically)
			if (!$file->isDir())
			{
				// Get real and relative path for current file
				$filePath     = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);

				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
		}

		// Zip archive will be created only after closing object
		$zip->close();

		echo "File created\n";

		$email           = new PHPMailer();
		$email->From     = 'support@neno-translate.com';
		$email->FromName = 'Neno Test';
		$email->Subject  = 'Test fails';
		$email->Body     = '';
		$email->AddAddress('victor@notwebdesign.com');
		$email->AddAttachment($zipPath, 'output.zip');

		if ($email->Send())
		{
			echo "Email sent\n";
		}
	}
}