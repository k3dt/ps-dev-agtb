
nv.models.funnelChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var funnel = nv.models.funnel()
    , yAxis = nv.models.axis()
    , legend = nv.models.legend()
    ;

  var margin = {top: 30, right: 20, bottom: 20, left: 60}
    , width = null
    , height = null
    , color = nv.utils.defaultColor()
    , showLegend = true
    , showTitle = false
    , reduceXTicks = false // if false a tick will show for every data point
    , tooltips = true
    , tooltip = function(key, x, y, e, graph) {
        return '<h3>' + key + " - " + x + '</h3>' +
               '<p>' +  y + '</p>'
      }
    , x //can be accessed via chart.xScale()
    , y //can be accessed via chart.yScale()
    , noData = "No Data Available."
    , dispatch = d3.dispatch('tooltipShow', 'tooltipHide')
    ;

  yAxis
    .orient('left')
    .tickFormat(d3.format(',.1f'))
    ;

  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var showTooltip = function(e, offsetElement, properties) {
    var left = e.pos[0] + ( (offsetElement && offsetElement.offsetLeft) || 0 ),
        top = e.pos[1] + ( (offsetElement && offsetElement.offsetTop) || 0),
        x = (e.point.y * 100 / properties.total).toFixed(1)
        y = ( yAxis ).tickFormat()( funnel.y()(e.point, e.pointIndex) ),
        content = tooltip(e.series.key, x, y, e, chart);

    nv.tooltip.show([left, top], content, e.value < 0 ? 'n' : 's', null, offsetElement);
  };

  //============================================================


  function chart(selection) {

    selection.each(function(chartData) {

      var properties = chartData.properties
        , data = chartData.data;

      var container = d3.select(this),
          that = this;

      var availableWidth = (width  || parseInt(container.style('width')) || 960)
                             - margin.left - margin.right,
          availableHeight = (height || parseInt(container.style('height')) || 400)
                             - margin.top - margin.bottom;

      //------------------------------------------------------------
      // Display noData message if there's nothing to show.

      if (!data || !data.length || !data.filter(function(d) { return d.values.length }).length) {
        container.append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('x', availableWidth / 2)
          .attr('y', availableHeight / 2)
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle')
          .text(noData);
          return chart;
      } else {
        container.select('.nv-noData').remove();
      }

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup Scales

      x = funnel.xScale();
      //y = funnel.yScale(); //see below

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-funnelWithLegend').data([data]);
      var gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-funnelWithLegend').append('g');
      var g = wrap.select('g');

      gEnter.append('g').attr('class', 'nv-y nv-axis');
      gEnter.append('g').attr('class', 'nv-funnelWrap');

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Title & Legend

      var titleHeight = 0
        , legendHeight = 0;

      if (showLegend)
      {
        gEnter.append('g').attr('class', 'nv-legendWrap');

        legend.width(availableWidth+margin.left);

        g.select('.nv-legendWrap')
            .datum(data)
            .call(legend);

        legendHeight = legend.height() + 10;

        if ( margin.top !== legendHeight + titleHeight ) {
          margin.top = legendHeight + titleHeight;
          availableHeight = (height || parseInt(container.style('height')) || 400)
                             - margin.top - margin.bottom;
        }

        g.select('.nv-legendWrap')
            .attr('transform', 'translate('+ (-margin.left) +',' + (-margin.top) +')');
      }

      if (showTitle && properties.title )
      {
        gEnter.append('g').attr('class', 'nv-titleWrap');

        g.select('.nv-title').remove();

        g.select('.nv-titleWrap')
          .append('text')
            .attr('class', 'nv-title')
            .attr('x', 0)
            .attr('y', 0 )
            .attr('text-anchor', 'start')
            .text(properties.title)
            .attr('stroke', 'none')
            .attr('fill', 'black')
          ;

        titleHeight = parseInt( g.select('.nv-title').style('height') ) +
          parseInt( g.select('.nv-title').style('margin-top') ) +
          parseInt( g.select('.nv-title').style('margin-bottom') );

        if ( margin.top !== titleHeight + legendHeight )
        {
          margin.top = titleHeight + legendHeight;
          availableHeight = (height || parseInt(container.style('height')) || 400)
                             - margin.top - margin.bottom;
        }

        g.select('.nv-titleWrap')
            .attr('transform', 'translate(0,' + (-margin.top+parseInt( g.select('.nv-title').style('height') )) +')');
      }

      //------------------------------------------------------------


      //------------------------------------------------------------

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');


      //------------------------------------------------------------
      // Main Chart Component(s)

      funnel
        .width(availableWidth)
        .height(availableHeight)
        // .color(
        //   data.map( function(d,i) {
        //     return d.color || color(d, i);
        //   }).filter( function(d,i) { return !data[i].disabled } )
        // );


      var funnelWrap = g.select('.nv-funnelWrap')
          .datum( data.filter(function(d) { return !d.disabled }) );

      d3.transition(funnelWrap).call(funnel);

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup Scales (again, not sure why it has to be here and not above?)

      var series1 = [{x:0,y:0}];
      var series2 = data.filter(
          function(d) {
            return !d.disabled
          }
        ).map(
          function(d) {
            return d.values.map(
              function(d,i) {
                return { x: d.x, y: d.y0+d.y }
              }
            )
          }
      );
      var tickData = d3.merge( series1.concat(series2) );

      // remap and flatten the data for use in calculating the scales' domains
      var minmax = d3.extent( tickData , function(d) { return d.y } );
      var aTicks = d3.merge( tickData ).map( function(d) { return d.y } );

      y = d3.scale.linear().domain(minmax).range([availableHeight,0]);

      yScale = d3.scale.quantile()
                 .domain(aTicks)
                 .range(aTicks.map( function(d){ return y(d) } ));

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup Axes

      var w = availableHeight/1.1
        , r = ( ( (w/8) - w ) / 2 ) / availableHeight
        , c = availableWidth/2
        ;

      var yAxis = d3.svg.axis()
            .scale(yScale)
            .orient('left')
            .tickSize( -availableWidth/2, 0)
            .tickValues(aTicks)
            .tickFormat( function(d,i) {
              return '$' + d + 'K';
            })
          ;

      d3.transition(g.select('.nv-y.nv-axis'))
        .call(yAxis)
          .style('stroke-width', '2')
          .selectAll('line')
            .attr('x2', function(d,i){ return aTicks[i] ? ( c + ( r * y(aTicks[i]) ) + w/2 + 60 ) : 0 } );

      //------------------------------------------------------------


      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      legend.dispatch.on('legendClick', function(d,i) {
        d.disabled = !d.disabled;

        if (!data.filter(function(d) { return !d.disabled }).length) {
          data.map(function(d) {
            d.disabled = false;
            wrap.selectAll('.nv-series').classed('disabled', false);
            return d;
          });
        }

        selection.transition().call(chart);
      });

      dispatch.on('tooltipShow', function(e) {
        if (tooltips) showTooltip(e, that.parentNode, properties)
      });

      //============================================================

      //TODO: decide if this makes sense to add into all the models for ease of updating (updating without needing the selection)
      chart.update = function() { selection.transition().call(chart) };
      chart.container = this; // I need a reference to the container in order to have outside code check if the chart is visible or not

    });

    return chart;
  }


  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  funnel.dispatch.on('elementMouseover.tooltip', function(e) {
    e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
    dispatch.tooltipShow(e);
  });

  funnel.dispatch.on('elementMouseout.tooltip', function(e) {
    dispatch.tooltipHide(e);
  });
  dispatch.on('tooltipHide', function() {
    if (tooltips) nv.tooltip.cleanup();
  });

  //============================================================


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.legend = legend;
  chart.funnel = funnel;
  chart.yAxis = yAxis;

  d3.rebind(chart, funnel, 'x', 'y', 'xDomain', 'yDomain', 'forceX', 'forceY', 'clipEdge', 'id', 'delay', 'color', 'gradient', 'useClass');

  chart.colorData = function(_) {
    if (arguments[0] === 'graduated')
    {
      var c1 = arguments[1].c1
        , c2 = arguments[1].c2
        , l = arguments[1].l;
      var color = function (d,i) { return d3.interpolateHsl( d3.rgb(c1), d3.rgb(c2) )(i/l) };
    }
    else if (_ === 'class')
    {
      chart.useClass(true);
      legend.useClass(true);
      var color = function (d,i) { return 'inherit' };
    }
    else
    {
      var color = nv.utils.defaultColor();
    }

    legend.color(color);
    funnel.color(color);

    return chart;
  };

  chart.colorFill = function(_) {
    if (_ === 'gradient')
    {
      var fill = function (d,i) { return chart.gradient()(d,i) };
    }
    else
    {
      var fill = chart.color();
    }

    funnel.fill(fill);

    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) return getX;
    getX = _;
    lines.x(_);
    funnelWrap.x(_);
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) return getY;
    getY = _;
    lines.y(_);
    funnel.y(_);
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin = _;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) return width;
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) return height;
    height = _;
    return chart;
  };

  chart.color = function(_) {
    if (!arguments.length) return color;
    color = nv.utils.getColor(_);
    legend.color(color);
    return chart;
  };

  chart.showLegend = function(_) {
    if (!arguments.length) return showLegend;
    showLegend = _;
    return chart;
  };

  chart.showTitle = function(_) {
    if (!arguments.length) return showTitle;
    showTitle = _;
    return chart;
  };

  chart.tooltip = function(_) {
    if (!arguments.length) return tooltip;
    tooltip = _;
    return chart;
  };

  chart.tooltips = function(_) {
    if (!arguments.length) return tooltips;
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function(_) {
    if (!arguments.length) return tooltip;
    tooltip = _;
    return chart;
  };

  chart.noData = function(_) {
    if (!arguments.length) return noData;
    noData = _;
    return chart;
  };

  //============================================================


  return chart;
}
