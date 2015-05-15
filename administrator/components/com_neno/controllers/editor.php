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


require_once JPATH_COMPONENT_ADMINISTRATOR . '/controllers/strings.php';

/**
 * Manifest Editor controller class
 *
 * @since  1.0
 */
class NenoControllerEditor extends NenoControllerStrings
{
	/**
	 * Method to handle ajax call for google translation
	 *
	 * @return string
	 */
	public function translate()
	{
		$app             = JFactory::getApplication();
		$input           = $app->input;
		$text            = html_entity_decode($input->getHtml('text'));
		$workingLanguage = NenoHelper::getWorkingLanguage();
		$defaultLanguage = JFactory::getLanguage()->getDefault();
		$translator      = NenoSettings::get('translator');

		try
		{
			/* @var $nenoTranslate NenoTranslateApi */
			$nenoTranslate = NenoTranslateApi::getAdapter($translator);
			$result        = $nenoTranslate->translate($text, $defaultLanguage, $workingLanguage);

			if ($result == null)
			{
				$result = $text;
			}
		}
		catch (UnexpectedValueException $e)
		{
			$result = $text;
		}

		echo $result;

		$app->close();
	}

	/**
	 * Get a translations
	 *
	 * @return void
	 */
	public function getTranslation()
	{
		$input         = $this->input;
		$translationId = $input->getInt('id');

		if (!empty($translationId))
		{
			$translation = NenoContentElementTranslation::getTranslation($translationId);
			echo JLayoutHelper::render('editor', $translation->prepareDataForView(true), JPATH_NENO_LAYOUTS);
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Save translation as draft
	 *
	 * @return void
	 */
	public function saveAsDraft()
	{
		$input           = $this->input;
		$translationId   = $input->getInt('id');
		$translationText = $input->getHtml('text');

		if ($this->saveTranslation($translationId, $translationText, NenoContentElementTranslation::NOT_TRANSLATED_STATE))
		{
			/* @var $translation NenoContentElementTranslation */
			$translation = NenoContentElementTranslation::load($translationId, false);

			echo json_encode($translation->prepareDataForView());
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Save translation into the database
	 *
	 * @param   int    $translationId   Translation ID
	 * @param   string $translationText Translation Text
	 * @param   int    $changeState     Translation status
	 *
	 * @return bool
	 */
	protected function saveTranslation($translationId, $translationText, $changeState = false)
	{
		/* @var $translation NenoContentElementTranslation */
		$translation = NenoContentElementTranslation::load($translationId, false, true);

		$translation
			->setString($translationText)
			->setState($changeState)
			->addTranslationMethod(NenoContentElementTranslation::MANUAL_TRANSLATION_METHOD, 1);

		if ($changeState == NenoContentElementTranslation::TRANSLATED_STATE)
		{
			$translation->setTimeCompleted(new DateTime);
		}

		$result = $translation->persist();

		return $result;
	}

	/**
	 * Save translation as completed
	 *
	 * @return void
	 */
	public function saveAsCompleted()
	{
		$input           = $this->input;
		$translationId   = $input->getInt('id');
		$translationText = $input->getHtml('text');

		if ($this->saveTranslation($translationId, $translationText, NenoContentElementTranslation::TRANSLATED_STATE))
		{
			/* @var $translation NenoContentElementTranslation */
			$translation = NenoContentElementTranslation::load($translationId, false);

			$data = array (
				'translation' => $translation->prepareDataForView()
			);

			$original_text = $translation->getOriginalText();

			$model   = $this->getModel();
			$counter = $model->getSimilarTranslationsCounter($translationId, $translation->getLanguage(), $original_text);

			if ($counter != 0)
			{
				$data['message'] = JText::sprintf('COM_NENO_EDITOR_CONSOLIDATE_MESSAGE', $counter, NenoHelper::html2text($original_text, 200), NenoHelper::html2text($translationText, 200));
			}

			echo json_encode($data);
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Get model
	 *
	 * @param   string $name   Model name
	 * @param   string $prefix Model prefix
	 * @param   array  $config Model configuration
	 *
	 * @return NenoModelEditor
	 */
	public function getModel($name = 'Editor', $prefix = 'NenoModel', $config = array ())
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Consolidate translation
	 *
	 * @return void
	 */
	public function consolidateTranslation()
	{
		$input         = $this->input;
		$translationId = $input->post->getInt('id');

		if (!empty($translationId))
		{
			$model = $this->getModel();
			$model->consolidateTranslations($translationId);
		}
	}
}
