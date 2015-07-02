<?php

/**
 * @package     Neno
 * @subpackage  Helper
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Class NenoHelperApi
 *
 * @since  1.0
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
		list($status, $userData) = self::makeApiCall('user');

		if ($status !== false && is_array($userData))
		{
			return empty($userData['tcAvailable']) ? 0 : $userData['tcAvailable'];
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
	 * @return array
	 */
	public static function makeApiCall($apiCall, $method = 'GET', $parameters = array ())
	{
		self::getHttp();

		$apiEndpoint    = NenoSettings::get('api_server_url');
		$licenseCode    = NenoSettings::get('license_code');
		$response       = null;
		$responseStatus = false;

		if (!empty($apiEndpoint) && !empty($licenseCode))
		{
			$method = strtolower($method);

			if (method_exists(self::$httpClient, $method))
			{
				if ($method === 'get')
				{
					$apiResponse = self::$httpClient->{$method}($apiEndpoint . $apiCall, array ('Authorization' => $licenseCode), $parameters);
				}
				else
				{
					$apiResponse = self::$httpClient->{$method}(
						$apiEndpoint . $apiCall,
						json_encode($parameters),
						array (
							'Content-Type'  => 'application/json',
							'Authorization' => $licenseCode
						)
					);
				}

				$data = json_decode($apiResponse->body, true);

				if (!empty($data['response']))
				{
					$response = $data;
				}

				if ($apiResponse->code == 200)
				{
					$responseStatus = true;
				}
			}
		}

		return array ($responseStatus, $response);
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
