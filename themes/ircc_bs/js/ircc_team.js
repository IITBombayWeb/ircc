!function($) {
        $(document).ready(function(){
                $("#block-irccstaff li").siblings(':not(.menu-item--expanded)').has("a.is-active").css({"background-color":"#234645"});
        });
}(jQuery);