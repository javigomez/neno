<?php
/**
 * @package     Lingo
 * @subpackage  Tables
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * translation Table class
 *
 * @since  1.0
 */
class LingoTableTranslation extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 */
	public function __construct($db)
	{
		parent::__construct('#__lingo_langfile_translations', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return null|string null is operation was satisfactory, otherwise returns an error
	 *
	 * @see        JTable:bind
	 * @since      1.5
	 */
	public function bind($src, $ignore = '')
	{
		if (isset($src['params']) && is_array($src['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($src['params']);
			$src['params'] = (string) $registry;
		}

		if (isset($src['metadata']) && is_array($src['metadata']))
		{
			$registry = new JRegistry;
			$registry->loadArray($src['metadata']);
			$src['metadata'] = (string) $registry;
		}

		if (!JFactory::getUser()->authorise('core.admin', 'com_lingo.translation.' . $src['id']))
		{
			$actions         = JFactory::getACL()->getActions('com_lingo', 'translation');
			$default_actions = JFactory::getACL()->getAssetRules('com_lingo.translation.' . $src['id'])->getData();
			$array_jaccess   = array();

			foreach ($actions as $action)
			{
				$array_jaccess[$action->name] = $default_actions[$action->name];
			}

			$src['rules'] = $this->JAccessRulesToArray($array_jaccess);
		}

		// Bind the rules for ACL where supported.
		if (isset($src['rules']) && is_array($src['rules']))
		{
			$this->setRules($src['rules']);
		}

		return parent::bind($src, $ignore);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param   array  $jAccessRules  JAccess array
	 *
	 * @return array
	 */
	private function JAccessRulesToArray($jAccessRules)
	{
		$rules = array();

		foreach ($jAccessRules as $action => $jaccess)
		{
			$actions = array();

			foreach ($jaccess->getData() as $group => $allow)
			{
				$actions[$group] = ((bool) $allow);
			}

			$rules[$action] = $actions;
		}

		return $rules;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return boolean
	 */
	public function check()
	{
		// If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->id == 0)
		{
			$this->ordering = self::getNextOrder();
		}

		return parent::check();
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not
	 *                            set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return    boolean    True on success.
	 *
	 * @since    1.0.4
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array( $this->$k );
			}
			// Nothing to set publishing state on, return false.
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'), 'error');

				return false;
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);

		// Determine if there is checkin support for the table.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time'))
		{
			$checkin = '';
		}
		else
		{
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
		}

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery(
			'UPDATE `' . $this->_tbl . '`' .
			' SET `state` = ' . (int) $state .
			' WHERE (' . $where . ')' .
			$checkin
		);
		$this->_db->query();

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin each row.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

		return true;
	}

	/**
	 * Define a namespace asset name for inclusion in the #__assets table
	 *
	 * @return string The asset name
	 *
	 * @see JTable::_getAssetName
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_lingo.translation.' . (int) $this->$k;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param   JTable   $table  A JTable object for the asset parent.
	 * @param   integer  $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 * @see JTable::_getAssetParentId
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		// We will retrieve the parent-asset from the Asset-table
		$assetParent = JTable::getInstance('Asset');

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// The item has the component as asset-parent
		$assetParent->loadByName('com_lingo');

		// Return the found asset-parent-id
		if ($assetParent->id)
		{
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
	}
}
