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
		//Bind the loader unto the new selector
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

		jQuery('.method-1').change(toggleMethodSelect);

		jQuery("[data-issue]").off('click').on('click', fixIssue);

	}

	function loadMissingTranslationMethodSelectors() {
		if (typeof jQuery(this).prop("tagName") == 'undefined') {
			i = 1;
			jQuery('.method-selectors').each(function () {
				//Count how many we currently are showing
				var n = jQuery(this).find('.translation-method-selector-container').length;

				//If we are loading because of changing a selector, remove all children
				var selector_id = jQuery(this).find('.translation-method-selector').attr('data-selector-id');
				if (typeof selector_id !== 'undefined') {
					//Loop through each selector and remove the ones that are after this one
					for (var i = 0; i < n; i++) {
						if (i > selector_id) {
							jQuery(this).find("[data-selector-container-id='" + i + "']").remove();
						}
					}
				}

				//Create a string to pass the current selections
				var selected_methods_string = '';
				jQuery(this).find('.translation-method-selector').each(function () {
					selected_methods_string += '&selected_methods[]=' + jQuery(this).find(':selected').val();
				});

				executeAjax(n, selected_methods_string, jQuery(this).find('.translation-method-selector'));
			});
		}
		else {
			//If we are loading because of changing a selector, remove all children
			var selector_id = jQuery(this).data('selector-id');
			var n = jQuery(this).closest('.method-selectors').find('.translation-method-selector-container').length;
			if (typeof selector_id !== 'undefined') {
				//Loop through each selector and remove the ones that are after this one
				for (var i = 0; i < n; i++) {
					if (i > selector_id) {
						console.log(jQuery(this).closest('.method-selectors').find("[data-selector-container-id='" + i + "']"));
						jQuery(this).closest('.method-selectors').find("[data-selector-container-id='" + i + "']").remove();
					}
				}
			}
			var selected_methods_string = '&selected_methods[]=' + jQuery(this).find(':selected').val();
			var lang = jQuery(this).closest('.method-selectors').data('language');
			var otherParams = '&language=' + lang;
			saveTranslationMethod(jQuery(this).find(':selected').val(), lang, selector_id + 1);
			executeAjax(n, selected_methods_string, jQuery(this), otherParams);
		}


		function executeAjax(n, selected_methods_string, element, otherParams) {
			if (typeof otherParams == 'undefined') {
				otherParams = '';
			}
			jQuery.ajax({
				beforeSend: onBeforeAjax,
				url: 'index.php?option=com_neno&task=getTranslationMethodSelector&placement=language&n=' + n + selected_methods_string + otherParams,
				success: function (html) {
					if (html !== '') {
						jQuery(element).closest('.method-selectors').append(html);
					}

					jQuery('.translation-method-selector').off('change').on('change', loadMissingTranslationMethodSelectors);
					jQuery('select').chosen();
				}
			});
		}

		function saveTranslationMethod(translationMethod, language, ordering) {
			jQuery.ajax({
				beforeSend: onBeforeAjax,
				url: 'index.php?option=com_neno&task=saveTranslationMethod',
				type: 'POST',
				data: {
					translationMethod: translationMethod,
					language: language,
					ordering: ordering
				}
			});
		}
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
				jQuery('#languages-modal').modal('show');
			}
		});
	})
</script>
