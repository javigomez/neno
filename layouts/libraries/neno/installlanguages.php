<?php

defined('_JEXEC') or die;
$languages = $displayData->languages;

?>
<table class="table">
	<tr>
		<th><?php echo JText::_('COM_NENO_INSTALL_LANGUAGES_LANGUAGE_NAME'); ?></th>
		<th><?php echo JText::_('JVERSION'); ?></th>
		<th></th>
	</tr>
	<?php foreach ($languages as $language): ?>
		<tr>
			<td><?php echo $language['name']; ?></td>
			<td><?php echo $language['version']; ?></td>
			<td>
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
		jQuery(this).attr('disabled', true);
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
					jQuery(html).insertBefore('#add-languages-button');
					bindEvents();
				}
			}
		});
	});
</script>
