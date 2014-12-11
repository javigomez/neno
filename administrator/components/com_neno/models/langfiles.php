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
		$new_strings = $this->getNewStringsInLangfiles('source');

		if (!empty($new_strings))
		{
			foreach ($new_strings as $lang => $strings)
			{
				$this->addStringsToDatabase('source', $strings, $lang);
			}
		}

		// Deleted source strings
		$deleted_strings = $this->getDeletedSourceStringsInLangfiles();

		if (!empty($deleted_strings[$this->sourceLanguage]))
		{
			$this->markSourceStringsDeletedInDatabase($deleted_strings[$this->sourceLanguage]);
		}

		// Changed source strings
		$changed_strings = $this->getChangedStringsInLangfiles('source');

		if (!empty($changed_strings[$this->sourceLanguage]))
		{
			$this->updateStringsInSourceDatabase($changed_strings[$this->sourceLanguage]);
		}

		NenoLog::log('Finished importing Source Language Strings from files');
	}

	/**
	 * Find new strings in files
	 *
	 * @param   string  $type  'source' or 'target'
	 *
	 * @return array with new constants indexed by language
	 */
	public function getNewStringsInLangfiles($type)
	{
		// Create an array of languages we need to load depending on type
		if ($type == 'source')
		{
			$lang_object = new stdClass();
            $lang_object->lang_code = $this->sourceLanguage;
            $langs = array( $lang_object );			
		}
		else
		{
			$langs = NenoHelper::getTargetLanguages();
		}

		$new_strings = array();

		foreach ($langs as $lang)
		{
            $lang_code = $lang->lang_code;

            // Load constants from files
			$file_strings = $this->loadLanguageStringsFromFiles($lang_code);

			// If we are finding new target strings then don't import the strings
			// that does not have a matching source string
			if ($type == 'target')
			{
				// Filter out strings that are not already in the source database
				$source_language_strings = $this->loadLanguageStringsFromDatabase('source');
				$file_strings            = array_intersect_key($file_strings, $source_language_strings);
			}

			// Load constants from database
			$database_strings = $this->loadLanguageStringsFromDatabase($type, $lang_code);

			// Filter the list to only have strings that are not already imported
			$new_strings[$lang_code] = array_diff_key($file_strings, $database_strings);

			NenoLog::log('Found ' . count($new_strings[$lang_code]) . ' new strings in [' . $lang_code . '] language files', 2);
		}

		return $new_strings;
	}

	/**
	 * Load all constants from a given language
	 *
	 * @param   string  $language  eg. 'en-GB'
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
	 * @param   string  $lang  'en-GB'
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
	 * Return a list of folders where language files may reside, ordered by override order
	 *
	 * @param   string  $extension  The name of the extension such as 'com_neno'
	 * @param   string  $lang       eg. 'en-GB'
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
			$extension_parts = explode('_', $extension);

			$specific_path = '';

			if ($extension_parts[0] == 'com')
			{
				$specific_path = JPATH_SITE . '/components/' . $extension . '/language/';
			}
			else
			{
				if ($extension_parts[0] == 'mod')
				{
					$specific_path = JPATH_SITE . '/modules/' . $extension . '/language/';
				}
				else
				{
					if ($extension_parts[0] == 'plg')
					{
						$specific_path = JPATH_SITE . '/plugins/';
					}
				}
			}

			if (is_file($specific_path))
			{
				$folders[] = $specific_path;
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
	 * Return a list of all the language files in the given path
	 *
	 * @param   string   $path                Full path to the folder that should be looked in
	 * @param   string   $lang                Language tag
	 * @param   boolean  $recursive           Weather or not sub folders should be looked in
	 * @param   boolean  $ignore_joomla_core  Weather core Joomla files should be ignored, defaults to true
	 *
	 * @return array
	 */
	protected function getLanguageFilesInPath($path, $lang = null, $recursive = true, $ignore_joomla_core = true)
	{
		jimport('joomla.filesystem.folder');

		if (is_null($lang))
		{
			$filter = '\.ini$';
		}
		else
		{
			$filter = '^' . $lang . '.*\.ini$';
		}

		NenoLog::log('Looking for language files in [' . $lang . '] inside: ' . $path, 3);

		// Load list
		$files = JFolder::files($path, $filter, $recursive, true);

		// Remove Joomla core files if needed
		if ($ignore_joomla_core === true)
		{
			$files = $this->removeCoreLanguageFilesFromArray($files, $lang);
		}

		// Debug
		if (!empty($files))
		{
			foreach ($files as $file)
			{
				NenoLog::log('Found file: ' . $file, 3);
			}
		}

		return $files;
	}

	/**
	 * Takes an array of language files and filters out known language files shipped with Joomla
	 *
	 * @param   array   $files  Files to translate
	 * @param   string  $lang   Language tag
	 *
	 * @return array
	 */
	protected function removeCoreLanguageFilesFromArray($files, $lang = null)
	{
		if (is_null($lang))
		{
			$lang = $this->sourceLanguage;
		}

		$core_files = array(
			$lang . '.com_ajax.ini'
		, $lang . '.com_config.ini'
		, $lang . '.com_contact.ini'
		, $lang . '.com_content.ini'
		, $lang . '.com_finder.ini'
		, $lang . '.com_neno.ini'
		, $lang . '.com_mailto.ini'
		, $lang . '.com_media.ini'
		, $lang . '.com_messages.ini'
		, $lang . '.com_newsfeeds.ini'
		, $lang . '.com_search.ini'
		, $lang . '.com_tags.ini'
		, $lang . '.com_users.ini'
		, $lang . '.com_weblinks.ini'
		, $lang . '.com_wrapper.ini'
		, $lang . '.files_joomla.sys.ini'
		, $lang . '.finder_cli.ini'
		, $lang . '.ini'
		, $lang . '.lib_fof.sys.ini'
		, $lang . '.lib_idna_convert.sys.ini'
		, $lang . '.lib_joomla.ini'
		, $lang . '.lib_joomla.sys.ini'
		, $lang . '.lib_phpass.sys.ini'
		, $lang . '.lib_phpmailer.sys.ini'
		, $lang . '.lib_phputf8.sys.ini'
		, $lang . '.lib_simplepie.sys.ini'
		, $lang . '.mod_articles_archive.ini'
		, $lang . '.mod_articles_archive.sys.ini'
		, $lang . '.mod_articles_categories.ini'
		, $lang . '.mod_articles_categories.sys.ini'
		, $lang . '.mod_articles_category.ini'
		, $lang . '.mod_articles_category.sys.ini'
		, $lang . '.mod_articles_latest.ini'
		, $lang . '.mod_articles_latest.sys.ini'
		, $lang . '.mod_articles_news.ini'
		, $lang . '.mod_articles_news.sys.ini'
		, $lang . '.mod_articles_popular.ini'
		, $lang . '.mod_articles_popular.sys.ini'
		, $lang . '.mod_banners.ini'
		, $lang . '.mod_banners.sys.ini'
		, $lang . '.mod_breadcrumbs.ini'
		, $lang . '.mod_breadcrumbs.sys.ini'
		, $lang . '.mod_custom.ini'
		, $lang . '.mod_custom.sys.ini'
		, $lang . '.mod_feed.ini'
		, $lang . '.mod_feed.sys.ini'
		, $lang . '.mod_finder.ini'
		, $lang . '.mod_finder.sys.ini'
		, $lang . '.mod_footer.ini'
		, $lang . '.mod_footer.sys.ini'
		, $lang . '.mod_languages.ini'
		, $lang . '.mod_languages.sys.ini'
		, $lang . '.mod_login.ini'
		, $lang . '.mod_login.sys.ini'
		, $lang . '.mod_menu.ini'
		, $lang . '.mod_menu.sys.ini'
		, $lang . '.mod_random_image.ini'
		, $lang . '.mod_random_image.sys.ini'
		, $lang . '.mod_related_items.ini'
		, $lang . '.mod_related_items.sys.ini'
		, $lang . '.mod_search.ini'
		, $lang . '.mod_search.sys.ini'
		, $lang . '.mod_stats.ini'
		, $lang . '.mod_stats.sys.ini'
		, $lang . '.mod_syndicate.ini'
		, $lang . '.mod_syndicate.sys.ini'
		, $lang . '.mod_tags_popular.ini'
		, $lang . '.mod_tags_popular.sys.ini'
		, $lang . '.mod_tags_similar.ini'
		, $lang . '.mod_tags_similar.sys.ini'
		, $lang . '.mod_users_latest.ini'
		, $lang . '.mod_users_latest.sys.ini'
		, $lang . '.mod_weblinks.ini'
		, $lang . '.mod_weblinks.sys.ini'
		, $lang . '.mod_whosonline.ini'
		, $lang . '.mod_whosonline.sys.ini'
		, $lang . '.mod_wrapper.ini'
		, $lang . '.mod_wrapper.sys.ini'
		, $lang . '.tpl_beezsss3.ini'
		, $lang . '.tpl_beez3.sys.ini'
		, $lang . '.tpl_beez3.ini'
		, $lang . '.tpl_protostar.ini'
		, $lang . '.tpl_protostar.sys.ini'

			// Template overrides that should be ignored
		, $lang . '.tpl_hathor.ini'
		, $lang . '.tpl_hathor.sys.ini'
		, $lang . '.tpl_isis.ini'
		, $lang . '.tpl_isis.sys.ini'
		);

		// Filter
		foreach ($files as $key => $file)
		{
			foreach ($core_files as $core_file)
			{
				$strlen = strlen($core_file);

				if (substr($file, strlen($file) - $strlen, $strlen) == $core_file)
				{
					unset($files[$key]);
					continue 2;
				}
			}
		}

		// Get new keys
		$files = array_values($files);

		return $files;
	}

	/**
	 * Takes an array of full file names and loads the language strings into an array with a unique key for each string
	 * For easy comparison the keys are as follows: [filename:constant]
	 *
	 * @param   array  $language_files  list of file names to parse
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
	 * @param   string  $path  Language file path
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
	 * @param   string  $type  'source' or 'target'
	 * @param   string  $lang  optional 'en-GB'
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

		$query->select('a.*');
		$query->select('CONCAT(a.extension,".ini:", UPPER(a.constant)) AS arraykey');
		$query->from('#__neno_langfile_source AS a');

		$query->where('a.lang = "' . $lang . '"');
		$query->where('a.state = 1');

		// Order by lang and then extension
		$query->order('a.lang')->order('a.extension');

		$db->setQuery($query);
		$this->sourceLanguageStrings = $db->loadObjectList('arraykey');

		NenoLog::log('Loaded ' . count($this->sourceLanguageStrings) . ' source language strings in the database', 3);

		return $this->sourceLanguageStrings;
	}

	/**
	 * Load all language strings from the database
	 *
	 * @param   string  $lang  eg. 'en-GB'
	 * @param   array   $ids   The id of the strings that should be returned
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
			$query->where('a.lang = "' . $lang . '"');
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
	 * Takes and array of strings and adds them to the database
	 *
	 * @param   string  $type     either 'source' or 'target'
	 * @param   array   $strings  Strings to add
	 * @param   string  $lang     eg. 'en-GB'
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
		$limit_per_insert = 100;

		$chunked_strings = array_chunk($strings, $limit_per_insert, true);

		foreach ($chunked_strings as $chunked_string)
		{
			$db = JFactory::getDbo();

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

			foreach ($chunked_string as $key => $string)
			{
				$string_info = $this->getInfoFromStringKey($key);
				$constant    = $string_info['constant'];
				$extension   = $string_info['extension'];

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
	 * Takes a string (used as key in internal arrays) and splits it into an array of information
	 * Example key: com_phocagallery.sys.ini:COM_PHOCAGALLERY_XML_DESCRIPTION
	 *
	 * @param   string  $key  Language file key
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
		$keyParts         = explode(':', $key);
		$file_name        = $keyParts[0];
		$info['constant'] = $keyParts[1];

		// Split the file name by . for additional information
		$fileParts         = explode('.', $file_name);
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
	 * @param   string  $type  Type name
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
		$file_strings = $this->loadLanguageStringsFromFiles($this->sourceLanguage);

		// Load constants from database
		$database_strings = $this->loadLanguageStringsFromDatabase('source');

		// Find differences between the two datasets
		$deleted_strings = array_diff_key($database_strings, $file_strings);

		// For consistency with new and changed strings deliver the result in a lang array
		$langs                        = array();
		$langs[$this->sourceLanguage] = $deleted_strings;

		NenoLog::log('Found ' . count($deleted_strings) . ' strings deleted from [' . $this->sourceLanguage . '] language files', 2);

		return $langs;
	}

	/**
	 * Mark both source and target as deleted when we discover a deleted source string
	 *
	 * @param   array  $strings  object list with ->id defining the source id that needs deleting
	 *
	 * @return boolean
	 */
	protected function markSourceStringsDeletedInDatabase($strings)
	{
		if (empty($strings))
		{
			return false;
		}

		/**
		 * Mark source table
		 */
		$db    = JFactory::getDbo();
		$query = 'UPDATE #__neno_langfile_source SET state = -1, time_deleted = NOW()';

		$ids = array();

		foreach ($strings as $key => $string)
		{
			$ids[] = $string->id;
			NenoLog::log('Deleting string from #__neno_langfile_source: ' . $key . ' = "' . $string->string . '"', 3);
		}

		$query .= 'WHERE id IN (' . implode(',', $ids) . ')';

		$db->setQuery($query);
		$db->execute();

		/**
		 * Mark target table
		 */
		$db    = JFactory::getDbo();
		$query = 'UPDATE #__neno_langfile_translations SET state = -1, time_deleted = NOW()';

		$ids = array();

		foreach ($strings as $key => $string)
		{
			$ids[] = $string->id;
			NenoLog::log('Deleting string from #__neno_langfile_target: ' . $key . ' = "' . $string->string . '"', 3);
		}

		$query .= 'WHERE source_id IN (' . implode(',', $ids) . ')';

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
	 * @param   string  $type  'source' or 'target'
	 *
	 * @return array
	 */
	public function getChangedStringsInLangfiles($type)
	{
		// Create an array of languages we need to load depending on type
		if ($type == 'source')
		{
			$lang_object = new stdClass();
            $lang_object->lang_code = $this->sourceLanguage;
            $langs = array( $lang_object );
		}
		else
		{
			$langs = NenoHelper::getTargetLanguages();
		}

		$changes = array();

		foreach ($langs as $lang)
		{
            $lang_code = $lang->lang_code;
            $changes[$lang_code] = array();

			// Load constants from files
			$file_strings = $this->loadLanguageStringsFromFiles($lang_code);

			// Load constants from database
			$database_strings = $this->loadLanguageStringsFromDatabase($type, $lang_code);

			foreach ($file_strings as $key => $file_string)
			{
				// Skip if not in the database (new string)
				if (!isset($database_strings[$key]))
				{
					NenoLog::log('Skipping string change comparison on ' . $key . ' as it is not found in the database', 3);
					continue;
				}

				if ($database_strings[$key]->string != $file_string)
				{
					$changes[$lang_code][$key] = $file_string;
				}
			}
		}

		return $changes;
	}

	/**
	 * Takes and array of strings and updates them in the database
	 *
	 * @param   array  $strings  Strings to update
	 *
	 * @return boolean
	 */
	protected function updateStringsInSourceDatabase($strings)
	{
		if (empty($strings))
		{
			return false;
		}

		foreach ($strings as $key => $string)
		{
			$db    = JFactory::getDbo();
			$query = 'UPDATE #__neno_langfile_source';

			$string_info = $this->getInfoFromStringKey($key);
			$constant    = $string_info['constant'];
			$lang        = $this->sourceLanguage;
			$extension   = $string_info['extension'];
			$query .= "\n SET string = " . $db->quote($string)
				. ", time_changed = NOW()"
				. ", version = version+1"
				. "\n WHERE constant = " . $db->quote($constant)
				. "AND lang = " . $db->quote($lang)
				. "AND extension = " . $db->quote($extension);

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
		$new_strings = $this->getNewStringsInLangfiles('target');

		if (!empty($new_strings))
		{
			foreach ($new_strings as $lang => $strings)
			{
				$this->addStringsToDatabase('target', $strings, $lang);
			}
		}
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
	 * Finds language files for a given language extension and replaces the constant with string in each of them
	 *
	 * @param   string  $lang       Language tag
	 * @param   string  $extension  Extension name
	 * @param   string  $constant   Language Constant
	 * @param   string  $string     String used as replaced
	 *
	 * @return void
	 */
	public function updateLanguageFileString($lang, $extension, $constant, $string)
	{
		// Replace " in the string with &quot;
		$string = str_replace('"', '&quot;', $string);

		// Load language files for this extension
		$files = $this->getLanguageFileListForExtension($lang, $extension);

		// Search and replace inside each matching file
		if (!empty($files))
		{
			foreach ($files as $file)
			{
				if (is_file($file))
				{
					$content = file_get_contents($file);

					$regex           = '/(^' . trim($constant) . ' *= *")(.*)(".*)/im';
					$updated_content = preg_replace($regex, '${1}' . $string . '${3}', $content);

					if ($updated_content != $content)
					{
						file_put_contents($file, $updated_content);
					}
				}
			}
		}
	}

	/**
	 * Look for language files for a specific extension in various directories
	 *
	 * @param   string  $lang       Language tag
	 * @param   string  $extension  Extension name
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
	 * Takes and array of strings and updates them in the database
	 *
	 * @param   array  $rows  an object list that must contain the id of the row to update and the new string
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

            //Reset execution time
            set_time_limit(ini_get('max_execution_time'));            
            
		}

		// Set a message in log and for display
		$message = JText::sprintf('COM_NENO_LANGFILES_MSG_UPDATED_STRINGS', count($rows));
		NenoLog::log($message, 2, true);

		return true;
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
	 * @param   string  $type     'source' or 'target'
	 * @param   array   $strings  Array of strings to delete
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
