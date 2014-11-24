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

		parent::display($cachable, $urlparams);

		return $this;
	}
}
