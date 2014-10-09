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
class LingoControllerLangfilesexporter extends LingoControllerLangfiles
{
    
    public $target_langs = array();
    
    public function __construct() {
        
        parent::__construct();

        $this->target_langs = $this->getTargetLanguages();
        
        
    }
    
    
    public function export() {
        
        //Load all source strings
        $source_language_strings = $this->loadTranslations();
        
        //Create a structured array that allows us to loop per language and then per file and then per line
//        $data = $this->getStructuredExportData($source_language_strings);
//        echo '<pre class="debug"><small>' . __file__ . ':' . __line__ . "</small>\n\$data = ". print_r($data, true)."\n</pre>";


        
    }
    
    
    
    
    protected function loadTRanslations() {
        
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
    
    


}