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
class LingoViewSources extends JViewLegacy
{
	/**
	 * @var array
	 */
	protected $items;

	/**
	 * @var JPagination
	 */
	protected $pagination;

	/**
	 * @var JRegistry
	 */
	protected $state;

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
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		LingoHelper::addSubmenu('sources');

		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/lingo.php';

		$state = $this->get('State');
		$canDo = LingoHelper::getActions($state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('COM_LINGO_TITLE_SOURCES'), 'sources.png');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/source';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('source.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList('source.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('sources.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('sources.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			else
			{
				if (isset($this->items[0]))
				{
					// If this component does not use state then show a direct delete button as we can not trash
					JToolBarHelper::deleteList('', 'sources.delete', 'JTOOLBAR_DELETE');
				}
			}

			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::archiveList('sources.archive', 'JTOOLBAR_ARCHIVE');
			}

			if (isset($this->items[0]->checked_out))
			{
				JToolBarHelper::custom('sources.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('', 'sources.delete', 'JTOOLBAR_EMPTY_TRASH');
				JToolBarHelper::divider();
			}
			else
			{
				if ($canDo->get('core.edit.state'))
				{
					JToolBarHelper::trash('sources.trash', 'JTOOLBAR_TRASH');
					JToolBarHelper::divider();
				}
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_lingo');
		}

		// Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_lingo&view=sources');

		$this->extra_sidebar = '';
	}

	/**
	 * Get an array of fields to sort by
	 *
	 * @return array
	 */
	protected function getSortFields()
	{
		return array(
			'a.id'           => JText::_('JGRID_HEADING_ID'),
			'a.string'       => JText::_('COM_LINGO_SOURCES_STRING'),
			'a.constant'     => JText::_('COM_LINGO_SOURCES_CONSTANT'),
			'a.lang'         => JText::_('COM_LINGO_SOURCES_LANG'),
			'a.extension'    => JText::_('COM_LINGO_SOURCES_EXTENSION'),
			'a.time_added'   => JText::_('COM_LINGO_SOURCES_TIME_ADDED'),
			'a.time_changed' => JText::_('COM_LINGO_SOURCES_TIME_CHANGED'),
			'a.time_deleted' => JText::_('COM_LINGO_SOURCES_TIME_DELETED'),
			'a.version'      => JText::_('COM_LINGO_SOURCES_VERSION'),
		);
	}
}
