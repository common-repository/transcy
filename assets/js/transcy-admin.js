jQuery(document).ready(function ($) {
    function menuLinkSwitcher() {
        var switcherURL = $('.transcy_menu_switcher');
        if (switcherURL.length > 0) {
            switcherURL.attr("target", "_blank");
        }
    }
    menuLinkSwitcher();
});