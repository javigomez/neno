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
class NenoViewDebug extends JViewLegacy
{
	/**
	 * @var string
	 */
	protected $sidebar;

	/**
	 * Show view
	 *
	 * @param   string|null $tpl Template to use
	 *
	 * @return mixed
	 */
	public function display($tpl = null)
	{
		NenoHelperBackend::addSubmenu('debug');
		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);
	}
}
