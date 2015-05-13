/**
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

function loadTranslation(string) {
    var idString;
    if (jQuery.type(string) == 'object') {
        jQuery('.string-activated').removeClass('string-activated');

        idString = string.data('id')
    } else {
        idString = string;
        jQuery('div[data-id=' + string + ']').addClass('string-activated');
    }

    // Get information
    jQuery.ajax({
            beforeSend: onBeforeAjax,
            url: 'index.php?option=com_neno&task=editor.getTranslation&id=' + idString,
            success: function (data) {
                jQuery('#editor-wrapper').html(data);
            }
        }
    );
}

function loadNextTranslation() {
    var nextString = jQuery('.string-activated').next('div').next('div');
    if (nextString.length) {
        loadTranslation(nextString);
    }
}

function saveTranslationAndNext() {
    var text = jQuery('.translated-content').val();
    var translationId = jQuery('#save-next-button').data('id');
    var statuses = ['', 'translated', 'queued', 'changed', 'not-translated'];
    jQuery.ajax({
            beforeSend: onBeforeAjax,
            type: 'POST',
            url: 'index.php?option=com_neno&task=editor.saveAsCompleted',
            dataType: "json",
            data: {
                id: translationId,
                text: text
            },
            success: function (data) {
                var row = jQuery('#elements-wrapper .string[data-id=' + data.translation.id + ']');
                if (row) {
                    var string = data.translation.string;
                    if (string.length > 40) {
                        string = string.substr(0, 35) + '...';
                    }
                    row.find('.string-text').html(string);
                    row.find('.status').removeClass().addClass('status');
                    row.find('.status').addClass(statuses[data.translation.state]);
                }

                if (typeof data.message != 'undefined') {
                    jQuery('#consolidate-modal .modal-body p').html(data.message);
                    jQuery('#consolidate-button').off('click').data('translation', translationId).on('click', function () {
                        var translationId = jQuery(this).data('translation');
                        jQuery.ajax({
                                beforeSend: onBeforeAjax,
                                type: 'POST',
                                data: {
                                    id: translationId
                                },
                                url: 'index.php?option=com_neno&task=editor.consolidateTranslation',
                                success: function () {
                                    jQuery('#consolidate-modal').modal('hide');
                                    loadStrings(true);
                                }
                            }
                        );
                    });
                    jQuery('#consolidate-modal').modal('show');
                }

                loadNextTranslation();
            }
        }
    );
}

function saveDraft() {
    var text = jQuery('.translated-content').val();
    var translationId = jQuery('#draft-button').data('id');
    var statuses = ['', 'translated', 'queued', 'changed', 'not-translated'];
    jQuery.ajax({
            beforeSend: onBeforeAjax,
            type: 'POST',
            url: 'index.php?option=com_neno&task=editor.saveAsDraft',
            dataType: "json",
            data: {
                id: translationId,
                text: text
            },
            success: function (data) {
                var row = jQuery('#elements-wrapper .string[data-id=' + data.id + ']');
                if (row) {
                    var string = data.string;
                    if (string.length > 40) {
                        string = string.substr(0, 35) + '...';
                    }
                    row.find('.string-text').html(string);
                    row.find('.status').removeClass().addClass('status');
                    row.find('.status').addClass(statuses[data.state]);
                }
            }
        }
    );
}

function translate() {
    var text = jQuery('.original-text').html().trim();
    jQuery.ajax({
            beforeSend: onBeforeAjax,
            type: 'POST',
            url: 'index.php?option=com_neno&task=editor.translate',
            data: {
                text: text
            },
            success: function (data) {
                jQuery('.translated-content').val(data);
                jQuery('.translated-by').show();
            }
        }
    );
}

// Check if the user has lost the session
function onBeforeAjax() {
    jQuery.get('index.php?option=com_neno&task=checkSession', function (response) {
        if (response != 'ok') {
            document.location.reload();
        }
    });
}

function fixIssue() {
    var button = jQuery(this);
    button.closest('.alert').remove();
    jQuery.ajax({
        beforeSend: onBeforeAjax,
        url: 'index.php?option=com_neno&task=fixLanguageIssue',
        data: {
            language: button.data('language'),
            issue: button.data('issue')
        },
        type: 'POST'
    });
}
