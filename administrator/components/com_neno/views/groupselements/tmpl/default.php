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

$workingLanguage = NenoHelper::getWorkingLanguage();

?>

<style>
    
    .toggler {
        cursor: pointer;
    }
    
    .loading-row {
        background-color: #fff !important;
        background-image: url('../media/neno/images/ajax-loader.gif');  
        background-position: 40px 8px;
        background-repeat: no-repeat;
    }
    
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
	/*.table-groups-elements .cell-check,*/
	.table-groups-elements .cell-expand,
	.table-groups-elements .cell-collapse {
		width: 15px;
	}
	.table-groups-elements .cell-check {
		width: 18px !important;
	}
	.table-groups-elements .cell-check input {
		margin-top: 0;
	}
	.table-groups-elements .cell-expand,
	.table-groups-elements .cell-collapse {
		padding-top: 10px;
		padding-bottom: 6px;
		cursor: pointer;
	}
	.table-groups-elements th,
	.table-groups-elements .row-group > td,
	.table-groups-elements .row-table > td {
		background-color: #ffffff !important;
		color: #2E87CB;
	}
	.table-groups-elements .row-file > td {
		background-color: #ffffff !important;
	}
	.table-groups-elements .type-icon {
		color: #333 !important;
	}
	.table-groups-elements th {
		border-top: none;
	}
	.table-groups-elements .icon-arrow-right-3,
	.table-groups-elements .icon-arrow-down-3 {
		color: #A7A7A7;
	}
	.table-groups-elements .group-label {
		width: 500px;
	}
	.table-groups-elements .table-groups-elements-label {
		width: 220px;
	}
	/*.table-groups-elements .table-groups-elements-label.translation-methods {
		width: 200px;
	}*/
	.table-groups-elements .table-groups-elements-blank {
		width: 15%;
	}
	.table-groups-elements .row-field {
		background-color: white;
	}
	.table-groups-elements .translation-progress-bar .word-count {
		float: left;
        text-align: right;
        width: 40px;
        padding-right: 4px;
	}
	.table-groups-elements .translation-progress-bar .bar {
		width: 120px;
		height: 14px;
		margin-left: 44px;
		margin-top: 2px;
	}
	.table-groups-elements .translation-progress-bar .bar div {
		height: 100%;
		float: left;
	}
	.table-groups-elements .translation-progress-bar .translated {
		background-color: #6BC366;
	}
	.table-groups-elements .translation-progress-bar .queued {
		background-color: #368AB6;
	}
	.table-groups-elements .translation-progress-bar .changed {
		background-color: #FAC819;
	}
	.table-groups-elements .translation-progress-bar .not-translated {
		background-color: #DB3F35;
	}
	.table-groups-elements .translation-progress-bar .bar-disabled div {
		background-color: #CACACA;
		/*width: 100px;*/
	}
	.toggle-translate .btn-group > .btn {
		font-size: 11px;
		line-height: 8px;
	}

</style>

