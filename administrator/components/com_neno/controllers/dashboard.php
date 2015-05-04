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
 * Manifest Groups & Elements controller class
 *
 * @since  1.0
 */
class NenoControllerDashboard extends JControllerAdmin
{
	/**
	 * Toggle language
	 *
	 * @return void
	 */
	public function toggleLanguage()
	{
		$input    = $this->input;
		$language = $input->getString('language');
		$db       = JFactory::getDbo();
		$query    = $db->getQuery(true);

		$query
			->update('#__languages')
			->set('published = (published + 1) % 2')
			->where('lang_code = ' . $db->quote($language));

		$db->setQuery($query);
		$db->execute();

		JFactory::getApplication()->close();
	}

	public function deleteLanguage()
	{

	}

	public function confirmationMessageForLanguageDeletion()
	{
		$input    = $this->input;
		$language = $input->getString('language');
		$db       = JFactory::getDbo();
		$query    = $db->getQuery(true);

		$query
			->update('#__languages')
			->set('published = (published + 1) % 2')
			->where('lang_code = ' . $db->quote($language));

		$db->setQuery($query);
		$db->execute();
	}
}
