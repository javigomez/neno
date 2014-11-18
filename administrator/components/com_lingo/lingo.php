<?php
/**
 * @version     1.0.0
 * @package     com_lingo
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Soren Beck Jensen <soren@notwebdesign.com> - http://www.notwebdesign.com
 */

// no direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_lingo'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependencies
jimport('joomla.application.component.controller');
require_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/lingo.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/debug.php');

$controller = JControllerLegacy::getInstance('Lingo');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
