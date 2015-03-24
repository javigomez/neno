<?php

/**
 * @package     Neno
 * @subpackage  Helpers
 *
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('JPATH_NENO') or die;

if ($displayData === null): ?>

    <tr><td>---</td></tr>
    
<?php else: ?>
    
    <?php if (!empty($displayData['tables'])): ?>
        <?php foreach ($displayData['tables'] as $table): ?>

            <tr class="row-table" data-id="table-<?php echo $table->id; ?>" data-parent="<?php echo $table->group->id; ?>">
                <td></td>
                <td class="toggler toggler-collapsed toggle-fields"><span class="icon-arrow-right-3"></span></td>
                <td class="cell-check"><input type="checkbox"/></td>
                <td colspan="2"><?php echo $table->table_name; ?></td>
                <td class="type-icon"><span class="icon-grid-view-2"></span> <?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_TABLE'); ?></td>
                <td class="translation-progress-bar">
                    <?php echo NenoHelper::printWordCountProgressBar($table->word_count, 1); ?>
                </td>
                <td></td>
                <td></td>
            </tr>

            <?php /* @var $field NenoContentElementField */ ?>
            <?php if (!empty($table->fields)): ?>
                <?php foreach ($table->fields as $field): ?>
                    <tr class="row-field" data-parent="<?php echo $table->id; ?>" data-grandparent="<?php echo $table->group->id; ?>" style="display:none;">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo $field->field_name ?></td>
                        <td><?php echo strtoupper($field->field_type) ?></td>
                        <td class="translation-progress-bar">
                            <?php echo NenoHelper::printWordCountProgressBar($field->word_count, $field->translate); ?>
                        </td>
                        <td class="toggle-translate">
                            <fieldset id="check-toggle-translate-<?php echo $field->id;?>" class="radio btn-group btn-group-yesno" data-field="<?php echo $field->id; ?>">
                                <input class="check-toggle-translate-radio" type="radio" id="check-toggle-translate-<?php echo $field->id;?>-1" name="jform[check-toggle-translate]" value="1" <?php echo ($field->translate) ? 'checked="checked"' : ''; ?>>
                                <label for="check-toggle-translate-<?php echo $field->id;?>-1" class="btn <?php echo ($field->translate) ? 'active btn-success' : ''; ?>">Translate</label>
                                <input class="check-toggle-translate-radio" type="radio" id="check-toggle-translate-<?php echo $field->id;?>-0" name="jform[check-toggle-translate]" value="0" <?php echo (!$field->translate) ? 'checked="checked"' : ''; ?>>
                                <label for="check-toggle-translate-<?php echo $field->id;?>-0" class="btn <?php echo (!$field->translate) ? 'active btn-danger' : ''; ?>">Don't translate</label>
                            </fieldset>
                        </td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>   
            <?php endif; ?>

        <?php endforeach; ?>
    <?php endif; ?>
                    
    <?php
    //echo '<pre class="debug"><small>' . __file__ . ':' . __line__ . "</small>\n\$displayData = ". print_r($displayData, true)."\n</pre>";
    ?>
            
    <?php if(!empty($displayData['files'])): ?>
        <?php foreach ($displayData['files'] as $file): ?>
            
            <tr class="row-table" data-id="row-<?php echo $file->filename; ?>" data-parent="<?php echo $displayData['group']->getId(); ?>">
                <td></td>
                <td class="toggler toggler-collapsed toggle-fields"><span class="icon-arrow-right-3"></span></td>
                <td class="cell-check"><input type="checkbox"/></td>
                <td colspan="2" style="white-space: nowrap;"><?php echo $file->filename; ?></td>
                <td class="type-icon"><span class="icon-file-2"></span> <?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_FILE'); ?></td>
                <td class="translation-progress-bar">
                    <?php echo NenoHelper::printWordCountProgressBar($file->wordCount, 1); ?>
                </td>
                <td></td>
                <td></td>
            </tr>

        <?php endforeach; ?>
    <?php endif; ?>
            
<?php endif; ?>

