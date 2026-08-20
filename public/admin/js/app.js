/* ==========================================================================
   Hall Management — shared front-end behaviour.
   Replaces the inline <script> block that used to sit in the layout and ran on
   every page whether or not the elements it touched existed.
   ========================================================================== */

(function ($) {
    'use strict';

    /* ------------------------------------------------------------- theming */

    var THEME_KEY = 'hm-theme';

    function currentTheme() {
        return document.documentElement.getAttribute('data-bs-theme') || 'light';
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        try {
            localStorage.setItem(THEME_KEY, theme);
        } catch (e) {}

        // The icon shows the theme you would switch TO.
        $('[data-theme-icon]').text(theme === 'dark' ? 'light_mode' : 'dark_mode');
    }

    function initTheme() {
        applyTheme(currentTheme());

        $(document).on('click', '#themeToggle', function () {
            applyTheme(currentTheme() === 'dark' ? 'light' : 'dark');
        });
    }

    /* ---------------------------------------------------------- data tables */

    function initDataTables() {
        if (!$.fn.DataTable) {
            return;
        }

        $('table.datatable').each(function () {
            var $table = $(this);

            if ($.fn.DataTable.isDataTable($table)) {
                return;
            }

            // A table with no body rows (or only an empty-state row) should not
            // get pagination furniture bolted on.
            var realRows = $table.find('tbody > tr').not('.dt-skip').filter(function () {
                return !$(this).find('td[colspan]').length;
            }).length;

            if (realRows === 0) {
                return;
            }

            var order = $table.data('order');

            $table.DataTable({
                paging: realRows > ($table.data('page-length') || 15),
                pageLength: $table.data('page-length') || 15,
                lengthChange: false,
                searching: $table.data('searching') !== false,
                ordering: $table.data('ordering') !== false,
                info: true,
                autoWidth: false,
                order: order || [],
                // Skip columns marked data-orderable="false" (action columns).
                columnDefs: [{ orderable: false, targets: 'no-sort' }],
                language: {
                    search: '',
                    searchPlaceholder: 'Search…',
                    emptyTable: 'No records found',
                    zeroRecords: 'No matching records found',
                    info: 'Showing _START_ to _END_ of _TOTAL_',
                    infoEmpty: 'No records',
                    infoFiltered: '(filtered from _MAX_)',
                    paginate: { previous: '‹', next: '›' }
                }
            });
        });
    }

    /* --------------------------------------------------------------- select2 */

    function initSelect2() {
        if (!$.fn.select2) {
            return;
        }

        $('.select2').each(function () {
            var $el = $(this);

            if ($el.hasClass('select2-hidden-accessible')) {
                return;
            }

            $el.select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: !$el.prop('required'),
                placeholder: $el.data('placeholder') || 'Select an option',
                // Without this a select inside a modal renders behind it.
                dropdownParent: $el.closest('.modal').length ? $el.closest('.modal') : $(document.body)
            });
        });
    }

    /* ---------------------------------------------------------------- alerts */

    function initAutoDismiss() {
        $('[data-auto-dismiss]').each(function () {
            var $alert = $(this);
            var delay = parseInt($alert.data('auto-dismiss'), 10) || 6000;

            window.setTimeout(function () {
                if (window.bootstrap && bootstrap.Alert) {
                    bootstrap.Alert.getOrCreateInstance($alert[0]).close();
                } else {
                    $alert.fadeOut(200, function () { $(this).remove(); });
                }
            }, delay);
        });
    }

    /* ------------------------------------------------------- form protection */

    /**
     * Disable a submit button once its form is submitted, so an impatient
     * double-click cannot create the same booking or payment twice.
     */
    function initSubmitGuards() {
        $(document).on('submit', 'form:not([data-no-guard])', function () {
            var $form = $(this);

            // Never block a form the browser rejected on client-side validation.
            if (this.checkValidity && !this.checkValidity()) {
                return;
            }

            $form.find('button[type="submit"], input[type="submit"]').each(function () {
                var $btn = $(this);
                var label = $btn.data('loading-text') || 'Working…';

                $btn.prop('disabled', true);

                if ($btn.is('button')) {
                    $btn.data('original-html', $btn.html());
                    $btn.html('<span class="spinner-border spinner-border-sm me-1"></span>' + label);
                }
            });

            // Re-enable if the page is restored from cache instead of navigating.
            $(window).one('pageshow', function () {
                $form.find('button[type="submit"], input[type="submit"]').each(function () {
                    var $btn = $(this);
                    $btn.prop('disabled', false);
                    if ($btn.data('original-html')) {
                        $btn.html($btn.data('original-html'));
                    }
                });
            });
        });
    }

    /** Confirmation prompts for destructive buttons. */
    function initConfirmations() {
        $(document).on('click', '[data-confirm]', function (event) {
            if (!window.confirm($(this).data('confirm'))) {
                event.preventDefault();
                event.stopImmediatePropagation();
                return false;
            }
        });
    }

    /* ----------------------------------------------------------------- print */

    function initPrint() {
        $(document).on('click', '[data-print]', function (event) {
            event.preventDefault();
            window.print();
        });
    }

    /* -------------------------------------------------------------- currency */

    /** Rs. 1,234 — shared by every inline total on the booking form. */
    window.hmMoney = function (value, decimals) {
        var n = Number(value) || 0;
        return 'Rs. ' + n.toLocaleString('en-PK', {
            minimumFractionDigits: decimals || 0,
            maximumFractionDigits: decimals || 0
        });
    };

    $(function () {
        initTheme();
        initDataTables();
        initSelect2();
        initAutoDismiss();
        initSubmitGuards();
        initConfirmations();
        initPrint();
    });
})(jQuery);
