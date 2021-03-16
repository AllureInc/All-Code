/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function($) {
    'use strict';
    return function (options) {
        let blockContent = options.html,
            actionUrl = options.url,
            contentPopupBtn = options.trigger_btn,
            isModelOpen = options.is_model_open;

        let configDataCollect = $('<div>').html(blockContent).modal({
            modalClass: 'config-data-collect',
            responsive: true,
            innerScroll: true,
            title: $.mage.__('We need to collect configuration data from PlentyMarkets.'),
            type: 'popup',
            autoOpen: isModelOpen,
            buttons: [{
                text: 'Collect Data Now',
                class: 'action-primary',
                click: function () {
                    (function ($) {
                        $.ajax({
                            url: actionUrl,
                            method: 'GET',
                            // data: {profile_id:2},
                            showLoader: true
                        }).done(function (data) {
                            createMessage(null, null);
                            if (data.error) {
                                createMessage(data.error, 'error');
                            } else {
                                createMessage(data.success, 'success');
                                configDataCollect.modal('closeModal');
                                location.reload();
                            }
                        }).fail(function (jqXHR, textStatus) {
                            if (window.console) {
                                console.log(textStatus);
                            }
                            createMessage(textStatus, 'error');
                            // location.reload();
                        });
                    })(jQuery);
                }
            }]
        });

        $('#'+contentPopupBtn).on('click',  function() {
            configDataCollect.modal('openModal');
        });

        function createMessage(txt, type) {
            if (!txt && !type) {
                $('.page-main-actions').next('.messages').remove();
                $('.page-main-actions').next('#messages').remove();
            } else {
                let html = '<div id="messages">' +
                    '<div class="messages">' +
                    '<div class="message message-' + type + type + '">' +
                    '<div data-ui-id="messages-message-' + type + '">' +
                    txt +
                    '</div></div></div>';
                $('.page-main-actions').after(html);
            }
        }
    };
});