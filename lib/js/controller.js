// Controller Version 3.0

//Global array containing timers
//Add your timers here if you want process information to handle aut stopping

var timers = new Array();
/*
// Just test... move dashboard widgets to a specific module
new Morris.Line({
  // ID of the element in which to draw the chart.
  element: 'myfirstchart',
  // Chart data records -- each entry in this array corresponds to a point on
  // the chart.
  data: [
    { year: '2008', value: 20 },
    { year: '2009', value: 10 },
    { year: '2010', value: 5 },
    { year: '2011', value: 5 },
    { year: '2012', value: 20 }
  ],
  // The name of the data record attribute that contains x-values.
  xkey: 'year',
  // A list of names of data record attributes that contain y-values.
  ykeys: ['value'],
  // Labels for the ykeys -- will be displayed when you hover over the
  // chart.
  labels: ['Value']
});*/

/**
 * Menu Navigation
 */
function menu_navigation(clicked, func, module, isHistory) {
  // Remove active classes
  $('li', '.sidebar-menu').removeClass('active');
  $('a', '.sidebar-menu').removeClass('menu-active-item')

  isHistory = isHistory || false;
  var myLocation = '';
  fromMenu = 1;
  // Store the counter inside an object such as {counter:0} along with extra to test speed
  if (!isHistory) {
    counter++;
    $.history({'counter': counter});
    myLocations[counter] = "menu_navigation('" + clicked + "', '" + func + "', '" + module + "', true)";
  }
  $('#menu-item-' + clicked).addClass('menu-active-item').parents('li').addClass('active');

  // Close left sidebar if window is less than 992px after clicking a menu item
  if ($(window).width() <= 992 && $('.row-offcanvas').hasClass('active')) {
    $('.row-offcanvas').toggleClass('active');
    $('.left-side').removeClass('collapse-left');
    $('.right-side').removeClass('strech');
    $('.row-offcanvas').toggleClass('relative');
  }

  // Call process information
  process_information(null, func, module);
}

/*
 * jQuery History (back button)
 */
var counter = 0;
var fromMenu = 0;
var myLocations = new Array();
$.history._cache = 'cache.html';
// Default location is dashboard
myLocations[0] = 'menu_navigation( \'dashboard_main\',\'dashboard_home\', \'dashboard\', true)';
// Function to handle the data coming back from the history upon back/fwd hit
$.history.callback = function(reinstate, cursor) {
  if (typeof(reinstate) == 'undefined') {
    counter = 0;
  }
  else {
    counter = parseInt(reinstate.counter) || 0;
  }
  if (fromMenu < 1) {
    return (new Function('return (' + myLocations[counter.valueOf()] + ')')());
  }
  else {
    fromMenu = 0;
  }
};

/**
 * Serialize objects
 */
$.fn.serializeObject = function() {
  var o = {};
  var a = this.serializeArray();
  $.each(a, function() {
    if (o[this.name]) {
      if (!o[this.name].push) {
        o[this.name] = [o[this.name]];
      }
      o[this.name].push(this.value || '');
    }
    else {
      o[this.name] = this.value || '';
    }
  });
  return o;
};

/**
 * Ajax calls
 * Process Information
 */
