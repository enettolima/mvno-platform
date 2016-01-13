/**
 * Dashboard Sortable Function
 */
function build_dashboard() {

  $(".dashboard").disableSelection();

  $(".button-min").click(function() {
    $( this ).children().toggleClass( "fa-minus fa-plus" );
    $( this ).parents( ".grid-stack-item:first" ).find( ".graph" ).toggle();
    $( this ).parents( ".grid-stack-item:first" ).setAttribute("data-gs-height", "1");
  });


  $( ".button-close" ).click(function() {
    dashboard_delete_widget($( this ).parents( ".grid-stack-item:first" ).attr("id"));
  });

  $('.dashboard-setup-title').click(function() {
    if ($(this).hasClass('closed')) {
      $(this).removeClass('closed').addClass('opened');
      $('#dashboard-setup').slideDown('normal');
    } else {
      $(this).removeClass('opened').addClass('closed');
      $('#dashboard-setup').slideUp('normal');
    }
  });


  $('.grid-stack').on('resizestop', function (event, ui) {
      //this here should not be needed if morris graphs resize the grahp without need to load again.
      //maybe this change later on the next release version.
      var grid = this;
      var element = event.target.id;

      var arr = event.target.id.toString().split('__');
      var fn = arr[1];
      var id = arr[2];
      var graph_id = '#'+fn+'__'+id;
      $("#" + fn ).empty();
      $.ajax({
        url: 'modules/dashboard_widgets/index.php?fn='+fn+'&id='+id,
        type: 'GET',
        beforeSend: function() {
          $('#'+fn).html('<div class="naturalwidget-loading"></div>');
        },
        success: function(data) {
          router(fn, graph_id, data);
        }
      });
  });

  $('.grid-stack').on('change', function (e, items) {
            var res = _.map($('.grid-stack .grid-stack-item:visible'), function (el) {
            el = $(el);
            var node = el.data('_gridstack_node');
            return {
                widget_id: el.attr('id'),
                fn: el.attr('data-custom-fn'),
                id: el.attr('data-custom-id'),
                x: node.x,
                y: node.y,
                width: node.width,
                height: node.height
            };
        });
        $.ajax({
          url: 'modules/dashboard_widgets/index.php?fn=dashboard_user_update',
          type: 'POST',
          data: JSON.stringify(res),
          contentType: 'application/json; charset=utf-8',
          beforeSend: function() {
            //do your thing before send
          },
          success: function (response) {
              //your success code
          },
          error: function () {
              //your error code
          }
        });
  });


  $(function () {
      $('.grid-stack').gridstack({
          width: 12,
          always_show_resize_handle: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
          resizable: {
              handles: 'e, se, s, sw, w'
          }
      });
  });
  $(function () {
      var options = {
          cell_height: 100,
          vertical_margin: 20
      };
      $('.grid-stack').gridstack(options);
  });

  var func = $('.functions').map(function(){
    return $(this).val()
  }).get();

  var arr = func.toString().split(',');
//var resp = process_information(null, arr[0], 'dashboard_widgets', null, null, null, null, 'return_response');

$.each(arr, function( key, value ) {
  var sp = value.split('#');
  var fn = sp[0];
  var id = sp[1];
  var graph_id = '#'+fn+'__'+id;
  $.ajax({
    url: 'modules/dashboard_widgets/index.php?fn='+fn+'&id='+id,
    type: 'GET',
    beforeSend: function() {
      $(graph_id).html('<div class="naturalwidget-loading"></div>');
    },
    success: function(data) {
      router(fn, graph_id, data);
    }
  });
});
}

/**
 * Dashboard Setup
 */
function dashboard_setup(id, fn) {
    if($('#input_widget_' + id).is(":checked") == true) {
      $.ajax({
        url: 'modules/dashboard_widgets/index.php?fn='+fn+'&id=' + id,
        type: 'GET',
        success: function(data) {
           var grid = $('.grid-stack').data('gridstack');
           grid.add_widget($(data['html']), data['x'], data['y'], data['width'], data['height']);

           $(".button-min").click(function() {
             $( this ).children().toggleClass( "fa-minus fa-plus" );
             $( this ).parents( ".grid-stack-item:first" ).find( ".graph" ).toggle();
             $( this ).parents( ".grid-stack-item:first" ).setAttribute("data-gs-height", "1");
           });

           $( ".button-close" ).click(function() {
             dashboard_delete_widget($( this ).parents( ".grid-stack-item:first" ).attr("id"));
           });

           router(fn, '#'+fn+'__'+id, data);
        }
      });
    }else{
      var grid = $('.grid-stack').data('gridstack');
      grid.remove_widget('#widget__'+fn+'__'+id, true);
    }
    $(".button-min").click(function() {
      $( this ).children().toggleClass( "fa-minus fa-plus" );
      $( this ).parents( ".grid-stack-item:first" ).find( ".graph" ).toggle();
      $( this ).parents( ".grid-stack-item:first" ).setAttribute("data-gs-height", "1");
    });
    $( ".button-close" ).click(function() {
      dashboard_delete_widget($( this ).parents( ".grid-stack-item:first" ).attr("id"));
    });

}


/**
 * Dashboard Delete Widget
 */
function dashboard_delete_widget(widget) {
    var grid = $('.grid-stack').data('gridstack');
    grid.remove_widget('#' + widget, true);
    var sp = widget.split('__');
    $('#input_widget_'+sp[2]).attr('checked', false);
}

/**
 * Dashboard Setup Menu
 */
$(document.body).on('click', '#demo-setting', function () {
  $('.demo').toggleClass( "activate" );
});

