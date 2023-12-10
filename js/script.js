(function($) {

  $.fn.menumaker = function(options) {
      
      var cssmenu = $(this), settings = $.extend({
        title: "Menú",
        format: "dropdown",
        sticky: false
      }, options);

      return this.each(function() {
        cssmenu.prepend('<div id="menu-button">' + settings.title + '</div>');
        $(this).find("#menu-button").on('click', function(){
          $(this).toggleClass('menu-opened');
          var mainmenu = $(this).next('ul');
          if (mainmenu.hasClass('open')) { 
            mainmenu.hide().removeClass('open');
          }
          else {
            mainmenu.show().addClass('open');
            if (settings.format === "dropdown") {
              mainmenu.find('ul').show();
            }
          }
        });

        cssmenu.find('li ul').parent().addClass('has-sub');

        multiTg = function() {
          cssmenu.find(".has-sub").prepend('<span class="submenu-button"></span>');
          cssmenu.find('.submenu-button').on('click', function() {
            $(this).toggleClass('submenu-opened');
			
            if ($(this).siblings('ul').hasClass('open')) {
              $(this).siblings('ul').removeClass('open').hide();
			  $(this).siblings('ul').hide();
            }
            else {
              $(this).siblings('ul').addClass('open').show();
			  $(this).siblings('ul').show();
            }
          });
        };

        if (settings.format === 'multitoggle') multiTg();
        else cssmenu.addClass('dropdown');

        if (settings.sticky === true) cssmenu.css('position', 'fixed');

        resizeFix = function() {
          if ($( window ).width() > 1200) {
            cssmenu.find('ul').show();
			$('#menu-button').css('display', 'none');
          }

          if ($(window).width() <= 1200) {
            cssmenu.find('ul').hide().removeClass('open');
			$('#menu-button').css('display', 'block');
          }
        };
        resizeFix();
        return $(window).on('resize', resizeFix);

      });
  };
})(jQuery);

(function($){

$(document).ready(function() {
  $("#menu-superior .menu-horizontal").menumaker({
    title: "Men&uacute;",
    format: "multitoggle"
  });
	var margen = ($('#titulo h1').height() - $('#titulo h1 span').height())/2;
	$('#titulo h1 span').css('margin-top', margen+'px');
	$(window).resize(function(){
		
		var margen = ($('#titulo h1').height() - $('#titulo h1 span').height())/2;
		$('#titulo h1 span').css('margin-top', margen+'px');
	});
});
})(jQuery);
