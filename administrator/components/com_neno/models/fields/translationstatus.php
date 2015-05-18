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
 *
 */
class JFormFieldTranslationStatus extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   1.6
	 */
	protected $type = 'TranslationStatus';

	/**
	 * Method to get the translation method options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	public function getOptions()
	{
		$options = array();

		$options[] = JHtml::_('select.option', NenoContentElementTranslation::TRANSLATED_STATE, JText::_('COM_NENO_STATUS_TRANSLATED'));
		$options[] = JHtml::_('select.option', NenoContentElementTranslation::QUEUED_FOR_BEING_TRANSLATED_STATE, JText::_('COM_NENO_STATUS_QUEUED'));
		$options[] = JHtml::_('select.option', NenoContentElementTranslation::SOURCE_CHANGED_STATE, JText::_('COM_NENO_STATUS_CHANGED'));
		$options[] = JHtml::_('select.option', NenoContentElementTranslation::NOT_TRANSLATED_STATE, JText::_('COM_NENO_STATUS_NOTTRANSLATED'));

		return array_merge(parent::getOptions(), $options);
	}
}
