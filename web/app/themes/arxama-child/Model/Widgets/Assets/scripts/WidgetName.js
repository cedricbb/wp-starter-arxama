jQuery(document).ready(function () {
    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/WidgetName.default', function ($scope) {
            //
        });
    });
});