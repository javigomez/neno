/**
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

function toggleCollapseRow (row) {
    var rowType = '';
    if (row.hasClass('row-group')) {
        rowType = 'group';
    } else if (row.hasClass('row-table')) {
        rowType = 'table';
    }
    var nextRow = row.next('tr');
    while (nextRow.length!=0 && !nextRow.hasClass('row-'+rowType)) {
        if (nextRow.attr('data-level') == parseInt(row.attr('data-level')) + 1) {
            nextRow.toggleClass('hide');
            if (nextRow.hasClass('row-table') && row.hasClass('expanded') && nextRow.hasClass('expanded')) {
                toggleCollapseRow(nextRow);
            }
        }
        nextRow = nextRow.next('tr');
    }
    if (row.attr('data-level') != 3) {
        row.toggleClass('collapsed');
        row.toggleClass('expanded');
        row.children('td.cell-expand').first().children('span').first().toggleClass('icon-arrow-right-3');
        row.children('td.cell-expand').first().children('span').first().toggleClass('icon-arrow-down-3');
    }
}

function checkDescendant (check) {
    var state = check.prop('checked'),
        row = jQuery(check).closest('tr'),
        nextRow = row.next('tr');
    while (nextRow.length!=0 && nextRow.attr('data-level') > row.attr('data-level') ) {
        nextRow.find('input[type=checkbox]').prop('checked', state);
        nextRow = nextRow.next('tr');
    }
}

function uncheckAncestor (check) {
    // Use function only when checkbox is not checked
    if (jQuery(check).prop('checked')) {
        return;
    }
    var row = jQuery(check).closest('tr'),
        targetLevel = row.attr('data-level') - 1,
        prevRow = row.prev('tr');
    while (prevRow.length!=0) {
        if (prevRow.attr('data-level') == targetLevel) {
            prevRow.find('input[type=checkbox]').prop('checked', false);
            targetLevel--;
        }
        prevRow = prevRow.prev('tr');
    }
}


