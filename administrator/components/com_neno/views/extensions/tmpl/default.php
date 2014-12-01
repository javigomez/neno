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
JHtml::_('formbehavior.chosen', 'select');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_neno/assets/css/neno.css');

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_neno&task=extensions.import'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
			<?php endif; ?>
			<div class="accordion" id="accordion2">
				<?php foreach ($this->items as $extension): ?>
					<div class="accordion-group">
						<div class="accordion-heading">
							<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapse-<?php echo $extension->extension_id; ?>">
								Name: <?php echo $extension->name; ?> Type: <?php echo $extension->type; ?>
							</a>
						</div>
						<div id="collapse-<?php echo $extension->extension_id; ?>" class="accordion-body collapse">
							<div class="accordion-inner">
								<div class="accordion" id="accordion-<?php echo md5($extension->name); ?>">
									<table class="table table-striped" id="sourceList">
										<tr>
											<th></th>
											<th>Table</th>
										</tr>
										<tbody>
										<?php foreach ($extension->tables as $table): ?>
											<tr>
												<td>
													<input type="checkbox" name="jform[<?php echo $extension->extension_id; ?>][]"
														value="<?php echo $table->table_name; ?>" <?php echo $table->enabled ? 'checked="checked"' : ''; ?>
														/>
												</td>
												<td><?php echo $table->table_name; ?></td>
											</tr>
										<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>

			<button type="submit" class="btn btn-primary">Save Data</button>
		</div>
</form>        

		
