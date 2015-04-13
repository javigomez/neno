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
class NenoViewGroupsElements extends JViewLegacy
{
	/**
	 * @var array
	 */
	protected $items;

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
		$this->state        = $this->get('State');
		$this->items        = NenoHelper::convertNenoObjectListToJObjectList($this->get('Items'));
        
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		NenoHelper::addSubmenu('groupselements');

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
		$canDo = NenoHelper::getActions();
        
        JToolbarHelper::custom('moveelementconfirm.show', 'move', 'move', JText::_('COM_NENO_VIEW_GROUPSELEMENTS_BTN_MOVE_ELEMENTS'), TRUE);
        
		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_neno');
		}

		// Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_neno&view=groupselements');

		$this->extra_sidebar = '';
	}


}
