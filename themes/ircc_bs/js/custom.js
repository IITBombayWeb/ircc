!function($) {
  
  var mainHeader = $('#navbar'),
  		secondaryNavigation = $('.cd-secondary-nav'),
  		//this applies only if secondary nav is below intro section
  		belowNavHeroContent = $('.sub-nav-hero'),
  		headerHeight = mainHeader.height();

  	//set scrolling variables
  	var scrolling = false,
  		previousTop = 0,
  		currentTop = 0,
  		scrollDelta = 2,
  		scrollOffset = 100;

  	mainHeader.on('click', '.nav-trigger', function(event){
  		// open primary navigation on mobile
  		event.preventDefault();
  		mainHeader.toggleClass('nav-open');
  	});

  	$(window).on('scroll', function(){
  		if( !scrolling ) {
  			scrolling = true;
  			(!window.requestAnimationFrame)
  				? setTimeout(autoHideHeader, 250)
  				: requestAnimationFrame(autoHideHeader);
  		}
  	});

  	$(window).on('resize', function(){
  		headerHeight = mainHeader.height();
  	});

  	function autoHideHeader() {
  		var currentTop = $(window).scrollTop();

  		( belowNavHeroContent.length > 0 )
  			? checkStickyNavigation(currentTop) // secondary navigation below intro
  			: checkSimpleNavigation(currentTop);

  	   	previousTop = currentTop;
  		scrolling = false;
  	}

  	function checkSimpleNavigation(currentTop) {
		  //there's no secondary nav or secondary nav is below primary nav
		  if ( $(window).width() > 767 ){
			if (previousTop - currentTop > scrollDelta) {
				//if scrolling up...
				mainHeader.removeClass('is-hidden');
			}
			else if( currentTop - previousTop > scrollDelta && currentTop > scrollOffset) {
				//if scrolling down...
				mainHeader.addClass('is-hidden');
			}
		}
	  }

	  (function() {
		$("#views-exposed-form-solr-search-content-page-1 input").attr("placeholder", "Enter search terms...");
    $("#views-exposed-form-solr-search-area-of-expertise-block-1 input").attr("placeholder", "Enter search terms...");
    $("#views-exposed-form-solr-search-technolo-page-1 input").attr("placeholder", "Enter search terms...");
    $("#block-searchsidebar .nav > li > a").addClass('hvr-sweep-to-right');
    $("div.node-type-link a").addClass('hvr-underline-from-left');
    $("#views-exposed-form-solr-search-content-page-1").addClass('animated fadeInDown delay-01s');

		$(".open-form").click(function(){
			$(".open-form").hide();
			$(".close-form").css("display","block");
			$(".search-block-form").show();
			$(".search-block-form input").focus();
			return false;
		});

		$(".close-form").click(function(){
			$(".close-form").hide();
			$(".open-form").css("display","block");
			$(".search-block-form").fadeOut();
      return false;
    });


    $("#menu-toggle").click(function(){
      $(".search-head").hide();
      return false;
    });

    $("#search-toggle").click(function(){
      $(".menu-head").hide();
      return false;
    });


	})();




	/*Scroll to top when arrow up clicked BEGIN*/
$(window).scroll(function() {
    var height = $(window).scrollTop();
    if (height > 100) {
        $('#back2Top').fadeIn();
    } else {
        $('#back2Top').fadeOut();
    }
});
$(document).ready(function() {
    $("#back2Top").click(function(event) {
        event.preventDefault();
        $("html, body").animate({ scrollTop: 0 }, "slow");
        return false;
    });

});
 /*Scroll to top when arrow up clicked END*/


// top box responsive js
$(window).resize(function () {
    var viewportWidth = $(window).width();
    if (viewportWidth < 450) {
            $(".top-wrapper").removeClass("col-xs-6").addClass("col-xs-12");
    }
    else{
      $(".top-wrapper").removeClass("col-xs-12").addClass("col-xs-6");
    }
});


jQuery(document).ready(function($){
	theme_menu();
});


function theme_menu(){

	//Main menu
	jQuery('#main-menu').smartmenus();

	//Mobile menu toggle
	jQuery('.navbar-toggle').click(function(){
		jQuery('.region-primary-menu').slideToggle();
	});

	//Mobile dropdown menu
	if ( jQuery(window).width() < 767) {
		jQuery(".region-primary-menu li a:not(.has-submenu)").click(function () {
			jQuery('.region-primary-menu').hide();
	    });
	}

}


///////////////////////////
// Preloader
$(window).on('load', function() {
  $("#preloader").delay(600).fadeOut();
});



$(document).ready(function(){
      $('[data-toggle="popover"]').popover({ 
    html : true,
    content: function() {
      //console.log($(this).attr('id'));
      var nid=($(this).attr('id')).split('popover_');
      return $('#popover_content_wrapper_'+nid[1]+' div p').html();
    }
  });  
});

}(jQuery);
