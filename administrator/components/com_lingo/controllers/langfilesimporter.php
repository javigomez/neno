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

jimport('joomla.application.component.controller');
require_once 'langfiles.php';

/**
 * Source controller class.
 */
class LingoControllerLangfilesimporter extends LingoControllerLangfiles
{
    
    /**
     * Looks in all language files and imports any strings that have not been imported as well as marks deleted or changed 
     */
    public function import() {
        
        //Load language strings
        $file_language_strings = $this->getSourceLanguageStringsFromFiles();
        
        //Load all existing strings in the database
        $database_language_strings = $this->getSourceLanguageStringsFromDatabase();
        
        //Push to database
        $this->pushToDatabase($file_language_strings, $database_language_strings);
            
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
    private function getSourceLanguageStringsFromFiles() {
        
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
    
    
    


}