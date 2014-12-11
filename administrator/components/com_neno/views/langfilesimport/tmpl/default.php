<?php
/**
 * @package     NEno
 * @subpackage  Views
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
?>

<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {

	});

	Joomla.submitbutton = function (task) {
		Joomla.submitform(task, document.getElementById('adminForm'));
	}
</script>
<form action="index.php" name="adminForm" id="adminForm" method="post">
	<input type="hidden" name="option" value="com_neno" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php if ($this->changesPending): ?>
	<!-- show waring as strings are not synced -->
	<div class="alert alert-warning"><span
			class="icon-warning-2"></span> <?php echo JText::_('COM_NENO_LANGFILES_IMPORT_ALERT_CHANGES_PENDING'); ?>
	</div>
<?php else: ?>
	<!-- show all ok -->
	<div class="alert alert-success"><span
			class="icon-ok"></span> <?php echo JText::_('COM_NENO_LANGFILES_IMPORT_ALERT_NO_CHANGES_PENDING'); ?></div>
<?php endif; ?>

<?php $change_count = count($this->changedTargetStrings, COUNT_RECURSIVE) - count($this->changedTargetStrings); ?>
<?php if ($change_count): ?>
	<!-- show waring as target strings have changed -->
	<div class="alert alert-warning"><span
			class="icon-warning-2"></span> <?php echo JText::sprintf('COM_NENO_LANGFILES_IMPORT_ALERT_CHANGES_IN_TARGET', $change_count); ?>
	</div>
<?php endif; ?>

<div class="row-fluid">

	<div class="span6">

		<div class="well" id="accordion">
			<h2 class="module-title nav-header">
				<?php echo JText::sprintf('COM_NENO_VIEW_LANGFILESIMPORT_HL_SOURCE_CHANGES_IN_LANGFILES', $this->sourceLanguage); ?>
			</h2>
			<?php foreach ($this->sourceCounts as $key => $count): ?>
				<div class="row-striped">
					<div class="row-fluid">
						<?php $change_count = @count($count[$this->sourceLanguage]); ?>
						<strong class="row-title">
							<span class="badge <?php echo $change_count > 0 ? 'badge-important' : ''; ?>">
								<?php echo $change_count; ?>
							</span>
							<a data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $key; ?>">
								<?php echo JText::_('COM_NENO_VIEW_LANGFILESIMPORT_LABEL_CHANGES_' . strtoupper($key)); ?>
							</a>
						</strong>
					</div>
					<div id="collapse<?php echo $key; ?>" class="panel-collapse collapse">
						<div class="well well-small">
							<?php if (!empty($count[$this->sourceLanguage])): ?>
								<ul>
									<?php foreach ($count[$this->sourceLanguage] as $file => $content): ?>
										<li><?php echo $file; ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>

		</div>

	</div>

	<!-- Target changes -->
	<div class="span6">

		<div class="well" id="accordion">
			<h2 class="module-title nav-header"><?php echo JText::_('COM_NENO_VIEW_LANGFILESIMPORT_HL_TARGET_CHANGES_IN_LANGFILES'); ?></h2>
			<?php foreach ($this->newTargetStrings as $lang => $lines): ?>
				<div class="row-striped">
					<div class="row-fluid">
						<?php $change_count = @count($lines); ?>
						<strong class="row-title">
							<span
								class="badge <?php echo $change_count > 0 ? 'badge-important' : ''; ?>"><?php echo $change_count; ?></span>
							<a data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $lang; ?>">
								<?php echo NenoHelper::getLangNameFromCode($lang); ?>
							</a>
						</strong>
					</div>
					<div id="collapse<?php echo $lang; ?>" class="panel-collapse collapse">
						<div class="well well-small">
							<?php if (!empty($lines)): ?>
								<ul>
									<?php foreach ($lines as $file => $content): ?>
										<li><?php echo $file; ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>

		</div>

	</div>
</div>
    


