<?php

/**
 * @version   1.0.0
 * @package   com_lingo
 * @copyright Copyright (C) 2014. All rights reserved.
 * @author    Soren Beck Jensen <soren@notwebdesign.com> - http://www.notwebdesign.com
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class LingoViewDashboard extends JViewLegacy
{


    /**
     * Display the view
     */
    public function display($tpl = null)
    {

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user  = JFactory::getUser();
        $canDo = LingoHelper::getActions();

        JToolBarHelper::title(JText::_('COM_LINGO_TITLE_DASHBOARD'), 'dashboard.png');

    }

}
