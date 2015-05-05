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

/**
 * NenoViewGroupElement class
 *
 * @since  1.0
 */
class NenoViewSetting extends JViewLegacy
{
	/**
	 * @var Joomla\Registry\Registry
	 */
	protected $state;

	/**
	 * @var JForm
	 */
	protected $form;

	/**
	 * @var stdClass
	 */
	protected $item;

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
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

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
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$canDo = NenoHelper::getActions();

		JToolBarHelper::title(JText::_('COM_NENO_TITLE_SETTING'), 'test.png');

		// If not checked out, can save the item.
		if ($canDo->get('core.edit') || $canDo->get('core.create'))
		{
			JToolBarHelper::apply('setting.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('setting.save', 'JTOOLBAR_SAVE');
		}

		if (empty($this->item->id))
		{
			JToolBarHelper::cancel('setting.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::cancel('setting.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
