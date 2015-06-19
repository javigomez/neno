<?php

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;

class NenoExtension extends \Codeception\Platform\Extension
{
	// list events to listen to
	public static $events = array (
		Events::TEST_FAIL => 'testFailed',
	);

	public function _initialize()
	{
		$this->options['silent'] = false;
	}


	public function testFailed(\Codeception\Event\FailEvent $e)
	{
		echo "HOLA";
		print_r(realpath(dirname(__FILE__) . '../_output'));
	}
}