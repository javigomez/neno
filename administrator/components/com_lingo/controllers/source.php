<?php
/**
 * @package     Lingo
 * @subpackage  Controllers
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Source controller class.
 */
class LingoControllerSource extends JControllerForm
{

	function __construct()
	{
		$this->view_list = 'sources';
		parent::__construct();
	}

}