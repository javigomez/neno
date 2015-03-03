/**
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

function getMultiSelectValue(table) {
    var result = [],
        checked = [],
        checks = jQuery('#' + table.attr('id') + ' input[type=checkbox]');

    for (var i=0; i<checks.length; i++) {
        if (jQuery(checks[i]).prop('checked')) {
            var row = jQuery(checks[i]).closest('tr');
            if (jQuery.inArray(row.attr('data-parent'), checked) === -1) {
                result.push(row.attr('data-id'));
            }
            checked.push(row.attr('data-id'));
        }
    }

    return result;
}

jQuery(document).ready(function () {

    jQuery('.multiselect .dropdown-menu, .multiselect .dropdown-menu *').unbind('click');

    jQuery('.btn-toggle').click(function(e) {
        jQuery('#' + jQuery(this).attr('data-toggle')).slideToggle('fast');
        jQuery(this).toggleClass('open');
        jQuery(this).blur();
    });

    jQuery('#table-multiselect tr.collapsed .cell-expand').click(function (e) {
        var row = jQuery(this).parent();
        toggleCollapseRow (row);
    });

    jQuery('#table-multiselect input[type=checkbox]').click(function (e) {
        var checkbox = jQuery(this);
        checkDescendant(checkbox);
        if (!checkbox.prop('checked')) {
            uncheckAncestor(checkbox);
        }
        var checked = JSON.stringify(getMultiSelectValue(checkbox.closest('table')));
        jQuery('#multiselect-value').val(checked);
        jQuery.ajax({
            type: "POST",
            url: "index.php?option=com_neno&task=strings.getStrings",
            data: {jsonData: checked, limitStart: document.adminForm.limitstart.value, limit: document.adminForm.list_limit.value}
        })
            .done(function( ret ) {
                if (ret) {
                    /*fieldset.find('#check-toggle-translate-' + field + '-' + status).unbind('click');
                    fieldset.find('#check-toggle-translate-' + field + '-' + notstatus).click(function (e) {
                        toggleStringStateAjax(jQuery(this));
                    });
                    fieldset.closest('tr').find('.translation-progress-bar').html(ret);*/
                    jQuery('#elements-wrapper').html(ret);
                }
            });
    });

});

