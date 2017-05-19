(function ($) {
    $.fn.languageSwitcher = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist');
            return false;
        }
    };

    var defaultSettings = {};
    var settings = {};

    var methods = {
        init: function (options) {
            return this.each(function () {
                var $e = $(this);
                settings = $.extend({}, defaultSettings, $e.data(), options || {});

                methods._onMenuClick.apply($e);
            });
        },

        /**
         * @private
         */
        _onMenuClick: function () {
            var $e = $(this);
            $e.on('click', 'li a[data-language]', function (e) {
                var $this = $(this),
                    language = $this.data('language'),
                    url = mgcode.helpers.url.addParam(window.location.href, 'setLanguage', language);

                e.preventDefault();
                window.location.href = url;
            });
        }
    };
})(jQuery);