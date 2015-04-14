/**
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

jQuery(document).ready(function () {
    bindEvents();
});

function bindEvents() {
    jQuery('.multiselect *').unbind('click');

    jQuery('.btn-toggle').click(function (e) {
        jQuery('#' + jQuery(this).attr('data-toggle')).slideToggle('fast');
        jQuery(this).toggleClass('open');
        jQuery(this).blur();
    });

    jQuery('#table-multiselect .cell-expand').click(toggleElementVisibility);

    jQuery('#table-multiselect input[type=checkbox]').click(loadStrings);
}

/**
 * Toggle Elements (Tables and language files)
 */
function toggleElementVisibility() {
    var row = jQuery(this).parent('.element-row');
    var data_id = row.data('id');
    var id = data_id.split('-').pop();

    //Get the state of the current toggler to see if we need to expand or collapse
    if (row.hasClass('collapsed')) {

        // Expand
        row.removeClass('collapsed').addClass('expanded');
        jQuery(this).html('<span class="toggle-arrow icon-arrow-down-3"></span>');

        if (row.data('level') == 1) {
            if (!row.data('loaded')) {
                row.addClass('loading');
                jQuery.get('index.php?option=com_neno&task=strings.getElements&group_id=' + id
                    , function (html) {
                        row.after(html);
                        row.data('loaded', true);
                        bindEvents();
                        row.removeClass('loading');
                    }
                );
            }
            else {
                jQuery('[data-parent="' + data_id + '"]').removeClass('hide')
            }
        }
        else {
            jQuery('[data-parent="' + data_id + '"]').removeClass('hide')
        }
    } else {

        //Collapse
        row.removeClass('expanded').addClass('collapsed');
        jQuery(this).html('<span class="toggle-arrow icon-arrow-right-3"></span>');
        jQuery('[data-parent="' + data_id + '"]').removeClass('expanded').addClass('collapsed').addClass('hide');
    }
}

function loadStrings() {
    var checkbox = jQuery(this);
    checkUncheckFamilyCheckboxes(checkbox);
    var checked = JSON.stringify(getMultiSelectValue(checkbox.closest('table')));
    jQuery('#multiselect-value').val(checked);
    jQuery.ajax({
        type: "POST",
        url: "index.php?option=com_neno&task=strings.getStrings",
        data: {
            jsonData: checked,
            limitStart: 0, //document.adminForm.limitstart.value,
            limit: 20, //document.adminForm.list_limit.value,
            status: document.adminForm.filter_translation_status.value,
            method: document.adminForm.filter_translator_type.value,
            outputLayout: document.adminForm.outputLayout.value
        }
    })
        .done(function (ret) {
            if (ret) {
                /*fieldset.find('#check-toggle-translate-' + field + '-' + status).unbind('click');
                 fieldset.find('#check-toggle-translate-' + field + '-' + notstatus).click(function (e) {
                 toggleStringStateAjax(jQuery(this));
                 });
                 fieldset.closest('tr').find('.translation-progress-bar').html(ret);*/
                if (document.adminForm.outputLayout.value == 'editorStrings') {
                    setFilterTags(document.adminForm);
                }
                jQuery('#elements-wrapper').html(ret);
            }
        });
}

function getMultiSelectValue(table) {
    var result = [],
        checked = [],
        checks = jQuery('#' + table.attr('id') + ' input[type=checkbox]');

    for (var i = 0; i < checks.length; i++) {
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

/**
 * Check and uncheck checkboxes
 *  - Parent click: check/uncheck all children
 *  - Child click: uncheck parent if checked
 */
function checkUncheckFamilyCheckboxes(checkbox) {

    //Set some vars
    var state = checkbox.prop('checked');
    var this_data_id = checkbox.closest('tr').data('id');
    var this_parts = this_data_id.split('-');
    var this_id = this_parts[1];

    //Check uncheck all children
    jQuery('[data-parent="' + this_data_id + '"]').find('input[type=checkbox]').prop('checked', state);

    //Uncheck parents
    if (state === false) {
        var parent_id = jQuery('[data-id="' + this_data_id + '"').attr('data-parent');
        if (parent_id) {
            jQuery('[data-id="group-' + parent_id + '"]').find('input[type=checkbox]').prop('checked', false);
        }
    }
}

function setFilterTags(form) {
    jQuery('#filter-tags-wrapper').html('');
    var search = jQuery(form.filter_search);
    var status = jQuery(form.filter_translation_status);
    var method = jQuery(form.filter_translator_type);

    if (search.val() !== '') {
        printFilterTag('search', '"' + search.val() + '"');
    }
    if (status.val() !== '') {
        printFilterTag('status', status.find('option:selected').html());
    }
    if (method.val() !== '') {
        printFilterTag('method', method.find('option:selected').html());
    }
    //var checked = getMultiSelectValue(form.find('#multiselect table'));
}

function printFilterTag(type, label) {
    var tag = jQuery('<div class="filter-tag btn btn-small disabled" data-type="' + type + '"><span class="removeTag icon-remove"></span>' + label + '</div>');
    jQuery('#filter-tags-wrapper').append(tag);
}