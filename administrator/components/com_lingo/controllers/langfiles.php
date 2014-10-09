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

/**
 * Source controller class.
 */
class LingoControllerLangfiles extends JControllerLegacy
{
    
    public $debug = true;
    public $source_language = null;
    
    public function __construct() {
        
        $language = JFactory::getLanguage();
        $this->source_language = $language->getDefault();
        
        parent::__construct();
        
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