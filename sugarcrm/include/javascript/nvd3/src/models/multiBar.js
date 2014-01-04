nv.models.multiBar = function () {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 0, right: 0, bottom: 0, left: 0}
    , width = 960
    , height = 500
    , x = d3.scale.ordinal()
    , y = d3.scale.linear()
    , id = Math.floor(Math.random() * 10000) //Create semi-unique ID in case user doesn't select one
    , getX = function (d) { return d.x; }
    , getY = function (d) { return d.y; }
    , forceY = [0] // 0 is forced by default.. this makes sense for the majority of bar graphs... user can always do chart.forceY([]) to remove
    , stacked = false
    , barColor = null // adding the ability to set the color for each rather than the whole group
    , disabled // used in conjunction with barColor to communicate from multiBarHorizontalChart what series are disabled
    , clipEdge = true
    , showValues = false
    , valueFormat = d3.format(',.2f')
    , withLine = false
    , vertical = true
    , delay = 200
    , xDomain
    , yDomain
    , color = nv.utils.defaultColor()
    , fill = color
    , classes = function (d,i) { return 'nv-group nv-series-'+ i; }
    , dispatch = d3.dispatch('chartClick', 'elementClick', 'elementDblClick', 'elementMouseover', 'elementMouseout', 'elementMousemove')
    ;

  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var x0, y0 //used to store previous scales
      ;

  //============================================================

  function chart(selection) {
    selection.each(function (data) {
      var availableWidth = width - margin.left - margin.right
        , availableHeight = height - margin.top - margin.bottom
        , container = d3.select(this)
        , orientation = vertical ? 'vertical' : 'horizontal'
        , limX = vertical ? 'height' : 'width'
        , limY = vertical ? 'width' : 'height'
        , limDimX = vertical ? availableWidth : availableHeight
        , limDimY = vertical ? availableHeight : availableWidth
        , xVal = vertical ? 'x' : 'y'
        , yVal = vertical ? 'y' : 'x'
        , valuePadding = 0
        ;

      if (stacked) {
        data = d3.layout.stack()
                 .offset('zero')
                 .values(function (d) { return d.values; })
                 .y(getY)
                 (data);
      }

      //add series index to each data point for reference
      data = data.map(function (series, i) {
        series.values = series.values.map(function (point) {
          point.series = i;
          return point;
        });
        return series;
      });

      //------------------------------------------------------------
      // HACK for negative value stacking
      if (stacked) {
        data[0].values.map(function (d,i) {
          var posBase = 0, negBase = 0;
          data.map(function (d) {
            var f = d.values[i];
            f.size = Math.abs(f.y);
            if (f.y < 0) {
              f.y1 = negBase - (vertical ? 0 : f.size);
              negBase = negBase - f.size;
            } else {
              f.y1 = posBase + (vertical ? f.size : 0);
              posBase = posBase + f.size;
            }
          });
        });
      }

      //------------------------------------------------------------
      // Setup Scales

      // remap and flatten the data for use in calculating the scales' domains
      var seriesData = (xDomain && yDomain) ? [] : // if we know xDomain and yDomain, no need to calculate
            data.map(function (d) {
              return d.values.map(function (d,i) {
                return { x: getX(d,i), y: getY(d,i), y0: d.y0, y1: d.y1 };
              });
            });

      var bw = 48 * (stacked?1:data.length) + 16;
      var op = Math.max(0.25,(limDimX - data[0].values.length*bw + 16) / (2*bw));

      if (!withLine) {
        /*TODO: used in reports to keep bars from being too wide
          breaks pareto chart, so need to update line to adjust x position */
        x .domain(xDomain || d3.merge(seriesData).map(function (d) { return d.x; }))
          .rangeRoundBands([0, limDimX], 0.25, op);
      } else {
        x .domain(xDomain || d3.merge(seriesData).map(function (d) { return d.x; }))
          .rangeBands([0, limDimX], 0.3);
      }

      y .domain(yDomain || d3.extent(d3.merge(seriesData).map(function (d) {
          if (vertical) {
            return stacked ? (d.y > 0 ? d.y1 : d.y1 + d.y ) : d.y;
          } else {
            return stacked ? (d.y > 0 ? d.y1 + d.y : d.y1 ) : d.y;
          }
        }).concat(forceY)))
        .range(vertical ? [availableHeight, 0] : [0, availableWidth]);

      x0 = x0 || x;
      y0 = y0 || y;

      //------------------------------------------------------------
      // recalculate y.range if show values
      if (showValues && !stacked) {
        valuePadding = nv.utils.maxStringSetLength(
            d3.merge(seriesData).map(function (d) { return d.y; }),
            container,
            valueFormat
          );
        valuePadding += 4;
        if (vertical) {
          y.range([limDimY - (y.domain()[0] < 0 ? valuePadding : 0), (y.domain()[1] > 0 ? valuePadding : 0)]);
        } else {
          y.range([(y.domain()[0] < 0 ? valuePadding : 0), limDimY - (y.domain()[1] > 0 ? valuePadding : 0) ]);
        }
      }

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-multibar' + (vertical ? '' : 'Horizontal')).data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-multibar' + (vertical ? '' : 'Horizontal'));
      var defsEnter = wrapEnter.append('defs');
      var gEnter = wrapEnter.append('g');
      var g = wrap.select('g');

      //set up the gradient constructor function
      chart.gradient = function (d,i,p) {
        return nv.utils.colorLinearGradient( d, id +'-'+ i, p, color(d,i), wrap.select('defs') );
      };

      gEnter.append('g').attr('class', 'nv-groups');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------

      defsEnter.append('clipPath')
          .attr('id', 'nv-edge-clip-' + id)
        .append('rect');
      wrap.select('#nv-edge-clip-' + id + ' rect')
          .attr('width', availableWidth)
          .attr('height', availableHeight);

      g .attr('clip-path', clipEdge ? 'url(#nv-edge-clip-' + id + ')' : '');

      //------------------------------------------------------------

      var groups = wrap.select('.nv-groups').selectAll('.nv-group')
          .data(function (d) { return d; }, function (d) { return d.key; });
      groups.enter().append('g')
          .style('stroke-opacity', 1e-6)
          .style('fill-opacity', 1e-6);
      groups.exit()
          .style('stroke-opacity', 1e-6)
          .style('fill-opacity', 1e-6)
        .selectAll('g.nv-bar')
          .attr('y', function (d) { return stacked ? y0(d.y0) : y0(0); })
          .attr(limY, 0)
          .remove();
      groups
        .attr('class', function (d,i) { return this.getAttribute('class') || classes(d,i); })
        .classed('hover', function (d) { return d.hover; })
        .attr('fill', function (d,i){ return this.getAttribute('fill') || fill(d,i); })
        .attr('stroke', function (d,i){ return this.getAttribute('fill') || fill(d,i); })
        .style('stroke-opacity', 1)
        .style('fill-opacity', 1);


      var bars = groups.selectAll('g.nv-bar')
            .data(function (d) { return d.values; });

      bars.exit().remove();

      var barsEnter = bars.enter().append('g')
            .attr('class', function (d,i) { return getY(d,i) < 0 ? 'nv-bar negative' : 'nv-bar positive'; })
            .attr('transform', function (d,i,j) {
              var trans = {
                x: stacked ? 0 : (j * x.rangeBand() / data.length ) + x(getX(d,i)),
                y: y0(stacked ? d.y0 : 0)
              };
              return 'translate(' + trans[xVal] + ',' + trans[yVal] + ')';
            });

      barsEnter.append('rect')
        .attr(limY, 0)
        .attr(limX, x.rangeBand() / (stacked ? 1 : data.length) );

      bars
        .on('mouseover', function (d,i) { //TODO: figure out why j works above, but not here
          d3.select(this).classed('hover', true);
          dispatch.elementMouseover({
            value: getY(d,i),
            point: d,
            series: data[d.series],
            pos: [d3.event.pageX, d3.event.pageY],
            pointIndex: i,
            seriesIndex: d.series,
            e: d3.event
          });
        })
        .on('mouseout', function (d,i) {
          d3.select(this).classed('hover', false);
          dispatch.elementMouseout({
            value: getY(d,i),
            point: d,
            series: data[d.series],
            pointIndex: i,
            seriesIndex: d.series,
            e: d3.event
          });
        })
        .on('mousemove', function (d,i){
          dispatch.elementMousemove({
            point: d,
            pointIndex: i,
            pos: [d3.event.pageX, d3.event.pageY],
            id: id
          });
        })
        .on('click', function (d,i) {
          dispatch.elementClick({
            value: getY(d,i),
            point: d,
            series: data[d.series],
            pos: [x(getX(d,i)) + (x.rangeBand() * (stacked ? data.length / 2 : d.series + 0.5) / data.length), y(getY(d,i) + (stacked ? d.y0 : 0))],  // TODO: Figure out why the value appears to be shifted
            pointIndex: i,
            seriesIndex: d.series,
            e: d3.event
          });
          d3.event.stopPropagation();
        })
        .on('dblclick', function (d,i) {
          dispatch.elementDblClick({
            value: getY(d,i),
            point: d,
            series: data[d.series],
            pos: [x(getX(d,i)) + (x.rangeBand() * (stacked ? data.length / 2 : d.series + 0.5) / data.length), y(getY(d,i) + (stacked ? d.y0 : 0))],  // TODO: Figure out why the value appears to be shifted
            pointIndex: i,
            seriesIndex: d.series,
            e: d3.event
          });
          d3.event.stopPropagation();
        });


      barsEnter.append('text');

      if (showValues && !stacked) {
        bars.select('text')
          .attr('text-anchor', function (d,i) { return getY(d,i) < 0 ? 'end' : 'start'; })
          .attr('x', function (d,i) {
            if (!vertical) {
              return getY(d,i) < 0 ? -4 : y(getY(d,i)) - y(0) + 4;
            } else {
              return getY(d,i) < 0 ? y(0) - y(getY(d,i)) - 4 : 4;
            }
          })
          .attr('y', x.rangeBand() / data.length / 2)
          .attr('dy', '.45em')
          .attr('transform', 'rotate(' + (vertical ? -90 : 0) + ' 0,0)')
          .text(function (d,i) { return valueFormat(getY(d,i)); });
      } else {
        bars.selectAll('text').text('');
      }

      bars
        .attr('class', function (d,i) { return getY(d,i) < 0 ? 'nv-bar negative' : 'nv-bar positive'; });

      if (barColor) {
        if (!disabled) {
          disabled = data.map(function () { return true; });
        }
        bars
          //.style('fill', barColor)
          //.style('stroke', barColor)
          //.style('fill', function (d,i,j) { return d3.rgb(barColor(d,i)).darker(j).toString(); })
          //.style('stroke', function (d,i,j) { return d3.rgb(barColor(d,i)).darker(j).toString(); })
          .style('fill', function (d,i,j) {
            return d3.rgb(barColor(d,i)).darker(disabled.map(function (d,i) { return i; }).filter(function (d,i){ return !disabled[i]; })[j]).toString();
          })
          .style('stroke', function (d,i,j) {
            return d3.rgb(barColor(d,i)).darker(disabled.map(function (d,i) { return i; }).filter(function (d,i){ return !disabled[i]; })[j]).toString();
          });
      }


      if (stacked) {
        bars
          .attr('transform', function (d,i) {
            var trans = {
              x: x(getX(d,i)),
              y: y(d.y1)
            };
            return 'translate(' + trans[xVal] + ',' + trans[yVal] + ')';
          })
          .select('rect')
            .attr('x', function (d,i) {
              return getY(d,i) < 0 ? 0 : 1;
            })
            .attr(limX, function (d,i) {
              return Math.max(Math.abs(y(getY(d,i) + d.y0) - y(d.y0)) - 1, 0);
            })
            .attr(limY, x.rangeBand());
      } else {
        bars
          .attr('transform', function (d,i) {
            var trans = {
              x: d.series * x.rangeBand() / data.length + x(getX(d,i)),
              y: getY(d,i) < 0 ? (vertical ? y(0) : y(getY(d,i))) : (vertical ? y(getY(d,i)) : y(0))
            };
            return 'translate(' + trans[xVal] + ',' + trans[yVal] + ')';
          })
          .select('rect')
            .attr('x', function (d,i) {
              return getY(d,i) < 0 ? 0 : 2;
            })
            .attr(limX, function (d,i) {
              return Math.max(Math.abs(y(getY(d,i)) - y(0)) - 2, 0) || 0;
            })
            .attr(limY, x.rangeBand() / data.length );
      }

      //store old scales for use in transitions on update
      x0 = x.copy();
      y0 = y.copy();

    });

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  chart.dispatch = dispatch;

  chart.color = function (_) {
    if (!arguments.length) return color;
    color = _;
    return chart;
  };
  chart.fill = function (_) {
    if (!arguments.length) return fill;
    fill = _;
    return chart;
  };
  chart.classes = function (_) {
    if (!arguments.length) return classes;
    classes = _;
    return chart;
  };
  chart.gradient = function (_) {
    if (!arguments.length) return gradient;
    gradient = _;
    return chart;
  };

  chart.x = function (_) {
    if (!arguments.length) return getX;
    getX = _;
    return chart;
  };

  chart.y = function (_) {
    if (!arguments.length) return getY;
    getY = _;
    return chart;
  };

  chart.margin = function (_) {
    if (!arguments.length) return margin;
    margin.top    = typeof _.top    != 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  != 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom != 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   != 'undefined' ? _.left   : margin.left;
    return chart;
  };

  chart.width = function (_) {
    if (!arguments.length) return width;
    width = _;
    return chart;
  };

  chart.height = function (_) {
    if (!arguments.length) return height;
    height = _;
    return chart;
  };

  chart.xScale = function (_) {
    if (!arguments.length) return x;
    x = _;
    return chart;
  };

  chart.yScale = function (_) {
    if (!arguments.length) return y;
    y = _;
    return chart;
  };

  chart.xDomain = function (_) {
    if (!arguments.length) return xDomain;
    xDomain = _;
    return chart;
  };

  chart.yDomain = function (_) {
    if (!arguments.length) return yDomain;
    yDomain = _;
    return chart;
  };

  chart.forceY = function (_) {
    if (!arguments.length) return forceY;
    forceY = _;
    return chart;
  };

  chart.stacked = function (_) {
    if (!arguments.length) return stacked;
    stacked = _;
    return chart;
  };

  chart.clipEdge = function (_) {
    if (!arguments.length) return clipEdge;
    clipEdge = _;
    return chart;
  };

  chart.barColor = function (_) {
    if (!arguments.length) return barColor;
    barColor = nv.utils.getColor(_);
    return chart;
  };

  chart.disabled = function (_) {
    if (!arguments.length) return disabled;
    disabled = _;
    return chart;
  };

  chart.id = function (_) {
    if (!arguments.length) return id;
    id = _;
    return chart;
  };

  chart.delay = function (_) {
    if (!arguments.length) return delay;
    delay = _;
    return chart;
  };

  chart.showValues = function (_) {
    if (!arguments.length) return showValues;
    showValues = _;
    return chart;
  };

  chart.valueFormat= function (_) {
    if (!arguments.length) return valueFormat;
    valueFormat = _;
    return chart;
  };

  chart.withLine = function (_) {
    if (!arguments.length) return withLine;
    withLine = _;
    return chart;
  };

  chart.vertical = function (_) {
    if (!arguments.length) return vertical;
    vertical = _;
    return chart;
  };

  //============================================================


  return chart;
};
