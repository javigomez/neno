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

JHtml::_('behavior.keepalive');

?>

<style type="text/css">
	#translate_btn {
		float: right
	}
</style>

<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery('#translate_btn').on('click', translate);
	});

	// function to get translation using ajax
	function translate() {
		var source = jQuery("#source_text").val();
		if (source != "") {
			jQuery.ajax({
				url: "index.php?option=com_neno&task=demo.ajaxTranslate",
				data: {'api': 'google', 'source': source},
				type: 'post',
				success: function (msg) {
					jQuery("#translate_text").val(msg);
				}
			});
		}
	}
</script>

<div class="row-fluid">

	<div class="span1">Source Text</div>

	<div class="span4">
		<form>
			<textarea id="source_text" class="span12" rows="8" cols="200" placeholder="English"></textarea>
		</form>
	</div>


	<div class="span1">Translated Text</div>

	<div class="span4">

		<form action="" method="post" enctype="multipart/form-data">

			<textarea id="translate_text" class="span12" rows="8" cols="200" placeholder="French"></textarea>

			<input type="hidden" name="task" value=""/>
			<?php echo JHtml::_('form.token'); ?>

		</form>

	</div>

	<div class="span5">
		<button id="translate_btn" class="btn btn-primary">Translate</button>
	</div>

</div>
