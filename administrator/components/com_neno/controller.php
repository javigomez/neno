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

/**
 * Class NenoController
 *
 * @since  1.0
 */
class NenoController extends JControllerLegacy
{
	/**
	 * {@inheritdoc}
	 *
	 * @param   boolean $cachable  If Joomla should cache the response
	 * @param   array   $urlparams URL parameters
	 *
	 * @return JController
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$view = JFactory::getApplication()->input->getCmd('view', 'dashboard');
		JFactory::getApplication()->input->set('view', $view);

		// Ensure that a working language is set for some views
		$viewsThatRequireWorkingLanguage   = array();
		$viewsThatRequireWorkingLanguage[] = 'translations';
		$viewsThatRequireWorkingLanguage[] = 'translation';

		if (in_array($view, $viewsThatRequireWorkingLanguage))
		{
			// Get working language
			$workingLanguage = NenoHelper::getWorkingLanguage();
			
			if (empty($workingLanguage))
			{
				$url = JRoute::_('index.php?option=com_neno&view=setworkinglang&next=' . $view, false);
				$this->setRedirect($url);
				$this->redirect();
			}
		}

		parent::display($cachable, $urlparams);

		return $this;
	}

	/**
	 *
	 * @return void
	 */
	public function setWorkingLang()
	{
		$lang = JFactory::getApplication()->input->getString('lang', '');
		$next = JFactory::getApplication()->input->getString('next', 'dashboard');

		NenoHelper::setWorkingLanguage($lang);

		$url = JRoute::_('index.php?option=com_neno&view=' . $next, false);
		$this->setRedirect($url);
		$this->redirect();
	}
}
