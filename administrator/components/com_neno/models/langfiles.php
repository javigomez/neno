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

/**
 * NenoModelLangfiles class
 *
 * @since  1.0
 */
class NenoModelLangfiles extends JModelLegacy
{
	/**
	 * @var string
	 */
	public $sourceLanguage;

	/**
	 * @var array
	 */
	public $targetLanguages;

	/**
	 * @var array
	 */
	public $messages;

	/**
	 * @var array
	 */
	public $sourceLanguageStrings;

	/**
	 * @var array
	 */
	protected $languageFiles;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$language                    = JFactory::getLanguage();
		$this->sourceLanguage        = $language->getDefault();
		$this->targetLanguages       = NenoHelper::getTargetLanguages();
		$this->messages              = array();
		$this->sourceLanguageStrings = array();
		$this->languageFiles         = array();
	}


	/**
	 * Import from files into the database
	 *
	 * @return void
	 */
	public function import()
	{
		$this->importSourceLanguageStrings();
		$this->importTargetLanguageStrings();
	}

	/**
	 * Import source language from files
	 *
	 * @return void
	 */
	protected function importSourceLanguageStrings()
	{
		NenoLog::log('Importing Source Language Strings from files');

		// Load all new strings and add them to database
		$newStrings = $this->getNewStringsInLanguageFiles(NenoContentElementLangfile::SOURCE_LANGUAGE_TYPE);

		if (!empty($newStrings))
		{
			foreach ($newStrings as $lang => $strings)
			{
				$this->addStringsToDatabase(NenoContentElementLangfile::SOURCE_LANGUAGE_TYPE, $strings, $lang);
			}
		}

		// Deleted source strings
		$deletedStrings = $this->getDeletedSourceStringsInLangfiles();

		if (!empty($deletedStrings[$this->sourceLanguage]))
		{
			$this->markSourceStringsDeletedInDatabase($deletedStrings[$this->sourceLanguage]);
		}

		// Changed source strings
		$changedStrings = $this->getChangedStringsInLangFiles(NenoContentElementLangfile::SOURCE_LANGUAGE_TYPE);

		if (!empty($changedStrings[$this->sourceLanguage]))
		{
			$this->updateStringsInSourceDatabase($changedStrings[$this->sourceLanguage]);
		}

		NenoLog::log('Finished importing Source Language Strings from files');
	}

	/**
	 * Find new strings in files
	 *
	 * @param   string $type 'source' or 'target'
	 *
	 * @return array with new constants indexed by language
	 */
	public function getNewStringsInLanguageFiles($type)
	{
		// Create an array of languages we need to load depending on type
		if ($type == NenoContentElementLangfile::SOURCE_LANGUAGE_TYPE)
		{
			$langObject            = new stdClass;
			$langObject->lang_code = $this->sourceLanguage;
			$languages             = array($langObject);
		}
		else
		{
			$languages = NenoHelper::getTargetLanguages();
		}

		$newStrings = array();

		foreach ($languages as $lang)
		{
			$langCode = $lang->lang_code;

			// Load constants from files
			if (empty($this->languageFiles[$langCode]))
			{
				$this->languageFiles[$langCode] = NenoLanguageFile::getLanguagesFilesBasedOnLanguage($langCode);
			}

			$languageStrings = $this->getLanguageStringsFromLanguageFiles($this->languageFiles[$langCode]);

			// If we are finding new target strings then don't import the strings
			// that does not have a matching source string
			if ($type == NenoContentElementLangfile::TARGET_LANGUAGE_TYPE)
			{
				// Filter out strings that are not already in the source database
				$sourceLanguageStrings = $this->loadLanguageStringsFromDatabase(NenoContentElementLangfile::SOURCE_LANGUAGE_TYPE, $this->sourceLanguage);
				$languageStrings       = array_intersect_key($languageStrings, $sourceLanguageStrings);
			}

			// Load constants from database
			$databaseStrings = $this->loadLanguageStringsFromDatabase($type, $langCode);

			// Filter the list to only have strings that are not already imported
			$newStrings[$langCode] = array_diff_key($languageStrings, $databaseStrings);

			NenoLog::log('Found ' . count($newStrings[$langCode]) . ' new strings in [' . $langCode . '] language files', 2);
		}

		return $newStrings;
	}

	/**
	 * @param array $languageFiles
	 *
	 * @return array
	 */
	protected function getLanguageStringsFromLanguageFiles(array $languageFiles)
	{
		$languageStrings = array();

		foreach ($languageFiles as $fileString)
		{
			$languageFile    = NenoLanguageFile::getLanguageFileBasedOnPath($fileString);
			$languageStrings = array_merge($languageStrings, $languageFile->getStrings());
		}

		return $languageStrings;
	}

	/**
	 * Load language strings from the database
	 *
	 * @param   string $type     'source' or 'target'
	 * @param   string $language optional 'en-GB'
	 *
	 * @return array indexed array of constant/string pairs
	 */
	protected function loadLanguageStringsFromDatabase($type, $language = null)
	{
		return NenoContentElementLangfile::getLanguageStrings($type, array('language' => $language));
	}

	/**
	 * Takes and array of strings and adds them to the database
	 *
	 * @param   string $type    either 'source' or 'target'
	 * @param   array  $strings Strings to add
	 * @param   string $lang    eg. 'en-GB'
	 *
	 * @return boolean
	 */
	protected function addStringsToDatabase($type, $strings, $lang = null)
	{
		if (empty($strings))
		{
			return false;
		}

		if (is_null($lang))
		{
			$lang = $this->sourceLanguage;
		}

		foreach ($strings as $key => $string)
		{
			$stringInfo             = $this->getInfoFromStringKey($key);
			$stringInfo['language'] = $lang;
			$stringInfo['string']   = $string;

			if ($type === NenoContentElementLangfile::SOURCE_LANGUAGE_TYPE)
			{
				$languageString = new NenoContentElementLangfileSource($stringInfo);
			}
			else
			{
				$options = array(
					'language'  => $this->sourceLanguage,
					'extension' => $stringInfo['extension'],
					'constant'  => $stringInfo['constant']
				);

				$sourceLanguageString = NenoContentElementLangfile::getLanguageString(
					NenoContentElementLangfile::SOURCE_LANGUAGE_TYPE, $options
				);
				$languageString       = new NenoContentElementLangfileTranslation($stringInfo);
				$languageString->setSource($sourceLanguageString);
			}

			// Save it on the database
			$languageString->persist();
		}

		$message = JText::sprintf('COM_NENO_LANGFILES_MSG_NEW_STRINGS', count($strings), $this->getPrintableTypeName($type));
		NenoLog::log($message, 2, true);

		return true;
	}

	/**
	 * Takes a string (used as key in internal arrays) and splits it into an array of information
	 * Example key: com_phocagallery.sys.ini:COM_PHOCAGALLERY_XML_DESCRIPTION
	 *
	 * @param   string $key Language file key
	 *
	 * @return array
	 */
	public function getInfoFromStringKey($key)
	{
		$info = array();

		if (empty($key))
		{
			return $info;
		}

		// Split by : to separate file name and constant
		list($fileName, $info['constant']) = explode(':', $key);

		// Split the file name by . for additional information
		$fileParts         = explode('.', $fileName);
		$info['extension'] = $fileParts[0];

		// Add .sys and other file parts to the name
		foreach ($fileParts as $k => $filePart)
		{
			if ($k > 0 && $filePart != 'ini')
			{
				$info['extension'] .= '.' . $filePart;
			}
		}

		return $info;
	}

	/**
	 * Get Printable type names
	 *
	 * @param   string $type Type name
	 *
	 * @return string
	 */
	protected function getPrintableTypeName($type)
	{
		return JText::_('COM_NENO_LANGFILES_TYPE_' . strtoupper($type));
	}

	/**
	 * Get an array of strings deleted in the source file.
	 *
	 * @return array
	 */
	public function getDeletedSourceStringsInLangfiles()
	{
		// Load constants from files
		if (empty($this->languageFiles[$this->sourceLanguage]))
		{
			$this->languageFiles[$this->sourceLanguage] = NenoLanguageFile::getLanguagesFilesBasedOnLanguage($this->sourceLanguage);
		}

		// Get all the language strings from
		$languageStrings = $this->getLanguageStringsFromLanguageFiles($this->languageFiles[$this->sourceLanguage]);

		// Load constants from database
		$databaseStrings = $this->loadLanguageStringsFromDatabase(NenoContentElementLangfile::SOURCE_LANGUAGE_TYPE, $this->sourceLanguage);

		// Find differences between the two datasets
		$deletedStrings = array_diff_key($databaseStrings, $languageStrings);

		NenoLog::log('Found ' . count($deletedStrings) . ' strings deleted from [' . $this->sourceLanguage . '] language files', 2);

		return $deletedStrings;
	}

	/**
	 * Mark both source and target as deleted when we discover a deleted source string
	 *
	 * @param   array $sourceStrings object list with ->id defining the source id that needs deleting
	 *
	 * @return boolean
	 */
	protected function markSourceStringsDeletedInDatabase($sourceStrings)
	{
		if (empty($sourceStrings))
		{
			return false;
		}

		/* @var $string NenoContentElementLangfile */
		foreach ($sourceStrings as $key => $sourceString)
		{
			// Getting all the translations from this strings
			$targetStrings = NenoContentElementLangfile::getLanguageStrings(
				NenoContentElementLangfile::TARGET_LANGUAGE_TYPE,
				array('source_id' => $sourceString->getId())
			);

			/* @var $targetString NenoContentElementLangfileTranslation */
			foreach ($targetStrings as $targetString)
			{
				$targetString->setState(-1);
				$targetString->setTimeDeleted(new DateTime);

				$targetString->persist();
			}

			$sourceString->setState(-1);
			$sourceString->setTimeDeleted(new DateTime);
			$sourceString->persist();
		}

		// Message
		$message = JText::sprintf('COM_NENO_LANGFILES_MSG_DELETED_STRINGS', count($sourceStrings));
		NenoLog::log($message, 2, true);

		return true;
	}

	/**
	 * Compares two arrays and outputs a new array with only the items where the value is different
	 *
	 * @param   string $type 'source' or 'target'
	 *
	 * @return array
	 */
	public function getChangedStringsInLangFiles($type)
	{
		// Create an array of languages we need to load depending on type
		if ($type == NenoContentElementLangfile::SOURCE_LANGUAGE_TYPE)
		{
			$langObject            = new stdClass;
			$langObject->lang_code = $this->sourceLanguage;
			$languages             = array($langObject);
		}
		else
		{
			$languages = NenoHelper::getTargetLanguages();
		}

		$commonStrings = array();
		$changes       = array();

		foreach ($languages as $lang)
		{
			$langCode      = $lang->lang_code;
			$commonStrings = array();

			// Load constants from files
			if (empty($this->languageFiles[$langCode]))
			{
				$this->languageFiles[$langCode] = NenoLanguageFile::getLanguagesFilesBasedOnLanguage($langCode);
			}

			// Load constants from database
			$databaseStrings = $this->loadLanguageStringsFromDatabase($type, $langCode);

			// Load constants from files
			$languageStrings = $this->getLanguageStringsFromLanguageFiles($this->languageFiles[$langCode]);

			// Filter the list to only have strings that are not already imported
			$commonStrings = array_intersect_key($languageStrings, $databaseStrings);

			NenoLog::log('Found ' . count($commonStrings) . ' new strings in [' . $langCode . '] language files', 2);

			$changes[$langCode] = array();

			foreach ($commonStrings as $key => $fileString)
			{
				/* @var $databaseStrings [$key] NenoContentElementLangfile */
				// Skip if not in the database (new string)
				if (!isset($databaseStrings[$key]))
				{
					NenoLog::log('Skipping string change comparison on ' . $key . ' as it is not found in the database', 3);
				}
				else
				{
					if ($databaseStrings[$key]->getString() != $fileString)
					{
						$databaseStrings[$key]->setString($fileString);
						$changes[$langCode][] = $databaseStrings[$key];
					}
				}
			}
		}

		return $changes;
	}

	/**
	 * Takes and array of strings and updates them in the database
	 *
	 * @param   array $languageStrings Strings to update
	 *
	 * @return boolean
	 */
	protected function updateStringsInSourceDatabase($languageStrings)
	{
		if (empty($languageStrings))
		{
			return false;
		}

		/* @var $languageString NenoContentElementLangfileSource */
		foreach ($languageStrings as $key => $languageString)
		{
			$languageString->increaseVersion();
			$languageString->setTimeChanged(new DateTime);
			$languageString->persist();

			NenoLog::log('Updating database source string: ' . $languageString->getConstant() . ' = "' . $languageString->getString() . '"', 3);
		}

		// Set a message in log and for display
		$message = JText::sprintf('COM_NENO_LANGFILES_MSG_UPDATED_STRINGS', count($languageStrings));
		NenoLog::log($message, 2, true);

		return true;
	}

	/**
	 * Import translations from language files
	 * This is quite complicated as it involves comparing file language strings, source language strings and
	 * target language strings in multiple languages
	 * To do this properly a few steps are used.
	 * 1. Deal with one target language at a time
	 * 2. Load language strings from all three sources using the same key to make comparison easy
	 *
	 * @return void
	 */
	protected function importTargetLanguageStrings()
	{
		NenoLog::log('Starting import of target language files', 2);

		// Load all new strings and add them to database
		$newStrings = $this->getNewStringsInLanguageFiles(NenoContentElementLangfile::TARGET_LANGUAGE_TYPE);

		if (!empty($newStrings))
		{
			foreach ($newStrings as $lang => $strings)
			{
				$this->addStringsToDatabase(NenoContentElementLangfile::TARGET_LANGUAGE_TYPE, $strings, $lang);
			}
		}
	}

	/**
	 * Load all language strings from the database
	 *
	 * @param   string $lang eg. 'en-GB'
	 * @param   array  $ids  The id of the strings that should be returned
	 *
	 * @return array object list
	 */
	public function getTargetLanguageStringsFromDatabase($lang, $ids = null)
	{
		NenoLog::log('Loading target language strings from database', 3);

		// Load from DB
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*');
		$query->select('CONCAT(s.extension,".ini:", UPPER(s.constant)) AS arraykey');
		$query->from('#__neno_langfile_translations AS a');
		$query->join('INNER', '#__neno_langfile_source AS s ON s.id = a.source_id');

		if (!is_null($lang))
		{
			$query->where('a.language = "' . $lang . '"');
		}

		if (!is_null($ids) && is_array($ids) && count($ids) > 0)
		{
			$query->where('a.id IN (' . implode(',', $ids) . ')');
		}

		// Order by lang and then extension
		$query->order('s.lang')->order('s.extension');

		$db->setQuery($query);
		$rows = $db->loadObjectList('arraykey');

		return $rows;
	}

	/**
	 * Export from database to files
	 *
	 * @return void
	 */
	public function export()
	{
		// $source_language_strings = $this->loadTranslations();
	}

	/**
	 * Takes and array of strings and updates them in the database
	 *
	 * @param   array $rows an object list that must contain the id of the row to update and the new string
	 *
	 * @return boolean
	 */
	public function updateStringsInTargetDatabase($rows)
	{
		if (empty($rows))
		{
			return false;
		}

		foreach ($rows as $row)
		{
			$db    = JFactory::getDbo();
			$query = 'UPDATE #__neno_langfile_translations';

			$query .= "\n SET string = " . $db->quote($row->string)
				. ", time_translated = NOW()"
				. ", version = version+1"
				. ", translation_method = 'langfile'"
				. "\n WHERE id = " . $db->quote($row->id);

			$db->setQuery($query);
			$db->execute();

			NenoLog::log('Updating database target string: ' . $row->id . ' = "' . $row->string . '"', 3);

			// Reset execution time
			set_time_limit(ini_get('max_execution_time'));

		}

		// Set a message in log and for display
		$message = JText::sprintf('COM_NENO_LANGFILES_MSG_UPDATED_STRINGS', count($rows));
		NenoLog::log($message, 2, true);

		return true;
	}

	/**
	 *
	 * /**
	 * Look for language files for a specific extension in various directories
	 *
	 * @param   string $lang      Language tag
	 * @param   string $extension Extension name
	 *
	 * @return array of files with full path
	 */
	protected function getLanguageFileListForExtension($lang, $extension)
	{
		$files = array();

		// Look for a matching file
		$file_name = $lang . '.' . $extension . '.ini';

		// Look for language files in these paths
		$folders = $this->getLanguageFileFolders($extension);

		foreach ($folders as $folder)
		{
			$files = array_merge($files, JFolder::files($folder, $file_name, true, true));
		}

		return $files;
	}

	/**
	 * Takes an array of full file names and loads the language strings into an array with a unique key for each string
	 * For easy comparison the keys are as follows: [filename:constant]
	 *
	 * @param   array $language_files list of file names to parse
	 *
	 * @return array
	 */
	protected function getLanguageStringsFromFileList($language_files)
	{
		$strings = array();

		if (!empty($language_files))
		{
			foreach ($language_files as $language_file)
			{
				$file_strings = $this->getLanguageStringsFromFile($language_file);

				if (!empty($file_strings))
				{
					// Remove the language code from the file name
					$language_file                = basename($language_file);
					$language_file_parts          = explode('.', $language_file);
					$language_file_lancode_length = strlen($language_file_parts[0]) + 1;
					$language_file                = substr($language_file, $language_file_lancode_length - strlen($language_file));

					// Loop each string in the file
					foreach ($file_strings as $constant => $string)
					{
						$string_key           = $language_file . ':' . strtoupper($constant);
						$strings[$string_key] = $string;
					}
				}
			}
		}

		array_unique($strings);

		NenoLog::log('Found ' . count($strings) . ' language strings in ' . count($language_files) . ' language files', 3);

		return $strings;
	}

	/**
	 * Loads a language file and returns an associated array of key value pairs
	 *
	 * @param   string $path Language file path
	 *
	 * @return array of strings
	 */
	protected function getLanguageStringsFromFile($path)
	{
		if (!is_file($path))
		{
			return false;
		}

		$contents = file_get_contents($path);
		$contents = str_replace('_QQ_', '"\""', $contents);
		$strings  = @parse_ini_string($contents);

		return $strings;
	}

	/**
	 * Load all language strings from the database
	 *
	 * @return array object list
	 */
	protected function getSourceLanguageStringsFromDatabase()
	{
		// Return cached results if we have them
		if (!empty($this->sourceLanguageStrings))
		{
			NenoLog::log('Loaded ' . count($this->sourceLanguageStrings) . ' source language strings from cache', 3);

			return $this->sourceLanguageStrings;
		}

		// Default to source language
		$lang = $this->sourceLanguage;

		// Load from DB
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*');
		$query->select('CONCAT(a.extension,".ini:", UPPER(a.constant)) AS arraykey');
		$query->from('#__neno_langfile_source AS a');

		$query->where('a.language = "' . $lang . '"');
		$query->where('a.state = 1');

		// Order by lang and then extension
		$query->order('a.language')->order('a.extension');

		$db->setQuery($query);
		$this->sourceLanguageStrings = $db->loadObjectList('arraykey');

		NenoLog::log('Loaded ' . count($this->sourceLanguageStrings) . ' source language strings in the database', 3);

		return $this->sourceLanguageStrings;
	}

	/**
	 * Load a list of unique extensions from the source table
	 *
	 * @return array of extension names
	 */
	protected function loadSourceExtensions()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('DISTINCT extension');
		$query->from('#__neno_langfile_source');

		$query->where('lang = ' . $db->quote($this->sourceLanguage));
		$query->where('state = 1');

		$db->setQuery($query);
		$rows = $db->loadColumn();

		return $rows;
	}

	/**
	 * Takes and object list and marks the id of each object as deleted
	 *
	 * @param   string $type    'source' or 'target'
	 * @param   array  $strings Array of strings to delete
	 *
	 * @return boolean
	 */
	protected function deleteStringsFromDatabase($type, $strings)
	{
		if (empty($strings))
		{
			return false;
		}

		if ($type == 'source')
		{
			$table = '#__neno_langfile_source';
		}
		else
		{
			if ($type == 'target')
			{
				$table = '#__neno_langfile_translations';
			}
			else
			{
				return false;
			}
		}

		$db = JFactory::getDbo();

		$query = 'UPDATE ' . $table . ' SET state = -1, time_deleted = NOW()';

		$ids = array();

		foreach ($strings as $key => $string)
		{
			$ids[] = $string->id;
			NenoLog::log('Deleting ' . $type . ' string from database: ' . $key . ' = "' . $string->string . '"', 3);
		}

		$query .= 'WHERE id IN (' . implode(',', $ids) . ')';

		$db->setQuery($query);
		$db->execute();

		// Message
		$message = JText::sprintf('COM_NENO_LANGFILES_MSG_DELETED_STRINGS', count($strings), $this->getPrintableTypeName($type), $lang);
		NenoLog::log($message, 2, true);

		return true;
	}
}
