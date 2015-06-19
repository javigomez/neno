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

<div class="btn-group" data-field="<?php echo $displayData['fieldId']; ?>">
	<a class="btn dropdown-toggle btn-small" data-toggle="dropdown" href="#">
		<?php echo $displayData['selected']; ?>
		<span class="caret"></span>
	</a>
	<ul class="dropdown-menu">
		<?php foreach ($displayData['filters'] as $filter): ?>
			<li data-filter="<?php echo $filter; ?>"
			    class="filter <?php echo $filter == $displayData['selected'] ? 'hide' : ''; ?>">
				<a href="#"><?php echo $filter; ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
