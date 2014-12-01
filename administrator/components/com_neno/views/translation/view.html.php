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
 * View to edit
 *
 * @since  1.0
 */
class NenoViewTranslation extends JViewLegacy
{

    protected $item;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return void
	 *
	 * @throws Exception This will happen if there are errors during the process to load the data
	 */
	public function display($tpl = null)
	{
        
        $this->item = $this->get('Item');
		$this->addToolbar();
        
        parent::display($tpl);
	}

    
	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolBarHelper::title(LingoHelper::getAdminTitle(), 'nope');

        JToolBarHelper::apply('source.apply', 'JTOOLBAR_APPLY');
        JToolBarHelper::save('source.save', 'JTOOLBAR_SAVE');

    	JToolBarHelper::cancel('translation.cancel', 'JTOOLBAR_CLOSE');
	}
    
    
    
}
