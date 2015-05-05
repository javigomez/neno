<?php
/**
 * Created by PhpStorm.
 * User: victor
 * Date: 21/04/15
 * Time: 16:09
 */

defined('JPATH_NENO') or die;

?>

<?php foreach ($displayData->messages as $message): ?>
	<div class="alert <?php echo $displayData->error ? 'alert-error' : ''; ?>">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong><?php echo $message; ?></strong>
	</div>
<?php endforeach; ?>