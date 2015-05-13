<?php
/**
 * @package     Neno
 * @subpackage  Models
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/strings.php';

/**
 * NenoModelEditor class
 *
 * @since  1.0
 */
class NenoModelEditor extends NenoModelStrings
{
	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array ())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array ();
		}

		parent::__construct($config);
	}

	/**
	 * Consolidate Translation
	 *
	 * @param   int $translationId Translation id
	 *
	 * @return bool True on success
	 */
	public function consolidateTranslations($translationId)
	{
		/* @var $translation NenoContentElementTranslation */
		$translation = NenoContentElementTranslation::load($translationId);
		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);

		$query
			->update('#__neno_content_element_translations')
			->set(
				array (
					'string = ' . $db->quote($translation->getString()),
					'translation_method = 1',
					'state = 1'
				)
			)
			->where(
				array (
					'original_text =' . $db->quote($translation->getOriginalText()),
					'id <> ' . $translationId
				)
			);

		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Get the amount of translations that contains the same text
	 *
	 * @param   int    $translationId   Translation Id
	 * @param   string $translationText Translation Text
	 *
	 * @return int
	 */
	public function getSimilarTranslationsCounter($translationId, $translationText)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('COUNT(*)')
			->from('#__neno_content_element_translations')
			->where(
				array (
					'original_text = ' . $db->quote($translationText),
					'state = ' . NenoContentElementTranslation::NOT_TRANSLATED_STATE,
					'id <> ' . $translationId,
				)
			);

		$db->setQuery($query);

		return (int) $db->loadResult();
	}
}
