<?php
/**
 * @version     1.0.0
 * @package     com_lingo
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Soren Beck Jensen <soren@notwebdesign.com> - http://www.notwebdesign.com
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Lingo records.
 */
class LingoModelLangfiles extends JModelLegacy {

    public $source_language = null;
    public $target_languages = array();

    public function __construct() {

        parent::__construct();

        $language = JFactory::getLanguage();
        $this->source_language = $language->getDefault();
        $this->target_languages = $this->getTargetLanguages();
    }

    /**
     * Import from files into the database
     */
    public function import() {

        $this->importSourceLanguageStrings();

        $this->importTargetLanguageStrings();
    }

    /**
     * Export from database to files
     */
    public function export() {

        //$source_language_strings = $this->loadTranslations();
    }
    
    
    /**
     * Import translations from language files
     * This is quite complicated as it involvescomparing file language strings, source language strings and 
     * target language strings in multiple languages
     * To do this properly a few steps are used.
     * 1. Deal with one target language at a time
     * 2. Load language strings from all three sources using the same key to make comparison easy
     */
    protected function importTargetLanguageStrings() {
        
        //Load source language from database
        $source_strings_in_db = $this->getSourceLanguageStringsFromDatabase();
        
        //One language at a time
        if (!empty($this->target_languages)) {
            foreach ($this->target_languages as $target_lang) {

                LingoDebug::log('Looking for target language files to import', 3);

                //Find language strings in files for this language
                $files = $this->findTargetLanguageFiles($target_lang->lang_code);   
                $target_strings_in_files = $this->getLanguageStringsFromFileList($files);
                
                //Filter out strings that are not already in the source database
                $target_strings_in_files = array_intersect_key($target_strings_in_files, $source_strings_in_db);
                
                //Skip existing strings for now to get some content
                $target_strings_in_db = $this->getTargetLanguageStringsFromDatabase($target_lang->lang_code);
                //$target_strings_in_db = array(); //Change to actually load something
                
                $this->pushStringsToDatabase('target', $target_strings_in_files, $target_strings_in_db, $target_lang->lang_code);

                
            }
        }
        

        
        //One
        
        

//
//        //Find matching files per extension
//        foreach ($extensions as $extension) {
//
//            //Matching files
//            $files = $this->findTargetLanguageFiles($extension);
//            echo '<pre class="debug"><small>' . __file__ . ':' . __line__ . "</small>\n\$files = ". print_r($files, true)."\n</pre>";
//
//            if (!empty($files)) {
//                foreach ($files as $file) {
//
//                    //Target strings
//                    $target_language_file_strings = $this->getLanguageStringsFromFile($file);
//
//                    echo '<pre class="debug"><small>' . __file__ . ':' . __line__ . "</small>\n\$target_language_file_strings = ". print_r($target_language_file_strings, true)."\n</pre>";
//
//
//                    //Load existing source strings from database for comparison
//                    $existing_database_source_strings = $this->getSourceLanguageStringsFromDatabase(NULL, $extension, true);
//
//                    //Build an array of strings that needs to be imported
//                    $new_translated_strings_in_files = array();
//                    foreach ($existing_database_source_strings as $key => $existing_database_source_string) {
//                        if ($existing_database_source_string->time_translated != '0000-00-00 00:00:00') {
//
//                        }
//                    }
//                    echo '<pre class="debug"><small>' . __file__ . ':' . __line__ . "</small>\n\$existing_strings = ". print_r($existing_database_source_strings, true)."\n</pre>";
//
//
//
//                }
//                
//            }
//            
//        }

    }

    /**
     * Load a list of unique extensions from the source table
     * @return array of extension names
     */
    protected function loadSourceExtensions() {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('DISTINCT extension');
        $query->from('#__lingo_langfile_source');

        $query->where('lang = ' . $db->quote($this->source_language));
        $query->where('state = 1');

        $db->setQuery($query);
        $rows = $db->loadColumn();

        return $rows;
    }

    /**
     * Find already existing language files in the targeted languages
     * @param string $lang
     * @return array indexed first by language
     */
    protected function findTargetLanguageFiles($lang) {
        
        $files = array();
        
        $folders = $this->getLanguageFileFolders(null, $lang); 
        if (!empty($folders)) {
            foreach ($folders as $folder) {
                $files = array_merge($files, $this->getLanguageFilesInPath($folder, $lang));
            }
        }

        //Debug
        LingoDebug::log('Found '.count($files).' target language files in '.$lang.'', 3);
        if (!empty($files)) {
            foreach ($files as $file) {
                LingoDebug::log('Found file '.$file.' in '.$lang.'', 3);
            }
        }

        return $files;
        
    }

