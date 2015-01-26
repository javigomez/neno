<?php

/**
 * @package     Neno
 * @subpackage  ContentElement
 *
 * @copyright   Copyright (c) 2014 Jensen Technologies S.L. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_NENO') or die;

/**
 * Class NenoContentElementLangfile
 *
 * @since  1.0
 */
class NenoContentElementLangfile extends NenoContentElement
{
	/**
	 * Source language string type
	 */
	const SOURCE_LANGUAGE_TYPE = 'source';

	/**
	 * Target language string type
	 */
	const TARGET_LANGUAGE_TYPE = 'target';

	/**
	 * This state is for a string that has been translated
	 */
	const TRANSLATED_STATE = 1;

	/**
	 * This state is for a string that has been sent to be translated but the translation has not arrived yet.
	 */
	const QUEUED_FOR_BEING_TRANSLATED_STATE = 2;

	/**
	 * This state is for a string that its source string has changed.
	 */
	const SOURCE_CHANGED_STATE = 3;

	/**
	 * This state is for a string that has not been translated yet or the user does not want to translated it
	 */
	const NOT_TRANSLATED_STATE = 4;

	/**
	 * @var String
	 */
	protected $string;

	/**
	 * @var string
	 */
	protected $language;

	/**
	 * @var DateTime
	 */
	protected $timeDeleted;

	/**
	 * @var integer
	 */
	protected $state;

	/**
	 * @var integer
	 */
	protected $version;

	/**
	 * @var string
	 */
	protected $constant;

	/**
	 * @var string
	 */
	protected $extension;

	/**
	 * @var DateTime
	 */
	protected $timeAdded;

	/**
	 * @var DateTime
	 */
	protected $timeChanged;

	/**
	 * @var NenoContentElementGroup
	 */
	protected $group;

	/**
	 * @param string $language
	 *
	 * @return array
	 */
	public static function loadSourceLanguageStrings($language)
	{
		// Load from DB
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select(
				array(
					'a.*',
					'CONCAT(a.extension,".ini:", UPPER(a.constant)) AS arraykey'
				)
			)
			->from($db->quoteName(self::getDbTable()) . ' AS a')
			->where(
				array(
					'a.language = ' . $db->quote($language),
					'a.state = 1'
				)
			)
			->order(
			// Order by lang and then extension
				array(
					'a.language',
					'a.extension'
				)
			);


		$db->setQuery($query);
		$sourceLanguageStrings = $db->loadObjectList('arraykey');

		$arrayKeys = array_keys($sourceLanguageStrings);

		foreach ($arrayKeys as $arrayKey)
		{
			$sourceLanguageStrings[$arrayKey] = new NenoContentElementLangfile($sourceLanguageStrings[$arrayKeys]);
		}

		// Log it if the debug mode is on
		if (JDEBUG)
		{
			NenoLog::log('Loaded ' . count($sourceLanguageStrings) . ' source language strings in the database', 3);
		}

		return $sourceLanguageStrings;
	}

	/**
	 * @param string $type
	 * @param array  $options ('fieldName' => 'fieldValue')
	 *
	 * @return NenoContentElementLangfile
	 */
	public static function getLanguageString(array $options)
	{
		$db = JFactory::getDbo();
		$db->setQuery(static::getLanguageStringQuery($options));
		$data           = $db->loadAssoc();
		$languageString = new NenoContentElementLangfile($data);

		return $languageString;
	}

	/**
	 * @param string $type
	 * @param array  $options
	 *
	 * @return JDatabaseQuery
	 */
	protected static function getLanguageStringQuery(array $options)
	{
		$tableName = self::getDbTable();
		$db        = JFactory::getDbo();
		$query     = $db->getQuery(true);

		$query
			->select('*')
			->from($tableName);

		foreach ($options as $fieldName => $fieldValue)
		{
			if (!is_null($fieldValue) && !is_null($fieldName))
			{
				$query->where($db->quoteName($fieldName) . ' = ' . $db->quote($fieldValue));
			}
		}

		return $query;
	}

