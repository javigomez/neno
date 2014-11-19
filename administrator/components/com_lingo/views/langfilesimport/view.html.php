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

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class LingoViewLangfilesImport extends JViewLegacy
{

    var $source_language = null;
    var $source_counts = array();
    var $new_target_strings = array();
    var $changed_target_strings = array();
    var $changes_pending = false;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {

        $language              = JFactory::getLanguage();
        $this->source_language = $language->getDefault();

        /* @var $model LingoModelLangfiles */
        $model = LingoHelper::getModel('Langfiles');

        $this->source_counts['new_source_lines']     = $model->getNewStringsInLangfiles('source');
        $this->source_counts['deleted_source_lines'] = $model->getDeletedSourceStringsInLangfiles();
        $this->source_counts['updated_source_lines'] = $model->getChangedStringsInLangfiles('source');
        $this->new_target_strings                    = $model->getNewStringsInLangfiles('target');
        $this->changed_target_strings                = $model->getChangedStringsInLangfiles('target');


        //Check for changes
        if (count($this->source_counts['new_source_lines'][$this->source_language])
            || count($this->source_counts['deleted_source_lines'][$this->source_language])
            || count($this->source_counts['updated_source_lines'][$this->source_language])
        )
        {
            $this->changes_pending = true;
        }
        foreach ($this->new_target_strings as $new_target_lines)
        {
            if (count($new_target_lines))
            {
                $this->changes_pending = true;
                break;
            }
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user  = JFactory::getUser();
        $canDo = LingoHelper::getActions();

        JToolBarHelper::title(JText::_('COM_LINGO_LANGFILES_IMPORT_TITLE'), 'download.png');

        JToolBarHelper::custom('langfiles.import', 'download', 'download', 'COM_LINGO_VIEW_LANGFILESIMPORT_BTN_IMPORT', false);
        JToolBarHelper::custom('langfiles.refresh', 'redo-2', 'redo-2', 'COM_LINGO_VIEW_LANGFILESIMPORT_BTN_REFRESH', false);
        JToolBarHelper::cancel('langfiles.cancel');

        if ($canDo->get('core.admin'))
        {
            JToolBarHelper::preferences('com_lingo');
        }

    }

}