    /**
     * Import source language from files
     */
    protected function importSourceLanguageStrings() {

        LingoDebug::log('Importing Source Language Strings from files');

        //Load language strings
        $file_language_strings = $this->getSourceLanguageStringsFromFiles();

        //Load all existing strings in the database
        $database_language_strings = $this->getSourceLanguageStringsFromDatabase();

        //Push to database
        $this->pushStringsToDatabase('source', $file_language_strings, $database_language_strings);

        LingoDebug::log('Finished importing Source Language Strings from files');

    }

    /**
     * Finds language files for a given language, extention and replaces the constant with string in each of them
     * @param string $lang
     * @param string $extension
     * @param string $constant
     * @param string $string
     */
    protected function updateLanguageFileString($lang, $extension, $constant, $string) {

        //Replace " in the string with &quot;
        $string = str_replace('"', '&quot;', $string);

        //Load language files for this extension
        $files = $this->getLanguageFileListForExtension($lang, $extension);

        //Search and replace inside each matching file
        if (!empty($files)) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    $content = file_get_contents($file);

                    $regex = '/(^' . trim($constant) . ' *= *")(.*)(".*)/im';
                    $updated_content = preg_replace($regex, '${1}' . $string . '${3}', $content);

                    if ($updated_content != $content) {
                        file_put_contents($file, $updated_content);
                    }
                }
            }
        }
    }

    
    /**
     * Look for additions, changes and deletions in the file system and push them to the database
     * @param string $type Either "source" or "target"
     * @param array $file_language_strings
     * @param array $database_language_strings
     * @param string $lang
     */
    protected function pushStringsToDatabase($type, $file_language_strings, $database_language_strings, $lang=null) {

        //New strings
        $new_strings = array_diff_key($file_language_strings, $database_language_strings);
        if (!empty($new_strings)) {
            $this->addStringsToDatabase($type, $new_strings, $lang);
        }

        //Deleted strings
        $deleted_strings = array_diff_key($database_language_strings, $file_language_strings);
        if (!empty($deleted_strings)) {
            $this->deleteStringsFromDatabase($type, $deleted_strings);
        }

        //Changed strings
        if ($type == 'source') {
            $changed_strings = $this->findChangedStrings($file_language_strings, $database_language_strings);
            if (!empty($changed_strings)) {
                $this->updateStringsInDatabase($changed_strings);
            }
        }
    }

    

    
    /**
     * Compares two arrays and outputs a new array with only the items where the value is different
     * @param array $file_language_strings (simple array)
     * @param array $database_language_strings (object list)
     * @return array
     */
    protected function findChangedStrings($file_language_strings, $database_language_strings) {

        $changes = array();
        foreach ($file_language_strings as $key => $file_language_string) {

            //Skip if not in the database (new string)
            if (!isset($database_language_strings[$key])) {
                continue;
            }

            if ($database_language_strings[$key]->string != $file_language_string) {
                $changes[$key] = $file_language_string;
            }
        }

        return $changes;
    }

    /**
     * Takes and array of strings and adds them to the database
     * @param string $type either 'source' or 'target'
     * @param array $strings
     * @param string $lang eg. 'en-GB'
     * @return boolean
     */
    protected function addStringsToDatabase($type, $strings, $lang=null) {

        if (empty($strings)) {
            return false;
        }
        
        if (is_null($lang)) {
            $lang = $this->source_language;            
        }
        
        //To not overload the system insert 100 strings at a time
        $limit_per_insert = 100;

        $chunked_strings = array_chunk($strings, $limit_per_insert, true);

        foreach ($chunked_strings as $chunked_string) {

            $db = JFactory::getDbo();

            if ($type == 'source') {
                $query = 'INSERT INTO' . ' #__lingo_langfile_source (string,constant,lang,extension,time_added,version) VALUES ';
            } else {
                $query = 'INSERT INTO' . ' #__lingo_langfile_translations (source_id, string, time_translated, version, lang, translation_method) VALUES ';
            }

            $sql_inserts = array();
            foreach ($chunked_string as $key => $string) {

                $string_info = $this->getInfoFromStringKey($key);
                $constant = $string_info['constant'];
                $extension = $string_info['extension'];

                if ($type == 'source') {
                    $sql_inserts[] = ("\n (" . $db->quote($string) . ', ' . $db->quote($constant) . ', ' . $db->quote($lang) . ', ' . $db->quote($extension) . ', NOW(), 1)');
                } else {
                    $sql_inserts[] = "\n ("
                            . '(SELECT id FROM #__lingo_langfile_source WHERE constant = ' . $db->quote($constant) . ' AND extension = ' . $db->quote($extension) . ')'
                            . ', ' . $db->quote($string)
                            . ', NOW()'
                            . ', 1'
                            . ', ' . $db->quote($lang)
                            . ', "langfile"'
                            . ')';
                }


                LingoDebug::log('Adding new '.$type.' string to DB in language ['.$lang.']: ' . $constant . ' = "' . $string . '"',3);

            }


            $db->setQuery($query . implode(',', $sql_inserts));
            //echo $db->getQuery();
            $db->execute();
        }

        LingoDebug::log('Added '.count($strings).' to database '.$limit_per_insert.' at a time', 3);

        return true;
    }

    /**
     * Takes and array of strings and updates them in the database
     * @param array $strings
     * @return boolean
     */
    protected function updateStringsInDatabase($strings) {

        if (empty($strings)) {
            return false;
        }

        foreach ($strings as $key => $string) {

            $db = JFactory::getDbo();
            $query = 'UPDATE #__lingo_langfile_source';

            $string_info = $this->getInfoFromStringKey($key);
            $constant = $string_info['constant'];
            $lang = $this->source_language;
            $extension = $string_info['extension'];
            $query .= "\n SET string = " . $db->quote($string)
                    . ", time_changed = NOW()"
                    . ", version = version+1"
                    . "\n WHERE constant = " . $db->quote($constant)
                    . "AND lang = " . $db->quote($lang)
                    . "AND extension = " . $db->quote($extension);

            $db->setQuery($query);
            //echo $db->getQuery();
            $db->execute();

            LingoDebug::log('Updating database source string: ' . $constant . ' = "' . $string . '"',3);
        }

        return true;
    }

    /**
     * Takes and object list and marks the id of each object as deleted
     * @param string $type 'source' or 'target'
     * @param array $strings
     * @return boolean
     */
    protected function deleteStringsFromDatabase($type, $strings) {

        if (empty($strings)) {
            return false;
        }

        if ($type == 'source') {
            $table = '#__lingo_langfile_source';
        } else if ($type == 'target') {
            $table = '#__lingo_langfile_translations';
        } else {
            return false;
        }

        $db = JFactory::getDbo();

        $query = 'UPDATE '.$table.' SET state = -1, time_deleted = NOW()';

        $ids = array();
        foreach ($strings as $key => $string) {
            $ids[] = $string->id;
            LingoDebug::log('Deleting '.$type.' string from database: ' . $string->constant . ' = "' . $string->string . '"',3);
        }

        $query .= 'WHERE id IN (' . implode(',', $ids) . ')';

        $db->setQuery($query);
        //echo $db->getQuery();
        $db->execute();

        return true;
    }

    /**
     * Load all language strings from the database
     * @param string $lang eg. 'en-GB'
     * @return array object list
     */
    protected function getSourceLanguageStringsFromDatabase($lang = null) {

        //Default to source language
        if (is_null($lang)) {
            $lang = $this->source_language;
        }

        //Load from DB
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.*');
        $query->select('CONCAT(a.extension,".ini:", UPPER(a.constant)) AS arraykey');
        $query->from('#__lingo_langfile_source AS a');

        $query->where('a.lang = "' . $lang . '"');
        $query->where('a.state = 1');

        //Order by lang and then extension
        $query->order('a.lang')->order('a.extension');

        $db->setQuery($query);
        //echo $db->getQuery();
        $rows = $db->loadObjectList('arraykey');

        LingoDebug::log('Found '.count($rows).' language strings in the database', 3);

        return $rows;
    }


    /**
     * Load all language strings from the database
     * @param string $lang eg. 'en-GB'
     * @return array object list
     */
    protected function getTargetLanguageStringsFromDatabase($lang) {

        //Load from DB
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.*');
        $query->select('CONCAT(s.extension,".ini:", UPPER(s.constant)) AS arraykey');
        $query->from('#__lingo_langfile_translations AS a');
        $query->join('INNER', '#__lingo_langfile_source AS s ON s.id = a.source_id');
        $query->where('a.lang = "' . $lang . '"');

        //Order by lang and then extension
        $query->order('s.lang')->order('s.extension');

        $db->setQuery($query);
        //echo $db->getQuery();
        $rows = $db->loadObjectList('arraykey');

        return $rows;
    }



    /**
     * Get an associative array with language key/value pairs of all language vars
     * @return array
     */
    protected function getSourceLanguageStringsFromFiles() {

        //Build array of strings
        $strings = array();

        $folders = $this->getLanguageFileFolders();
        foreach ($folders as $folder) {
            $source_language_files = $this->getLanguageFilesInPath($folder, $this->source_language);
            $strings = array_merge($this->getLanguageStringsFromFileList($source_language_files), $strings);
        }

        //Remove duplicates
        array_unique($strings);

        LingoDebug::log('Found '.count($strings).' language strings in files', 3);

        return $strings;
    }

    /**
     * Takes an array of full file names and loads the language strings into an array with a unique key for each string
     * For easy comparison the keys are as follows: [filename:constant]
     * @param array $language_files list of file names to parse
     * @return array
     */
    protected function getLanguageStringsFromFileList($language_files) {

        $strings = array();

        if (!empty($language_files)) {
            foreach ($language_files as $language_file) {
                $file_strings = $this->getLanguageStringsFromFile($language_file);
                if (!empty($file_strings)) {
                    
                    //Remove the language code from the file name
                    $language_file = basename($language_file);
                    $language_file_parts = explode('.',$language_file);
                    $language_file_lancode_length = strlen($language_file_parts[0])+1;
                    $language_file = substr($language_file, $language_file_lancode_length - strlen($language_file));
                    
                    //Loop each string in the file
                    foreach ($file_strings as $constant => $string) {
                        $string_key = $language_file . ':' . strtoupper($constant);
                        $strings[$string_key] = $string;
                    }
                }
            }
        }

        LingoDebug::log('Found '.count($strings). ' language strings in '.count($language_files).' language files', 3);

        return $strings;
    }

    /**
     * Loook for language files for a specific extension in various directories
     * @param string $lang
     * @param string $extension
     * @return array of files with full path
     */
    protected function getLanguageFileListForExtension($lang, $extension) {

        $files = array();

        //Look for a matching file
        $file_name = $lang . '.' . $extension . '.ini';

        //Look for lanuguage files in these paths
        $folders = $this->getLanguageFileFolders($extension);

        foreach ($folders as $folder) {
            $files = array_merge($files, JFolder::files($folder, $file_name, TRUE, TRUE));
        }

        return $files;
    }

    /**
     * Return a list of folders where language files may reside, ordered by override order
     * @param string $extension The name of the extension such as 'com_lingo'
     * @param string $lang eg. 'en-GB'
     * @return array with a list of full path names
     */
    protected function getLanguageFileFolders($extension = null, $lang=null) {

        $folders = array();

        //Always language first
        if (!is_null($lang)) {
            $folders[] = JPATH_SITE . '/language/'.$lang.'/';
        } else {
            $folders[] = JPATH_SITE . '/language/';
        }

        //If extension is given then try to be more specific about where the folders may be (for performance)
        if (!empty($extension)) {

            //Split extension name by _ to determine if it is a component, module or plugin
            $extension_parts = explode('_', $extension);

            $specific_path = '';
            if ($extension_parts[0] == 'com') {
                $specific_path = JPATH_SITE . '/components/' . $extension . '/language/';
            } else if ($extension_parts[0] == 'mod') {
                $specific_path = JPATH_SITE . '/modules/' . $extension . '/language/';
            } else if ($extension_parts[0] == 'plg') {
                $specific_path = JPATH_SITE . '/plugins/';
            }

            if (is_file($specific_path)) {
                $folders[] = $specific_path;
            }
        } else {

            $folders[] = JPATH_SITE . '/components/';
            $folders[] = JPATH_SITE . '/modules/';
            $folders[] = JPATH_SITE . '/plugins/';
        }

        //Always template overwrite last
        $folders[] = JPATH_SITE . '/templates/';


        return $folders;
    }

    /**
     * Takes a string (used as key in internal arrays) and splits it into an array of information
     * Example key: com_phocagallery.sys.ini:COM_PHOCAGALLERY_XML_DESCRIPTION
     * @param string $key
     * @return array
     */
    protected function getInfoFromStringKey($key) {

        $info = array();
        if (empty($key)) {
            return $info;
        }

        //Split by : to separate file name and constant
        $keyparts = explode(':', $key);
        $file_name = $keyparts[0];
        $info['constant'] = $keyparts[1];

        //Split the file name by . for additional information
        $fileparts = explode('.', $file_name);
        $info['extension'] = $fileparts[0];

        //Add .sys and other file parts to the name
        foreach ($fileparts as $k => $filepart) {
            if ($k > 0 && $filepart != 'ini') {
                $info['extension'] .= '.' . $filepart;
            }
        }

        return $info;
    }

    /**
     * Loads a language file and returns an associated array of key value pairs
     * @param string $path
     * @return array of strings
     */
    protected function getLanguageStringsFromFile($path) {

        if (!is_file($path)) {
            return false;
        }

        $contents = file_get_contents($path);
        $contents = str_replace('_QQ_', '"\""', $contents);
        $strings = @parse_ini_string($contents);

        return $strings;
    }

    /**
     * Return a list of all the language files in the given path
     * @param string $path full path to the folder that should be looked in
     * @param string $lang
     * @param boolean $recursive weather or not sub folders should be looked in
     * @param boolean $ignore_joomla_core weather core Joomla files should be ignored, defaults to true
     * @return array
     */
    protected function getLanguageFilesInPath($path, $lang = null, $recursive = true, $ignore_joomla_core = true) {

        jimport('joomla.filesystem.folder');

        if (is_null($lang)) {
            $filter = '\.ini$';
        } else {
            $filter = '^' . $lang . '.*\.ini$';
        }

        LingoDebug::log('Looking for language files in ['.$lang.'] inside: '.$path, 3);

        //Load list
        $files = JFolder::files($path, $filter, $recursive, true);

        //Remove Joomla core files if needed
        if ($ignore_joomla_core === true) {
            $files = $this->removeCoreLanguageFilesFromArray($files, $lang);
        }

        //Debug
        if (!empty($files)) {
            foreach ($files as $file) {
                LingoDebug::log('Found file: '.$file, 3);
            }
        }

        return $files;
    }

    /**
     * Takes an array of language files and filters out known language files shipped with Joomla
     * @param array $files
     * @param string $lang
     * @return array
     */
    protected function removeCoreLanguageFilesFromArray($files, $lang=null) {
        
        if (is_null($lang)) {
            $lang = $this->source_language;
        }

        $core_files = array(
            $lang . '.com_ajax.ini'
            , $lang . '.com_config.ini'
            , $lang . '.com_contact.ini'
            , $lang . '.com_content.ini'
            , $lang . '.com_finder.ini'
            , $lang . '.com_lingo.ini'
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

            //Template overrides that should be ignored  
            , $lang . '.tpl_hathor.ini'
            , $lang . '.tpl_hathor.sys.ini'
            , $lang . '.tpl_isis.ini'
            , $lang . '.tpl_isis.sys.ini'
        );

        //Filter
        foreach ($files as $key => $file) {
            foreach ($core_files as $core_file) {
                $strlen = strlen($core_file);
                if (substr($file, strlen($file) - $strlen, $strlen) == $core_file) {
                    unset($files[$key]);
                    continue 2;
                }
            }
        }

        //Get new keys
        $files = array_values($files);

        return $files;
    }

    /**
     * Get an array indexed by language code of the target languages
     * @return array objectList
     */
    protected function getTargetLanguages() {

        //Load all published languages
        $languages = $this->getLanguages();

        //Remove the source language
        unset($languages[$this->source_language]);

        return $languages;
    }

    /**
     * Load all published languages on the site
     * @param boolean $published weather or not only the published language should be loaded
     * @return array objectList
     */
    protected function getLanguages($published = true) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*');
        $query->from('#__languages');

        if ($published) {
            $query->where('published = 1');
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList('lang_code');

        return $rows;
    }

}
