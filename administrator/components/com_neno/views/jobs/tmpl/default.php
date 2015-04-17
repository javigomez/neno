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

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$listOrder     = $this->state->get('list.ordering');
$listDirection = $this->state->get('list.direction');

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

?>

<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<form action="<?php echo JRoute::_('index.php?option=com_neno&view=strings'); ?>" method="post" name="adminForm"
      id="adminForm">
	<div id="j-main-container" class="span10">
		<div id="elements-wrapper">
			<table class="table table-striped table-jobs" id="table-jobs">
				<thead>
				<tr>
					<th>
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirection, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'state', $listDirection, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_NENO_JOBS_LANGUAGE', 'to_language', $listDirection, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_NENO_JOBS_TRANSLATION_METHOD', 'translation_method', $listDirection, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_NENO_JOBS_WORD_COUNT', 'word_count', $listDirection, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_NENO_JOBS_TRANSLATION_CREDIT', 'translation_credit', $listDirection, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_NENO_JOBS_CREATION_DATE', 'created_time', $listDirection, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_NENO_JOBS_ESTIMATED_COMPLETION', 'completion_time', $listDirection, $listOrder); ?>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php /* @var $item JObject */ ?>
				<?php foreach ($this->items as $item): ?>
					<tr class="row-string">
						<td class="cell-status">
							<?php echo $item->id; ?>
						</td>
						<td>
							<?php echo NenoHelper::html2text($translation->string, 200); ?>
						</td>
						<td>
							<?php echo $translation->breadcrumbs[0]; ?>
						</td>
						<td>
							<?php echo $translation->breadcrumbs[1]; ?>
						</td>
						<td>
							<?php echo $translation->breadcrumbs[2]; ?>
						</td>
						<td>
							<?php echo JText::_('COM_NENO_TRANSLATION_METHODS_' . strtoupper($translation->translation_method)); ?>
						</td>
						<td>
							<?php echo $translation->word_counter; ?>
						</td>
						<td>
							<?php echo $translation->characters_counter; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<tfoot>
				<tr>
					<td colspan="9">
						<?php /*echo $this->pagination->getListFooter();*/ ?>
					</td>
				</tr>
				</tfoot>
			</table>
		</div>
		<?php echo $this->pagination->getListFooter(); ?>
	</div>
</form>
