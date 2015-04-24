/**
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

jQuery(document).ready(function () {
    bindEvents();

    // Load hierarchy if some groups has been marked
    jQuery('.expanded').each(function () {
        loadHierarchy(jQuery(this));
    });
});

function loadHierarchy(row) {
    var data_id = row.data('id');
    var id = data_id.split('-').pop();
    if (row.data('level') == 1) {
        if (!row.data('loaded')) {
            row.addClass('loading');
            jQuery.get('index.php?option=com_neno&task=strings.getElements&group_id=' + id
                , function (html) {
                    row.after(html);
                    row.data('loaded', true);
                    bindEvents();
                    row.removeClass('loading');
                    checkUncheckFamilyCheckboxes(row.find('input[type=checkbox]').first());
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
}

function bindEvents() {
    jQuery('.multiselect *').unbind('click');

    jQuery('.btn-toggle').click(function (e) {
        jQuery('#' + jQuery(this).attr('data-toggle')).slideToggle('fast');
        jQuery(this).toggleClass('open');
        jQuery(this).blur();
    });

    jQuery('#table-multiselect .cell-expand').click(toggleElementVisibility);

    jQuery('#table-multiselect input[type=checkbox]').unbind('click').click(function () {
        document.adminForm.limitstart.value = 0;
        jQuery('#elements-wrapper').html('');
        checkUncheckFamilyCheckboxes(jQuery(this));
        loadStrings();
    });
    jQuery('#status-multiselect input[type=checkbox], #method-multiselect input[type=checkbox]').unbind('click').click(function () {
        document.adminForm.limitstart.value = 0;
        jQuery('#elements-wrapper').html('');
        loadStrings();
    });
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

        loadHierarchy(row);
    } else {
        //Collapse
        row.removeClass('expanded').addClass('collapsed');
        jQuery(this).html('<span class="toggle-arrow icon-arrow-right-3"></span>');
        jQuery('[data-parent="' + data_id + '"]').removeClass('expanded').addClass('collapsed').addClass('hide');
    }
}

function loadStrings() {
    var checkedGroupsElements = getMultiSelectValue(jQuery('#table-multiselect'));
    var checkedStatus = getMultiSelectValue(jQuery('#status-multiselect'));
    var checkedMethod = getMultiSelectValue(jQuery('#method-multiselect'));
    var limitStart = document.adminForm.limitstart.value;
    var limit = document.adminForm.list_limit.value;

    var urlElements = [];

    if (checkedGroupsElements.length != 0) {
        for (var i = 0; i < checkedGroupsElements.length; i++) {
            var data = checkedGroupsElements[i].split('-');
            urlElements.push(data[0] + '[]=' + data[1]);
        }
    }

    if (checkedStatus.length != 0) {
        for (var i = 0; i < checkedStatus.length; i++) {
            var data = checkedStatus[i].split('-');
            urlElements.push('translation_status[]=' + data[1]);
        }
    } else {
        checkedStatus.push('status-none');
        urlElements.push('translation_status[]=none');
    }

    if (checkedMethod.length != 0) {
        for (var i = 0; i < checkedMethod.length; i++) {
            var data = checkedMethod[i].split('-');
            urlElements.push('translator_type[]=' + data[1]);
        }
    } else {
        checkedMethod.push('method-none');
        urlElements.push('translator_type[]=none');
    }

    var url = document.location.origin + document.location.pathname + '?option=com_neno&view=editor';

    if (urlElements.length != 0) {
        history.pushState(null, null, url + '&' + urlElements.join('&'));
    }
    else {
        history.pushState(null, null, url);
    }


    jQuery('#multiselect-value').val(checkedGroupsElements);
    jQuery.ajax({
        type: "POST",
        url: "index.php?option=com_neno&task=strings.getStrings",
        data: {
            jsonGroupsElements: JSON.stringify(checkedGroupsElements),
            limitStart: limitStart,
            limit: limit,
            jsonStatus: JSON.stringify(checkedStatus),
            jsonMethod: JSON.stringify(checkedMethod),
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
                jQuery('#elements-wrapper').append(ret);
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

function getSimpleMultiSelectValue(table) {
    var result = [],
        checks = jQuery('#' + table.attr('id') + ' input[type=checkbox]');
    for (var i = 0; i < checks.length; i++) {
        if (jQuery(checks[i]).prop('checked')) {
            var row = jQuery(checks[i]).closest('tr');
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
    var status = getMultiSelectValue(jQuery('#status-multiselect'));
    var method = getMultiSelectValue(jQuery('#method-multiselect'));

    if (search.val() !== '') {
        printFilterTag('search', '"' + search.val() + '"');
    }

    for (s in status) {
        printFilterTag(status[s], jQuery('[data-id="' + status[s] + '"]').attr('data-label'));
    }
    for (m in method) {
        printFilterTag(method[m], jQuery('[data-id="' + method[m] + '"]').attr('data-label'));
    }
    //var checked = getMultiSelectValue(form.find('#multiselect table'));
}

function printFilterTag(type, label) {
    var tag = jQuery('<div class="filter-tag btn btn-small disabled" data-type="' + type + '"><span class="removeTag icon-remove"></span>' + label + '</div>');
    jQuery('#filter-tags-wrapper').append(tag);
    tag.find('.removeTag').click(function () {
        jQuery('[data-id="' + type + '"]').find('input[type=checkbox]').prop('checked', false);
        document.adminForm.limitstart.value = 0;
        jQuery('#elements-wrapper').html('');
        loadStrings();
        jQuery(this).parent().remove();
    });
}