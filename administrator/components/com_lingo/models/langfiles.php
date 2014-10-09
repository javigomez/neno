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

    public $debug = true;
    public $source_language = null;
    public $target_langs = array();
    
    public function __construct() {
        
        parent::__construct();

        $language = JFactory::getLanguage();
        $this->source_language = $language->getDefault();
        $this->target_langs = $this->getTargetLanguages();
        
        
    }    
    

    public function import() {
        
        //Load language strings
        $file_language_strings = $this->getSourceLanguageStringsFromFiles();
        
        //Load all existing strings in the database
        $database_language_strings = $this->getSourceLanguageStringsFromDatabase();
        
        //Push to database
        $this->pushToDatabase($file_language_strings, $database_language_strings);        
    }
    
    
    public function export() {
        
        $source_language_strings = $model->loadTranslations();        
        echo '<pre class="debug"><small>' . __file__ . ':' . __line__ . "</small>\n\$source_language_strings = ". print_r($source_language_strings, true)."\n</pre>";

        
    }
    
    
    
    protected function loadTranslations() {
        
        //Prepare a var for returning
        $translations = array();
        
        //Get target languages
        $target_languages = $this->getTargetLanguages();
        
        //For each target language, load the source and the translation
        $db		= JFactory::getDbo();
        $query	= $db->getQuery(true);

        $query->select('*');
        $query->from('#__');

        $query->where('');

        //echo nl2br(str_replace('#__','jos_',$query));

        $db->setQuery( $query );
        $rows = $db->loadObjectList();
        
        
        
        return $translations;
        
        
    }
    
    
    
    /**
     * Takes an object list of all source language strings and turns it into a mutidimentional array indexed by language, extension, constant
     * @param array $source_language_strings
     * @return array
     */
    protected function getStructuredExportData($source_language_strings) {
        
        $data = array();
        foreach ($source_language_strings as $source_language_string) {
            if (!isset($data[$source_language_string->lang])) {
                $data[$source_language_string->lang] = array();
            }
            if (!isset($data[$source_language_string->lang][$source_language_string->extension])) {
                $data[$source_language_string->lang][$source_language_string->extension] = array();
            }
            $data[$source_language_string->lang][$source_language_string->extension][$source_language_string->constant] = $source_language_string->string;
        }
        
        return $data;
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

                    $regex = '/(^'.trim($constant).' *= *")(.*)(".*)/im';
                    $updated_content = preg_replace($regex, '${1}'.$string.'${3}', $content);
                    
                    if ($updated_content != $content) {
                        if ($this->debug) {
                            echo "<br />==== Found regex (".$regex.") inside file ".$file;
                        }
                        file_put_contents($file, $updated_content);
                    }
                }
            }
        }
        
    }
    
        
    
    /**
     * Look for additions, changes and deletions in the file system and push them to the database
     * @param type $file_language_strings
     * @param type $database_language_strings
     */
    protected function pushToDatabase($file_language_strings, $database_language_strings) {
        
        //New strings
        $new_strings = array_diff_key($file_language_strings, $database_language_strings);
        if (!empty($new_strings)) {
            $this->addSourceStringsToDatabase($new_strings);
            $this->addTranslatedStringsToDatabase($new_strings); 
        }
        
        //Deleted strings
        $deleted_strings = array_diff_key($database_language_strings, $file_language_strings);
        if (!empty($deleted_strings)) {
            $this->deleteStringsFromDatabase($deleted_strings);
        }
        
        //Changed strings
        $changed_strings = $this->findChangedStrings($file_language_strings, $database_language_strings);
        if (!empty($changed_strings)) {
            $this->updateStringsInDatabase($changed_strings);
        }
        
    }
    
    
    /**
     * Takes newly discovered strings and sees if we have any translations for them already in files and copies them to the database
     * @param array $new_strings
     */
    protected function addTranslatedStringsToDatabase($new_strings) {
        
        $target_languages = $this->getTargetLanguages();
        
        //Create a structured array with data that can be looped through
        $data = array();
        foreach ($new_strings as $key => $new_string) {
            
            $string_info = $this->getInfoFromStringKey($key);
            if (!isset($data[$string_info['extension']])) {
                $data[$string_info['extension']] = array();
            }
            $data[$string_info['extension']][$string_info['constant']] = $string_info['string'];            
            
        }
        
        //Foreach language... loop through and try to find a matching file
        foreach ($target_languages as $target_language_code => $target_language) {
            
            foreach ($data as $extension => $constants) {
                
                $potential_files = $this->getLanguageFileListForExtension($target_language_code, $extension);
                foreach ($potential_files as $potential_file) {
                    if (file_exists($potential_file)) {
                        echo "<br />Found a translation file: ".$potential_file;
                    }
                }
                
                
            }
            
        }
        
    }
    
    
    
    /**
     * Compares two arrays and outputs the differences
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
     * @param array $strings
     * @return boolen
     */
    protected function addSourceStringsToDatabase($strings) {
        
        if (empty($strings)) {
            return;
        }
        
        $db		= JFactory::getDbo();

        $query = 'INSERT INTO #__lingo_langfile_source (string,constant,lang,extension,time_added,version) VALUES ';
        
        $sql_inserts = array();
        foreach ($strings as $key => $string) {
            
            $string_info = $this->getInfoFromStringKey($key);
            $constant = $string_info['constant'];
            $lang = $string_info['lang'];
            $extension = $string_info['extension'];
            $sql_inserts[] = ("\n (".$db->quote($string).', '.$db->quote($constant).', '.$db->quote($lang).', '.$db->quote($extension).', NOW(), 1)');
            
            if ($this->debug) {
                echo '<br />==== Adding string: '.$constant.' = "'.$string.'"';
            }
            
        }


        $db->setQuery( $query . implode(',', $sql_inserts) );
        //echo $db->getQuery();
        $db->execute();
        
        return true;
        
        
    }
    

    /**
     * Takes and array of strings and updates them in the database
     * @param array $strings
     * @return boolen
     */
    protected function updateStringsInDatabase($strings) {
        
        if (empty($strings)) {
            return;
        }
        
        foreach ($strings as $key => $string) {

            $db		= JFactory::getDbo();
            $query = 'UPDATE #__lingo_langfile_source';
            
            $string_info = $this->getInfoFromStringKey($key);
            $constant = $string_info['constant'];
            $lang = $string_info['lang'];
            $extension = $string_info['extension'];
            $query .= "\n SET string = ".$db->quote($string)
                        .", time_changed = NOW()"
                        .", version = version+1"
                        ."\n WHERE constant = ".$db->quote($constant)
                        ."AND lang = ".$db->quote($lang)
                        ."AND extension = ".$db->quote($extension);

            $db->setQuery( $query );
            //echo $db->getQuery();
            $db->execute();
            
            if ($this->debug) {
                echo '<br />==== Updating string: '.$constant.' = "'.$string.'"';
            }
            
        }
        
        return true;
        
        
    }
        
    
    
    /**
     * Takes and object list and marks the id of each object as deleted
     * @param array $strings
     * @return boolean
     */
    protected function deleteStringsFromDatabase($strings) {
        
        if (empty($strings)) {
            return;
        }
        
        $db		= JFactory::getDbo();

        $query = 'UPDATE #__lingo_langfile_source SET state = -1, time_deleted = NOW()';
        
        $ids = array();
        foreach ($strings as $key => $string) {
            $ids[] = $string->id;
            if ($this->debug) {
                echo '<br />==== Deteting string: '.$string->constant.' = "'.$string->string.'"';
            }
        }
        
        $query .= 'WHERE id IN ('.implode(',', $ids).')';

        $db->setQuery( $query );
        //echo $db->getQuery();
        $db->execute();
        
        return true;
                
        
    }
    
    
    /**
     * Load all language strings from the database
     * @return array object list
     */
    protected function getSourceLanguageStringsFromDatabase() {
        
        $db		= JFactory::getDbo();
        $query	= $db->getQuery(true);

        $query->select('*');
        $query->select('CONCAT(lang,".",extension,".ini:", UPPER(constant)) AS arraykey');
        $query->from('#__lingo_langfile_source');

        $query->where('lang = "'.$this->source_language.'"');
        $query->where('state = 1');
        
        $query->order('lang')->order('extension');

        $db->setQuery( $query );
        $rows = $db->loadObjectList('arraykey');
        
        return $rows;
        
    }
        
    
    
    /**
     * Get an assosiative array with language key/value pairs of all language vars 
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

        return $strings;
        
    }
    
    
    /**
     * Takes an array of full file names and loads the language strings into an array with a unique key for each string
     * For easy comparison the keys are as follows: [filename:constant]
     * @param array $language_files list of file names to parse
     * @param array $strings optional already parsed string in an array to enable appending additional strings to the same array
     * @return array
     */
    protected function getLanguageStringsFromFileList($language_files) {
        
        $strings = array();
        
        if (!empty($language_files)) {
            foreach ($language_files as $language_file) {
                $file_strings = $this->getLanguageStringsFromFile($language_file);
                if (!empty($file_strings)) {
                    foreach ($file_strings as $constant => $string) {
                        $string_key = (string) basename($language_file).':'.strtoupper($constant);
                        $strings[$string_key] = $string;
                    }
                }
            }
        }
        
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
        $file_name = $lang.'.'.$extension.'.ini';
        
        //Look for lanuguage files in these paths
        $folders = $this->getLanguageFileFolders();
        
        foreach ($folders as $folder) {
            $files = array_merge($files, JFolder::files($folder, $file_name, TRUE, TRUE));
        }        
        
        return $files;
        
    }
    
    
    /**
     * Return a list of folders where language files may reside, ordered by override order
     * @return array with a list of full path names
     */
    protected function getLanguageFileFolders() {

        $folders = array();
        $folders[] = JPATH_SITE.'/language/';
        $folders[] = JPATH_SITE.'/components/';
        $folders[] = JPATH_SITE.'/modules/';
        $folders[] = JPATH_SITE.'/plugins/';
        $folders[] = JPATH_SITE.'/templates/';
        
        return $folders;
        
    }
    

    
    /**
     * Takes a string (used as key in internal arrays) and splits it into an array of information
     * Example key: en-GB.com_phocagallery.sys.ini:COM_PHOCAGALLERY_XML_DESCRIPTION
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
        $info['lang'] = $fileparts[0];
        $info['extension'] = $fileparts[1];
        
        //Add .sys and other file parts to the name
        foreach ($fileparts as $k => $filepart) {
            if ($k > 1 && $filepart != 'ini') {
                $info['extension'] .= '.'.$filepart;
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
     * @param type $lang
     * @return type
     */
    protected function getLanguageFilesInPath($path, $lang=null, $recursive=true, $ignore_joomla_core=true) {
        
        jimport('joomla.filesystem.folder');
        
        if (is_null($lang)) {
            $filter = '\.ini$';
        } else {
            $filter = '^'.$lang.'.*\.ini$';
        }
        
        //Load list
        $files = JFolder::files($path, $filter, $recursive, true);

        //Remove Joomla core files if needed
        if ($ignore_joomla_core === true) {
            $files = $this->removeCoreLanguageFilesFromArray($files);
        }
        
        return $files;
        
    }
    
    
    /**
     * Takes an array of language files and filters out known language files shipped with Joomla
     * @param array $files
     * @return array
     */
    protected function removeCoreLanguageFilesFromArray($files) {
        
        $core_files = array(
            $this->source_language.'.com_ajax.ini'
            , $this->source_language.'.com_config.ini'
            , $this->source_language.'.com_contact.ini'
            , $this->source_language.'.com_content.ini'
            , $this->source_language.'.com_finder.ini'
            , $this->source_language.'.com_lingo.ini'
            , $this->source_language.'.com_mailto.ini'
            , $this->source_language.'.com_media.ini'
            , $this->source_language.'.com_messages.ini'
            , $this->source_language.'.com_newsfeeds.ini'
            , $this->source_language.'.com_search.ini'
            , $this->source_language.'.com_tags.ini'
            , $this->source_language.'.com_users.ini'
            , $this->source_language.'.com_weblinks.ini'
            , $this->source_language.'.com_wrapper.ini'
            , $this->source_language.'.files_joomla.sys.ini'
            , $this->source_language.'.finder_cli.ini'
            , $this->source_language.'.ini'
            , $this->source_language.'.lib_fof.sys.ini'
            , $this->source_language.'.lib_idna_convert.sys.ini'
            , $this->source_language.'.lib_joomla.ini'
            , $this->source_language.'.lib_joomla.sys.ini'
            , $this->source_language.'.lib_phpass.sys.ini'
            , $this->source_language.'.lib_phpmailer.sys.ini'
            , $this->source_language.'.lib_phputf8.sys.ini'
            , $this->source_language.'.lib_simplepie.sys.ini'
            , $this->source_language.'.mod_articles_archive.ini'
            , $this->source_language.'.mod_articles_archive.sys.ini'
            , $this->source_language.'.mod_articles_categories.ini'
            , $this->source_language.'.mod_articles_categories.sys.ini'
            , $this->source_language.'.mod_articles_category.ini'
            , $this->source_language.'.mod_articles_category.sys.ini'
            , $this->source_language.'.mod_articles_latest.ini'
            , $this->source_language.'.mod_articles_latest.sys.ini'
            , $this->source_language.'.mod_articles_news.ini'
            , $this->source_language.'.mod_articles_news.sys.ini'
            , $this->source_language.'.mod_articles_popular.ini'
            , $this->source_language.'.mod_articles_popular.sys.ini'
            , $this->source_language.'.mod_banners.ini'
            , $this->source_language.'.mod_banners.sys.ini'
            , $this->source_language.'.mod_breadcrumbs.ini'
            , $this->source_language.'.mod_breadcrumbs.sys.ini'
            , $this->source_language.'.mod_custom.ini'
            , $this->source_language.'.mod_custom.sys.ini'
            , $this->source_language.'.mod_feed.ini'
            , $this->source_language.'.mod_feed.sys.ini'
            , $this->source_language.'.mod_finder.ini'
            , $this->source_language.'.mod_finder.sys.ini'
            , $this->source_language.'.mod_footer.ini'
            , $this->source_language.'.mod_footer.sys.ini'
            , $this->source_language.'.mod_languages.ini'
            , $this->source_language.'.mod_languages.sys.ini'
            , $this->source_language.'.mod_login.ini'
            , $this->source_language.'.mod_login.sys.ini'
            , $this->source_language.'.mod_menu.ini'
            , $this->source_language.'.mod_menu.sys.ini'
            , $this->source_language.'.mod_random_image.ini'
            , $this->source_language.'.mod_random_image.sys.ini'
            , $this->source_language.'.mod_related_items.ini'
            , $this->source_language.'.mod_related_items.sys.ini'
            , $this->source_language.'.mod_search.ini'
            , $this->source_language.'.mod_search.sys.ini'
            , $this->source_language.'.mod_stats.ini'
            , $this->source_language.'.mod_stats.sys.ini'
            , $this->source_language.'.mod_syndicate.ini'
            , $this->source_language.'.mod_syndicate.sys.ini'
            , $this->source_language.'.mod_tags_popular.ini'
            , $this->source_language.'.mod_tags_popular.sys.ini'
            , $this->source_language.'.mod_tags_similar.ini'
            , $this->source_language.'.mod_tags_similar.sys.ini'
            , $this->source_language.'.mod_users_latest.ini'
            , $this->source_language.'.mod_users_latest.sys.ini'
            , $this->source_language.'.mod_weblinks.ini'
            , $this->source_language.'.mod_weblinks.sys.ini'
            , $this->source_language.'.mod_whosonline.ini'
            , $this->source_language.'.mod_whosonline.sys.ini'
            , $this->source_language.'.mod_wrapper.ini'
            , $this->source_language.'.mod_wrapper.sys.ini'
            , $this->source_language.'.tpl_beezsss3.ini'
            , $this->source_language.'.tpl_beez3.sys.ini'
            , $this->source_language.'.tpl_beez3.ini'
            , $this->source_language.'.tpl_protostar.ini'
            , $this->source_language.'.tpl_protostar.sys.ini'
            
            //Template overrides that should be ignored  
            , $this->source_language.'.tpl_hathor.ini'            
            , $this->source_language.'.tpl_hathor.sys.ini'            
            , $this->source_language.'.tpl_isis.ini'            
            , $this->source_language.'.tpl_isis.sys.ini'            
        );
        
        //Filter
        foreach ($files as $key => $file) {
            foreach($core_files as $core_file) {
                $strlen = strlen($core_file);
                if (substr($file, strlen($file)-$strlen, $strlen) == $core_file) {
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
     * @return objectList
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
     * @return objectList
     */
    protected function getLanguages($published=true) {
        
        $db		= JFactory::getDbo();
        $query	= $db->getQuery(true);

        $query->select('*');
        $query->from('#__languages');
        
        if ($published) {
            $query->where('published = 1');
        }

        $db->setQuery( $query );
        $rows = $db->loadObjectList('lang_code');
        
        return $rows;
        
    }
        

}
