<?php
/**
 * @package    Lingo
 *
 * @author     Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright  Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Class LingoController
 *
 * @since  1.0
 */
class LingoController extends JControllerLegacy
{
	/**
	 * {@inheritdoc}
	 *
	 * @param   boolean  $cachable   If Joomla should cache the response
	 * @param   array    $urlparams  URL parameters
	 *
	 * @return JController
	 */
	public function display($cachable = false, $urlparams = array())
	{
		require_once JPATH_COMPONENT . '/helpers/lingo.php';

		$view = JFactory::getApplication()->input->getCmd('view', 'dashboard');
		JFactory::getApplication()->input->set('view', $view);
        
        // Ensure that a working language is set for some views
        $viewsThatRequireWorkingLanguage = array();
        $viewsThatRequireWorkingLanguage[] = 'translations';
        $viewsThatRequireWorkingLanguage[] = 'translation';
        if (in_array($view, $viewsThatRequireWorkingLanguage))
        {
            if (empty(LingoHelper::getWorkingLanguage())) 
            {
                $url = JRoute::_('index.php?option=com_lingo&view=setworkinglang&next='.$view, false);
                $this->setRedirect($url);
                $this->redirect();
            }
        }
                
		parent::display($cachable, $urlparams);

		return $this;
	}
    
    
    public function setWorkingLang() {
        
        require_once JPATH_COMPONENT . '/helpers/lingo.php';
        
		$lang = JFactory::getApplication()->input->getString('lang', '');
		$next = JFactory::getApplication()->input->getString('next', 'dashboard');
        
        LingoHelper::setWorkingLanguage($lang);
        
        $url = JRoute::_('index.php?option=com_lingo&view='.$next, false);
        $this->setRedirect($url);
        $this->redirect();
        
    }
    
    
}
