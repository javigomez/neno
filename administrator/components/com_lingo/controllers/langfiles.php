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

jimport('joomla.application.component.controller');

/**
 * Source controller class.
 */
class LingoControllerLangfiles extends JControllerLegacy
{

    public function export()
    {

        /* @var $model LingoModelLangfiles */
        $model = $this->getModel('Langfiles');
        $model->export();

    }


    /**
     * Looks in all language files and imports any strings that have not been imported as well as marks deleted or changed
     */
    public function import()
    {

        JSession::checkToken() or die('Invalid Token');

        /* @var $model LingoModelLangfiles */
        $model = $this->getModel('Langfiles');
        $model->import();

        //Check to see if there are any changes to target language files as we will have to redirect to a page where the user can chose what to do with them
        $changed_strings = $model->getChangedStringsInLangfiles('target');
        if (count($changed_strings))
        {
            $this->setRedirect(JRoute::_('index.php?option=com_lingo&view=langfilesimporttargetchanges', false));
        }
        else
        {
            $this->setRedirect(JRoute::_('index.php?option=com_lingo&view=langfilesimport', false));
        }

    }

    /**
     * Move strings from files to database
     */
    public function pulltargetstrings()
    {
        $cid = JFactory::getApplication()->input->post->get('cid', array(), 'array');

        /* @var $model LingoModelLangfilesimporttargetchanges */
        $model = LingoHelper::getModel('Langfilesimporttargetchanges');
        $model->updateTargetStrings($cid, 'pull');

        $this->setRedirect(JRoute::_('index.php?option=com_lingo&view=langfilesimport', false));
    }

    /**
     * Move strings from database to files
     */
    public function pushtargetstrings()
    {
        $cid = JFactory::getApplication()->input->post->get('cid', array(), 'array');

        /* @var $model LingoModelLangfilesimporttargetchanges */
        $model = LingoHelper::getModel('Langfilesimporttargetchanges');
        $model->updateTargetStrings($cid, 'push');

        $this->setRedirect(JRoute::_('index.php?option=com_lingo&view=langfilesimport', false));
    }


    public function cancel()
    {
        $this->setRedirect(JRoute::_('index.php?option=com_lingo&view=dashboard', false));
    }

    public function refresh()
    {
        $this->setRedirect(JRoute::_('index.php?option=com_lingo&view=langfilesimport', false));
    }


}

