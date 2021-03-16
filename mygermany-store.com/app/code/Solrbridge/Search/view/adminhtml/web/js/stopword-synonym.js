define(['jquery','mage/translate','mage/template','jquery/ui','mage/validation','jquery/ui'], function ($, $t) {
    "use strict";
    function showTable(table)
    {
        var rows = $(table).select('tbody tr').length;
        if (rows > 0) {
            $(table).show();
        } else {
            $(table).hide();
        }
    }
    
    function toggleDeleteSelected(button)
    {
        var table = $(button).parents('table').first();
        var display = false;
        $(table).find('tbody input[type="checkbox"]').each(function (item) {
            if ($(this).prop('checked') === true) {
                display = true;
                return;
            }
        });
        if (display) {
            button.show();
        } else {
            button.hide();
        }
    }
    
    function createNewStopwordRow(stopword)
    {
        var tr = $('<tr />');
        var td1 = $('<td />', {style:'text-align:center'});
        var inputCheck = $('<input />', {type:'checkbox'});
        $(inputCheck).click(function () {
            toggleDeleteSelected($('#delete-selected-stopword-button'));
        });
        $(td1).append(inputCheck);
        var td2 = $('<td />');
        $(td2).html('<input type="text" name="stopwords[]" value="'+stopword+'" class="input-text" />');
        var td3 = $('<td />');
        var deleteButton = $('<button />', {type:'button'});
        $(deleteButton).html($t('Delete'));
        $(deleteButton).click(function () {
            var tr = $(this).parents('tr').first();
            $(tr).remove();
            showTable($('#stopword-table'));
        });
        $(td3).append(deleteButton);
        $(tr).append(td1);
        $(tr).append(td2);
        $(tr).append(td3);
        $('#stopword-table').find('tbody').first().append(tr);
        showTable($('#stopword-table'));
    }
    
    function deleteSelectedStopwords()
    {
        $('#stopword-table').find('tbody').first().find('input[type="checkbox"]').each(function (item) {
            var item = this;
            if ($(item).prop('checked') == true) {
                $(item).parents('tr').first().remove();
            }
        });
        showTable($('#stopword-table'));
    }
    
    function deleteSelectedSynonyms()
    {
        $('#synonym-table').find('tbody').first().find('input[type="checkbox"]').each(function (item) {
            var item = this;
            if ($(item).prop('checked') == true) {
                $(item).parents('tr').first().remove();
            }
        });
        showTable($('#synonym-table'));
    }
    
    function createNewSynonymRow(synonym)
    {
        var tr = $('<tr />');
        var td1 = $('<td />', {style:'text-align:center'});
        var inputCheck = $('<input />', {type:'checkbox'});
        $(inputCheck).click(function () {
            toggleDeleteSelected($('#delete-selected-synonym-button'));
        });
        $(td1).append(inputCheck);
        var td2 = $('<td />');
        
        var term = '';
        if (typeof synonym.term !== 'undefined') {
            term = synonym.term;
        }
        $(td2).html('<input type="text" name="synonyms[term][]" value="'+term+'" class="input-text" />');
        
        var td3 = $('<td />');
        var textarea = $('<textarea />', {class:'textarea', name: 'synonyms[synonym][]'});
        
        if (typeof synonym.synonyms !== 'undefined') {
            $(textarea).html(synonym.synonyms);
        }
        $(td3).append(textarea);
        
        var td4 = $('<td />');
        var deleteButton = $('<button />', {type:'button'});
        $(deleteButton).html($t('Delete'));
        $(deleteButton).click(function () {
            var tr = $(this).parents('tr').first();
            $(tr).remove();
            showTable($('#synonym-table'));
        });
        $(td4).append(deleteButton);
        
        $(tr).append(td1);
        $(tr).append(td2);
        $(tr).append(td3);
        $(tr).append(td4);
        $('#synonym-table').find('tbody').first().append(tr);
        showTable($('#synonym-table'));
    }
    
    function _init_stopword_synonym(config)
    {
        $(document).ready(function () {
            $('#add-more-stopword').click(function () {
                createNewStopwordRow('');
            });
        
            $('#add-more-synonym').click(function () {
                createNewSynonymRow('');
            });
        
            var stopwords = config.stopwords;//<?php echo json_encode($this->getStopwords()) ?>;
            $(stopwords).each(function (key, item) {
                createNewStopwordRow(item);
            });
        
            var synonyms = config.synonyms;//<?php echo json_encode($this->getSynonyms()) ?>;
            $(synonyms).each(function (key, item) {
                createNewSynonymRow(item);
            });
        
            $('input.checkall').each(function () {
                var input = this;
                $(this).click(function () {
                    var table = $(this).parents('table').first();
                    var isChecked = $(this).prop('checked');
                    $(table).find('tbody input[type="checkbox"]').each(function (item) {
                        //$(item).setAttribute('checked', checkValue);
                        $(this).prop('checked', isChecked);
                    });

                    if ($(this).hasClass('synonym')) {
                        toggleDeleteSelected($('#delete-selected-synonym-button'));
                    } else if ($(this).hasClass('stopword')) {
                        toggleDeleteSelected($('#delete-selected-stopword-button'));
                    }
                });
            });
            showTable($('#synonym-table'));
            showTable($('#stopword-table'));
            toggleDeleteSelected($('#delete-selected-synonym-button'));
            toggleDeleteSelected($('#delete-selected-stopword-button'));
            $('#delete-selected-synonym-button').click(function () {
                deleteSelectedSynonyms();
            });
            $('#delete-selected-stopword-button').click(function () {
                deleteSelectedStopwords();
            });
        });
    }
    
    return function (config, element) {
        $('#select-solr-core').change(function (e) {
            window.location.href = $(this.options[e.target.selectedIndex]).data('url');
        });
        _init_stopword_synonym(config);
    }
});