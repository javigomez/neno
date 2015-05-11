<?php

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
$items = $displayData->languages;

?>

<style>
	.schedule-task-option {
		border: 1px solid #000;
		padding: 5px 10px;
	}
</style>

<div class="installation-step">
	<div class="installation-body span12">
		<div class="error-messages"></div>
		<h2><?php echo JText::_('COM_NENO_INSTALLATION_TARGET_LANGUAGES_TITLE'); ?></h2>

		<p><?php echo JText::_('COM_NENO_INSTALLATION_TARGET_LANGUAGES_MESSAGE'); ?></p>

		<?php foreach ($items as $item): ?>
			<?php echo JLayoutHelper::render('languageconfiguration', $item, JPATH_NENO_LAYOUTS); ?>
		<?php endforeach; ?>

		<button type="button" class="btn btn-primary">
			<?php echo JText::_('COM_NENO_INSTALLATION_TARGET_LANGUAGES_ADD_LANGUAGE_BUTTON'); ?>
		</button>

		<button type="button" class="btn btn-success next-step-button">
			<?php echo JText::_('COM_NENO_INSTALLATION_NEXT'); ?>
		</button>
	</div>

	<?php echo JLayoutHelper::render('installationbottom', 3, JPATH_NENO_LAYOUTS); ?>
</div>