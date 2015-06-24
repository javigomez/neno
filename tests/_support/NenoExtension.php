<?php

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;

require_once realpath(dirname(__FILE__) . '/../../vendor/autoload.php');

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
		// Upload image
		Cloudinary::config(
			array (
				'cloud_name' => $this->config['cloud_name'],
				'api_key'    => $this->config['api_key'],
				'api_secret' => $this->config['api_secret']
			)
		);

		$result = \Cloudinary\Uploader::upload(realpath(dirname(__FILE__) . '/../_output/InstallNenoCest.installNeno.fail.png'));

/*
		// Create Github issue
		$client = new \Github\Client();
		$token  = $this->config['token'];

		$client->authenticate($token, \Github\Client::AUTH_URL_TOKEN);

		$client
			->api('issue')
			->create('Jensen-Technologies', 'neno', array ('title' => 'Issue testing Neno', 'body' => $result['secure_url']));*/
	}
}