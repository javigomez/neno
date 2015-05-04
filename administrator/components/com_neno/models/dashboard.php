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
class NenoModelDashboard extends JModelList
{
	/**
	 * {@inheritdoc}
	 *
	 * @return array
	 */
	public function getItems()
	{
		/* @var $db NenoDatabaseDriverMysqlx */
		$db = JFactory::getDbo();
		$db->setQuery($this->getListQuery());

		$languages = $db->loadObjectListMultiIndex('lang_code');
		$items     = array ();

		foreach ($languages as $language)
		{
			$translated      = 0;
			$queued          = 0;
			$changed         = 0;
			$untranslated    = 0;
			$item            = new stdClass;
			$item->lang_code = $language[0]->lang_code;
			$item->published = $language[0]->published;
			$item->title     = $language[0]->title;
			$item->image     = $language[0]->image;

			foreach ($language as $internalItem)
			{
				switch ($internalItem->state)
				{
					case NenoContentElementTranslation::TRANSLATED_STATE:
						$untranslated = (int) $internalItem->word_count;
						break;
					case NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE:
						$untranslated = (int) $internalItem->word_count;
						break;
					case NenoContentElementTranslation::SOURCE_CHANGED_STATE:
						$untranslated = (int) $internalItem->word_count;
						break;
					case NenoContentElementTranslation::NOT_TRANSLATED_STATE:
						$untranslated = (int) $internalItem->word_count;
						break;
				}
			}

			$item->wordCount               = new stdClass;
			$item->wordCount->translated   = $translated;
			$item->wordCount->queued       = $queued;
			$item->wordCount->changed      = $changed;
			$item->wordCount->untranslated = $untranslated;
			$item->wordCount->total        = $translated + $queued + $changed + $untranslated;

			$items[] = $item;
		}

		return $items;
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
// Create a new query object.
		$db    = JFactory::getDbo();
		$query = parent::getListQuery();

		$query
			->select(
				array (
					'l.lang_code',
					'l.published',
					'l.title',
					'l.image',
					'tr.state',
					'SUM(tr.word_counter) AS word_count'
				)
			)
			->from('#__languages AS l')
			->leftJoin('#__neno_content_element_translations AS tr ON tr.language = l.lang_code')
			->where('l.lang_code <> ' . $db->quote(JFactory::getLanguage()->getDefault()))
			->group(
				array (
					'l.lang_code',
					'tr.state'
				)
			)
			->order('state');

		return $query;
	}
}