<script type="text/javascript">
    
	jQuery(document).ready(function () {
        
        //Bind
        bindEvents();
        
	});
    
    
    function bindEvents() {

        // Bind load elements
        jQuery('.toggle-elements').off().on('click',toggleElementVisibility);
        
        // Bind toggle fields
        jQuery('.toggle-fields').off().on('click',toggleFieldVisibility);
        
        //Bind checking and unchecking checkboxes
		jQuery('#table-groups-elements input[type=checkbox]').off().on('click', checkUncheckFamilyCheckboxes);        
        
        //Attach the translate state toggler
        jQuery('.check-toggle-translate-radio').off().on('click', changeFieldTranslateState);
        
    }
    
    
    
    /**
     * Toggle Elements (Tables and language files_
     */
    function toggleElementVisibility() 
    {
        var row = jQuery(this).parent('.row-group');
        var id_parts = row.attr('data-id').split('-');
        var id = id_parts[1];
        
        console.log(jQuery(this).hasClass('toggler-collapsed'));
        
        //Get the state of the current toggler to see if we need to expand or collapse
        if (jQuery(this).hasClass('toggler-collapsed')) {
            
            // Expand
            jQuery(this).removeClass('toggler-collapsed').addClass('toggler-expanded').html('<span class="icon-arrow-down-3"></span>');
            
            // Show a loader row while loading
            row.after('<tr id="loader-'+id+'"><td colspan="9" class="loading-row">&nbsp;</td></tr>');

            jQuery.get('index.php?option=com_neno&task=groupselements.getElements&group_id='+id
                , function(html) {
                    jQuery('#loader-'+id).replaceWith(html);
                    
                    //Bind events to new fields
                    bindEvents();
        
                }
            );
            
        } else {
            
            //Collapse
            jQuery(this).removeClass('toggler-expanded').addClass('toggler-collapsed').html('<span class="icon-arrow-right-3"></span>');
            
            //Remove children
            jQuery('[data-parent="'+id+'"]').remove();
            jQuery('[data-grandparent="'+id+'"]').remove();
            
        }
        
    }
    
    function toggleFieldVisibility() {
        
        var row = jQuery(this).parent('.row-table');
        var id_parts = row.attr('data-id').split('-');
        var id = id_parts[1];
        
        //Get the state of the current toggler to see if we need to expand or collapse
        if (jQuery(this).hasClass('toggler-collapsed')) {
            
            // Expand
            jQuery(this).removeClass('toggler-collapsed').addClass('toggler-expanded').html('<span class="icon-arrow-down-3"></span>');
            
            jQuery('[data-parent="'+id+'"]').show();
            
        } else {
            
            //Collapse
            jQuery(this).removeClass('toggler-expanded').addClass('toggler-collapsed').html('<span class="icon-arrow-right-3"></span>');
            
            //hide children
            jQuery('[data-parent="'+id+'"]').hide();
            
        }
        
    }
    
    
    function changeFieldTranslateState() {
        
        var id = jQuery(this).parent('fieldset').attr('data-field');
        var status = jQuery(this).val();
        
        if (status == 1) {
            jQuery(this).parents('.row-field').find('.bar').removeClass('bar-disabled');
            jQuery('[for="check-toggle-translate-'+id+'-1"]').addClass('active btn-success');
            jQuery('[for="check-toggle-translate-'+id+'-0"]').removeClass('active btn-danger');
        } else {
            jQuery(this).parents('.row-field').find('.bar').addClass('bar-disabled');
            jQuery('[for="check-toggle-translate-'+id+'-0"]').addClass('active btn-danger');
            jQuery('[for="check-toggle-translate-'+id+'-1"]').removeClass('active btn-success');
        }
        
        jQuery.get('index.php?option=com_neno&task=groupselements.toggleContentElementField&fieldId='+id+'&translateStatus='+status);        
        
    }
    
    /**
    * Check and uncheck checkboxes 
    *  - Parent click: check/uncheck all children
    *  - Child click: uncheck parent if unchecked
    */
    function checkUncheckFamilyCheckboxes() {
        
        //Set some vars
        var state = jQuery(this).prop('checked');
        var this_data_id = jQuery(this).closest('tr').attr('data-id');
        var this_parts = this_data_id.split('-');
        var this_id = this_parts[1];

        //Check uncheck all children
        jQuery('[data-parent="'+this_id+'"]').find('input[type=checkbox]').prop('checked', state);

        //Uncheck parents
        if (state === false) {
            var parent_id = jQuery('[data-id="'+this_data_id+'"').attr('data-parent');
            if (parent_id) {
                jQuery('[data-id="group-'+parent_id+'"]').find('input[type=checkbox]').prop('checked', false);    
            }
        }
        
        // Make available to Joomla if a checkbox is checked to prevent submitting without a checked item
        Joomla.isChecked(state);        
    }



</script>

<form action="<?php echo JRoute::_('index.php?option=com_neno&view=groupselements'); ?>" method="post" name="adminForm" id="adminForm">

<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
		<table class="table table-striped table-groups-elements" id="table-groups-elements">
			<tr class="row-header" data-level="0" data-id="header">
				<th></th>
				<th class="cell-check"></th>
				<th colspan="3" class="group-label"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_GROUPS'); ?></th>
				<th class="table-groups-elements-label"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_ELEMENTS'); ?></th>
				<th class="table-groups-elements-label"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_COUNT'); ?></th>
				<th class="table-groups-elements-label translation-methods"><?php echo JText::_('COM_NENO_VIEW_GROUPSELEMENTS_METHODS'); ?></th>
				<th class="table-groups-elements-blank"></th>
			</tr>
            
			<?php // @var $group NenoContentElementGroup ?>
			<?php foreach ($this->items as $group): ?>
                
				<tr class="row-group" data-id="group-<?php echo $group->id; ?>">
					<td class="toggler toggler-collapsed toggle-elements"><span class="icon-arrow-right-3"></span></td>
					<td class="cell-check"><input type="checkbox" name="groups[]" value="<?php echo $group->id; ?>" /></td>
					<td colspan="3"><?php echo $group->group_name; ?></td>
					<td<?php echo ($group->element_count) ? ' class="load-elements"' : ''; ?>><?php echo $group->element_count; ?></td>
					<td class="translation-progress-bar"><?php echo NenoHelper::printWordCountProgressBar($group->word_count, 1); ?></td>
					<td></td>
					<td></td>
				</tr>
                
            <?php endforeach; ?>
                
            
                
		</table>
        
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
        
	</div>

</form>


