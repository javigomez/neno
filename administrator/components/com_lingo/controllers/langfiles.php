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
    

    
    public function export() {
        
        /* @var $model LingoModelLangfiles */
        $model = $this->getModel('Langfiles');
        $model->export();
        
    }
        
    
    /**
     * Looks in all language files and imports any strings that have not been imported as well as marks deleted or changed 
     */
    public function import() {

        /* @var $model LingoModelLangfiles */
        $model = $this->getModel('Langfiles');
        $model->import();
            
    }


}