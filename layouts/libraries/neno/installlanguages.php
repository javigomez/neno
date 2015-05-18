<?php

defined('_JEXEC') or die;
$languages = $displayData->languages;

?>
<table class="table table-striped">
	<tr>
		<th><?php echo JText::_('COM_NENO_INSTALL_LANGUAGES_LANGUAGE_NAME'); ?></th>
		<th><?php echo JText::_('JVERSION'); ?></th>
		<th></th>
	</tr>
	<?php foreach ($languages as $language): ?>
		<tr>
			<td><?php echo $language['name']; ?></td>
			<td><?php echo $language['version']; ?></td>
			<td class="action-cell" data-language-iso="<?php echo $language['iso'] ?>">
				<button type="button" class="btn" data-update="<?php echo $language['update_id']; ?>"
				        data-language="<?php echo $language['iso'] ?>">
					<?php echo JText::_('JTOOLBAR_INSTALL'); ?>
				</button>
			</td>
		</tr>
	<?php endforeach; ?>
</table>

<script>
	jQuery("[data-language]").click(function () {
		var button = jQuery(this);
		button.hide();
		button.parent().append('<div class="loading"></div>')
		jQuery.ajax({
			beforeSend: onBeforeAjax,
			url: 'index.php?option=com_neno&task=installLanguage',
			data: {
				update: jQuery(this).data('update'),
				language: jQuery(this).data('language'),
				placement: '<?php echo $displayData->placement; ?>'
			},
			type: 'POST',
			success: function (html) {
				if (html != 'err') {
					var response = jQuery(html);
					var iso = response.find('fieldset').attr('data-language');
					var cell = jQuery('.action-cell [data-language-iso="' + iso + '"]');
					cell.html('<div class="icon-checkmark"></div>');
					response.insertBefore('#add-languages-button');
					bindEvents();
					loadMissingTranslationMethodSelectors();
				}
			}
		});
	});
</script>
