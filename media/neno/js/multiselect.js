/**
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

jQuery(document).ready(function () {
    bindEvents();
    setFilterTags(document.adminForm);

    // Load hierarchy if some groups has been marked
    jQuery('.expanded').each(function () {
        loadHierarchy(jQuery(this));
    });

    loadStrings(true);
});

function loadHierarchy(row) {
    var data_id = row.data('id');
    var id = data_id.split('-').pop();
    if (row.data('level') == 1) {
        if (!row.data('loaded')) {
            row.addClass('loading');
            jQuery.ajax({
                    beforeSend: onBeforeAjax,
                    url: 'index.php?option=com_neno&task=' + getParameterByName('view') + '.getElements&group_id=' + id,
                    success: function (html) {
                        row.after(html);
                        row.data('loaded', true);
                        bindEvents();
                        row.removeClass('loading');
                        checkUncheckFamilyCheckboxes(row.find('input[type=checkbox]').first());
                        setFilterTags(document.adminForm);
                    }
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

function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
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
        jQuery("input[name='limitstart']").val(0);
        jQuery('#elements-wrapper').html('');
        checkUncheckFamilyCheckboxes(jQuery(this));
        loadStrings(true);
    });
    jQuery('#status-multiselect input[type=checkbox], #method-multiselect input[type=checkbox]').unbind('click').click(function () {
        jQuery("input[name='limitstart']").val(0);
        jQuery('#elements-wrapper').html('');
        loadStrings(true);
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
        jQuery('[data-parent="' + data_id + '"]').removeClass('expanded').addClass('collapsed').addClass('hide').each(function () {
            // Collapse also grandchildren
            jQuery(this).find('.cell-expand').html('<span class="toggle-arrow icon-arrow-right-3"></span>');
            var descendant_data_id = jQuery(this).data('id');
            jQuery('[data-parent="' + descendant_data_id + '"]').addClass('hide');
        });
    }
}

function loadStrings(reset) {
    var checkedGroupsElements = getMultiSelectValue(jQuery('#table-multiselect'));
    var checkedStatus = getMultiSelectValue(jQuery('#status-multiselect'));
    var checkedMethod = getMultiSelectValue(jQuery('#method-multiselect'));
    var search = jQuery('#filter_search').val();
    if (reset == true) {
        jQuery("input[name='limitstart']").val(0)
    }
    var limitStart = jQuery("input[name='limitstart']").val();
    var limit = document.adminForm.list_limit.value;

    var urlElements = [];

    if (checkedGroupsElements.length != 0) {
        for (var i = 0; i < checkedGroupsElements.length; i++) {
            var data = checkedGroupsElements[i].split('-');
            urlElements.push(data[0] + '[]=' + data[1]);
        }
    }
    else {
        checkedGroupsElements.push('groups-none');
        urlElements.push('group[]=none');
    }

    if (checkedStatus.length != 0) {
        for (var i = 0; i < checkedStatus.length; i++) {
            var data = checkedStatus[i].split('-');
            urlElements.push('status[]=' + data[1]);
        }
    } else {
        checkedStatus.push('status-none');
        urlElements.push('status[]=none');
    }

    if (checkedMethod.length != 0) {
        for (var i = 0; i < checkedMethod.length; i++) {
            var data = checkedMethod[i].split('-');
            urlElements.push('type[]=' + data[1]);
        }
    } else {
        checkedMethod.push('method-none');
        urlElements.push('type[]=none');
    }

    var url = document.location.origin + document.location.pathname + '?option=com_neno&view=editor';

    if (urlElements.length != 0) {
        history.pushState(null, null, url + '&' + urlElements.join('&'));
    }
    else {
        history.pushState(null, null, url);
    }

    jQuery('#multiselect-value').val(checkedGroupsElements);
    jQuery.urlParam = function (name) {
        var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.href);
        return results[1] || 0;
    };
    jQuery.ajax({
        beforeSend: onBeforeAjax,
        type: "POST",
        url: "index.php?option=com_neno&task=" + jQuery.urlParam('view') + ".getStrings",
        data: {
            jsonGroupsElements: JSON.stringify(checkedGroupsElements),
            filter_search: search,
            limitStart: limitStart,
            limit: limit,
            jsonStatus: JSON.stringify(checkedStatus),
            jsonMethod: JSON.stringify(checkedMethod),
            outputLayout: document.adminForm.outputLayout.value
        }
    })
        .done(function (ret) {
            if (ret) {
                var targetContainer = jQuery('#elements-wrapper');
                if (document.adminForm.outputLayout.value == 'editorStrings') {
                    setFilterTags(document.adminForm);
                }
                if (reset == true) {
                    targetContainer.html(ret);
                    if (targetContainer.find('.string').length) {
                        loadTranslation(targetContainer.find('.string').first());
                    }
                } else {
                    targetContainer.append(ret);
                }
                // Print messages if no results at all
                if (targetContainer.find('div.string').length == 0) {
                    targetContainer.find(".no-results").show();
                }
                // Set results wrapper height
                setResultsWrapperHeight();
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
function checkUncheckFamilyCheckboxes(checkbox, recursive) {

    //Set some vars
    var state = checkbox.prop('checked');
    var this_data_id = checkbox.closest('tr').data('id');
    var children = jQuery('[data-parent="' + this_data_id + '"]');
    if (recursive === undefined) {
        recursive = true;
    }

    if (recursive) {
        //Check uncheck all children
        if (children.find('input[type=checkbox]').length == children.find('input[type=checkbox]:checked').length || state == true) {
            children.find('input[type=checkbox]').prop('checked', state);
        }

        children.find('input[type=checkbox]').each(function () {
            checkUncheckFamilyCheckboxes(jQuery(this), true);
        });
    }

    //Check uncheck parent
    var parent_data_id = jQuery('[data-id="' + this_data_id + '"').attr('data-parent');
    var parent = jQuery('[data-id="' + parent_data_id + '"]');
    if (parent_data_id) {
        if (state === true) {
            // Search all siblings to see if any of them is unchecked
            var uncheckedSiblings = jQuery('[data-parent="' + parent_data_id + '"]').find('input[type=checkbox]:not(:checked)');
            if (uncheckedSiblings.length == 0) {
                parent.find('input[type=checkbox]').prop('checked', true);
                if (recursive) {
                    parent.find('input[type=checkbox]').each(function () {
                        checkUncheckFamilyCheckboxes(jQuery(this), false);
                    });
                }
            }
        } else {
            parent.find('input[type=checkbox]').prop('checked', false);
            if (recursive) {
                parent.find('input[type=checkbox]').each(function () {
                    checkUncheckFamilyCheckboxes(jQuery(this), false);
                });
            }
        }
    }
}

function setFilterTags(form) {
    jQuery('#filter-tags-wrapper').html('');
    var search = jQuery(form.filter_search);
    var status = getMultiSelectValue(jQuery('#status-multiselect'));
    var method = getMultiSelectValue(jQuery('#method-multiselect'));
    var groupsElements = getMultiSelectValue(jQuery('#table-multiselect'));

    if (search.val() !== '') {
        printFilterTag('search', '"' + search.val() + '"');
    }

    for (s in status) {
        if (String(status[s]).indexOf('status') !== 0) {
            continue;
        }
        printFilterTag(status[s], jQuery('[data-id="' + status[s] + '"]').attr('data-label'));
    }
    for (m in method) {
        if (String(method[m]).indexOf('method') !== 0) {
            continue;
        }
        printFilterTag(method[m], jQuery('[data-id="' + method[m] + '"]').attr('data-label'));
    }
    for (ge in groupsElements) {
        if (String(groupsElements[ge]).indexOf('group') !== 0 && String(groupsElements[ge]).indexOf('table') !== 0 && String(groupsElements[ge]).indexOf('field') !== 0 && String(groupsElements[ge]).indexOf('file') !== 0) {
            continue;
        }
        var row = jQuery('[data-id="' + groupsElements[ge] + '"]');
        var label = '';
        if (row.attr('data-parent') && row.attr('data-parent') != 'header') {
            var parent = jQuery('[data-id="' + row.data('parent') + '"]');
            if (parent.attr('data-parent') && parent.attr('data-parent') != 'header') {
                label += jQuery('[data-id="' + parent.data('parent') + '"]').attr('data-label') + ' > ';
            }
            label += parent.attr('data-label') + ' > ';
        }
        label += row.attr('data-label');

        printFilterTag(groupsElements[ge], label);
    }
}

function printFilterTag(type, label) {
    label = label.replace('<', '&lt;' );
    label = label.replace('>', '&gt;' );
    var tag = jQuery('<div class="filter-tag btn btn-small disabled" data-type="' + type + '"><span class="removeTag icon-remove"></span>' + label + '</div>');
    jQuery('#filter-tags-wrapper').append(tag);
    tag.find('.removeTag').click(function () {
        jQuery('[data-id="' + type + '"]').find('input[type=checkbox]').prop('checked', false);
        // Check if the tag is from a Group/Element/Key
        if (type.indexOf('group') != -1 || type.indexOf('table') != -1 || type.indexOf('field') != -1) {
            checkUncheckFamilyCheckboxes(jQuery('[data-id="' + type + '"]').find('input[type=checkbox]'), true);
        }
        if (type == 'search') {
            jQuery('#filter_search').val('');
        }
        loadStrings(true);
        jQuery(this).parent().remove();
    });
}