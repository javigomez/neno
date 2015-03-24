<?php
/**
 * @package     Neno
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
 * NenoViewGroupsElements class
 *
 * @since  1.0
 */
class NenoViewMoveElementConfirm extends JViewLegacy
{
    
    var $tables = null;
    var $files = null;
    
    /**
	 * Display the view
	 *
	 * @param   string $tpl Template
	 *
	 * @return void
	 *
	 * @throws Exception This will happen if there are errors during the process to load the data
	 *
	 * @since 1.0
	 */
	public function display($tpl = null)
	{
		
        echo '<pre class="debug"><small>' . __file__ . ':' . __line__ . "</small>\n\$this = ". print_r($this, true)."\n</pre>";

        
		$this->addToolbar();

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
		$canDo = NenoHelper::getActions();
        
        JToolbarHelper::save('moveelementconfirm.save');
        JToolbarHelper::cancel('moveelementconfirm.cancel');
        JToolbarHelper::custom('moveelementconfirm.show', 'move', 'move', JText::_('COM_NENO_VIEW_GROUPSELEMENTS_BTN_MOVE_ELEMENTS'), TRUE);
        
		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_neno');
		}

		// Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_neno&view=groupselements');

		$this->extra_sidebar = '';
	}

	/**
	 * Get an array of fields to sort by
	 *
	 * @return array
	 */
	protected function getSortFields()
	{
		return array (
			'a.id'           => JText::_('JGRID_HEADING_ID'),
			'a.string'       => JText::_('COM_NENO_SOURCES_STRING'),
			'a.constant'     => JText::_('COM_NENO_SOURCES_CONSTANT'),
			'a.lang'         => JText::_('COM_NENO_SOURCES_LANG'),
			'a.extension'    => JText::_('COM_NENO_SOURCES_EXTENSION'),
			'a.time_added'   => JText::_('COM_NENO_SOURCES_TIME_ADDED'),
			'a.time_changed' => JText::_('COM_NENO_SOURCES_TIME_CHANGED'),
			'a.time_deleted' => JText::_('COM_NENO_SOURCES_TIME_DELETED'),
			'a.version'      => JText::_('COM_NENO_SOURCES_VERSION')
		);
	}

	/**
	 * Check if a field has been imported already
	 *
	 * @param   string $fieldName Field name to check
	 * @param   array  $fieldList List of fields that have been imported.
	 *
	 * @return bool
	 */
	protected function isAlreadyChecked($fieldName, array $fieldList)
	{
		foreach ($fieldList as $field)
		{
			if ($field->field === $fieldName)
			{
				return true;
			}
		}

		return false;
	}
}
