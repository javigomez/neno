<?php
/**
 * @package     Neno
 * @subpackage  Models
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/langfiles.php';

/**
 * NenoModelLangfilesImportTargetChanges class
 *
 * @since  1.0
 */
class NenoModelLangfilesImportTargetChanges extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'source_id', 'a.source_id',
				'time_translated', 'a.time_translated',
				'version', 'a.version',
				'lang', 'a.lang',

			);
		}

		parent::__construct($config);
	}

	/**
	 * Update either a file or the database when they are out of sync
	 *
	 * @param   array  $cid         Integer array
	 * @param   string $direction   Either 'pull' or 'push' if 'pull' the data in files is imported to database,
	 *                              if 'push' the data in database is pushed to files
	 *
	 * @return boolean
	 */
	public function updateTargetStrings($cid, $direction = 'pull')
	{
		if (!empty($cid))
		{
			$changed_strings = $this->getChangedStrings();

			// Flip the cid array so the keys become the ids
			$cid = array_flip($cid);

			// Intersect the two arrays to get the array we need to work with
			$strings = array_intersect_key($changed_strings, $cid);

			if (!empty($strings))
			{
				/* @var $model NenoModelLangfiles */
				$model = NenoHelper::getModel('Langfiles');

				if ($direction == 'pull')
				{
					// Prepare the array to be used in the updateStringsInTargetDatabase() method
					foreach ($strings as $key => $string)
					{
						$strings[$key]->string = $string->text_in_file;
					}

					// Update the database
					$model->updateStringsInTargetDatabase($strings);


				}
				else
				{
					foreach ($strings as $string)
					{
						// Update the database
						$keyInfo = $model->getInfoFromStringKey($string->key);
						$model->updateLanguageFileString($string->lang, $keyInfo['extension'], $keyInfo['constant'], $string->text_in_db);
					}
				}
			}
		}

		return true;
	}

	/**
	 * Load an objectList of strings that are out of sync
	 *
	 * @return array
	 */
	public function getChangedStrings()
	{
		$items = array();

		// Load the strings that are changed
		/* @var $model NenoModelLangfiles */
		$model          = NenoHelper::getModel('Langfiles');
		$changedStrings = $model->getChangedStringsInLangFiles(NenoContentElementLangstring::TARGET_LANGUAGE_TYPE);

		// Loop each string and load additional information as object list
		if (count($changedStrings))
		{
			foreach ($changedStrings as $lang => $strings)
			{
				if (count($strings))
				{
					// Load all target strings in the database for this language
					$targetStringsInDb = $model->getTargetLanguageStringsFromDatabase($lang);

					// Merge the arrays
					$relevantTargetStringsInDb = array_intersect_key($targetStringsInDb, $strings);

					if (count($relevantTargetStringsInDb))
					{
						foreach ($relevantTargetStringsInDb as $key => $relevantString)
						{
							$item                     = new stdClass;
							$item->key                = $key;
							$item->text_in_file       = $strings[$key];
							$item->text_in_db         = $relevantString->string;
							$item->id                 = $relevantString->id;
							$item->version            = $relevantString->version;
							$item->translation_method = $relevantString->translation_method;
							$item->lang               = $lang;
							$items[$item->id]         = $item;
						}
					}
				}
			}
		}

		return $items;
	}

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string $ordering  Ordering field
	 * @param   string $direction Ordering direction [ASC,DESC]
	 *
	 * @return void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_neno');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string $id A prefix for the store id.
	 *
	 * @return    string        A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}
}
