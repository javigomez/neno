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
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<div class="control-group">
		<label class="control-label" for="inputPassword">Debug report</label>

		<div class="controls">
		<textarea class="span10"
		          rows="200"><?php echo NenoHelper::printServerInformation(NenoHelper::getServerInfo()); ?></textarea>
		</div>
	</div>
</div>

