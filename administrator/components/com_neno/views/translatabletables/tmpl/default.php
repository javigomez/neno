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


<h1>Tables</h1>

<form action="<?php echo JRoute::_('index.php?option=com_neno&task=translatabletables.importDatabaseTables'); ?>"
	method="POST">
	<div class="accordion" id="accordion">

		<?php foreach ($this->dbTables as $table_name => $fields): ?>
			<div class="accordion-group">
				<div class="accordion-heading">
					<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion"
						href="#collapse-<?php echo str_replace('#', '', $table_name); ?>">
						<?php echo $table_name; ?>
					</a>
				</div>
				<div id="collapse-<?php echo str_replace('#', '', $table_name); ?>" class="accordion-body collapse">
					<div class="accordion-inner">
						<table class="table">
							<tr>
								<th>
									Field Name
								</th>
								<th>
									Translatable
								</th>
							</tr>
							<?php foreach ($fields as $key => $field): ?>
								<tr>
									<td>
										<?php echo $field; ?>
									</td>
									<td>
										<input type="checkbox" value="<?php echo $field; ?>"
											name="jform[<?php echo $table_name; ?>][]" />
									</td>

								</tr>
							<?php endforeach; ?>
						</table>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<input type="submit" class="btn btn-primary" value="Translate" />
</form>
