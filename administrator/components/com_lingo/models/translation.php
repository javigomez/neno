<?php
/**
 * @package     Lingo
 * @subpackage  Models
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Lingo model.
 *
 * @since  1.0
 */
class LingoModelTranslation extends JModelItem
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_LINGO';
    

    

	/**
	 * Method to get a single record.
	 *
	 * @param   integer|array  $id  The id of the primary key.
	 *
	 * @return    mixed    Object on success, false on failure.
	 *
	 * @since    1.0
	 */
	public function getItem($id = null)
	{
        
        // Ensure that there is an ID
        if (is_null($id)) 
        {
            $id = JFactory::getApplication()->input->getInt('id');
            if (is_null($id))
            {
                throw new Exception('Error loading translation, no ID was supplied!');
            }            
        }
                
        $db		= JFactory::getDbo();
        $query	= $db->getQuery(true);

        $query->select('t.*');
        $query->from('#__lingo_langfile_translations AS t');
        
        $query->join('left', '#__lingo_langfile_source AS s ON s.id = t.source_id');
        $query->select('s.string AS source_string');
        
        $query->where('t.id = '.(int) $id);

        //echo nl2br(str_replace('#__','jos_',$query));

        $db->setQuery( $query );
        $item = $db->loadObject();
        
		return $item;
        
	}

	
}
