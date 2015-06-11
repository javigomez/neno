<?php
/**
 * @package    Neno
 *
 * @author     Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright  Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>

<?php foreach ($displayData->messages as $message): ?>
	<div class="alert <?php echo $displayData->error ? 'alert-error' : ''; ?>">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong><?php echo $message; ?></strong>
	</div>
<?php endforeach; ?>