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

/**
 * Lingo helper.
 */
class LingoHelper {

    
    /**
     * Get a printable name from a language code
     * @param string $code 'da-DK'
     * @return string the name or boolean false on error
     */
    public static function getLangnameFromCode($code) {
        
        $metadata = JLanguage::getMetadata($code);
        if (isset($metadata['name'])) {
            return $metadata['name'];
        } else {
            return false;
        }

    }
    
    
    /**
     * Get an instance of the named model
     * @param string $name the filename of the model
     * @return object An instantiated object of the given model
     */
    public static function getModel($name) {
        include_once JPATH_ADMINISTRATOR . '/components/com_lingo/models/' . strtolower($name) . '.php';
        $model_class = 'LingoModel' . ucwords($name);
        return new $model_class();
    }
    
    
    /**
     * Configure the Linkbar.
     */
    public static function addSubmenu($vName = '') {
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
     * @return	JObject
     * @since	1.6
     */
    public static function getActions() {
        $user = JFactory::getUser();
        $result = new JObject;

        $assetName = 'com_lingo';

        $actions = array(
            'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
        );

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }


}
