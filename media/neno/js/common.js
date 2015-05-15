/**
 * @author      Jensen Technologies S.L. <info@notwebdesign.com>
 * @copyright   Copyright (C) 2014 Jensen Technologies S.L. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

function loadTranslation(string) {
    var idString;
    if (jQuery.type(string) == 'object') {
        jQuery('.string-activated').removeClass('string-activated');
        idString = string.data('id');
    } else {
        idString = string;
    }
    jQuery('div[data-id=' + idString + ']').addClass('string-activated');

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

function loadMissingTranslationMethodSelectors() {
    if (typeof jQuery(this).prop("tagName") == 'undefined') {
        i = 1;
        jQuery('.method-selectors').each(function () {
            //Count how many we currently are showing
            var n = jQuery(this).find('.translation-method-selector-container').length;

            //If we are loading because of changing a selector, remove all children
            var selector_id = jQuery(this).find('.translation-method-selector').attr('data-selector-id');
            if (typeof selector_id !== 'undefined') {
                //Loop through each selector and remove the ones that are after this one
                for (var i = 0; i < n; i++) {
                    if (i > selector_id) {
                        jQuery(this).find("[data-selector-container-id='" + i + "']").remove();
                    }
                }
            }
            //Create a string to pass the current selections
            var selected_methods_string = '';
            jQuery(this).find('.translation-method-selector').each(function () {
                selected_methods_string += '&selected_methods[]=' + jQuery(this).find(':selected').val();
            });
            var lang = jQuery(this).closest('.method-selectors').data('language');
            var otherParams = '&language=' + lang;

            executeAjaxForTranslationMethodSelectors(n, selected_methods_string, jQuery(this).find('.translation-method-selector'), otherParams);
        });
    }
    else {
        //If we are loading because of changing a selector, remove all children
        var selector_id = jQuery(this).data('selector-id');
        var n = jQuery(this).closest('.method-selectors').find('.translation-method-selector-container').length;
        if (typeof selector_id !== 'undefined') {
            //Loop through each selector and remove the ones that are after this one
            for (var i = 0; i < n; i++) {
                if (i > selector_id) {
                    console.log(jQuery(this).closest('.method-selectors').find("[data-selector-container-id='" + i + "']"));
                    jQuery(this).closest('.method-selectors').find("[data-selector-container-id='" + i + "']").remove();
                }
            }
        }
        var selected_methods_string = '&selected_methods[]=' + jQuery(this).find(':selected').val();
        var lang = jQuery(this).closest('.method-selectors').data('language');
        var otherParams = '&language=' + lang;
        saveTranslationMethod(jQuery(this).find(':selected').val(), lang, selector_id + 1);
        executeAjaxForTranslationMethodSelectors(n, selected_methods_string, jQuery(this), otherParams);
    }
}


function executeAjaxForTranslationMethodSelectors(n, selected_methods_string, element, otherParams) {
    if (typeof otherParams == 'undefined') {
        otherParams = '';
    }
    jQuery.ajax({
        beforeSend: onBeforeAjax,
        url: 'index.php?option=com_neno&task=getTranslationMethodSelector&placement=language&n=' + n + selected_methods_string + otherParams,
        success: function (html) {
            if (html !== '') {
                jQuery(element).closest('.method-selectors').append(html);
            }

            jQuery('.translation-method-selector').off('change').on('change', loadMissingTranslationMethodSelectors);
            jQuery('select').chosen();
            var container = element.parents('.language-configuration');
            var select1 = element.parents('.method-selectors').find("[data-selector-container-id='1']");
            if (select1.length) {
                if (!container.hasClass('expanded')) {
                    container.height(
                        container.height() + 26
                    );
                    container.addClass('expanded');
                }
            } else if (container.hasClass('expanded')) {
                container.height(
                    container.height() - 26
                );
            }
        }
    });
}

function saveTranslationMethod(translationMethod, language, ordering) {
    jQuery.ajax({
        beforeSend: onBeforeAjax,
        url: 'index.php?option=com_neno&task=saveTranslationMethod',
        type: 'POST',
        data: {
            translationMethod: translationMethod,
            language: language,
            ordering: ordering
        }
    });
}
function copyOriginal() {
    var original = jQuery('.original-text').html().trim();
    original = original.replace(/<span class="highlighted-tag">|<\/span>/g, '');
    original = original.replace(/&lt;/g, '<');
    original = original.replace(/&gt;/g, '>');
    jQuery('.translated-content').val(original);
    jQuery('.translated-by').hide();
}