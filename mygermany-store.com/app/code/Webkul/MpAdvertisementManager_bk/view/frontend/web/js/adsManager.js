/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerTaxManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define(
    [
      'jquery',
      'mage/translate',
      'handlebars',
      'Magento_Ui/js/modal/alert',
      'Magento_Ui/js/modal/confirm',
      'mage/mage',
      'jquery/ui',
      'mage/adminhtml/wysiwyg/tiny_mce/setup',
      'mage/validation',
      'mage/calendar'
    ],
    function ($,$t,$h,alert,confirm) {
        'use strict';
 
        $.widget(
            'webkul.adsManager',
            {
                _create: function () {
                    var self = this;
                    if (self.options.blockData && self.options.blockData.rowClass) {
                        var rowClass = self.options.blockData.rowClass;
                    
                        $('body').on(
                            'click',
                            rowClass,
                            function (e) {
                                if (!$(e.target).hasClass('mpcheckbox')) {
                                    window.location.href = $(this).attr('title');
                                }
                            }
                        );

                        $("#mpselecctall").on(
                            'change',
                            function () {
                                if ($(this).prop("checked") == true ) {
                                    $('.mpcheckbox').prop('checked', true);
                                } else {
                                    $('.mpcheckbox').prop('checked', false);
                                }
                            }
                        );
                    }
                    $('#wk_mpads_imageupload').on('click',function () {
                        $('input#content').trigger('click');
                    });
                    $('input[type="file"]').on('change',function (e) {
                      var preview = $('#wk_mpads_imageupload');
                      var file    = this.files[0];
                      var reader  = new FileReader();
                      reader.onloadend = function () {
                        preview.css('background-image','url(' +reader.result+ ')');
                        preview.css('background-size','cover');
                      }
                      if (file) {
                        reader.readAsDataURL(file);
                      } else {
                        preview.src = "";
                      }
                    });
                    if (self.options.blockEditData) {
                        // var editorConfig = self.options.blockEditData.wysywygConfig;
                        // if (editorConfig) {
                            // self.addEditor(editorConfig);
                        // }
                    }
                },
                addEditor: function (config) {
                    var editor;
                    $.extend(
                        config,
                        {
                            settings: {
                                mode : "exact",
                                elements: "custom_wysiwyg",
                                theme : "advanced",
                                plugins : "inlinepopups,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
                                theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
                                theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                                theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
                                theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,|,visualchars,nonbreaking",
                                theme_advanced_toolbar_location : "top",
                                theme_advanced_toolbar_align : "left",
                                theme_advanced_path_location : "bottom",
                                extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
                                theme_advanced_resize_horizontal : 'true',
                                theme_advanced_resizing : 'true',
                                apply_source_formatting : 'true',
                                convert_urls : 'false',
                                force_br_newlines : 'true',
                                doctype : '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'
                            },
                            files_browser_window_url: false
                        }
                    );

                    editor = new tinyMceWysiwygSetup(
                        'content',
                        config
                    );

                    editor.turnOn();

                    $('#content')
                    .addClass('wysiwyg-editor')
                    .data(
                        'wysiwygEditor',
                        editor
                    );
                }
            }
        );

        return $.webkul.adsManager;
    }
);  