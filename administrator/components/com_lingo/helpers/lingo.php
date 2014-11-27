<?php

/**
 * @package     Lingo
 * @subpackage  Helpers
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Lingo helper.
 *
 * @since  1.0
 */
class LingoHelper
{
	/**
	 * Get a printable name from a language code
	 *
	 * @param   string  $code  'da-DK'
	 *
	 * @return string the name or boolean false on error
	 */
	public static function getLangnameFromCode($code)
	{
		$metadata = JLanguage::getMetadata($code);

		if (isset($metadata['name']))
		{
			return $metadata['name'];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get an instance of the named model
	 *
	 * @param   string  $name  The filename of the model
	 *
	 * @return object An instantiated object of the given model
	 */
	public static function getModel($name)
	{
		include_once JPATH_ADMINISTRATOR . '/components/com_lingo/models/' . strtolower($name) . '.php';
		$model_class = 'LingoModel' . ucwords($name);

		return new $model_class;
	}

	/**
	 * Configure the Link bar.
	 *
	 * @param   string  $vName  View name
	 *
	 * @return void
	 */
	public static function addSubmenu($vName = '')
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_LINGO_TITLE_TRANSLATIONS'),
			'index.php?option=com_lingo&view=translations',
			$vName == 'translations'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_LINGO_TITLE_SOURCES'),
			'index.php?option=com_lingo&view=sources',
			$vName == 'sources'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return    JObject
	 *
	 * @since    1.6
	 */
	public static function getActions()
	{
		$user   = JFactory::getUser();
		$result = new JObject;

		$assetName = 'com_lingo';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
    
    
    public static function ensureWorkingLanguageIsSelected() {
        
    }
    
    
    /**
     * Create the HTML for the fairly advanced title that allows changing the language you are working in
     */
    public static function getAdminTitle() {
        
        $title = '<a href="index.php?option=com_lingo&view=dashboard">'
                    .'<img src="'.JUri::base(true).'/components/com_lingo/assets/images/admin_top_lingo_logo.png" width="80" height="30" alt="Lingo logo" />'
                    .'</a>';
        
        // If there is a language constant then start with that
        $view = JFactory::getApplication()->input->getCmd('view', '');
        if (!empty($view)) 
        {
            $default_lang_constant = 'COM_LINGO_TITLE_'.strtoupper($view);
            if (JText::_($default_lang_constant) != $default_lang_constant)
            {
                // If the JText text is different from the constant then it actually exists and should be used
                $title .= ': '. JText::_($default_lang_constant);
            }
        }
        
        //Working language
        $workingLanguage = self::getWorkingLanguage();
        if (!empty($workingLanguage))
        {
            
            //Load all target languages from the list but remove the existing one
            $targetLanguages = self::getTargetLanguages();
            $workingLanguageTitleNative = $targetLanguages[$workingLanguage]->title_native;
            $workingLanguageFlag = '<img src="../media/mod_languages/images/'.$targetLanguages[$workingLanguage]->image.'.gif" />';
            unset($targetLanguages[$workingLanguage]);
            
            //If we have more than one target languages left then allow changing, if not only show the name
            if (count($targetLanguages) > 0)
            {
                
                $next = JFactory::getApplication()->input->getCmd('view', 'dashboard');
                
                $title .= ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<ul id="workingLangSelect">';
                $title .= ' <li class="dropdown">Translating: <a class="dropdown-toggle" data-toggle="dropdown" href="#">'
                        . $workingLanguageFlag
                        . ' '.$workingLanguageTitleNative.''
                        . '<span class="caret"></span></a>';
                $title .= ' <ul class="dropdown-menu">';
                foreach ($targetLanguages as $targetLanguage)
                {
                    $title .= ' <li><a class="" href="index.php?option=com_lingo&task=setworkinglang&lang='.$targetLanguage->lang_code.'&next='.$next.'">'
                            . '<img src="../media/mod_languages/images/'.$targetLanguage->image.'.gif" />'
                            . ' '.$targetLanguage->title_native.'</a></li>';
                }
                $title .= ' </ul>';
                $title .= ' </ul>';
            }
            else
            {
                $title .= ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<ul id="workingLangSelect">';
                $title .= ' <li class="dropdown">Translating: ['.$workingLanguageTitleNative.']';
                $title .= ' </ul>';
            }
            
            //@todo move to custom css file
            $title .= ' <style>';
            $title .= '#workingLangSelect a {color:#d9d9d9;text-decoration:none;}'
                    . '#workingLangSelect a:hover {color:#fff}'
                    . '#workingLangSelect .caret {border-top: 4px solid #d9d9d9;}'
                    . '#workingLangSelect {display:inline-block;margin: 0px;font-size: 14px;}'
                    . '#workingLangSelect li {list-style-type: none;padding:0;}'
                    . '#workingLangSelect .dropdown-menu a {text-shadow:none; color:#000;}'
                    . '#workingLangSelect .dropdown-menu a:hover {color:#fff;}'
                    ;
            $title .= ' </style>';
            
            
        }
        
        
        return $title;
    }
    
    
    

	/**
	 * Get an array indexed by language code of the target languages
	 *
	 * @return array objectList
	 */
	public static function getTargetLanguages($published = true)
	{
		// Load all published languages
		$languages = self::getLanguages($published);

		// Create a simple array
		$arr = array();

		foreach ($languages as $lang)
		{
			$arr[$lang->lang_code] = $lang;
		}

		// Remove the source language
		$language = JFactory::getLanguage();
		unset($arr[$language->getDefault()]);

		return $arr;
	}

	/**
	 * Load all published languages on the site
	 *
	 * @param   boolean  $published  weather or not only the published language should be loaded
	 *
	 * @return array objectList
	 */
	public static function getLanguages($published = true)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__languages');

		if ($published)
		{
			$query->where('published = 1');
		}
        
        $query->order('ordering');
		$db->setQuery($query);
		$rows = $db->loadObjectList('lang_code');

		return $rows;
	}
    
    /**
     * Set the working language on the currently logged in user
     * @param string $lang 'eb-GB' or 'de-DE'
     * @return boolean
     */
    public static function setWorkingLanguage($lang) {
        
        $userId = JFactory::getUser()->id;
        
        $db = JFactory::getDbo();
        $db->setQuery(
                "REPLACE INTO #__user_profiles" .
                " SET profile_value = '".$lang."' "
                . ", profile_key = 'lingo_working_language'"
                . ", user_id = ".(int) $userId 
        );
        //echo $db->getQuery();
        
        $db->execute();        
        return true;
        
    }
    
    /**
     * Get the working language for the current user
     * The value is stored in #__user_profiles
     * @return string 'eb-GB' or 'de-DE'
     */
    public static function getWorkingLanguage() {
        
        $userId = JFactory::getUser()->id;
        
        $db = JFactory::getDbo();
        $db->setQuery(
                'SELECT profile_value FROM #__user_profiles' .
                ' WHERE user_id = '.(int) $userId .
                ' AND profile_key = "lingo_working_language"'
        );
        
        $lang = $db->loadResult();        
        return $lang;
        
    }
    
    
    
}
