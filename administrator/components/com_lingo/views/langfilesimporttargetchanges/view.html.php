<?php
/**
 * @package     Lingo
 * @subpackage  Views
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Lingo.
 *
 * @since  1.0
 */
class LingoViewLangfilesImportTargetChanges extends JViewLegacy
{
	/**
	 * @var array
	 */
	protected $items;

	/**
	 * @var string
	 */
	protected $sidebar;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return void
	 *
	 * @throws Exception This will happen if there are errors during the process to load the data
	 *
	 * @since 1.0
	 */
	public function display($tpl = null)
	{
		$this->items = $this->get('ChangedStrings');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		LingoHelper::addSubmenu('translations');

		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    1.0
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/lingo.php';

		JToolBarHelper::title(JText::_('COM_LINGO_TITLE_LANGFILESIMPORTTARGETCHANGES'), 'loop.png');

		JToolBarHelper::custom(
			'langfiles.pullTargetStrings',
			'arrow-right.png',
			'arrow-right.png',
			'COM_LINGO_VIEW_LANGFILESIMPORTTARGETCHANGES_BTN_PULL',
			true
		);

		JToolBarHelper::custom(
			'langfiles.pushTargetStrings',
			'arrow-left.png',
			'arrow-left.png',
			'COM_LINGO_VIEW_LANGFILESIMPORTTARGETCHANGES_BTN_PUSH',
			true
		);

		JToolBarHelper::cancel('langfiles.cancel');
	}

	/**
	 * Get an array of fields to sort by
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.id'              => JText::_('JGRID_HEADING_ID'),
			'a.source_id'       => JText::_('COM_LINGO_TRANSLATIONS_SOURCE_ID'),
			'a.time_translated' => JText::_('COM_LINGO_TRANSLATIONS_TIME_TRANSLATED'),
			'a.version'         => JText::_('COM_LINGO_TRANSLATIONS_VERSION'),
			'a.lang'            => JText::_('COM_LINGO_TRANSLATIONS_LANG'),
		);
	}
}
