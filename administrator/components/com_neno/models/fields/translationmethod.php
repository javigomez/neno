<?php
/**
 * @package     Neno
 * @subpackage  Fields
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Class JFormFieldTranslationMethod
 *
 * @since  1.0
 */
class JFormFieldTranslationMethod extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since   1.6
	 */
	protected $type = 'TranslationMethod';

	/**
	 * Method to get the translation method options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	public function getOptions()
	{
		$options = array ();

		$options[] = JHtml::_('select.option', 'machine', JText::_('COM_NENO_TRANSLATION_METHODS_MACHINE'));
		$options[] = JHtml::_('select.option', 'professional', JText::_('COM_NENO_TRANSLATION_METHODS_PROFESSIONAL'));

		return array_merge(parent::getOptions(), $options);
	}
}
