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
        
        // Overwrite the view
        $input->set('view', 'moveelementconfirm');
        
        $tables = $input->get('tables', array(), 'array');
        $files = $input->get('files', array(), 'files');

        // If a group is selected then load all the elements from that group
        $groups = $input->get('groups', array(), 'array');
        if (!empty($groups))
        {
            foreach ($groups as $groupId)
            {
                $group = NenoContentElementGroup::getGroup($groupId);
                
                //Handle tables
                $group_tables = $group->getTables();
                if (!empty($group_tables))
                {
                    foreach ($group_tables as $group_table)
                    {
                        //Add the table id to the tables array
                        $tables[] = $group_table->getId();
                    }
                }

                // Handle files
                $group_files = $group->getLanguageFiles();
                if(!empty($group_files))
                {
                    foreach ($group_files as $group_file)
                    {
                        // Add the file id to the files array
                        // @todo Get the id when files have id
                        // $files[] = $group_file->getId();
                    }
                }

            }
        }
        
        //Remove duplicates
        array_unique($tables);
        array_unique($files);
        
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
