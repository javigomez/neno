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
			$translated        = 0;
			$queued            = 0;
			$changed           = 0;
			$untranslated      = 0;
			$item              = new stdClass;
			$item->lang_code   = $language[0]->lang_code;
			$item->comment     = $language[0]->comment;
			$item->published   = $language[0]->published;
			$item->title       = $language[0]->title;
			$item->image       = $language[0]->image;
			$item->errors      = NenoHelper::getLanguageErrors((array) $language[0]);
			$item->isInstalled = NenoHelper::isCompletelyInstall($item->lang_code);

			foreach ($language as $internalItem)
			{
				switch ($internalItem->state)
				{
					case NenoContentElementTranslation::TRANSLATED_STATE:
						$translated = (int) $internalItem->word_count;
						break;
					case NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE:
						$queued = (int) $internalItem->word_count;
						break;
					case NenoContentElementTranslation::SOURCE_CHANGED_STATE:
						$changed = (int) $internalItem->word_count;
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
			$item->translationMethods      = NenoHelper::getLanguageDefault($item->lang_code);

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
					'SUM(tr.word_counter) AS word_count',
					'lc.comment'
				)
			)
			->from('#__languages AS l')
			->leftJoin('#__neno_language_external_translators_comments AS lc ON l.lang_code = lc.language')
			->leftJoin('#__neno_content_element_translations AS tr ON tr.language = l.lang_code')
			->where('l.lang_code <> ' . $db->quote(NenoSettings::get('source_language')))
			->group(
				array (
					'l.lang_code',
					'tr.state'
				)
			)
			->order('lang_code');

		return $query;
	}

	/**
	 * Check if it's possible to install a language
	 *
	 * @return bool
	 */
	public function getIsPossibleToInstallLanguage()
	{
		$memoryDetails = NenoHelperBackend::getMemoryDetails();

		if (!empty($memoryDetails))
		{
			return $memoryDetails['free_space'] == 0 || $memoryDetails['free_space'] > $memoryDetails['current_data_space'];
		}

		return true;
	}

	/**
	 * Get position field
	 *
	 * @return string
	 */
	public function getPositionField()
	{
		// Adding necessary files
		require_once JPATH_ADMINISTRATOR . '/components/com_templates/helpers/templates.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_modules/helpers/modules.php';
		JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_modules/helpers/html');

		$language = JFactory::getLanguage();
		$language->load('com_modules', JPATH_BASE);
		$state     = 1;
		$positions = JHtml::_('modules.positions', 0, $state);

		// Add custom position to options
		$customGroupText = JText::_('COM_MODULES_CUSTOM_POSITION');

		// Build field
		$attr = array (
			'id'        => 'jform_position',
			'list.attr' => 'class="chzn-custom-value" '
				. 'data-custom_group_text="' . $customGroupText . '" '
				. 'data-no_results_text="' . JText::_('COM_MODULES_ADD_CUSTOM_POSITION') . '" '
				. 'data-placeholder="' . JText::_('COM_MODULES_TYPE_OR_SELECT_POSITION') . '" '
		);

		return JHtml::_('select.groupedlist', $positions, 'jform[position]', $attr);
	}

	/**
	 * Check if the language switcher has been published already
	 *
	 * @param   bool $createdAndPublished True to check whether the module has created and published or just created.
	 *
	 * @return bool
	 */
	public function getIsSwitcherPublished($createdAndPublished = true)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select(1)
			->from('#__modules')
			->where('module = ' . $db->quote('mod_languages'));

		if ($createdAndPublished)
		{
			$query->where(
				array (
					'position <> \'\'',
					'published = 1 '
				)
			);
		}

		$db->setQuery($query);

		return $db->loadResult() == 1 || !NenoSettings::get('show_language_switcher_warning', 1);
	}
}
