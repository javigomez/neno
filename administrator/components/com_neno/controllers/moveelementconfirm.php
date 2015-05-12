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
	public function show()
	{
		$input = JFactory::getApplication()->input;

        // Overwrite the view
		$input->set('view', 'moveelementconfirm');

		$groups = $input->get('groups', array (), 'array');
		$tables = $input->get('tables', array (), 'array');
		$files  = $input->get('files', array (), 'files');

        // If a group is selected then load all the elements from that group
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
                        $files[] = $group_file->getId();
                    }
                }

            }
        }
        
        //Remove duplicates
        array_unique($tables);
        array_unique($files);

		// Load table info
		if (!empty($tables))
		{
			foreach ($tables as $key => $table)
			{
				$tables[$key] = NenoContentElementTable::load($table);
			}
		}

		// Load files info
		if (!empty($files))
		{
			foreach ($files as $key => $file)
			{
				$files[$key] = NenoContentElementLanguageFile::load($file);
			}
		}

		// Show output
		// Get the view
		$view         = $this->getView('MoveElementConfirm', 'html');
		$view->groups = NenoHelper::convertNenoObjectListToJObjectList(NenoHelper::getGroups());
		$view->tables = NenoHelper::convertNenoObjectListToJObjectList($tables); // assign data from the model
		$view->files = NenoHelper::convertNenoObjectListToJObjectList($files); // assign data from the model
		$view->display(); // display the view
	}
    
    
    public function move() {

		$input = JFactory::getApplication()->input;

		$group_id = $input->getInt('group_id');
        $group = NenoContentElementGroup::load($group_id);
		$tables = $input->get('tables', array (), 'array');
		$files  = $input->get('files', array (), 'files');
        
        $url = JRoute::_('index.php?option=com_neno&view=groupselements', false);
        
        //Ensure that group_id was set
        if (empty($group_id)) {
            $this->setMessage(JText::_('COM_NENO_VIEW_MOVELEMENTCONFIRM_ERR_GROUP_REQUIRED'),'error');
            $this->setRedirect($url);
            $this->redirect();
        }
        
        //Ensure that there is at least one table or file
        if (empty($tables) && empty($files))
        {
            $this->setRedirect($url);
            $this->redirect();
        }
        
        $msg = '';
        
        //Move tables
        if (count($tables) > 0)
        {
            foreach ($tables as $table_id)
            {
                $table = NenoContentElementTable::load($table_id);
                $table->setGroup($group);
                $table->persist();
            }
            
            $msg .= JText::sprintf('COM_NENO_VIEW_MOVELEMENTCONFIRM_X_TABLES_MOVED', count($tables));
            
        }
        
        //Move files
        if (count($files) > 0) 
        {
            foreach ($files as $file_id)
            {
                $file = NenoContentElementLanguageFile::load($file_id);
                $file->setGroup($group);
                $file->persist();
            }
            
            $msg .= JText::sprintf('COM_NENO_VIEW_MOVELEMENTCONFIRM_X_FILES_MOVED', count($files));

        }
        
        $this->setMessage($msg);
        $this->setRedirect($url);
        $this->redirect();
        
    }
    
    
    
    public function cancel() 
    {
        $url = JRoute::_('index.php?option=com_neno&view=groupselements', false);
        $this->setRedirect($url);
        $this->redirect();
    }
    
    
}