/*
* Functions to plot the charts into the widgets
*/
function render_widget_graph(graph_id, chart_data){
  // donut
  $(graph_id).html('');
  if ($(graph_id).length && (chart_data["data"] != null)) {
    switch(chart_data["graph_type"]) {
    case 'Line':
          Morris.Line({
            element : graph_id.substring(1),
            data : chart_data["data"],
            xkey : chart_data["graph_options"]["xkey"],
            ykeys : chart_data["graph_options"]["ykeys"],
            labels : chart_data["graph_options"]["labels"],
            lineColors: chart_data["graph_options"]["lineColors"],
            lineWidth: parseInt(chart_data["graph_options"]["lineWidth"]),
            pointSize: parseInt(chart_data["graph_options"]["pointSize"]),
            pointFillColors: chart_data["graph_options"]["pointFillColors"],
            pointStrokeColors: chart_data["graph_options"]["pointStrokeColors"],
            smooth: chart_data["graph_options"]["smooth"],
            postUnits: chart_data["graph_options"]["postUnits"],
            preUnits: chart_data["graph_options"]["preUnits"],
            axes : chart_data["graph_options"]["axes"],
            grid : chart_data["graph_options"]["grid"],
            gridTextColor : chart_data["graph_options"]["gridTextColor"],
            gridTextSize : chart_data["graph_options"]["gridTextSize"],
            gridTextFamily : chart_data["graph_options"]["gridTextFamily"],
            gridTextWeight : chart_data["graph_options"]["gridTextWeight"],
            fillOpacity : chart_data["graph_options"]["fillOpacity"]
          });
          break;
    case 'Donut':
          //we will calculate the percentage
          var total = 0;
          var graph_data = [];
          $.each(chart_data['data'], function( key, value ) {
             total += parseInt(value['value']);
          });
          $.each(chart_data['data'], function( key, value ) {
            value['value'] = Math.round((100 * parseInt(value['value']))/total);
          });
          //porcentage calculated
          Morris.Donut({
            element : graph_id.substring(1),
            resize : true,
            data : chart_data["data"],
            formatter : function(x) {
              return x + "%"
            }
          });
          break;
     case 'Bar':
          Morris.Bar({
            element : graph_id.substring(1),
            data : chart_data["data"],
            xkey : chart_data["graph_options"]["xkey"],
            ykeys : chart_data["graph_options"]["ykeys"],
            labels : chart_data["graph_options"]["labels"],
            barColors: chart_data["graph_options"]["barColors"],
            stacked: chart_data["graph_options"]["stacked"],
            axes : chart_data["graph_options"]["axes"],
            grid : chart_data["graph_options"]["grid"],
            gridTextColor : chart_data["graph_options"]["gridTextColor"],
            gridTextSize : chart_data["graph_options"]["gridTextSize"],
            gridTextFamily : chart_data["graph_options"]["gridTextFamily"],
            gridTextWeight : chart_data["graph_options"]["gridTextWeight"]
          });
          break;
      case 'Area':
            Morris.Area({
              element : graph_id.substring(1),
              data : chart_data["data"],
              xkey : chart_data["graph_options"]["xkey"],
              ykeys : chart_data["graph_options"]["ykeys"],
              labels : chart_data["graph_options"]["labels"],
              lineColors: chart_data["graph_options"]["lineColors"],
              lineWidth: parseInt(chart_data["graph_options"]["lineWidth"]),
              pointSize: parseInt(chart_data["graph_options"]["pointSize"]),
              pointFillColors: chart_data["graph_options"]["pointFillColors"],
              pointStrokeColors: chart_data["graph_options"]["pointStrokeColors"],
              smooth: chart_data["graph_options"]["smooth"],
              postUnits: chart_data["graph_options"]["postUnits"],
              preUnits: chart_data["graph_options"]["preUnits"],
              axes : chart_data["graph_options"]["axes"],
              grid : chart_data["graph_options"]["grid"],
              gridTextColor : chart_data["graph_options"]["gridTextColor"],
              gridTextSize : chart_data["graph_options"]["gridTextSize"],
              gridTextFamily : chart_data["graph_options"]["gridTextFamily"],
              gridTextWeight : chart_data["graph_options"]["gridTextWeight"],
              fillOpacity : chart_data["graph_options"]["fillOpacity"]
            });
            break;
      case 'Template':
           $(graph_id).html(chart_data["data"]);
            break;
    default:
          $(graph_id).html(chart_data["response_nodata"]);
    }
    if(chart_data["update_seconds"] > 0){
      $(graph_id).delay(chart_data["update_seconds"]).queue(function() {
        $.ajax({
          url: 'modules/dashboard_widgets/index.php?fn='+chart_data["fn"]+'&id='+chart_data["id"],
          type: 'GET',
          success: function(data) {
            $(graph_id).dequeue();
            router(chart_data["fn"], graph_id, data);
          }
        });
      });
    }
  }else {
    $(graph_id).html(chart_data["response_nodata"]);
  }

}






/*
* This is the function where you can add your custom graphic
*/
function router(fn, graph_id, data){
  switch (fn) {
    case 'render_widget_graph':
      render_widget_graph(graph_id, data);
      break;
    case 'custom_graph_example':
      custom_graph_example(graph_id, data);
      break;
  }
}

/*
* Below here you can add your custom graphics
*/


function custom_graph_example(graph_id, chart_data){
  $(graph_id).html('');
  // bar graph color
  if ($(graph_id).length) {
    Morris.Bar({
      resize : true,
      element : graph_id.substring(1),
      data : chart_data['data'],
      xkey : 'x',
      ykeys : ['y'],
      labels : ['Y'],
      barColors : function(row, series, type) {
        if (type === 'bar') {
          var red = Math.ceil(150 * row.y / this.ymax);
          return 'rgb(' + red + ',0,0)';
        } else {
          return '#000';
        }
      }
    });
  }
}
// End of plot functions
