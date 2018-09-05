!function($) {
  $(document).ready(function(){
    $(".display").hide();

    $('[item="dean-adean"]').click(function(){
        $(".display").hide();
        $("#dean-adean").show();
        return false;
    });
    
    $('[item="pa-dean"]').click(function(){
        $(".display").hide();
        $("#pa-dean").show();
        return false;
    });

  });
}(jQuery);
