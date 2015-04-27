<?php

/**
 * @package     Neno
 * @subpackage  Helpers
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('JPATH_NENO') or die;

$groupId = $displayData;

?>

<script>
	jQuery().ready(function () {
		loadMissingTranslationMethodSelectors();
	});

	function loadMissingTranslationMethodSelectors() {

		//Count how many we currently are showing
		var n = jQuery('.translation-method-selector-container').length;
		var groupId = jQuery('#translation-method-selectors').data('group-id');

		//If we are loading because of changing a selector, remove all children
		var selector_id = jQuery(this).attr('data-selector-id');
		if (typeof selector_id !== 'undefined') {
			//Loop through each selector and remove the ones that are after this one
			for (i = 0; i < n; i++) {
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

		jQuery.get('index.php?option=com_neno&task=groupselements.getTranslationMethodSelector&group_id=' + groupId + '&n=' + n + selected_methods_string
			, function (html) {
				if (html !== '') {

					jQuery('#translation-method-selectors').append(html);

					//Bind the loader unto the new selector
					jQuery('.translation-method-selector').off('change').on('change', loadMissingTranslationMethodSelectors);

					loadMissingTranslationMethodSelectors();

				}
			}
		);
	}

</script>

<div id="translation-method-selectors" data-group-id="<?php echo $groupId; ?>">

</div>