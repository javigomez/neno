<?php

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

?>

<div class="installation-step">
	<div class="installation-body span12">

		<div class="error-messages"></div>
		<h2><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_TITLE'); ?></h2>

		<div class="span6">
			<p><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_MESSAGE'); ?></p>

			<div id="translation-method-selectors"></div>

		</div>
		<div class="span6 doc">
			<p><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_P1'); ?></p>

			<p><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_P2'); ?></p>

			<p><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_P3'); ?></p>

			<h3><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_MANUAL_TRANSLATION_TITLE'); ?></h3>

			<p><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_MANUAL_TRANSLATION_MESSAGE'); ?></p>

			<h3><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_MACHINE_TRANSLATION_TITLE'); ?></h3>

			<p><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_MACHINE_TRANSLATION_MESSAGE'); ?></p>

			<h3><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_PROFESSIONAL_TRANSLATION_TITLE'); ?></h3>

			<p><?php echo JText::_('COM_NENO_INSTALLATION_DEFAULT_SETTINGS_DESCRIPTION_TEXT_PROFESSIONAL_TRANSLATION_MESSAGE'); ?></p>
		</div>
		<div class="span12">
			<button type="button" class="btn btn-success next-step-button">
				<?php echo JText::_('COM_NENO_INSTALLATION_NEXT'); ?>
			</button>

		</div>
	</div>

	<?php echo JLayoutHelper::render('installationbottom', 2, JPATH_NENO_LAYOUTS); ?>
</div>

<script>
	function loadMissingTranslationMethodSelectors() {

		//Count how many we currently are showing
		var n = jQuery('.translation-method-selector-container').length;

		//If we are loading because of changing a selector, remove all children
		var selector_id = jQuery(this).attr('data-selector-id');
		if (typeof selector_id !== 'undefined') {
			//Loop through each selector and remove the ones that are after this one
			for (var i = 0; i < n; i++) {
				if (i > selector_id) {
					jQuery("[data-selector-container-id='" + i + "']").remove();
				}
			}
		}

		//Create a string to pass the current selections
		var selected_methods_string = '';
		jQuery('.translation-method-selector').each(function () {
			selected_methods_string += '&selected_methods[]=' + jQuery(this).find(':selected').val();
		});

		jQuery.ajax({
				beforeSend: onBeforeAjax,
				url: 'index.php?option=com_neno&task=installation.getTranslationMethodSelector&n=' + n + selected_methods_string,
				success: function (html) {
					if (html !== '') {

						jQuery('#translation-method-selectors').append(html);

						//Bind the loader unto the new selector
						jQuery('.translation-method-selector').off('change').on('change', loadMissingTranslationMethodSelectors);

						jQuery('select').chosen();

						loadMissingTranslationMethodSelectors();
					}
				}
			}
		);
	}

	console.log("hola");

	loadMissingTranslationMethodSelectors();
</script>
