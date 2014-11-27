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
class LingoViewSetworkinglang extends JViewLegacy
{

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->langs = LingoHelper::getTargetLanguages(false);
        
		JFactory::getApplication()->input->set('hidemainmenu', true);
		JToolBarHelper::title(LingoHelper::getAdminTitle(), 'nope');

		parent::display($tpl);
	}

}