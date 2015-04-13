<?php

// No direct access
defined('_JEXEC') or die;

/**
 * test Table class
 *
 * @since  1.0
 */
class NenoTableGroupElement extends JTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__neno_content_element_groups', 'id', $db);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param   mixed $pk Primary key
	 *
	 * @return bool
	 */
	public function delete($pk = null)
	{
		/* @var $group NenoContentElementGroup */
		$group = NenoContentElementGroup::load($pk);

		return $group->remove();
	}

	public function save($src, $orderingFilter = '', $ignore = '')
	{
		Kint::dump($src);
		exit;
	}


}
