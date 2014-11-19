<?php
/**
 * @version     1.0.0
 * @package     com_lingo
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Soren Beck Jensen <soren@notwebdesign.com> - http://www.notwebdesign.com
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_lingo/assets/css/lingo.css');

$user   = JFactory::getUser();
$userId = $user->get('id');
?>

<form action="<?php echo JRoute::_('index.php?option=com_lingo'); ?>" method="post" name="adminForm" id="adminForm">


    <div class="clearfix"></div>
    <table class="table table-striped" id="translationList">
        <thead>
        <tr>
            <th width="1%" class="hidden-phone">
                <input type="checkbox" name="checkall-toggle" value=""
                       title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
            </th>

            <th class='left'>
                <?php echo JText::_('COM_LINGO_VIEW_LANGFILESIMPORTTARGETCHANGES_TH_LANG'); ?>
            </th>
            <th class='left'>
                <?php echo JText::_('COM_LINGO_VIEW_LANGFILESIMPORTTARGETCHANGES_TH_FILE'); ?>
            </th>
            <th class='left'>
                <?php echo JText::_('COM_LINGO_VIEW_LANGFILESIMPORTTARGETCHANGES_TH_DATABASE'); ?>
            </th>

            <?php if (isset($this->items[0]->id)): ?>
                <th width="1%" class="nowrap center hidden-phone">
                    <?php echo JText::_('JGRID_HEADING_ID'); ?>
                </th>
            <?php endif; ?>
        </tr>
        </thead>
        <tfoot>
        <?php
        if (isset($this->items[0]))
        {
            $colspan = count(get_object_vars($this->items[0]));
        }
        else
        {
            $colspan = 10;
        }
        ?>
        </tfoot>
        <tbody>
        <?php foreach ($this->items as $i => $item) : ?>

            <tr class="row<?php echo $i % 2; ?>">

                <td class="hidden-phone">
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>

                <td>
                    <?php echo $item->lang; ?>
                </td>
                <td>
                    <?php echo $item->text_in_file; ?>
                </td>
                <td>
                    <?php echo $item->text_in_db; ?>
                </td>


                <?php if (isset($this->items[0]->id)): ?>
                    <td class="center hidden-phone">
                        <?php echo (int) $item->id; ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <?php echo JHtml::_('form.token'); ?>
</form>        

		
