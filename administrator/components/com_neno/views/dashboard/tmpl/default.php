<?php
/**
 * @package     Neno
 * @subpackage  Views
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

// Include the CSS file
JHtml::stylesheet('media/neno/css/admin.css');

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

$workingLanguage = NenoHelper::getWorkingLanguage();

?>

<script type="text/javascript">

	jQuery(document).ready(bindEvents);

	function bindEvents() {
		//Bind the loader into the new selector
		loadMissingTranslationMethodSelectors();
		jQuery('.configuration-button').on('click', function () {
			jQuery(this).siblings('.language-configuration').slideToggle('fast');
		});

		jQuery(".radio").on('change', function () {
			jQuery.ajax({
				beforeSend: onBeforeAjax,
				url: 'index.php?option=com_neno&task=dashboard.toggleLanguage&language=' + jQuery(this).data('language')
			});
		});

		jQuery(".remove-language-button").off('click').on('click', function () {
			var result = confirm("<?php echo JText::_('COM_NENO_DASHBOARD_REMOVING_LANGUAGE_MESSAGE_1') ?>\n\n<?php echo JText::_('COM_NENO_DASHBOARD_REMOVING_LANGUAGE_MESSAGE_2'); ?>")

			if (result) {
				jQuery(this).closest('.language-wrapper').slideUp();
				jQuery.ajax({
					beforeSend: onBeforeAjax,
					url: 'index.php?option=com_neno&task=removeLanguage&language=' + jQuery(this).data('language')
				});
			}

		});

		jQuery("[data-issue]").off('click').on('click', fixIssue);
	}

</script>


<form action="<?php echo JRoute::_('index.php?option=com_neno&view=groupselements'); ?>" method="post" name="adminForm"
      id="adminForm">

	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<div class="languages-holder">
			<?php foreach ($this->items as $item): ?>
				<?php $item->placement = 'dashboard'; ?>
				<?php echo JLayoutHelper::render('languageconfiguration', $item, JPATH_NENO_LAYOUTS); ?>
			<?php endforeach; ?>
			<button type="button" class="btn btn-primary" id="add-languages-button">
				<?php echo JText::_('COM_NENO_INSTALLATION_TARGET_LANGUAGES_ADD_LANGUAGE_BUTTON'); ?>
			</button>
		</div>
	</div>
	<div class="modal hide fade" id="languages-modal">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Modal header</h3>
		</div>
		<div class="modal-body"></div>
		<div class="modal-footer">
			<a href="#" class="btn"><?php echo JText::_('JCLOSE'); ?></a>
		</div>
	</div>
</form>

<script>
	jQuery('#add-languages-button').click(function () {
		jQuery.ajax({
			beforeSend: onBeforeAjax,
			url: 'index.php?option=com_neno&task=showInstallLanguagesModal&placement=dashboard',
			success: function (html) {
				jQuery('#languages-modal .modal-body').empty().append(html);
				jQuery('#languages-modal .modal-header h3').html("<?php echo JText::_('COM_NENO_INSTALLATION_TARGET_LANGUAGES_LANGUAGE_MODAL_TITLE'); ?>");
				jQuery('#languages-modal').modal('show');
			}
		});
	})
</script>
