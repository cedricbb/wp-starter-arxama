jQuery(document).ready(function($){
    //Si on est sur une page qui ne charge pas Elementor
    if(jQuery('body').hasClass('js_loading_page'))
    {
        setTimeout(function() {
            jQuery('body').removeClass('js_loading_page');
        }, 800);
    }

    // Au chargement d'Elementor
    jQuery(window).on('elementor/frontend/init', function()
    {
        jQuery('body').removeClass('js_loading_page');
    });
    //	vérification pour affichage dans l'éditeur Elementor
    if(jQuery('body').hasClass('logged-in'))
    {
        setTimeout(function() {
            jQuery('body').removeClass('js_loading_page');
        }, 800);
    }
});