	/**
	 * @param string $type
	 * @param array  $options
	 *
	 * @return array
	 */
	public static function getLanguageStrings($type, array $options)
	{
		$db = JFactory::getDbo();
		$db->setQuery(static::getLanguageStringQuery($type, $options));
		$dataList = $db->loadAssocList();

		$languageStringList = array();

		foreach ($dataList as $data)
		{
			// Sanitize the array
			$data                                               = NenoHelper::convertDatabaseArrayToClassArray($data);
			$languageString                                     = new NenoContentElementLangfile($data);
			$languageStringList[$languageString->generateKey()] = $languageString;
		}

		return $languageStringList;

	}

	/**
	 * Generate the language key based on its datas
	 *
	 * @return string
	 */
	public function generateKey()
	{
		return $this->getExtension() . '.ini:' . $this->getConstant();
	}

	/**
	 * Get the name of the extension that owns this string
	 *
	 * @return string
	 */
	public function getExtension()
	{
		return $this->extension;
	}

	/**
	 * Set the name of the extension that owns this string
	 *
	 * @param string $extension
	 *
	 * @return NenoContentElementLangfile
	 */
	public function setExtension($extension)
	{
		$this->extension = $extension;

		return $this;
	}

	/**
	 * Get the constant that identifies the string
	 *
	 * @return string
	 */
	public function getConstant()
	{
		return $this->constant;
	}

	/**
	 * Set the constant that identifies the string
	 *
	 * @param   string $constant Constant
	 *
	 * @return NenoContentElementLangfile
	 */
	public function setConstant($constant)
	{
		$this->constant = $constant;

		return $this;
	}

	/**
	 * @return NenoContentElementGroup
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @param NenoContentElementGroup $group
	 */
	public function setGroup(NenoContentElementGroup $group)
	{
		$this->group = $group;
	}

	/**
	 * Get the time when the string was discovered
	 *
	 * @return DateTime
	 */
	public function getTimeAdded()
	{
		return $this->timeAdded;
	}

	/**
	 * Set the time when the string was discovered
	 *
	 * @param   DateTime $timeAdded Discover time
	 *
	 * @return NenoContentElementLangfile
	 */
	public function setTimeAdded($timeAdded)
	{
		$this->timeAdded = $timeAdded;

		return $this;
	}

	/**
	 * Get the time when the string was changed (if it has been changed)
	 *
	 * @return DateTime
	 */
	public function getTimeChanged()
	{
		return $this->timeChanged;
	}

