<?php
/**
 * @package    Neno
 *
 * @author     Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright  Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_neno'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependencies
jimport('joomla.application.component.controller');
JLoader::register('NenoHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/neno.php');

$controller = JControllerLegacy::getInstance('Neno');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
