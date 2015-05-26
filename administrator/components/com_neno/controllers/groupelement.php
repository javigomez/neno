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

/**
 * Manifest Groups & Elements controller class
 *
 * @since  1.0
 */
class NenoControllerGroupElement extends JControllerForm
{
	/**
	 * Constructor
	 *
	 * @param   array $config Constructor configuration
	 *
	 * @throws Exception
	 */
	public function __construct($config = array ())
	{
		$this->view_list = 'groupselements';
		parent::__construct($config);
	}

	/**
	 * Generate content element file
	 *
	 * @return void
	 */
	public function downloadContentElementFile()
	{
		$input    = JFactory::getApplication()->input;
		$table_id = $input->getInt('table_id');

		/* @var $table NenoContentElementTable */
		$table = NenoContentElementTable::load($table_id, false, true);

		/* @var $table stdClass */
		$table = $table->prepareDataForView();

		// Make file name
		$table_name           = str_replace('#__', '', $table->table_name);

		$file_name = $table_name . '_contentelements.xml';
		$displayData               = array ();
		$displayData['table_name'] = $table_name;
		$displayData['table']      = $table;

		// Output XML
		header('Content-Type: application/xml; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $file_name . '"');

		echo JLayoutHelper::render('contentelementxml', $displayData, JPATH_NENO_LAYOUTS);

		JFactory::getApplication()->close();
	}
}
