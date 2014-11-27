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
 * View to edit
 *
 * @since  1.0
 */
class LingoViewTranslatableTables extends JViewLegacy
{
	/**
	 * @var array
	 */
	protected $translatableTables;

	/**
	 * @var array
	 */
	protected $dbTables;

	/**
	 * @var string
	 */
	protected $dbPrefix;

	/**
	 * {@inheritDoc}
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		$this->translatableTables = $this->get('Items');
		$this->dbTables           = $this->get('AllJoomlaTables');
		$this->dbPrefix           = JFactory::getConfig()->get('dbprefix');
		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		JToolBarHelper::title(JText::_('COM_LINGO_TITLE_DASHBOARD'), 'dashboard.png');
	}
}
