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
if (!empty($this->extraSidebar))
{
	$this->sidebar .= $this->extraSidebar;
}

$workingLanguage = NenoHelper::getWorkingLanguage();

?>

<script type="text/javascript">

	jQuery(document).ready(bindEvents);

	function bindEvents() {
		//Bind the loader into the new selector
		loadMissingTranslationMethodSelectors();
		jQuery('.configuration-button').off('click').on('click', function () {
			jQuery(this).siblings('.language-configuration').slideToggle('fast');
		});

		jQuery(".radio").off('change').on('change', function () {
			jQuery.ajax({
				beforeSend: onBeforeAjax,
				url: 'index.php?option=com_neno&task=dashboard.toggleLanguage&language=' + jQuery(this).data('language')
			});
		});

		jQuery(".remove-language-button").off('click').on('click', function () {
			var result = confirm("<?php echo JText::_('COM_NENO_DASHBOARD_REMOVING_LANGUAGE_MESSAGE_1') ?>\n\n<?php echo JText::_('COM_NENO_DASHBOARD_REMOVING_LANGUAGE_MESSAGE_2'); ?>");

			if (result) {
				jQuery(this).closest('.language-wrapper').slideUp();
				jQuery.ajax({
					beforeSend: onBeforeAjax,
					url: 'index.php?option=com_neno&task=removeLanguage&language=' + jQuery(this).data('language')
				});
			}

		});

		jQuery("[data-issue]").off('click').on('click', fixIssue);

		jQuery('.not-ready').off('click').on('click', function (e) {
			e.preventDefault();
			alert('<?php echo JText::_('COM_NENO_LANGUAGE_IS_NOT_READY_YET_MESSAGE'); ?>');
		});
	}

</script>


<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<?php if (!$this->isLanguageSwitcherPublished): ?>
		<div class="alert">
			<form action="index.php?option=com_neno&task=dashboard.publishSwitcher" method="POST">
				<h3><?php echo JText::_('COM_NENO_DASHBOARD_LANGUAGE_SWITCHER_NOT_PUBLISHED_H3'); ?></h3>

				<p><?php echo JText::_('COM_NENO_DASHBOARD_LANGUAGE_SWITCHER_NOT_PUBLISHED_P1'); ?></p>

				<p><?php echo JText::sprintf('COM_NENO_DASHBOARD_LANGUAGE_SWITCHER_NOT_PUBLISHED_P2', $this->positionField); ?></p>
				<button class="btn btn-success">
					<?php echo JText::_('COM_NENO_DASHBOARD_LANGUAGE_SWITCHER_NOT_PUBLISHED_PUBLISH_BUTTON'); ?>
				</button>
				<a href="index.php?option=com_neno&task=dashboard.doNotShowWarningMessage" class="btn">
					<?php echo JText::_('COM_NENO_DASHBOARD_LANGUAGE_SWITCHER_NOT_PUBLISHED_DO_NOT_REMIND_ME_BUTTON'); ?>
				</a>
			</form>
		</div>
	<?php endif; ?>
	<div class="languages-holder">
		<?php foreach ($this->items as $item): ?>
			<?php $item->placement = 'dashboard'; ?>
			<?php echo JLayoutHelper::render('languageconfiguration', $item, JPATH_NENO_LAYOUTS); ?>
		<?php endforeach; ?>
		<button type="button" class="btn btn-primary"
		        id="add-languages-button" <?php echo $this->canInstallLanguages ? '' : 'disabled'; ?>>
			<?php echo JText::_('COM_NENO_INSTALLATION_TARGET_LANGUAGES_ADD_LANGUAGE_BUTTON'); ?>
		</button>
	</div>
</div>
<div class="modal hide fade" id="languages-modal">
	<div class="modal-header">
		&nbsp;<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	</div>
	<div class="modal-body" style="height: 400px"></div>
	<div class="modal-footer">
		<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('JTOOLBAR_CLOSE'); ?></a>
	</div>
</div>

<div class="modal hide fade" id="translationMethodModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><?php echo JText::_('COM_NENO_TRANSLATION_METHOD_MODAL_TITLE'); ?></h3>
	</div>
	<div class="modal-body">
		<p>
			<?php echo JText::_('COM_NENO_TRANSLATION_METHOD_MODAL_MESSAGE'); ?>
		</p>
	</div>
	<div class="modal-footer">
		<a href="#" class="btn" data-dismiss="modal"><?php echo JText::_('JNO'); ?></a>
		<button type="button" class="btn btn-primary yes-btn"><?php echo JText::_('JYES'); ?></button>
	</div>
</div>

<script>
	jQuery('#add-languages-button').click(function () {
		jQuery.ajax({
			beforeSend: onBeforeAjax,
			url: 'index.php?option=com_neno&task=showInstallLanguagesModal&placement=dashboard',
			success: function (html) {
				var modal = jQuery('#languages-modal');
				modal.find('.modal-body').empty().append(html);
				modal.find('.modal-header h3').html("<?php echo JText::_('COM_NENO_INSTALLATION_TARGET_LANGUAGES_LANGUAGE_MODAL_TITLE'); ?>");
				modal.modal('show');
			}
		});
	})
</script>
