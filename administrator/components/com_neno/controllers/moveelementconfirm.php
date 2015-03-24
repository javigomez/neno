<?php
/**
 * @package     Neno
 * @subpackage  Controllers
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Manifest Groups & Elements controller class
 *
 * @since  1.0
 */
class NenoControllerMoveElementConfirm extends JControllerAdmin
{
    
    /**
     * Show confirmation before moving anything
     */
    public function show() {
        
        $input = JFactory::getApplication()->input;
        
        //Overwrite the view
        $input->set('view', 'moveelementconfirm');
        
        //We can ignore groups as they should have all their children checked if they are checked
        $tables = $input->get('tables', array(), 'array');
        $files = $input->get('files', array(), 'files');
        
        //Load table info
        if (!empty($tables)) 
        {
            foreach ($tables as $key => $table)
            {
                $tables[$key] = NenoContentElementTable::getTableById($table);
            }
        }
        
        //Show output
        $view   = $this->getView('MoveElementConfirm', 'html'); //get the view
        $view->assignRef('tables', $tables); // assign data from the model
        $view->display(); // display the view
        
    }
    
    
    

    

}
