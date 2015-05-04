<?php
/**
 * @package     Neno
 * @subpackage  Controllers
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Manifest Strings controller class
 *
 * @since  1.0
 */
class NenoControllerInstallation extends JControllerAdmin
{
	/**
	 * Get languages
	 *
	 * @return void
	 */
	public function getLanguages()
	{
		echo json_encode(NenoHelper::findLanguages());
		JFactory::getApplication()->close();
	}

	/**
	 * Installs languages
	 *
	 * @return void
	 */
	public function installLanguages()
	{
		$input     = $this->input;
		$languages = $input->get('languages', array (), 'ARRAY');

		foreach ($languages as $language)
		{
			NenoHelper::installLanguage($language);
		}

		JFactory::getApplication()->redirect('index.php?option=com_neno&view=installation');
	}

	public function doMenus()
	{
		NenoHelper::createMenuStructure();
	}

	public function checks()
	{
		$app             = JFactory::getApplication();
		$languages       = JFactory::getLanguage()->getKnownLanguages();
		$defaultLanguage = JFactory::getLanguage()->getDefault();

		foreach ($languages as $language)
		{
			if ($language['tag'] != $defaultLanguage)
			{
				if (NenoHelper::isLanguageFileOutOfDate($language['tag']))
				{
					$app->enqueueMessage('Language file of ' . $language['name'] . ' out of date. Please check', 'error');
				}

				if (!NenoHelper::hasContentCreated($language['tag']))
				{
					$app->enqueueMessage('We have detect that ' . $language['name'] . ' language does not have created a content record', 'error');
				}

				$contentCounter = NenoHelper::contentCountInOtherLanguages($language['tag']);

				if ($contentCounter !== 0)
				{
					$app->enqueueMessage('We have detect content in ' . $language['name'] . ' that have not been moved to the shadow tables', 'error');
				}
			}
		}
	}
}
