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
"jquery/ui",
], function ($) {
    'use strict';
    $.widget('mpmassupload.upload', {
        options: {},
        _create: function () {
            var self = this;
            var row = self.options.row;
            var linkSampleRow = self.options.linkSampleRow;
            var sampleRow = self.options.sampleRow;
            $(document).ready(function () {
                $('#is_downloadable').on('click', function () {
                    var val = $(this).prop("checked");
                    if (val == true) {
                        $("#base_fieldset").append(row);
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
                        $("#base_fieldset").append(linkSampleRow);
                    } else {
                        $("#link_sample_file").remove();
                    }
                });
                $(document).on('click', '#is_samples', function (event) {
                    var val = $(this).prop("checked");
                    if (val == true) {
                        $("#base_fieldset").append(sampleRow);
                    } else {
                        $("#sample_file").remove();
                    }
                });
            });
        }
    });
    return $.mpmassupload.upload;
});
