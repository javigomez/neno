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
?>
<h2><?php echo JText::_('COM_NENO_VIEW_MOVELEMENTCONFIRM_HEADER'); ?></h2>
<p><?php echo JText::_('COM_NENO_VIEW_MOVELEMENTCONFIRM_INTRO'); ?></p>
<form action="<?php echo JRoute::_('index.php?option=com_neno&view=groupselements'); ?>" method="post" name="adminForm" id="adminForm">

	<div class="row-fluid">
		<div class="span8 form-horizontal">
			<fieldset class="adminform">    
    
                <div class="control-group">                
                    <label for="group_id" class="control-label"><?php echo JText::_('COM_NENO_VIEW_MOVELEMENTCONFIRM_LABEL'); ?>:</label> 
                    <select name="group_id" id="group_id" class="controls">
                        <option value=""><?php echo JText::_('COM_NENO_VIEW_MOVELEMENTCONFIRM_GROUP_SELECT_DEFAULT'); ?></option>
                        <?php foreach ($this->groups as $group): ?>
                            <option value="<?php echo $group->id; ?>"><?php echo $group->group_name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>
        </div>
    </div>
    <br />
    <div class="thumbnail" style="background-color: #f5f5f5;color:#777;">
        <h4><?php echo JText::_('COM_NENO_VIEW_MOVELEMENTCONFIRM_SUB_HEADER'); ?></h4>
        <ul>
            <?php if (!empty($this->tables)): ?>
                <?php foreach ($this->tables as $table): ?>
                    <li><?php echo $table->table_name; ?></li>
                    <input type="hidden" name="tables[]" value="<?php echo $table->id; ?>" />
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if (!empty($this->files)): ?>
                <?php foreach ($this->files as $file): ?>
                    <li><?php echo $file->filename; ?></li>
                    <input type="hidden" name="files[]" value="<?php echo $file->id; ?>" />
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>

</form>


