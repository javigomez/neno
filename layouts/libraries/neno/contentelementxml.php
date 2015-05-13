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
?>

<?php if (!empty($displayData)): ?>

<?php $primary_keys = $displayData['table']->primary_key; ?>

<?xml version="1.0" encoding="UTF-8" ?>
<neno type="contentelement">
    <name><?php echo $displayData['group_name']; ?> - <?php echo $displayData['table_name']; ?></name>
    <author>Neno - http://www.neno-translate.com</author>
    <version>1.0.0</version>
    <description>Definition of the table <?php echo $displayData['table_name']; ?> for the <?php echo $displayData['group_name']; ?> component</description>
    <reference type="content">
        <table name="<?php echo $displayData['table_name']; ?>">
<?php foreach ($displayData['table']->fields as $field): ?>
            <field type="<?php echo ( in_array($field->field_name, $primary_keys) ) ? 'referenceid' : 'text'; ?>" name="<?php echo $field->field_name; ?>" translate="<?php echo $field->translate; ?>"><?php echo $field->field_name; ?></field>
<?php endforeach; ?>
        </table>
    </reference>
</neno>

<?php endif; ?>

