<?php
/**
 * @package     Neno
 * @subpackage  Models
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * NenoModelGroupsElements class
 *
 * @since  1.0
 */
class NenoModelExternalTranslations extends JModelList
{
	/**
	 * Get the amount of link credits available
	 *
	 * @return int
	 */
	public function getTC()
	{
		return 9000;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$query = parent::getListQuery();

		$query
			->select(
				array (
					'SUM(word_counter) AS words',
					'translation_method',
					'language'
				)
			)
			->from('#__neno_content_element_translations')
			->where('state = ' . NenoContentElementTranslation::NOT_TRANSLATED_STATE)
			->group(
				array (
					'translation_method',
					'language'
				)
			);

		return $query;
	}
}
