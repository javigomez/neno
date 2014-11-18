<?php

/**
 * @version     1.0.0
 * @package     com_lingo
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Soren Beck Jensen <soren@notwebdesign.com> - http://www.notwebdesign.com
 */
// No direct access
defined('_JEXEC') or die;

class LingoController extends JControllerLegacy
{

	/**
	 * {@inheritdoc}
	 */
	public function display($cachable = false, $urlparams = false)
	{

		require_once JPATH_COMPONENT . '/helpers/lingo.php';

		$view = JFactory::getApplication()->input->getCmd('view', 'dashboard');
		JFactory::getApplication()->input->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}

}
