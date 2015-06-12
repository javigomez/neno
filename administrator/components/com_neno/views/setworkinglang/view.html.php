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
class NenoViewSetWorkingLang extends JViewLegacy
{
	/**
	 * @var array
	 */
	protected $langs;

	/**
	 * @var string
	 */
	protected $sidebar;

	/**
	 * Display the view
	 *
	 * @param   string $tpl Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->langs = NenoHelper::getTargetLanguages(false);
		NenoHelperBackend::addSubmenu('');
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}
}
