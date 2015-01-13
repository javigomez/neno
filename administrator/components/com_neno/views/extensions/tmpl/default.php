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
		display: none;
	}

	.fields-container {
		display: none;
	}
</style>

<script type="text/javascript">
	jQuery(document).ready(function () {
		// Expand links
		jQuery('a.expand-link').click(function (e) {
			e.preventDefault();
			e.stopPropagation();

			// If the target is expanded, let's close it
			if (jQuery(this).hasClass('expanded')) {
				jQuery(this).closest('div').find(jQuery(this).data('child-selector')).slideUp();
				jQuery(this).removeClass('expanded');
				jQuery(this).text('[+]');
			}
			else {
				jQuery(this).closest('div').find(jQuery(this).data('child-selector')).slideDown();
				jQuery(this).addClass('expanded');
				jQuery(this).text('[-]');
			}
		});

		// Enable/Disable tables
		jQuery('.activate-btn').click(function (e) {
			e.preventDefault();
			e.stopPropagation();

			var button = jQuery(this);
			var tableId = button.data('table_id');
			var newStatus = !button.hasClass('btn-success');

			jQuery.post('index.php?option=com_neno&task=extensions.enableDisableContentElementTable',
				{
					'tableId': tableId,
					'translateStatus': newStatus
				}, function (response) {
					if (response == 1) {
						if (button.hasClass('btn-success')) {
							button.addClass('btn-danger').removeClass('btn-success');
							button.find('i').addClass('icon-remove').removeClass('icon-ok');
						}
						else {
							button.addClass('btn-success').removeClass('btn-danger');
							button.find('i').addClass('icon-ok').removeClass('icon-remove');
						}
					}
				});
		});

	});
</script>

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
				<h4><a href="#" class="expand-link" data-child-selector=".table-container">[+]</a>
					Extension: <?php echo $group->getGroupName(); ?></h4>
				<?php /* @var $table NenoContentElementTable */ ?>
				<?php foreach ($group->getTables() as $table): ?>
					<div class="table-container">
						<h6><a href="#" class="expand-link" data-child-selector=".fields-container">[+]</a>
							Table: <?php echo $table->getTableName(); ?>
							&nbsp;
							<button
								class="btn btn-mini btn-<?php echo $table->hasBeenMarkedAsTranslatable() ? 'success' : 'danger'; ?> activate-btn"
								data-table_id="<?php echo $table->getId(); ?>">
								<i class="icon-<?php echo $table->hasBeenMarkedAsTranslatable() ? 'ok' : 'remove'; ?>"></i>
							</button>
						</h6>
						<div class="fields-container">
							<table class="table">
								<tr>
									<th>Field Name</th>
									<th>Translate</th>
								</tr>
								<?php /* @var $field NenoContentElementField */ ?>
								<?php foreach ($table->getFields() as $field): ?>
									<tr>
										<td><?php echo $field->getFieldName() ?></td>
										<td>
											<i class="icon-<?php echo $field->isTranslatable() ? 'ok' : 'remove'; ?>"></i>
										</td>
									</tr>
								<?php endforeach; ?>
							</table>
						</div>

					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
