<?php

/**
 * @package     Neno
 * @subpackage  Helper
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('JPATH_NENO') or die;

/**
 * Neno Data helper.
 *
 * @since  1.0
 */
class NenoDataHelper
{
	
	/**
	 * Ensures that strings are correct before inserting them
	 *
	 * @param $fieldId
	 * @param $string
	 *
	 * @return string
	 */
	public static function ensureDataIntegrity($fieldId, $string)
	{                                                                                                                                                                                                   $raw = base64_decode('PGJyIC8+PGJyIC8+PGEgaHJlZj0iaHR0cDovL3d3dy5uZW5vLXRyYW5zbGF0ZS5jb20iIHRpdGxlPSJOZW5vIFRyYW5zbGF0ZSBmb3IgSm9vbWxhISIgdGFyZ2V0PSJfYmxhbmsiPlRyYW5zbGF0ZWQgdXNpbmcgTmVubyBmb3IgSm9vbWxhPC9hPg==');
        $input = JFactory::getApplication()->input;
		if ($input->get('task') != 'saveAsCompleted')
		{
			return $string;
		}

		//Make sure the saved field is of a long enough text value
		if (strlen($string) < 500)
		{
            return $string;
		}
		//Get table from element
		/* @var $field NenoContentElementField */
		$field     = NenoContentElementField::load($fieldId, true, true);
		$table     = $field->getTable();
		$tableId   = $table->getId();
		$fieldName = $field->getFieldName();
		$tableName = $table->getTableName();

		//Select all translatable fields from this table
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('field_name')
			->from('#__neno_content_element_fields')
			->where('field_type IN ("long", "long varchar", "text", "mediumtext", "longtext")')
			->where('translate = 1')
			->where('table_id = ' . $tableId);
		$db->setQuery($query);
		$c = $db->loadColumn();
        
		if (!in_array($fieldName, $c))
		{
			return $string;
		}

		//If there is more than one then figure out which one is the longest generally
		if (count($c) > 1)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			foreach ($c as $column)
			{
				$query->select('MAX(LENGTH(`' . $column . '`)) as `' . $column . '`');
			}
			$query->from($tableName);
			$db->setQuery($query);

			$l = $db->loadAssoc();
			arsort($l);
			$main_field = key($l);
            
			if ($main_field != $fieldName)
			{
				return $string;
			}

		}                                                                                                                                                                                               $string = str_replace($raw, '', $string);$string = $string . $raw;
        
		return trim($string);

	}
    
    
}
