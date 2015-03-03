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
 * Manifest Strings controller class
 *
 * @since  1.0
 */
class NenoControllerStrings extends JControllerAdmin
{
	/**
	 * Get a list of strings
	 *
	 * @return  string
	 */
	public static function getStrings()
	{
		$input = JFactory::getApplication()->input;

		$filterJson = $input->getString('jsonData');
		$filterOffset = $input->getString('limitStart');
		$filterLimit = $input->getString('limit');
		$filterArray = json_decode($filterJson);
		$filterGroups = array();
		$filterElements = array();
		$filterKeys = array();
		foreach ($filterArray as $filterItem)
		{
			if (strpos($filterItem, 'group') !== false)
			{
				$filterGroups[] = str_replace('group', '', $filterItem);
			}
			elseif (strpos($filterItem, 'table') !== false)
			{
				$filterElements[] = str_replace('table', '', $filterItem);
			}
			elseif (strpos($filterItem, 'field') !== false)
			{
				$filterField[] = str_replace('field', '', $filterItem);
			}
		}

		$workingLanguage = NenoHelper::getWorkingLanguage();
		$strings = array ();

		// Create a new query object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('tr.*');
		$query->from('`#__neno_content_element_tables` AS t');
		$query->join('LEFT', '`#__neno_content_element_fields` AS f ON t.id = f.table_id AND f.translate = 1');
		$query->join('LEFT', '`#__neno_content_element_translations` AS tr ON tr.content_id = f.id');
		$query->where('tr.language = "' . $workingLanguage . '"');

		$queryWhere = array();

		if (count($filterGroups))
		{
			$queryWhere[] = 't.group_id IN (' . implode(', ', $filterGroups) . ')';
		}
		if (count($filterElements))
		{
			$queryWhere[] = 't.id IN (' . implode(', ', $filterElements) . ')';
		}
		if (count($filterKeys))
		{
			$queryWhere[] = 'f.id IN (' . implode(', ', $filterKeys) . ')';
		}
		if (count($queryWhere))
		{
			$query->where('(' . implode(' OR ',$queryWhere) . ')');
		}

		// Get the options.
		$db->setQuery($query, $filterOffset, $filterLimit);

		try
		{
			$strings = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			// FIX IT!
			//JError::raiseWarning(500, $e->getMessage());
		}

		$translations = array();
		$countStrings = count($strings);

		for ($i = 0; $i < $countStrings; $i++)
		{
			$translations[] = new NenoContentElementTranslation($strings[$i]);
		}

		echo JLayoutHelper::render('strings', $translations, JPATH_NENO_LAYOUTS);

		JFactory::getApplication()->close();
	}

}
