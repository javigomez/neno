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
	/**
	 * @var array
	 */
	public $groups;

	/**
	 * @var array
	 */
	public $tables;

	/**
	 * @var array
	 */
	public $files;

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
		JToolBarHelper::title(JText::_('COM_NENO_VIEW_MOVELEMENTCONFIRM_TITLE'), 'move.png');

		JToolbarHelper::save('moveelementconfirm.move', JText::_('COM_NENO_VIEW_MOVELEMENTCONFIRM_CONFIRM_BTN'));
		JToolbarHelper::cancel('moveelementconfirm.cancel');

		// Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_neno&view=groupselements');

		$this->extra_sidebar = '';
	}
}
