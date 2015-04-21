<?php

/**
 * Created by PhpStorm.
 * User: victor
 * Date: 21/04/15
 * Time: 16:28
 */
class NenoHelperApi
{
	/**
	 * @var JHttp
	 */
	protected static $httpClient;

	/**
	 * Return the amount of the link credits available
	 *
	 * @return int
	 */
	public static function getTCAvailable()
	{
		$userData = self::makeApiCall('user');

		if ($userData !== false && is_array($userData))
		{
			return $userData['tcAvailable'];
		}

		return 0;
	}

	/**
	 * Execute API Call
	 *
	 * @param   string $apiCall    API Call
	 * @param   string $method     Http Method
	 * @param   array  $parameters API call parameters
	 *
	 * @return bool|array
	 */
	protected static function makeApiCall($apiCall, $method = 'GET', $parameters = array ())
	{
		self::getHttp();

		$apiEndpoint = NenoSettings::get('api_server_url');
		$licenseCode = NenoSettings::get('license_code');

		if (!empty($apiEndpoint) && !empty($licenseCode))
		{
			$apiResponse = self::$httpClient->get($apiEndpoint . $apiCall, array ('Authorization' => $licenseCode));

			if ($apiResponse->code == 200)
			{
				$data = json_decode($apiResponse->body, true);

				if (!empty($data['response']))
				{
					return $data['response'];
				}
			}

			return false;
		}

		return false;
	}

	/**
	 * Instanciate http client using Singleton approach
	 *
	 * @return void
	 */
	protected static function getHttp()
	{
		if (self::$httpClient === null)
		{
			self::$httpClient = JHttpFactory::getHttp();
		}
	}
}
