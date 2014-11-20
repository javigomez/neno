<?php
/**
 * @package     Lingo
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
 */
class LingoViewLangfilesImport extends JViewLegacy
{

	/**
	 * @var JLanguage
	 */
	protected $sourceLanguage;

	/**
	 * @var array
	 */
	protected $sourceCounts;

	/**
	 * @var array
	 */
	protected $newTargetStrings;

	/**
	 * @var array
	 */
	protected $changedTargetStrings;

	/**
	 * @var boolean
	 */
	protected $changesPending;

	/**
	 * Constructor
	 *
	 * @param   array  $config  Configuration parameters
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->sourceLanguage       = null;
		$this->sourceCounts         = array();
		$this->newTargetStrings     = array();
		$this->changedTargetStrings = array();
		$this->changesPending       = false;
	}

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return void
	 *
	 * @throws Exception This will happen if there are errors during the process to load the data
	 *
	 * @since 1.0
	 */
	public function display($tpl = null)
	{
		$language             = JFactory::getLanguage();
		$this->sourceLanguage = $language->getDefault();

		/* @var $model LingoModelLangfiles */
		$model = LingoHelper::getModel('Langfiles');

		$this->sourceCounts['new_source_lines']     = $model->getNewStringsInLangfiles('source');
		$this->sourceCounts['deleted_source_lines'] = $model->getDeletedSourceStringsInLangfiles();
		$this->sourceCounts['updated_source_lines'] = $model->getChangedStringsInLangfiles('source');
		$this->newTargetStrings                     = $model->getNewStringsInLangfiles('target');
		$this->changedTargetStrings                 = $model->getChangedStringsInLangfiles('target');

		// Check for changes
		if (count($this->sourceCounts['new_source_lines'][$this->sourceLanguage])
			|| count($this->sourceCounts['deleted_source_lines'][$this->sourceLanguage])
			|| count($this->sourceCounts['updated_source_lines'][$this->sourceLanguage]))
		{
			$this->changesPending = true;
		}

		foreach ($this->newTargetStrings as $new_target_lines)
		{
			if (count($new_target_lines))
			{
				$this->changesPending = true;
				break;
			}
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
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
