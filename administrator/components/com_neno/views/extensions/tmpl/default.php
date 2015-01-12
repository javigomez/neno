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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}
?>

<style>
	.group-container {
		padding-bottom: 15px;
		margin-bottom: 10px;
		border-bottom: 2px solid #ccc;
	}

	.table-container {
		padding-top: 5px;
		border-top: 2px solid #dddddd;
		margin-left: 25px;
	}
</style>

<?php if (!empty($this->sidebar)): ?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<?php else : ?>
	<div id="j-main-container">
		<?php endif; ?>
		<?php /* @var $group NenoContentElementGroup */ ?>
		<?php foreach ($this->items as $group): ?>
			<div class="group-container">
				<h4>Extension: <?php echo $group->getGroupName(); ?></h4>
				<?php /* @var $table NenoContentElementTable */ ?>
				<?php foreach ($group->getTables() as $table): ?>
					<div class="table-container">
						<h6>Table: <?php echo $table->getTableName(); ?></h6>
						<table class="table">
							<tr><th>Field Name</th><th>Translate</th></tr>
							<?php /* @var $field NenoContentElementField */ ?>
							<?php foreach ($table->getFields() as $field): ?>
								<tr>
									<td><?php echo $field->getFieldName() ?></td>
									<td>
										<i class="icon-<?php echo ($field->isTranslatable()) ? 'ok' : 'remove'; ?>"></i>
									</td>
								</tr>
							<?php endforeach; ?>
						</table>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
