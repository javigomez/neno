<?php

/**
 * @version     1.0.0
 * @package     com_lingo
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Soren Beck Jensen <soren@notwebdesign.com> - http://www.notwebdesign.com
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Lingo.
 */
class LingoViewLangfilesImportTargetChanges extends JViewLegacy
{

	protected $items;

	protected $sidebar;

	/**
	 * Display the view
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
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/lingo.php';

		$state = $this->get('State');
		$canDo = LingoHelper::getActions($state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('COM_LINGO_TITLE_LANGFILESIMPORTTARGETCHANGES'), 'loop.png');

		JToolBarHelper::custom('langfiles.pullTargetStrings', 'arrow-right.png', 'arrow-right.png', 'COM_LINGO_VIEW_LANGFILESIMPORTTARGETCHANGES_BTN_PULL', true);
		JToolBarHelper::custom('langfiles.pushTargetStrings', 'arrow-left.png', 'arrow-left.png', 'COM_LINGO_VIEW_LANGFILESIMPORTTARGETCHANGES_BTN_PUSH', true);
		JToolBarHelper::cancel('langfiles.cancel');

	}

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