function process_information(formname, func, module, ask_confirm, extra_value, error_el, response_el, response_type, request_type, parent, el, proc_message, timer) {
  error_el = (error_el == null) ? 'status-message' : error_el;
  response_el = (response_el == null) ? 'content' : response_el;
  request_type = (request_type == null) ? 'GET' : request_type;
  proc_message = (proc_message == null) ? 'Loading, Please wait...' : proc_message;
  var confirmation = (ask_confirm == null) ? false : ask_confirm;
  var ser = (formname == null) ? '' : $('#' + formname).serialize();

  if (timer == null) {
    for (var i = 0; i < timers.length; i++) {
      clearTimeout(timers[i]);
    }
  }

  // if it has a parent.
  if (parent) {
    // for response
    var parent_el = '';
    parent_el = $(el).parents('#' + parent);
    if (response_type != 'remove-item' && response_type != 'update_row') {
      response_el = $(parent_el).find('#' + response_el);
      if ($(response_el).length == 0) {
        response_el = 'content';
      }
    }

    // Get form id.
    if ($('#' + parent).is('form')) {
      ser = $(el).parents('#' + parent).serialize();
    }
    else { // Serialize everysingle form inside the given parent element
      var forms = new Array;
      $('#' + parent + ' form').each(function(i, value) {
        ser = $.toJSON($(this).serializeObject());
        ser = ser.replace(/[[]]/g, '');
        ser = ser.replace(/#/g, '%23');
        forms[i] = 'form[]=' + ser;
      });
      ser = forms.join('&');
    }
    //alert(ser);
    // Putting the error_el under its
    error_el = $(parent_el).find('#' + error_el);
  }

  // Test if response element is an object or just a element id
  if (typeof(response_el) != 'object') {
    response_el = "#" + response_el;
  }
  if (typeof(error_el) != 'object') {
    error_el = "#" + error_el;
  }

  if (extra_value == null) {
    extra_value = "";
  }
  else {
    if (extra_value.indexOf('&') !== -1) {
      //Extra value has multiple key/values separated by &
      //Let's split it and process one by one
      extra_values = extra_value.split('&');
      extra_value = "";
      for (var i = 0, len = extra_values.length; i < len; i++) {
        var val = extra_values[i].split('|');
        extra_value = "&" + val[0] + "=" + val[1] + extra_value;
      }
    }
    else {
      var val = extra_value.split('|');
      extra_value = "&" + val[0] + "=" + val[1];
    }
  }
  if (error_el) {
    $(error_el).html('').removeClass('error');
  }

  var do_ajax = true;
  if (confirmation) {
    do_ajax = confirm(ask_confirm);
  }

  if (request_type == "GET") {
    ul = "modules/" + module + "/index.php?fn=" + func + "&" + ser  + extra_value;
  }
  else {
    ul = "modules/" + module + "/index.php?fn=" + func;
    var reqdata = "fn=" + func + "&" + ser + extra_value;
  }

  if (do_ajax) {
    $.ajax({
      type: request_type,
      url: ul,
      data: reqdata,
      success: function(transport) {
        fromMenu = 0;
        if (transport == 'LOGOUT') {
          window.location = 'logout.php';
        }
        else if (transport == '' || transport == null) {
          return false;
        }
        else {
          $(response_el).removeClass('error');
          if (response_type != 'in_modal') {
            $('#modal').modal('hide');//code
          }
          switch (response_type) {
            case 'modal':
              $('#modal').modal('show').html(transport);
              break;
            case 'create_row':
              var row = $('tr[data-info-id]', transport).data('info-id');
              if ($('tbody', $('.table')).length > 0) {
                $('tbody', $('.table')).prepend($('tr[data-info-id]', transport));
              }
              else {
                $(response_el).html(transport);
              }
              break;
            case 'edit_row':
              var row = $('tr[data-info-id]', transport).data('info-id');
              $('tr[data-info-id="' + row + '"]').html($('tr[data-info-id]', transport).html()).fadeOut().fadeIn();
              break;
            case 'edit_cell':
              obj = JSON.parse(transport);
              var row = $('tr[data-info-id="' + obj.id + '"]');
              $(row).children('td').each(function() {
                if ($(this).data('info-field') in obj) {
                  $(this).html(obj[$(this).data('info-field')]).fadeOut().fadeIn();
                }
              });
              break;
            case 'delete_row':
              $('tr[data-info-id="' + transport + '"]').fadeOut(function() {
                $(this).remove();
              });
              break;
            case 'append':
              $(response_el).append(transport);
              break;
            case 'prepend':
              $(response_el).prepend(transport);
              break;
            case 'in_modal':
              $(response_el).html(transport);
              break;
            default:
              $(response_el).html(transport);
              // Build breadcrumbs
              var crumbs = new Array();
              $('.breadcrumb li').not('.breadcrumb-home').remove();
              $('.menu-active-item', '.sidebar-menu').parents('li').each(function(e) {
                crumbs[e] = $('<div />').append($(this).children('a:first').clone()).html().replace(/\s+/g, " ");
              });
              if (crumbs.length > 0 && $('.menu-active-item').parent('li').data('menu-id') != 1) { // 1 = Dashboard
                crumbs.reverse();
                for (var i = 0; i < crumbs.length; i++) {
                  $('.breadcrumb').append('<li>' + crumbs[i] + '</li>');
                }
                $('.breadcrumb li').not('.breadcrumb-home').children('a').css('margin', '').children('.fa').remove();
              }
              break;
          }
        }
      },
      complete: function() {
        // Process messages
        process_messages();

        // Conditional fields
        conditional_fields();

        // Disabled fields
        $(document).on('click change focus mousedown', '.form-readonly', function(e) {
          e.preventDefault();
        });

        // File Uplader field
        $('.uploader-button').each(function() {
          uploader_add_file($(this).data('id'));
        });

        // Tooltip
        //$('input,button,textarea,select').tooltip('hide');

        // Chosen plugin
        $('.chosen-select').chosen({disable_search_threshold: 10});

        if(func=="customer_plan_cancel" || func=="customer_plan_signup"){
          window.location.replace("customer_dash.php");
        }
        // Call the function that makes the dashboard sortable
        if (func == "dashboard_widgets_load_droplets_wrapper" || func == 'dashboard_update_list' || func == 'dashboard_setup') {
          //Calling function at dashboard.js
          //Also being called from
          build_dashboard();
        }
      },
    });
  }
}

/**
 * Process Natural Messages
 */
function process_messages() {
  // Call server side script for retrieve messages
  var boxClass = '';
  $.ajax({
    url: 'lib/messages.php',
    type: 'GET',
    dataType: 'json',
    success: function(data) {
      if (data.messages != undefined) {
        $.each(data.messages, function(index, item) {
          switch (item.type) {
            case 'error':
              boxClass = 'bs-callout-danger';
              break;
            case 'alert':
              boxClass = 'bs-callout-warning';
              break;
            case 'success':
              boxClass = 'bs-callout-success';
              break;
            default:
              boxClass = 'bs-callout-info';
              break;
          }
          // Using smallBox
          $.smallBox({
            title: item.type,
            content: item.msg,
            timeout: 6000,
            customClass: 'bs-callout' + ' ' + boxClass,
            //color: "#f5f5f5",
            //img:"static/img/pic1.png"
          })
        });
      }
    }
  });
}

/**
 * Conditional Fields
 */
function conditional_fields() {
  $('[data-condition]').each(function() {
    var field_value = $(this).data('condition').split('=');
    if ($('select[name="' + field_value[0] + '"]').val() == field_value[1] ||
        $('[name="' + field_value[0] + '"][value="' + field_value[1] + '"]').is(':checked') ||
        $('[name="' + field_value[0] + '[]"][value="' + field_value[1] + '"]').is(':checked')) {
      $(this).removeClass('hide');
    }
    else {
      $(this).addClass('hide');
    }

  });
}
