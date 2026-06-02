/* global jQuery, wp */
(function ($) {
    'use strict';

    /* ----------------------------------------------------------------
       Tab switching
    ---------------------------------------------------------------- */
    var $tabs     = $('.ack-lba-tabs .nav-tab');
    var $tabPanes = $('.ack-lba-tab');

    $tabs.on('click', function (e) {
        e.preventDefault();
        var target = $(this).attr('href'); // e.g. #tab-general
        $tabs.removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $tabPanes.hide();
        $(target).show();
    });

    /* ----------------------------------------------------------------
       Color picker — init on all existing + dynamically added
    ---------------------------------------------------------------- */
    function initColorPickers($scope) {
        $scope.find('.ack-lba-color-picker').wpColorPicker();
    }
    initColorPickers($(document));

    /* ----------------------------------------------------------------
       Sortable sections list
    ---------------------------------------------------------------- */
    if ($.fn.sortable) {
        $('#ack-lba-sections-list').sortable({
            handle: '.ack-lba-drag-handle',
            update: reindexSections
        });
    }

    /* ----------------------------------------------------------------
       Add section
    ---------------------------------------------------------------- */
    $('#ack-lba-add-section').on('click', function () {
        var si        = Date.now(); // temporary unique index until save/reload
        var tmpl      = $('#tmpl-lba-section').html();
        var rendered  = tmpl.replace(/__SI__/g, si);
        var $new      = $(rendered);
        $('#ack-lba-sections-list').append($new);
        initColorPickers($new);
        initQuestionSortable($new);
    });

    /* ----------------------------------------------------------------
       Remove section
    ---------------------------------------------------------------- */
    $(document).on('click', '.ack-lba-remove-section', function () {
        if (!confirm('Remove this section and all its questions?')) return;
        $(this).closest('.ack-lba-section-row').remove();
    });

    /* ----------------------------------------------------------------
       Add question
    ---------------------------------------------------------------- */
    $(document).on('click', '.ack-lba-add-question', function () {
        var $section  = $(this).closest('.ack-lba-section-row');
        var si        = $section.data('si');
        var tmpl      = $('#tmpl-lba-question').html();
        var rendered  = tmpl.replace(/__SI__/g, si);
        $section.find('.ack-lba-questions-list').append(rendered);
    });

    /* ----------------------------------------------------------------
       Remove question
    ---------------------------------------------------------------- */
    $(document).on('click', '.ack-lba-remove-question', function () {
        $(this).closest('.ack-lba-question-row').remove();
    });

    /* ----------------------------------------------------------------
       Sortable questions within each section
    ---------------------------------------------------------------- */
    function initQuestionSortable($section) {
        if (!$.fn.sortable) return;
        $section.find('.ack-lba-questions-list').sortable({
            handle: '.ack-lba-drag-handle'
        });
    }
    $('.ack-lba-section-row').each(function () {
        initQuestionSortable($(this));
    });

    /* ----------------------------------------------------------------
       Reindex section si attributes after drag-sort
       (keeps field name indices consistent for PHP array handling)
    ---------------------------------------------------------------- */
    function reindexSections() {
        $('#ack-lba-sections-list .ack-lba-section-row').each(function (i) {
            var $row = $(this);
            $row.attr('data-si', i);
            // Update all field names within this section to use new index
            $row.find('[name]').each(function () {
                var name = $(this).attr('name');
                // Replace ack_lba[sections][anything] with ack_lba[sections][i]
                name = name.replace(/ack_lba\[sections\]\[\d+\]/g, 'ack_lba[sections][' + i + ']');
                $(this).attr('name', name);
            });
        });
    }

    /* ----------------------------------------------------------------
       Add pattern rule
    ---------------------------------------------------------------- */
    $('#ack-lba-add-pattern').on('click', function () {
        var pi       = Date.now();
        var tmpl     = $('#tmpl-lba-pattern').html();
        var rendered = tmpl.replace(/__PI__/g, pi);
        $('#ack-lba-patterns-list').append(rendered);
    });

    /* ----------------------------------------------------------------
       Remove pattern rule
    ---------------------------------------------------------------- */
    $(document).on('click', '.ack-lba-remove-pattern', function () {
        $(this).closest('.ack-lba-pattern-row').remove();
    });

}(jQuery));
