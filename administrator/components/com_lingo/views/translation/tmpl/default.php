<?php
/**
 * @package     Lingo
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

<div class="row-flud">
    
    <div class="span3">Left</div>
    <div class="span4">
        <form>
            <textarea class="span12" rows="8" cols="200" disabled="disabled"><?php echo htmlentities($this->item->source_string); ?></textarea>
        </form>
    </div>
    <div class="span1">copy</div>

    <div class="span4">

        <form action="<?php echo JRoute::_('index.php?option=com_lingo&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm">

            <textarea class="span12" rows="8" cols="200"><?php echo htmlentities($this->item->string); ?></textarea>

            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>

        </form>

    </div>
    
</div>
