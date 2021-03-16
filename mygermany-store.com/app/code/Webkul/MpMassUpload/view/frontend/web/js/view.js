/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
"jquery",
'Magento_Ui/js/modal/alert',
"jquery/ui",
], function ($, alert) {
    'use strict';
    $.widget('mpmassupload.view', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var attributeSetInfo = self.options.attributeSetInfo;
                var attributeDetails = self.options.attributeDetails;
                var row = self.options.row;
                var linkSampleRow = self.options.linkSampleRow;
                var sampleRow = self.options.sampleRow;
                var defaultUrl = self.options.defaultUrl;
                var infoUrl = self.options.infoUrl;
                var defaultTitle = $(".wk-mu-options-content").html();
                var defaultContent = $(".wk-mu-custom-attribute").html();
                var options = [];
                $(document).on('click', '#downloadable', function (event) {
                    var val = $(this).prop("checked");
                    if (val == true) {
                        $("#wk_massupload_form .fieldset").append(row);
                    } else {
                        $("#link_file").remove();
                        $("#is_link_sample").remove();
                        $("#link_sample_file").remove();
                        $("#is_sample").remove();
                        $("#sample_file").remove();
                    }
                });
                $(document).on('click', '#is_link_samples', function (event) {
                    var val = $(this).prop("checked");
                    if (val == true) {
                        $("#wk_massupload_form .fieldset").append(linkSampleRow);
                    } else {
                        $("#link_sample_file").remove();
                    }
                });
                $(document).on('click', '#is_samples', function (event) {
                    var val = $(this).prop("checked");
                    if (val == true) {
                        $("#wk_massupload_form .fieldset").append(sampleRow);
                    } else {
                        $("#sample_file").remove();
                    }
                });
                $(document).on('click', '#run-profile', function (event) {
                    var id = $("#profile").val();
                    if (id == "") {
                        alert({
                            title: 'Warning',
                            content: "<div class='wk-warning-content'>Please Select Profile</div>",
                            actions: {
                                always: function (){}
                            }
                        });
                    } else {
                        var url = defaultUrl+id;
                        window.open(url);
                    }
                });
                $(document).on('change', '#attribute_set', function (event) {
                    var id = $(this).val();
                    if (id != "") {
                        setCustomAttributeData(id);
                    } else {
                        $(".wk-mu-custom-attribute").html(defaultContent);
                    }
                });
                $(document).on('change', '#attribute_info', function (event) {
                    showLoader();
                    var code = $(this).val();
                    if (code == "") {
                        setDefaultContent(defaultTitle);
                        hideLoader();
                    } else {
                        if (code in options) {
                            setOptions(options[code]);
                            hideLoader();
                        } else {
                            $.ajax({
                                url: infoUrl,
                                type: 'POST',
                                dataType: 'json',
                                data: { code : code },
                                success: function (data) {
                                    options[code] = data;
                                    setOptions(data);
                                    hideLoader();
                                }
                            });
                        }
                    }
                });
                $(document).on('change', '#massupload_csv', function (event) {
                    var fileName = $(this).val();
                    var ext = fileName.split('.').pop().toLowerCase();
                    if (ext == 'csv') {
                        validateFile(ext, 'csv', $(this));
                    } else if (ext == 'xml') {
                        validateFile(ext, 'xml', $(this));
                    } else {
                        validateFile(ext, 'xls', $(this));
                    }
                });
                $(document).on('change', '#massupload_image', function (event) {
                    var fileName = $(this).val();
                    var ext = fileName.split('.').pop().toLowerCase();
                    validateFile(ext, 'zip', $(this));
                });
                function validateFile(ext, val, obj)
                {
                    if (ext != val) {
                        alert({
                            title: 'Warning',
                            content: "<div class='wk-warning-content'>Invalid file type.</div>",
                            actions: {
                                always: function (){}
                            }
                        });
                        obj.val('');
                    }
                }
                function setDefaultContent(defaultTitle)
                {
                    $(".wk-mu-options-content").empty();
                    $(".wk-mu-options-content").append(defaultTitle);
                }
                function setOptions(json)
                {
                    $(".wk-mu-options-content").empty();
                    $.each(json, function (key, value) {
                        $(".wk-mu-options-content").append("<div class='wk-mu-options-item'>"+value+"</div>");
                    });
                }
                function showLoader()
                {
                    $(".wk-mu-sa-overlay").removeClass("wk-display-none");
                }
                function hideLoader()
                {
                    $(".wk-mu-sa-overlay").addClass("wk-display-none");
                }
                function setCustomAttributeData(id)
                {
                    var result = [];
                    var data = attributeSetInfo[id];
                    $.each(data, function (key, value) {
                        result.push(attributeDetails[value]);
                    });
                    var attributes = result.join(", ");
                    $(".wk-mu-custom-attribute").html(attributes);
                }
            });
        }
    });
    return $.mpmassupload.view;
});
