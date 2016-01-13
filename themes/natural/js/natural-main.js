$(function() {
  'use strict';

  // Enable sidebar toggle
  $('[data-toggle="offcanvas"]').click(function(e) {
    e.preventDefault();

    // If window is small enough, enable sidebar push menu
    if ($(window).width() <= 992) {
      $('.row-offcanvas').toggleClass('active');
      $('.left-side').removeClass('collapse-left');
      $('.right-side').removeClass('strech');
      $('.row-offcanvas').toggleClass('relative');
    }
    else {
      // Else, enable content streching
      $('.left-side').toggleClass('collapse-left');
      $('.right-side').toggleClass('strech');
    }
  });

  // Enable treeview
  $('.sidebar .treeview').tree();

  // Ajax Loading
  $(document).ajaxStart(function() {
    $('.loading').fadeIn();
  });
  $(document).ajaxStop(function() {
    $('.loading').fadeOut();
  });
});

/*
 * Treeview for the menu
 */
(function($) {
  'use strict';

  $.fn.tree = function() {
    return this.each(function() {
      var btn = $(this).children('a').first();
      var menu = $(this).children('.treeview-menu').first();
      var isActive = $(this).hasClass('active');

      // Initialize already active menus
      if (isActive) {
          menu.show();
          btn.children('.fa-angle-left').first().removeClass('fa-angle-left').addClass('fa-angle-down');
      }
      // Slide open or close the menu on link click
      btn.click(function(e) {
        e.preventDefault();
        if (isActive) {
          // Slide up to close menu
          menu.slideUp();
          isActive = false;
          btn.children('.fa-angle-down').first().removeClass('fa-angle-down').addClass('fa-angle-left');
          btn.parent('li').removeClass('active');
        }
        else {
          // Slide down to open menu
          menu.slideDown();
          isActive = true;
          btn.children('.fa-angle-left').first().removeClass('fa-angle-left').addClass('fa-angle-down');
          btn.parent('li').addClass('active');
        }
      });

      // Add margins to submenu elements to give it a tree look
      menu.find('li > a').each(function() {
        var pad = parseInt($(this).css('margin-left')) + 10;

        $(this).css({'margin-left': pad + 'px'});
      });

    });
  };
}(jQuery));