	/**
	 * Set the time when the string changed
	 *
	 * @param   DateTime $timeChanged
	 *
	 * @return NenoContentElementLangfile
	 */
	public function setTimeChanged($timeChanged)
	{
		$this->timeChanged = $timeChanged;

		return $this;
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
			$query = $db->getQuery();

			$query
				->update('#__neno_langfile_translations')
				->set(
					array(
						'string = ' . $db->quote($row->string),
						'time_translated = NOW()',
						'version = version + 1',
						'translation_method = ' . $db->quote('langfile')
					)
				)
				->where('id = ' . $db->quote($row->id));

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
	 * {@inheritdoc}
	 *
	 * @return JObject
	 */
	public function toObject()
	{
		$data = parent::toObject();
		$data->set('group_id', $this->group->getId());

		return $data;
	}

	/**
	 * @return String
	 */
	public function getString()
	{
		return $this->string;
	}

	/**
	 * @param String $string
	 */
	public function setString($string)
	{
		$this->string = $string;
	}

	public function increaseVersion()
	{
		$this->version = $this->version + 1;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * @param string $language
	 */
	public function setLanguage($language)
	{
		$this->language = $language;
	}

	/**
	 * @return DateTime
	 */
	public function getTimeDeleted()
	{
		return $this->timeDeleted;
	}

	/**
	 * @param DateTime $timeDeleted
	 */
	public function setTimeDeleted($timeDeleted)
	{
		$this->timeDeleted = $timeDeleted;
	}

	/**
	 * @return int
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @param int $state
	 */
	public function setState($state)
	{
		$this->state = $state;
	}

	/**
	 * @return int
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * @param int $version
	 */
	public function setVersion($version)
	{
		$this->version = $version;
	}

	/**
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
		$fileName = $lang . '.' . $extension . '.ini';

		// Look for language files in these paths
		$folders = $this->getLanguageFileFolders($extension);

		foreach ($folders as $folder)
		{
			$files = array_merge($files, JFolder::files($folder, $fileName, true, true));
		}

		return $files;
	}

	/**
	 * Return a list of folders where language files may reside, ordered by override order
	 *
	 * @param   string $extension The name of the extension such as 'com_neno'
	 * @param   string $lang      eg. 'en-GB'
	 *
	 * @return array with a list of full path names
	 */
	protected function getLanguageFileFolders($extension = null, $lang = null)
	{
		$folders = array();

		// Always language first
		if (!is_null($lang))
		{
			$folders[] = JPATH_SITE . '/language/' . $lang . '/';
		}
		else
		{
			$folders[] = JPATH_SITE . '/language/';
		}

		// If extension is given then try to be more specific about where the folders may be (for performance)
		if (!empty($extension))
		{
			// Split extension name by _ to determine if it is a component, module or plugin
			$extensionParts = explode('_', $extension);

			$specificPath = '';

			if ($extensionParts[0] == 'com')
			{
				$specificPath = JPATH_SITE . '/components/' . $extension . '/language/';
			}
			else
			{
				if ($extensionParts[0] == 'mod')
				{
					$specificPath = JPATH_SITE . '/modules/' . $extension . '/language/';
				}
				else
				{
					if ($extensionParts[0] == 'plg')
					{
						$specificPath = JPATH_SITE . '/plugins/';
					}
				}
			}

			if (is_file($specificPath))
			{
				$folders[] = $specificPath;
			}
		}
		else
		{
			$folders[] = JPATH_SITE . '/components/';
			$folders[] = JPATH_SITE . '/modules/';
			$folders[] = JPATH_SITE . '/plugins/';
		}

		// Always template overwrite last
		$folders[] = JPATH_SITE . '/templates/';

		return $folders;
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
		$newStrings = $this->getNewStringsInLanguageFiles('source');

		if (!empty($newStrings))
		{
			foreach ($newStrings as $lang => $strings)
			{
				$this->addStringsToDatabase('source', $strings, $lang);
			}
		}

		// Deleted source strings
		$deletedStrings = $this->getDeletedSourceStringsInLanguageFiles();

		if (!empty($deletedStrings[$this->sourceLanguage]))
		{
			$this->markSourceStringsDeletedInDatabase($deletedStrings[$this->sourceLanguage]);
		}

		// Changed source strings
		$changed_strings = $this->getChangedStringsInLanguageFiles('source');

		if (!empty($changed_strings[$this->sourceLanguage]))
		{
			$this->updateStringsInSourceDatabase($changed_strings[$this->sourceLanguage]);
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
		if ($type == 'source')
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
			$fileStrings = $this->loadLanguageStringsFromFiles($langCode);

			// If we are finding new target strings then don't import the strings
			// that does not have a matching source string
			if ($type == 'target')
			{
				// Filter out strings that are not already in the source database
				$sourceLanguageStrings = $this->loadLanguageStringsFromDatabase('source');
				$fileStrings           = array_intersect_key($fileStrings, $sourceLanguageStrings);
			}

			// Load constants from database
			$databaseStrings = $this->loadLanguageStringsFromDatabase($type, $langCode);

			// Filter the list to only have strings that are not already imported
			$newStrings[$langCode] = array_diff_key($fileStrings, $databaseStrings);

			NenoLog::log('Found ' . count($newStrings[$langCode]) . ' new strings in [' . $langCode . '] language files', 2);
		}

		return $newStrings;
	}

	/**
	 * Load all constants from a given language
	 *
	 * @param   string $language eg. 'en-GB'
	 *
	 * @return array indexed by file
	 */
	protected function loadLanguageStringsFromFiles($language)
	{
		$files = $this->findLanguageFiles($language);

		return $this->getLanguageStringsFromFileList($files);
	}

	/**
	 * Find language files for a given language
	 *
	 * @param   string $lang 'en-GB'
	 *
	 * @return array indexed first by language
	 */
	protected function findLanguageFiles($lang)
	{
		$files = array();

		$folders = $this->getLanguageFileFolders(null, $lang);

		if (!empty($folders))
		{
			foreach ($folders as $folder)
			{
				$files = array_merge($files, $this->getLanguageFilesInPath($folder, $lang));
			}
		}

		// Debug
		NenoLog::log('Found ' . count($files) . ' language files in ' . $lang . '', 3);

		if (!empty($files))
		{
			foreach ($files as $file)
			{
				NenoLog::log('Found file ' . $file . ' in ' . $lang . '', 3);
			}
		}

		return $files;
	}

	/**
	 * Takes an array of full file names and loads the language strings into an array with a unique key for each string
	 * For easy comparison the keys are as follows: [filename:constant]
	 *
	 * @param   array $languageFiles list of file names to parse
	 *
	 * @return array
	 */
	protected function getLanguageStringsFromFileList($languageFiles)
	{
		$strings = array();

		if (!empty($languageFiles))
		{
			foreach ($languageFiles as $languageFile)
			{
				$file_strings = $this->getLanguageStringsFromFile($languageFile);

				if (!empty($file_strings))
				{
					// Remove the language code from the file name
					$languageFile               = basename($languageFile);
					$languageFileParts          = explode('.', $languageFile);
					$languageFileLangCodeLength = strlen($languageFileParts[0]) + 1;
					$languageFile               = substr($languageFile, $languageFileLangCodeLength - strlen($languageFile));

					// Loop each string in the file
					foreach ($file_strings as $constant => $string)
					{
						$string_key           = $languageFile . ':' . strtoupper($constant);
						$strings[$string_key] = $string;
					}
				}
			}
		}

		array_unique($strings);

		NenoLog::log('Found ' . count($strings) . ' language strings in ' . count($languageFiles) . ' language files', 3);

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
	 * Load language strings from the database
	 *
	 * @param   string $type 'source' or 'target'
	 * @param   string $lang optional 'en-GB'
	 *
	 * @return array indexed array of constant/string pairs
	 */
	protected function loadLanguageStringsFromDatabase($type, $lang = null)
	{
		if ($type == 'source')
		{
			return $this->getSourceLanguageStringsFromDatabase();
		}
		else
		{
			if ($type == 'target')
			{
				return $this->getTargetLanguageStringsFromDatabase($lang);
			}
			else
			{
				return false;
			}
		}
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

		$query
			->select(
				array(
					'a.*',
					'CONCAT(a.extension,".ini:", UPPER(a.constant)) AS arraykey'
				)
			)
			->from('#__neno_langfile_source AS a')
			->where(
				array(
					'a.lang = "' . $lang . '"',
					'a.state = 1'
				)
			)
			->order(
				array(
					'a.lang',
					'a.extension'
				)
			);

		$db->setQuery($query);
		$this->sourceLanguageStrings = $db->loadObjectList('arraykey');

		NenoLog::log('Loaded ' . count($this->sourceLanguageStrings) . ' source language strings in the database', 3);

		return $this->sourceLanguageStrings;
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

		$query
			->select(
				array(
					'a.*',
					'CONCAT(s.extension,".ini:", UPPER(s.constant)) AS arraykey'
				)
			)
			->from('`#__neno_langfile_translations` AS a')
			->innerJoin('`#__neno_langfile_source` AS s ON s.id = a.source_id')
			->order(
			// Order by lang and then extension
				array(
					's.lang',
					's.extension'
				)
			);

		if (!is_null($lang))
		{
			$query->where('a.lang = ' . $db->quote($lang));
		}

		if (!is_null($ids) && is_array($ids) && count($ids) > 0)
		{
			$query->where('a.id IN (' . implode(',', $ids) . ')');
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList('arraykey');

		return $rows;
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

		// To not overload the system insert 100 strings at a time
		$limitPerInsert = 100;

		$chunkedStrings = array_chunk($strings, $limitPerInsert, true);
		$db             = JFactory::getDbo();

		foreach ($chunkedStrings as $chunkedString)
		{
			// Set table name and column names if we are saving translations
			$tableName = '#__neno_langfile_translations';
			$columns   = array(
				'source_id',
				'string',
				'time_translated',
				'version',
				'lang',
				'translation_method'
			);

			// Set table name and column names if we are saving source language strings
			if ($type == 'source')
			{
				$tableName = '#__neno_langfile_source';
				$columns   = array(
					'string',
					'constant',
					'lang',
					'extension',
					'time_added',
					'version'
				);
			}

			$query = $db->getQuery(true);

			$query
				->insert($tableName)
				->columns($columns);

			foreach ($chunkedString as $key => $string)
			{
				$stringInfo = NenoHelper::getLanguageFileStringInfoFromStringKey($key);
				$constant   = $stringInfo['constant'];
				$extension  = $stringInfo['extension'];

				if ($type == 'source')
				{
					$query->values($db->quote($string) . ', ' . $db->quote($constant) . ', ' . $db->quote($lang) . ', ' . $db->quote($extension) . ', NOW(), 1');
				}
				else
				{
					$selectQuery = $db->getQuery(true);
					$selectQuery
						->select('id')
						->from('#__neno_langfile_source')
						->where(
							array(
								'constant = ' . $db->quote($constant),
								'extension = ' . $db->quote($extension)
							)
						);

					$query->values('(' . (string) $selectQuery . '),' . $db->quote($string) . ', NOW(), 1, ' . $db->quote($lang) . ', ' . $db->quote('langfile'));
				}

				NenoLog::log('Adding new ' . $type . ' string to DB in language [' . $lang . ']: ' . $constant . ' = "' . $string . '"', 3);
			}

			$db->setQuery($query);
			$db->execute();
		}

		$message = JText::sprintf('COM_NENO_LANGFILES_MSG_NEW_STRINGS', count($strings), $this->getPrintableTypeName($type));
		NenoLog::log($message, 2, true);

		return true;
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
	public function getDeletedSourceStringsInLanguageFiles()
	{
		// Load constants from files
		$fileStrings = $this->loadLanguageStringsFromFiles($this->sourceLanguage);

		// Load constants from database
		$databaseStrings = $this->loadLanguageStringsFromDatabase('source');

		// Find differences between the two datasets
		$deletedStrings = array_diff_key($databaseStrings, $fileStrings);

		// For consistency with new and changed strings deliver the result in a lang array
		$languages                        = array();
		$languages[$this->sourceLanguage] = $deletedStrings;

		NenoLog::log('Found ' . count($deletedStrings) . ' strings deleted from [' . $this->sourceLanguage . '] language files', 2);

		return $languages;
	}

	/**
	 * Mark both source and target as deleted when we discover a deleted source string
	 *
	 * @param   array $strings object list with ->id defining the source id that needs deleting
	 *
	 * @return boolean
	 */
	protected function markSourceStringsDeletedInDatabase($strings)
	{
		if (empty($strings))
		{
			return false;
		}

		// Get source string ids
		$ids = array();

		foreach ($strings as $key => $string)
		{
			$ids[] = $string->id;
			NenoLog::log('Deleting string from #__neno_langfile_source: ' . $key . ' = "' . $string->string . '"', 3);
		}

		// Mark source table
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->update('#__neno_langfile_source')
			->set(
				array(
					'state = -1',
					'time_deleted = NOW()'
				)
			)
			->where('id IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);
		$db->execute();

		// Mark target table
		$query
			->update('#__neno_langfile_translations')
			->clear('where')
			->where('source_id IN (' . implode(',', $ids) . ')');


		$db->setQuery($query);
		$db->execute();

		// Message
		$message = JText::sprintf('COM_NENO_LANGFILES_MSG_DELETED_STRINGS', count($strings));
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
	public function getChangedStringsInLanguageFiles($type)
	{
		// Create an array of languages we need to load depending on type
		if ($type == 'source')
		{
			$langObject            = new stdClass();
			$langObject->lang_code = $this->sourceLanguage;
			$languages             = array($langObject);
		}
		else
		{
			$languages = NenoHelper::getTargetLanguages();
		}

		$changes = array();

		foreach ($languages as $lang)
		{
			$langCode           = $lang->lang_code;
			$changes[$langCode] = array();

			// Load constants from files
			$fileStrings = $this->loadLanguageStringsFromFiles($langCode);

			// Load constants from database
			$databaseStrings = $this->loadLanguageStringsFromDatabase($type, $langCode);

			foreach ($fileStrings as $key => $fileString)
			{
				// Skip if not in the database (new string)
				if (!isset($databaseStrings[$key]))
				{
					NenoLog::log('Skipping string change comparison on ' . $key . ' as it is not found in the database', 3);
				}
				else
				{
					if ($databaseStrings[$key]->string != $fileString)
					{
						$changes[$langCode][$key] = $fileString;
					}
				}
			}
		}

		return $changes;
	}

	/**
	 * Takes and array of strings and updates them in the database
	 *
	 * @param   array $strings Strings to update
	 *
	 * @return boolean
	 */
	protected function updateStringsInSourceDatabase($strings)
	{
		if (empty($strings))
		{
			return false;
		}

		$lang  = $this->sourceLanguage;
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__neno_langfile_source');

		foreach ($strings as $key => $string)
		{
			$string_info = NenoHelper::getLanguageFileStringInfoFromStringKey($key);
			$constant    = $string_info['constant'];

			$extension = $string_info['extension'];

			$query
				->clear('set')// Deleting SET clause
				->set(
					array(
						'string = ' . $db->quote($string),
						'time_changed = NOW()',
						'version = version + 1'
					)
				)
				->clear('where')// Deleting WHERE clause
				->where(
					array(
						'constant = ' . $db->quote($constant),
						'lang = ' . $db->quote($lang),
						'extension = ' . $db->quote($extension)
					)
				);

			$db->setQuery($query);
			$db->execute();

			NenoLog::log('Updating database source string: ' . $constant . ' = "' . $string . '"', 3);
		}

		// Destroy the cache of the source language as it now has new values
		$this->sourceLanguageStrings = array();

		// Set a message in log and for display
		$message = JText::sprintf('COM_NENO_LANGFILES_MSG_UPDATED_STRINGS', count($strings));
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
		$newStrings = $this->getNewStringsInLanguageFiles('target');

		if (!empty($newStrings))
		{
			foreach ($newStrings as $lang => $strings)
			{
				$this->addStringsToDatabase('target', $strings, $lang);
			}
		}
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

		$query
			->select('DISTINCT extension')
			->from('#__neno_langfile_source')
			->where(
				array(
					'lang = ' . $db->quote($this->sourceLanguage),
					'state = 1'
				)
			);

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

		$ids = array();

		foreach ($strings as $key => $string)
		{
			$ids[] = $string->id;
			NenoLog::log('Deleting ' . $type . ' string from database: ' . $key . ' = "' . $string->string . '"', 3);
		}

		$query = $db->getQuery(true);;
		$query
			->update($table)
			->set(
				array(
					'state = -1',
					'time_deleted = NOW()'
				)
			)
			->where('id IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);
		$db->execute();

		// Message
		$message = JText::sprintf('COM_NENO_LANGFILES_MSG_DELETED_STRINGS', count($strings), $this->getPrintableTypeName($type), $lang);
		NenoLog::log($message, 2, true);

		return true;
	}
}
