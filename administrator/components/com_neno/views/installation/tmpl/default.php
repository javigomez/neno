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
?>

<script>
	jQuery(document).ready(function () {
		jQuery('#find-languages').on('click', function () {
			jQuery.get('index.php?option=com_neno&task=installation.getLanguages', function (data) {
				data = JSON.parse(data);
				for (var i = 0; i < data.length; i++) {
					jQuery('#languages-form').append('<input type="checkbox" name="languages[]" value="' + data[i].update_id + '" /><img src="http://localhost/neno/media/mod_languages/images/' + data[i].iso.toLowerCase().replace('-', '_') + '.gif" />')
				}
			});
		});
	});
</script>

<h1>Neno Installation</h1>

<button type="button" class="btn" id="find-languages">Find Languages</button>

<div class="installation-form">
	<form action="index.php?option=com_neno&task=installation.installLanguages" method="post" id="languages-form">

		<button class="btn">Install</button>
	</form>
</div>

