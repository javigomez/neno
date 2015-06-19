<?php

/**
 * Created by PhpStorm.
 * User: victor
 * Date: 19/06/15
 * Time: 15:54
 */
class NenoExtension extends \Codeception\Platform\Extension
{
	// list events to listen to
	public static $events = array (
		'test.fail' => 'testFailed',
	);

	public function testFailed(\Codeception\Event\FailEvent $e)
	{
		print_r(realpath(dirname(__FILE__) . '../_output'));
	}
}