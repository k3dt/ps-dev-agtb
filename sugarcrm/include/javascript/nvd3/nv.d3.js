(function(){

var nv = window.nv || {};

nv.version = '0.0.1a';
nv.dev = false; //set false when in production

window.nv = nv;

nv.tooltip = {}; // For the tooltip system
nv.utils = {}; // Utility subsystem
nv.models = {}; //stores all the possible models/components
nv.charts = {}; //stores all the ready to use charts
nv.graphs = []; //stores all the graphs currently on the page
nv.logs = {}; //stores some statistics and potential error messages

nv.dispatch = d3.dispatch('render_start', 'render_end');

// *************************************************************************
//  Development render timers - disabled if dev = false

if (nv.dev) {
  nv.dispatch.on('render_start', function(e) {
    nv.logs.startTime = +new Date();
  });

  nv.dispatch.on('render_end', function(e) {
    nv.logs.endTime = +new Date();
    nv.logs.totalTime = nv.logs.endTime - nv.logs.startTime;
    nv.log('total', nv.logs.totalTime); // used for development, to keep track of graph generation times
  });
}

// ********************************************
//  Public Core NV functions

// Logs all arguments, and returns the last so you can test things in place
nv.log = function() {
  if (nv.dev && console.log && console.log.apply)
    console.log.apply(console, arguments)
  else if (nv.dev && console.log && Function.prototype.bind) {
    var log = Function.prototype.bind.call(console.log, console);
    log.apply(console, arguments);
  }
  return arguments[arguments.length - 1];
};


nv.render = function render(step) {
  step = step || 1; // number of graphs to generate in each timeout loop

  nv.render.active = true;
  nv.dispatch.render_start();

  setTimeout(function() {
    var chart, graph;

    for (var i = 0; i < step && (graph = nv.render.queue[i]); i++) {
      chart = graph.generate();
      if (typeof graph.callback == typeof(Function)) graph.callback(chart);
      nv.graphs.push(chart);
    }

    nv.render.queue.splice(0, i);

    if (nv.render.queue.length) setTimeout(arguments.callee, 0);
    else { nv.render.active = false; nv.dispatch.render_end(); }
  }, 0);
};

nv.render.active = false;
nv.render.queue = [];

nv.addGraph = function(obj) {
  if (typeof arguments[0] === typeof(Function))
    obj = {generate: arguments[0], callback: arguments[1]};

  nv.render.queue.push(obj);

  if (!nv.render.active) nv.render();
};

nv.identity = function(d) { return d; };

nv.strip = function(s) { return s.replace(/(\s|&)/g,''); };

function daysInMonth(month,year) {
  return (new Date(year, month+1, 0)).getDate();
}

function d3_time_range(floor, step, number) {
  return function(t0, t1, dt) {
    var time = floor(t0), times = [];
    if (time < t0) step(time);
    if (dt > 1) {
      while (time < t1) {
        var date = new Date(+time);
        if ((number(date) % dt === 0)) times.push(date);
        step(time);
      }
    } else {
      while (time < t1) { times.push(new Date(+time)); step(time); }
    }
    return times;
  };
}

d3.time.monthEnd = function(date) {
  return new Date(date.getFullYear(), date.getMonth(), 0);
};

d3.time.monthEnds = d3_time_range(d3.time.monthEnd, function(date) {
    date.setUTCDate(date.getUTCDate() + 1);
    date.setDate(daysInMonth(date.getMonth() + 1, date.getFullYear()));
  }, function(date) {
    return date.getMonth();
  }
);

/* Create static d3 axis for printing */
d3.svg.axisStatic = function() {
    var scale = d3.scale.linear(),
        orient = d3_svg_axisDefaultOrient,
        tickMajorSize = 6,
        tickMinorSize = 6,
        tickEndSize = 6,
        tickPadding = 3,
        tickArguments_ = [10],
        tickValues = null,
        tickFormat_, tickSubdivide = 0;
    var d3_svg_axisDefaultOrient = "bottom",
        d3_svg_axisOrients = {
            top: 1,
            right: 1,
            bottom: 1,
            left: 1
        };

    function d3_scaleExtent(domain) {
        var start = domain[0],
            stop = domain[domain.length - 1];
        return start < stop ? [start, stop] : [stop, start];
    }

    function d3_scaleRange(scale) {
        return scale.rangeExtent ? scale.rangeExtent() : d3_scaleExtent(scale.range());
    }

    function d3_svg_axisX(selection, x) {
        selection.attr("transform", function(d) {
            return "translate(" + x(d) + ",0)";
        });
    }

    function d3_svg_axisY(selection, y) {
        selection.attr("transform", function(d) {
            return "translate(0," + y(d) + ")";
        });
    }

    function d3_svg_axisSubdivide(scale, ticks, m) {
        subticks = [];
        if (m && ticks.length > 1) {
            var extent = d3_scaleExtent(scale.domain()),
                subticks, i = -1,
                n = ticks.length,
                d = (ticks[1] - ticks[0]) / ++m,
                j, v;
            while (++i < n) {
                for (j = m; --j > 0;) {
                    if ((v = +ticks[i] - j * d) >= extent[0]) {
                        subticks.push(v);
                    }
                }
            }
            for (--i, j = 0; ++j < m && (v = +ticks[i] + j * d) < extent[1];) {
                subticks.push(v);
            }
        }
        return subticks;
    }

    function axis(g) {
        g.each(function() {
            var g = d3.select(this);
            var ticks = tickValues == null ? scale.ticks ? scale.ticks.apply(scale, tickArguments_) : scale.domain() : tickValues,
                tickFormat = tickFormat_ == null ? scale.tickFormat ? scale.tickFormat.apply(scale, tickArguments_) : String : tickFormat_;
            var subticks = d3_svg_axisSubdivide(scale, ticks, tickSubdivide),
                subtick = g.selectAll(".tick.minor").data(subticks, String),
                subtickEnter = subtick.enter().insert("line", ".tick").attr("class", "tick minor").style("opacity", 1),
                subtickExit = subtick.exit().remove(),
                subtickUpdate = subtick.style("opacity", 1);
            var tick = g.selectAll(".tick.major").data(ticks, String),
                tickEnter = tick.enter().insert("g", "path").attr("class", "tick major").style("opacity", 1),
                tickExit = tick.exit().remove(),
                tickUpdate = tick.style("opacity", 1),
                tickTransform;
            var range = d3_scaleRange(scale),
                path = g.selectAll(".domain").data([0]),
                pathUpdate = (path.enter().append("path").attr("class", "domain"), path);
            var scale1 = scale.copy();

            tickEnter.append("line");
            tickEnter.append("text");

            var lineEnter = tickEnter.select("line"),
                lineUpdate = tickUpdate.select("line"),
                text = tick.select("text").text(tickFormat),
                textEnter = tickEnter.select("text"),
                textUpdate = tickUpdate.select("text");

            switch (orient) {
                case "bottom":
                    {
                        tickTransform = d3_svg_axisX;

                        subtickEnter.attr("y2", tickMinorSize);
                        subtickUpdate.attr("x2", 0).attr("y2", tickMinorSize);

                        lineEnter.attr("y2", tickMajorSize);
                        textEnter.attr("y", Math.max(tickMajorSize, 0) + tickPadding);

                        lineUpdate.attr("x2", 0).attr("y2", tickMajorSize);
                        textUpdate.attr("x", 0).attr("y", Math.max(tickMajorSize, 0) + tickPadding);

                        text.attr("dy", ".71em").style("text-anchor", "middle");

                        pathUpdate.attr("d", "M" + range[0] + "," + tickEndSize + "V0H" + range[1] + "V" + tickEndSize);
                        break;
                    }

                case "top":
                    {
                        tickTransform = d3_svg_axisX;
                        subtickEnter.attr("y2", -tickMinorSize);
                        subtickUpdate.attr("x2", 0).attr("y2", -tickMinorSize);
                        lineEnter.attr("y2", -tickMajorSize);
                        textEnter.attr("y", -(Math.max(tickMajorSize, 0) + tickPadding));
                        lineUpdate.attr("x2", 0).attr("y2", -tickMajorSize);
                        textUpdate.attr("x", 0).attr("y", -(Math.max(tickMajorSize, 0) + tickPadding));
                        text.attr("dy", "0em").style("text-anchor", "middle");
                        pathUpdate.attr("d", "M" + range[0] + "," + -tickEndSize + "V0H" + range[1] + "V" + -tickEndSize);
                        break;
                    }

                case "left":
                    {
                        tickTransform = d3_svg_axisY;
                        subtickEnter.attr("x2", -tickMinorSize);
                        subtickUpdate.attr("x2", -tickMinorSize).attr("y2", 0);
                        lineEnter.attr("x2", -tickMajorSize);
                        textEnter.attr("x", -(Math.max(tickMajorSize, 0) + tickPadding));
                        lineUpdate.attr("x2", -tickMajorSize).attr("y2", 0);
                        textUpdate.attr("x", -(Math.max(tickMajorSize, 0) + tickPadding)).attr("y", 0);
                        text.attr("dy", ".32em").style("text-anchor", "end");
                        pathUpdate.attr("d", "M" + -tickEndSize + "," + range[0] + "H0V" + range[1] + "H" + -tickEndSize);
                        break;
                    }

                case "right":
                    {
                        tickTransform = d3_svg_axisY;
                        subtickEnter.attr("x2", tickMinorSize);
                        subtickUpdate.attr("x2", tickMinorSize).attr("y2", 0);
                        lineEnter.attr("x2", tickMajorSize);
                        textEnter.attr("x", Math.max(tickMajorSize, 0) + tickPadding);
                        lineUpdate.attr("x2", tickMajorSize).attr("y2", 0);
                        textUpdate.attr("x", Math.max(tickMajorSize, 0) + tickPadding).attr("y", 0);
                        text.attr("dy", ".32em").style("text-anchor", "start");
                        pathUpdate.attr("d", "M" + tickEndSize + "," + range[0] + "H0V" + range[1] + "H" + tickEndSize);
                        break;
                    }
            }
            if (scale.ticks) {
                tickEnter.call(tickTransform, scale1);
                tickUpdate.call(tickTransform, scale1);
                tickExit.call(tickTransform, scale1);
                subtickEnter.call(tickTransform, scale1);
                subtickUpdate.call(tickTransform, scale1);
                subtickExit.call(tickTransform, scale1);
            } else {
                var dx = scale1.rangeBand() / 2,
                    x = function(d) {
                        return scale1(d) + dx;
                    };
                tickEnter.call(tickTransform, x);
                tickUpdate.call(tickTransform, x);
            }
        });
    }
    axis.scale = function(x) {
        if (!arguments.length) return scale;
        scale = x;
        return axis;
    };
    axis.orient = function(x) {
        if (!arguments.length) return orient;
        orient = x in d3_svg_axisOrients ? x + "" : d3_svg_axisDefaultOrient;
        return axis;
    };
    axis.ticks = function() {
        if (!arguments.length) return tickArguments_;
        tickArguments_ = arguments;
        return axis;
    };
    axis.tickValues = function(x) {
        if (!arguments.length) return tickValues;
        tickValues = x;
        return axis;
    };
    axis.tickFormat = function(x) {
        if (!arguments.length) return tickFormat_;
        tickFormat_ = x;
        return axis;
    };
    axis.tickSize = function(x, y) {
        if (!arguments.length) return tickMajorSize;
        var n = arguments.length - 1;
        tickMajorSize = +x;
        tickMinorSize = n > 1 ? +y : tickMajorSize;
        tickEndSize = n > 0 ? +arguments[n] : tickMajorSize;
        return axis;
    };
    axis.tickPadding = function(x) {
        if (!arguments.length) return tickPadding;
        tickPadding = +x;
        return axis;
    };
    axis.tickSubdivide = function(x) {
        if (!arguments.length) return tickSubdivide;
        tickSubdivide = +x;
        return axis;
    };
    return axis;
};


/*****
 * A no frills tooltip implementation.
 *****/


(function() {

  var nvtooltip = window.nv.tooltip = {};

  nvtooltip.show = function(pos, content, gravity, dist, container, classes) {

    var tooltip = document.createElement('div'),
        inner = document.createElement('div'),
        arrow = document.createElement('div');

    gravity = gravity || 's';
    dist = dist || 10;

    inner.className = 'tooltip-inner';
    arrow.className = 'tooltip-arrow';
    inner.innerHTML = content;
    tooltip.style.left = 0;
    tooltip.style.top = -1000;
    tooltip.style.opacity = 0;
    tooltip.className = 'tooltip xy-tooltip in';

    tooltip.appendChild(inner);
    tooltip.appendChild(arrow);
    container.appendChild(tooltip);

    nvtooltip.position(container, tooltip, pos, gravity, dist);
    tooltip.style.opacity = 1;

    return tooltip;
  };

  nvtooltip.cleanup = function() {
      // Find the tooltips, mark them for removal by this class
      // (so others cleanups won't find it)
      var tooltips = document.getElementsByClassName('tooltip'),
          purging = [],
          i = tooltips.length;

      while (i > 0) {
          i -= 1;

          if (tooltips[i].className.indexOf('xy-tooltip') !== -1) {
              purging.push(tooltips[i]);
              tooltips[i].style.transitionDelay = '0 !important';
              tooltips[i].style.opacity = 0;
              tooltips[i].className = 'nvtooltip-pending-removal out';
          }
      }

      setTimeout(function() {
          var removeMe;
          while (purging.length) {
              removeMe = purging.pop();
              removeMe.parentNode.removeChild(removeMe);
          }
      }, 500);
  };

  nvtooltip.position = function(container, tooltip, pos, gravity, dist) {
    gravity = gravity || 's';
    dist = dist || 10;
    var tooltipWidth = parseInt(tooltip.offsetWidth, 10),
        tooltipHeight = parseInt(tooltip.offsetHeight, 10),
        containerWidth = container.clientWidth,
        containerHeight = container.clientHeight,
        scrollTop = container.scrollTop,
        scrollLeft = container.scrollLeft,
        class_name = tooltip.className.replace(/ top| right| bottom| left/g, ''),
        left, top;

    function alignCenter() {
      var left = pos[0] - (tooltipWidth / 2);
      if (left < scrollLeft) left = scrollLeft;
      if (left + tooltipWidth > containerWidth) left = containerWidth - tooltipWidth;
      return left;
    }
    function alignMiddle() {
      var top = pos[1] - (tooltipHeight / 2);
      if (top < scrollTop) top = scrollTop;
      if (top + tooltipHeight > scrollTop + containerHeight) top = scrollTop - tooltipHeight;
      return top;
    }
    function arrowLeft(left) {
      var marginLeft = pos[0] - (tooltipWidth / 2) - left - 5,
          arrow = tooltip.getElementsByClassName('tooltip-arrow')[0];
      arrow.style.marginLeft = marginLeft + 'px';
    }
    function arrowTop(top) {
      var marginTop = pos[1] - (tooltipHeight / 2) - top - 5,
          arrow = tooltip.getElementsByClassName('tooltip-arrow')[0];
      arrow.style.marginTop = marginTop + 'px';
    }

    switch (gravity) {
      case 'e':
        top = alignMiddle();
        left = pos[0] - tooltipWidth - dist;
        arrowTop(top);
        if (left < scrollLeft) {
          left = pos[0] + dist;
          class_name += ' right';
        } else {
          class_name += ' left';
        }
        break;
      case 'w':
        top = alignMiddle();
        left = pos[0] + dist;
        arrowTop(top);
        if (left + tooltipWidth > containerWidth) {
          left = pos[0] - tooltipWidth - dist;
          class_name += ' left';
        } else {
          class_name += ' right';
        }
        break;
      case 'n':
        left = alignCenter();
        top = pos[1] + dist;
        arrowLeft(left);
        if (top + tooltipHeight > scrollTop + containerHeight) {
          top = pos[1] - tooltipHeight - dist;
          class_name += ' top';
        } else {
          class_name += ' bottom';
        }
        break;
      case 's':
        left = alignCenter();
        top = pos[1] - tooltipHeight - dist;
        arrowLeft(left);
        if (scrollTop > top) {
          top = pos[1] + 10;
          class_name += ' bottom';
        } else {
          class_name += ' top';
        }
        break;
    }

    tooltip.style.left = left + 'px';
    tooltip.style.top = top + 'px';

    tooltip.className = class_name;
  };

})();

nv.utils.windowSize = function () {
    // Sane defaults
    var size = {width: 640, height: 480};

    // Earlier IE uses Doc.body
    if (document.body && document.body.offsetWidth) {
        size.width = document.body.offsetWidth;
        size.height = document.body.offsetHeight;
    }

    // IE can use depending on mode it is in
    if (document.compatMode === 'CSS1Compat' &&
        document.documentElement &&
        document.documentElement.offsetWidth ) {
        size.width = document.documentElement.offsetWidth;
        size.height = document.documentElement.offsetHeight;
    }

    // Most recent browsers use
    if (window.innerWidth && window.innerHeight) {
        size.width = window.innerWidth;
        size.height = window.innerHeight;
    }
    return (size);
};

// Easy way to bind multiple functions to window.onresize
// TODO: give a way to remove a function after its bound, other than removing alkl of them
// nv.utils.windowResize = function (fun)
// {
//   var oldresize = window.onresize;

//   window.onresize = function (e) {
//     if (typeof oldresize == 'function') oldresize(e);
//     fun(e);
//   }
// }

nv.utils.windowResize = function (fun) {
  if (window.attachEvent) {
      window.attachEvent('onresize', fun);
  }
  else if (window.addEventListener) {
      window.addEventListener('resize', fun, true);
  }
  else {
      //The browser does not support Javascript event binding
  }
};

nv.utils.windowUnResize = function (fun) {
  if (window.detachEvent) {
      window.detachEvent('onresize', fun);
  }
  else if (window.removeEventListener) {
      window.removeEventListener('resize', fun, true);
  }
  else {
      //The browser does not support Javascript event binding
  }
};

nv.utils.resizeOnPrint = function (fn) {
    if (window.matchMedia) {
        var mediaQueryList = window.matchMedia('print');
        mediaQueryList.addListener(function (mql) {
            if (mql.matches) {
                fn();
            }
        });
    } else if (window.attachEvent) {
      window.attachEvent("onbeforeprint", fn);
    } else {
      window.onbeforeprint = fn;
    }
    //TODO: allow for a second call back to undo using
    //window.attachEvent("onafterprint", fn);
};

nv.utils.unResizeOnPrint = function (fn) {
    if (window.matchMedia) {
        var mediaQueryList = window.matchMedia('print');
        mediaQueryList.removeListener(function (mql) {
            if (mql.matches) {
                fn();
            }
        });
    } else if (window.detachEvent) {
      window.detachEvent("onbeforeprint", fn);
    } else {
      window.onbeforeprint = null;
    }
};

// Backwards compatible way to implement more d3-like coloring of graphs.
// If passed an array, wrap it in a function which implements the old default
// behavior
nv.utils.getColor = function (color) {
    if (!arguments.length) { return nv.utils.defaultColor(); } //if you pass in nothing, get default colors back

    if (Object.prototype.toString.call( color ) === '[object Array]') {
        return function (d, i) { return d.color || color[i % color.length]; };
    } else {
        return color;
        //can't really help it if someone passes rubbish as color
    }
};

// Default color chooser uses the index of an object as before.
nv.utils.defaultColor = function () {
    var colors = d3.scale.category20().range();
    return function (d, i) {
      return d.color || colors[i % colors.length];
    };
};


// Returns a color function that takes the result of 'getKey' for each series and
// looks for a corresponding color from the dictionary,
nv.utils.customTheme = function (dictionary, getKey, defaultColors) {
  getKey = getKey || function (series) { return series.key; }; // use default series.key if getKey is undefined
  defaultColors = defaultColors || d3.scale.category20().range(); //default color function

  var defIndex = defaultColors.length; //current default color (going in reverse)

  return function (series, index) {
    var key = getKey(series);

    if (!defIndex) defIndex = defaultColors.length; //used all the default colors, start over

    if (typeof dictionary[key] !== "undefined") {
      return (typeof dictionary[key] === "function") ? dictionary[key]() : dictionary[key];
    } else {
      return defaultColors[--defIndex]; // no match in dictionary, use default color
    }
  };
};



// From the PJAX example on d3js.org, while this is not really directly needed
// it's a very cool method for doing pjax, I may expand upon it a little bit,
// open to suggestions on anything that may be useful
nv.utils.pjax = function (links, content) {
  d3.selectAll(links).on("click", function () {
    history.pushState(this.href, this.textContent, this.href);
    load(this.href);
    d3.event.preventDefault();
  });

  function load(href) {
    d3.html(href, function (fragment) {
      var target = d3.select(content).node();
      target.parentNode.replaceChild(d3.select(fragment).select(content).node(), target);
      nv.utils.pjax(links, content);
    });
  }

  d3.select(window).on("popstate", function () {
    if (d3.event.state) { load(d3.event.state); }
  });
};

//SUGAR ADDITIONS

//gradient color
nv.utils.colorLinearGradient = function (d, i, p, c, defs) {
  var id = 'lg_gradient_' + i
    , grad = defs.select('#' + id);
  if ( grad.empty() ) {
    if (p.position === 'middle')
    {
      nv.utils.createLinearGradient( id, p, defs, [
        { 'offset': '0%',  'stop-color': d3.rgb(c).darker().toString(),  'stop-opacity': 1 },
        { 'offset': '20%', 'stop-color': d3.rgb(c).toString(), 'stop-opacity': 1 },
        { 'offset': '50%', 'stop-color': d3.rgb(c).brighter().toString(), 'stop-opacity': 1 },
        { 'offset': '80%', 'stop-color': d3.rgb(c).toString(), 'stop-opacity': 1 },
        { 'offset': '100%','stop-color': d3.rgb(c).darker().toString(),  'stop-opacity': 1 }
      ]);
    }
    else
    {
      nv.utils.createLinearGradient( id, p, defs, [
        { 'offset': '0%',  'stop-color': d3.rgb(c).darker().toString(),  'stop-opacity': 1 },
        { 'offset': '50%', 'stop-color': d3.rgb(c).toString(), 'stop-opacity': 1 },
        { 'offset': '100%','stop-color': d3.rgb(c).brighter().toString(), 'stop-opacity': 1 }
      ]);
    }
  }
  return 'url(#'+ id +')';
};

// defs:definition container
// id:dynamic id for arc
// radius:outer edge of gradient
// stops: an array of attribute objects
nv.utils.createLinearGradient = function (id, params, defs, stops) {
  var x2 = params.orientation === 'horizontal' ? '0%' : '100%'
    , y2 = params.orientation === 'horizontal' ? '100%' : '0%'
    , grad = defs.append('linearGradient')
        .attr('id', id)
        .attr('x1', '0%')
        .attr('y1', '0%')
        .attr('x2', x2 )
        .attr('y2', y2 )
        //.attr('gradientUnits', 'userSpaceOnUse')objectBoundingBox
        .attr('spreadMethod', 'pad');
  for (var i=0; i<stops.length; i+=1)
  {
    var attrs = stops[i]
      , stop = grad.append('stop');
    for (var a in attrs)
    {
      if ( attrs.hasOwnProperty(a) ) {
        stop.attr(a, attrs[a]);
      }
    }
  }
};

nv.utils.colorRadialGradient = function (d, i, p, c, defs) {
  var id = 'rg_gradient_' + i
    , grad = defs.select('#' + id);
  if ( grad.empty() )
  {
    nv.utils.createRadialGradient( id, p, defs, [
      { 'offset': p.s, 'stop-color': d3.rgb(c).brighter().toString(), 'stop-opacity': 1 },
      { 'offset': '100%','stop-color': d3.rgb(c).darker().toString(), 'stop-opacity': 1 }
    ]);
  }
  return 'url(#' + id + ')';
};

nv.utils.createRadialGradient = function (id, params, defs, stops) {
  var grad = defs.append('radialGradient')
        .attr('id', id)
        .attr('r', params.r)
        .attr('cx', params.x)
        .attr('cy', params.y)
        .attr('gradientUnits', params.u)
        .attr('spreadMethod', 'pad');
  for (var i=0; i<stops.length; i+=1) {
    var attrs = stops[i]
      , stop = grad.append('stop');
    for (var a in attrs)
    {
      if ( attrs.hasOwnProperty(a) ) {
        stop.attr(a, attrs[a]);
      }
    }
  }
};

nv.utils.getAbsoluteXY = function (element) {
  var viewportElement = document.documentElement
    , box = element.getBoundingClientRect()
    , scrollLeft = viewportElement.scrollLeft + document.body.scrollLeft
    , scrollTop = viewportElement.scrollTop + document.body.scrollTop
    , x = box.left + scrollLeft
    , y = box.top + scrollTop;

  return {'left': x, 'top': y};
};

// Creates a rectangle with rounded corners
nv.utils.roundedRectangle = function (x, y, width, height, radius) {
  return "M" + x + "," + y +
       "h" + (width - radius * 2) +
       "a" + radius + "," + radius + " 0 0 1 " + radius + "," + radius +
       "v" + (height - 2 - radius * 2) +
       "a" + radius + "," + radius + " 0 0 1 " + -radius + "," + radius +
       "h" + (radius * 2 - width) +
       "a" + -radius + "," + radius + " 0 0 1 " + -radius + "," + -radius +
       "v" + ( -height + radius * 2 + 2 ) +
       "a" + radius + "," + radius + " 0 0 1 " + radius + "," + -radius +
       "z";
};

nv.utils.dropShadow = function (id, defs, options) {
  var opt = options || {}
    , h = opt.height || '130%'
    , o = opt.offset || 2
    , b = opt.blur || 1;

  if (defs.select('#' + id).empty()) {
    var filter = defs.append('filter')
          .attr('id',id)
          .attr('height',h);
    var offset = filter.append('feOffset')
          .attr('in','SourceGraphic')
          .attr('result','offsetBlur')
          .attr('dx',o)
          .attr('dy',o); //how much to offset
    var color = filter.append('feColorMatrix')
          .attr('in','offsetBlur')
          .attr('result','matrixOut')
          .attr('type','matrix')
          .attr('values','1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 1 0');
    var blur = filter.append('feGaussianBlur')
          .attr('in','matrixOut')
          .attr('result','blurOut')
          .attr('stdDeviation',b); //stdDeviation is how much to blur
    var merge = filter.append('feMerge');
        merge.append('feMergeNode'); //this contains the offset blurred image
        merge.append('feMergeNode')
          .attr('in','SourceGraphic'); //this contains the element that the filter is applied to
  }
  return 'url(#' + id + ')';
};
// <svg xmlns="http://www.w3.org/2000/svg" version="1.1">
//   <defs>
//     <filter id="f1" x="0" y="0" width="200%" height="200%">
//       <feOffset result="offOut" in="SourceGraphic" dx="20" dy="20" />
//       <feColorMatrix result="matrixOut" in="offOut" type="matrix"
//       values="0.2 0 0 0 0 0 0.2 0 0 0 0 0 0.2 0 0 0 0 0 1 0" />
//       <feGaussianBlur result="blurOut" in="matrixOut" stdDeviation="10" />
//       <feBlend in="SourceGraphic" in2="blurOut" mode="normal" />
//     </filter>
//   </defs>
//   <rect width="90" height="90" stroke="green" stroke-width="3"
//   fill="yellow" filter="url(#f1)" />
// </svg>

nv.utils.stringSetLengths = function (_data, _container, _format) {
  var lengths = [],
      tempContainer = _container.append('g').attr('class', 'tmp-text-strings'),
      textStrings = tempContainer.selectAll('text')
        .data(_data).enter()
          .append('text')
          .text(_format);
  textStrings
    .each(function(d, i) {
      lengths.push(this.getBBox().width);
    });
  tempContainer.data([]);
  tempContainer.remove();
  return lengths;
};

nv.utils.maxStringSetLength = function (_data, _container, _format) {
  var maxLength = 0;
  _container.append('g').attr('class', 'tmp-text-strings');
  var calcContainers = _container.select('.tmp-text-strings').selectAll('text')
      .data(_data).enter()
        .append('text')
        .text(_format);
  calcContainers
    .each(function (d,i) {
      maxLength = Math.max(this.getBBox().width, maxLength);
    });
  _container.select('.tmp-text-strings').remove();
  return maxLength;
};

nv.utils.stringEllipsify = function(_string, _container, _length) {
  var txt = _container.select('.tmp-text-strings').select('text'),
      str = _string,
      len = 0,
      ell = 0;
  if (txt.empty()) {
    txt = _container.append('g').attr('class', 'tmp-text-strings').append('text');
  }
  txt.text('...');
  ell = txt.node().getBBox().width;
  txt.text(str);
  len = txt.node().getBBox().width;
  strLen = len;
  while (len > _length && len > 30) {
    str = str.slice(0, -1);
    txt.text(str);
    len = txt.node().getBBox().width + ell;
  }
  txt.text('');
  //_container.selectAll('.tmp-text-strings').remove();
  return str + (strLen > _length ? '...' : '');
};

nv.utils.getTextContrast = function(c, i, callback) {
  var back = c,
      backLab = d3.lab(back),
      backLumen = backLab.l,
      textLumen = backLumen > 50 ?
        backLab.darker(4 + (backLumen - 75) / 25).l : // (50..100)[1 to 3.5]
        backLab.brighter(4 + (25 - backLumen) / 25).l, // (0..50)[3.5..1]
      textLab = d3.lab(textLumen, 0, 0),
      text = textLab.toString();
  if (callback) {
    callback(backLab, textLab);
  }
  return text;
};

nv.utils.isRTLChar = function(c) {
  var rtlChars_ = '\u0591-\u07FF\uFB1D-\uFDFF\uFE70-\uFEFC',
      rtlCharReg_ = new RegExp('[' + rtlChars_ + ']');
  return rtlCharReg_.test(c);
};

nv.utils.polarToCartesian = function(centerX, centerY, radius, angleInDegrees) {
  var angleInRadians = angleInDegrees * Math.PI / 180.0;
  var x = centerX + radius * Math.cos(angleInRadians);
  var y = centerY + radius * Math.sin(angleInRadians);
  return [x, y];
};

nv.utils.getTextBBox = function(text, float) {
  var bbox = text.node().getBBox();
  if (!float) {
    bbox.width = parseInt(bbox.width, 10);
    bbox.height = parseInt(bbox.height, 10);
  }
  return bbox;
};
nv.models.axis = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var axis = d3.svg.axisStatic();

  var margin = {top: 0, right: 0, bottom: 0, left: 0},
      thickness = 0,
      scale = d3.scale.linear(),
      axisLabelText = null,
      showMaxMin = true, //TODO: showMaxMin should be disabled on all ordinal scaled axes
      highlightZero = true,
      direction = 'ltr',
      wrapTicks = false,
      staggerTicks = false,
      rotateTicks = 0, //one of (rotateTicks, staggerTicks, wrapTicks)
      reduceXTicks = false, // if false a tick will show for every data point
      rotateYLabel = true,
      isOrdinal = false,
      textAnchor = null,
      ticks = null,
      axisLabelDistance = 8; //The larger this number is, the closer the axis label is to the axis.

  axis
    .scale(scale)
    .orient('bottom')
    .tickFormat(function(d) { return d; });

  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var scale0;

  //============================================================

  function chart(selection) {
    selection.each(function(data) {
      var container = d3.select(this);

      //------------------------------------------------------------
      // Setup containers and skeleton of chart
      var wrap = container.selectAll('g.nv-wrap.nv-axis').data([data]),
          gEnter = wrap.enter()
            .append('g').attr('class', 'nvd3 nv-wrap nv-axis')
            .append('g').attr('class', 'nv-axis-inner'),
          g = wrap.select('.nv-axis-inner');

      //------------------------------------------------------------

      var orientation = axis.orient() === 'left' || axis.orient() === 'right' ? 'vertical' : 'horizontal',
          labelThickness = null,
          textAnchorString = '';

      var fmt = axis.tickFormat(),
          w = typeof scale.rangeExtent === 'function' ?
                scale.rangeExtent()[1] :
                //scale.range()[scale.range().length - 1] + (scale.range()[1] - scale.range()[0])
                Math.abs(scale.range()[0] - scale.range()[1]),
          label = {y: 0, dy: 0, x: 0, a: 'middle', t: ''},
          maxmin = {};

      if (ticks !== null) {
        axis.ticks(ticks);
      } else if (axis.orient() === 'top' || axis.orient() === 'bottom') {
        axis.ticks(Math.ceil(Math.abs(scale.range()[1] - scale.range()[0]) / 100));
      }

      // test to see if rotateTicks was passed as a boolean
      if (rotateTicks && !isFinite(String(rotateTicks))) {
        rotateTicks = 30;
      }

      //TODO: investigate why the ticks are not being removed on data.exit()
      var myTicks = g.selectAll('.tick')
            .data(data, function(d) { return d; });
      myTicks.exit().remove();

      g.call(axis);

      scale0 = scale0 || axis.scale();

      if (fmt === null) {
        fmt = scale0.tickFormat();
      }

      //------------------------------------------------------------
      //Calculate the longest tick width and height
      thickness = defaultThickness();

      var tickText = g.selectAll('g.tick').select('text');

      var tickValueArray = tickText[0].map(function(d, i, j) {
        return d3.select(d).text();
      });

      var maxTickWidth = 0,
          maxTickHeight = 0;

      calculateMax();

      function calculateMax() {
        var maxW = 0,
            maxH = 0;
        tickText.each(function(d, i) {
          var bbox = this.getBoundingClientRect(),
              w = parseInt(bbox.width, 10),
              h = parseInt(bbox.height / 1.15, 10);
          if (w > maxW) {
            maxW = w;
          }
          if (h > maxH) {
            maxH = h;
          }
        });
        maxTickWidth = maxW;
        maxTickHeight = maxH;
      }

      function labelCollision(s) {
        if (axis.scale().rangeBand) {
          return axis.scale().rangeBand() * s < maxTickWidth;
        } else {
          return false;
        }
      }

      function tickRotation(a) {
        //Convert to radians before calculating sin. Add 30 to margin for healthy padding.
        var tickAnchor = direction === 'rtl' ? a % 360 > 0 ? 'end' : 'start' : a % 360 > 0 ? 'start' : 'end',
            sin = Math.abs(Math.sin(a * Math.PI / 180));
        thickness = defaultThickness() + 2;
        thickness += sin ? sin * maxTickWidth : maxTickWidth;
        thickness += sin ? sin * maxTickHeight : 0;

        //Rotate all tickText
        tickText
          .attr('transform', function(d, i, j) {
            return 'translate(0,' + axis.tickPadding() + ') rotate(' + a + ')';
          })
          .attr('y', '0')
          .style('text-anchor', tickAnchor);
      }

      function resetTicks() {
        tickText.selectAll('tspan').remove();
        tickText
          .attr('dy', '.71em')
          .attr('y', axis.tickPadding())
          .attr('transform', 'translate(0,0)')
          .text(function(d, i) { return tickValueArray[i]; });
        calculateMax();
      }

      function defaultThickness() {
        return axis.tickPadding() + (!!axisLabelText ? axisLabelDistance : 0);
      }

      //------------------------------------------------------------
      // Orientation parameters

      switch (axis.orient()) {
        case 'top':

          if (axisLabelText) {
            label.y = -thickness;
            label.dy = '-.71em';
            label.x = w / 2;
          }

          if (showMaxMin) {
            maxmin = {
              data: scale.domain(),
              translate: function(d, i) { return 'translate(' + scale(d) + ',0)'; },
              dy: '0em',
              x: 0,
              y: -axis.tickPadding(),
              transform: '',
              anchor: rotateTicks ? (rotateTicks % 360 > 0 ? 'start' : 'end') : 'middle'
            };
          }

          break;

        case 'bottom':


          var wrapSucceeded = false,
              staggerSucceeded = false,
              rotateSucceeded = false;

          // if wrap is enabled, try it first
          if (wrapTicks && labelCollision(1.25)) {
            tickText.each(function(d) {

              var textContent = this.textContent,
                  textNode = d3.select(this),
                  textArray = textContent.replace('/', '/ ').split(' '),
                  i = 0,
                  l = textArray.length,
                  dy = 0.71,
                  maxWidth = axis.scale().rangeBand();

              // do wrapping if needed
              if (this.getBoundingClientRect().width > maxWidth) {
                this.textContent = '';

                do {
                  var textString,
                    textSpan = textNode.append('tspan')
                      .text(textArray[i] + ' ')
                      .attr('dy', dy + 'em')
                      .attr('x', 0 + 'px');

                  if (i === 0) {
                    dy = 1;
                  }

                  i += 1;

                  while (i < l) {
                    textString = textSpan.text();
                    textSpan.text(textString + ' ' + textArray[i]);
                    if (this.getBoundingClientRect().width <= maxWidth) {
                      i += 1;
                    } else {
                      textSpan.text(textString);
                      break;
                    }
                  }
                } while (i < l);
              }

            });

            // this resets the maxTickWidth for label collision detction
            calculateMax();

            // check to see if we still have collisions
            if (labelCollision(1.25)) {
              resetTicks();
            } else {
              wrapSucceeded = true;
              thickness = 1;
            }
          }

          // wrapping failed so fall back to stagger if enabled
          if (!wrapSucceeded && staggerTicks && labelCollision(1.25)) {
            tickText
              .text(function(d, i) { return tickValueArray[i]; });

            // this sets the maxTickWidth for label collision detction
            calculateMax();

            tickText
              .attr('transform', function(d, i) { return 'translate(0,' + (i % 2 * (maxTickHeight + 2)) + ')'; });

            // check to see if we still have collisions
            if (labelCollision(2.5)) {
              resetTicks();
            } else {
              staggerSucceeded = true;
              thickness = maxTickHeight + 2;
            }
          }

          // if we still have a collision
          if (!wrapSucceeded && !staggerSucceeded && rotateTicks % 360 && labelCollision(1.25)) {
            tickRotation(rotateTicks);
            rotateSucceeded = true;
          } else {
            textAnchorString = 'middle';
            thickness += defaultThickness() + maxTickHeight;
          }

          if (axisLabelText) {
            label.y = thickness;
            label.dy = '.71em';
            label.x = w / 2;
          }

          if (reduceXTicks) {
            g.selectAll('.tick')
                .each(function(d, i) {
                  d3.select(this).selectAll('text,line')
                    .style('opacity', i % Math.ceil(data[0].values.length / (w / 100)) !== 0 ? 0 : 1);
                });
          }

          if (showMaxMin) {
            maxmin = {
              data: [scale.domain()[0], scale.domain()[scale.domain().length - 1]],
              translate: function(d, i) {
                return 'translate(' + (scale(d) + (isOrdinal ? scale.rangeBand() / 2 : (d > 0 ? -8 : +4))) + ',0)';
              },
              dy: '.71em',
              x: 0,
              y: axis.tickPadding(),
              rotate: function(d) { return 'rotate(' + rotateTicks + ' 0,0)'; },
              anchor: rotateTicks ? (rotateTicks % 360 > 0 ? 'start' : 'end') : 'middle'
            };
          }

          break;

        case 'right':

          thickness += maxTickWidth;

          if (axisLabelText) {
            label = {
              y: rotateYLabel ? -(thickness + 2) : -10,
              dy: 0,
              x: rotateYLabel ? w / 2 : axis.tickPadding(),
              a: rotateYLabel ? 'middle' : 'begin',
              t: rotateYLabel ? 'rotate(90)' : ''
            };
          }

          if (showMaxMin) {
            maxmin = {
              data: scale.domain(),
              translate: function(d, i) { return 'translate(0,' + scale(d) + ')'; },
              dy: '.32em',
              x: axis.tickPadding(),
              y: 0,
              rotate: '',
              anchor: direction === 'rtl' ? 'end' : 'start'
            };
          }

          if (textAnchor) {
            if (direction === 'rtl') {
              if (textAnchor === 'start') {
                textAnchorString = 'end';
              } else if (textAnchor === 'end') {
                textAnchorString = 'start';
              } else {
                textAnchorString = textAnchor;
              }
            } else {
              textAnchorString = textAnchor;
            }
          } else {
            textAnchorString = direction === 'rtl' ? 'end' : 'start';
          }

          break;

        case 'left':

          thickness += maxTickWidth + 2;

          if (axisLabelText) {
            label = {
              y: rotateYLabel ? -(thickness + 2) : -10, //TODO: consider calculating this based on largest tick width... OR at least expose this on chart
              dy: 0,
              x: rotateYLabel ? -w / 2 : -axis.tickPadding(),
              a: rotateYLabel ? 'middle' : 'end',
              t: rotateYLabel ? 'rotate(-90)' : ''
            };
          }

          if (showMaxMin) {
            maxmin = {
              data: scale.domain(),
              translate: function(d, i) { return 'translate(0,' + scale(d) + ')'; },
              dy: '.32em',
              x: -axis.tickPadding(),
              y: 0,
              rotate: '',
              anchor: direction === 'rtl' ? 'start' : 'end'
            };
          }

          if (textAnchor) {
            if (direction === 'rtl') {
              if (textAnchor === 'start') {
                textAnchorString = 'end';
              } else if (textAnchor === 'end') {
                textAnchorString = 'start';
              } else {
                textAnchorString = textAnchor;
              }
            } else {
              textAnchorString = textAnchor;
            }
          } else {
            textAnchorString = direction === 'rtl' ? 'start' : 'end';
          }

          break;
      }

      //------------------------------------------------------------
      // Axis label

      var axisLabel = g.selectAll('text.nv-axislabel').data([axisLabelText]);

      if (textAnchorString !== '') {
        g.selectAll('g.tick') // the g's wrapping each tick
          .each(function(d, i) {
            d3.select(this).select('text')
              .style('text-anchor', textAnchorString);
          });
      }

      axisLabel.exit().remove();
      axisLabel.enter().append('text').attr('class', 'nv-axislabel');

      if (axisLabelText) {

        axisLabel
          .text(function(d) { return d; })
          .attr('y', label.y)
          .attr('dy', label.dy)
          .attr('x', label.x)
          .attr('transform', label.t)
          .style('text-anchor', label.a);

        axisLabel.each(function(d, i) {
          labelThickness += axis.orient() === 'left' || axis.orient() === 'right' ?
            parseInt(this.getBoundingClientRect().width / 1.15, 10) :
            parseInt(this.getBoundingClientRect().height / 1.15, 10);
        });

        thickness += labelThickness;
      }

      //------------------------------------------------------------
      // Min Max values

      if (showMaxMin) {
        var axisMaxMin = wrap.selectAll('g.nv-axisMaxMin').data(maxmin.data);
        axisMaxMin.enter().append('g').attr('class', 'nv-axisMaxMin').append('text')
          .style('opacity', 0);
        axisMaxMin.exit().remove();
        axisMaxMin
            .attr('transform', maxmin.translate)
          .select('text')
            .text(function(d, i) {
              var v = fmt(d);
              return ('' + v).match('NaN') ? '' : v;
            })
            .attr('dy', maxmin.dy)
            .attr('x', maxmin.x)
            .attr('y', maxmin.y)
            .attr('transform', maxmin.rotate)
            .style('text-anchor', maxmin.anchor);
        axisMaxMin
            .attr('transform', maxmin.translate)
          .select('text')
            .style('opacity', 1);
      }

      if (showMaxMin && (axis.orient() === 'left' || axis.orient() === 'right')) {
        //check if max and min overlap other values, if so, hide the values that overlap
        g.selectAll('g.tick') // the g's wrapping each tick
            .each(function(d, i) {
              d3.select(this).select('text').style('opacity', 1);
              if (scale(d) > scale.range()[0] - 10 || scale(d) < scale.range()[1] + 10) { // 10 is assuming text height is 16... if d is 0, leave it!
                if (d < 1e-10 && d > -1e-10) {// accounts for minor floating point errors... though could be problematic if the scale is EXTREMELY SMALL
                  d3.select(this).select('text').style('opacity', 0);
                  d3.select(this).select('line').style('opacity', 0);
                }
                d3.select(this).select('text').style('opacity', 0); // Don't remove the ZERO line!!
              }
            });

        //if Max and Min = 0 only show min, Issue #281
        if (scale.domain()[0] === scale.domain()[1] && scale.domain()[0] === 0) {
          wrap.selectAll('g.nv-axisMaxMin')
            .style('opacity', function(d, i) { return !i ? 1 : 0; });
        }
      }

      if (showMaxMin && (axis.orient() === 'top' || axis.orient() === 'bottom')) {
        var maxMinRange = [];
        wrap.selectAll('g.nv-axisMaxMin')
              .each(function(d, i) {
                try {
                  if (i) { // i== 1, max position
                    maxMinRange.push(scale(d) - this.getBoundingClientRect().width - 4);  //assuming the max and min labels are as wide as the next tick (with an extra 4 pixels just in case)
                  }
                  else { // i==0, min position
                    maxMinRange.push(scale(d) + this.getBoundingClientRect().width + 4);
                  }
                } catch (err) {
                  if (i) { // i== 1, max position
                    maxMinRange.push(scale(d) - 4);  //assuming the max and min labels are as wide as the next tick (with an extra 4 pixels just in case)
                  }
                  else { // i==0, min position
                    maxMinRange.push(scale(d) + 4);
                  }
                }
              });

        //check if max and min overlap other values, if so, hide the values that overlap
        g.selectAll('g.tick') // the g's wrapping each tick
            .each(function(d, i) {
              d3.select(this).select('text').style('opacity', 1);
              if (scale(d) < maxMinRange[0] || scale(d) > maxMinRange[1]) {
                if (d < 1e-10 && d > -1e-10) {// accounts for minor floating point errors... though could be problematic if the scale is EXTREMELY SMALL
                  d3.select(this).select('text').style('opacity', 0);
                  d3.select(this).select('line').style('opacity', 0);
                }
                d3.select(this).select('text').style('opacity', 0); // Don't remove the ZERO line!!
              }
            });
      }


      //highlight zero line ... Maybe should not be an option and should just be in CSS?
      if (highlightZero) {
        g.selectAll('line.tick')
            .filter(function(d) {
              return !parseFloat(Math.round(d * 100000) / 1000000);
            }) //this is because sometimes the 0 tick is a very small fraction, TODO: think of cleaner technique
              .classed('zero', true);
      }

      //store old scales for use in transitions on update
      scale0 = scale.copy();

      chart.labelThickness = function() {
        return labelThickness;
      };

    });

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.axis = axis;

  d3.rebind(chart, axis, 'orient', 'tickValues', 'tickSubdivide', 'tickSize', 'tickPadding', 'tickFormat');
  d3.rebind(chart, scale, 'domain', 'range', 'rangeBand', 'rangeBands'); //these are also accessible by chart.scale(), but added common ones directly for ease of use

  chart.margin = function(_) {
    if (!arguments.length) {
      return margin;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        margin[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) {
      return thickness;
    }
    thickness = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) {
      return thickness;
    }
    thickness = _;
    return chart;
  };

  chart.ticks = function(_) {
    if (!arguments.length) {
      return ticks;
    }
    ticks = _;
    return chart;
  };

  chart.axisLabel = function(_) {
    if (!arguments.length) {
      return axisLabelText;
    }
    axisLabelText = _;
    return chart;
  };

  chart.showMaxMin = function(_) {
    if (!arguments.length) {
      return showMaxMin;
    }
    showMaxMin = _;
    return chart;
  };

  chart.highlightZero = function(_) {
    if (!arguments.length) {
      return highlightZero;
    }
    highlightZero = _;
    return chart;
  };

  chart.scale = function(_) {
    if (!arguments.length) {
      return scale;
    }
    scale = _;
    axis.scale(scale);
    isOrdinal = typeof scale.rangeBands === 'function';
    d3.rebind(chart, scale, 'domain', 'range', 'rangeBand', 'rangeBands');
    return chart;
  };

  chart.wrapTicks = function(_) {
    if (!arguments.length) {
      return wrapTicks;
    }
    wrapTicks = _;
    return chart;
  };

  chart.rotateTicks = function(_) {
    if (!arguments.length) {
      return rotateTicks;
    }
    rotateTicks = _;
    return chart;
  };

  chart.staggerTicks = function(_) {
    if (!arguments.length) {
      return staggerTicks;
    }
    staggerTicks = _;
    return chart;
  };

  chart.reduceXTicks = function(_) {
    if (!arguments.length) {
      return reduceXTicks;
    }
    reduceXTicks = _;
    return chart;
  };

  chart.rotateYLabel = function(_) {
    if (!arguments.length) {
      return rotateYLabel;
    }
    rotateYLabel = _;
    return chart;
  };

  chart.axisLabelDistance = function(_) {
    if (!arguments.length) {
      return axisLabelDistance;
    }
    axisLabelDistance = _;
    return chart;
  };

  chart.maxTickWidth = function(_) {
    if (!arguments.length) {
      return maxTickWidth;
    }
    maxTickWidth = _;
    return chart;
  };

  chart.textAnchor = function(_) {
    if (!arguments.length) {
      return textAnchor;
    }
    textAnchor = _;
    return chart;
  };

  chart.direction = function(_) {
    if (!arguments.length) {
      return direction;
    }
    direction = _;
    return chart;
  };

  //============================================================


  return chart;
};
nv.models.legend = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 10, right: 10, bottom: 15, left: 10},
      width = 0,
      height = 0,
      align = 'right',
      direction = 'ltr',
      position = 'start',
      radius = 6, // size of dot
      diameter = radius * 2, // diamter of dot plus stroke
      gutter = 10, // horizontal gap between keys
      spacing = 12, // vertical gap between keys
      textGap = 5, // gap between dot and label accounting for dot stroke
      equalColumns = true,
      showAll = false,
      showMenu = false,
      collapsed = false,
      rowsCount = 3, //number of rows to display if showAll = false
      enabled = false,
      strings = {close: 'Hide legend', type: 'Show legend'},
      id = Math.floor(Math.random() * 10000), //Create semi-unique ID in case user doesn't select one
      getKey = function(d) {
        return d.key.length > 0 || (!isNaN(parseFloat(d.key)) && isFinite(d.key)) ? d.key : 'undefined';
      },
      color = function(d, i) { return nv.utils.defaultColor()(d, i); },
      classes = function(d, i) { return ''; },
      dispatch = d3.dispatch('legendClick', 'legendMouseover', 'legendMouseout', 'toggleMenu', 'closeMenu');

  // Private Variables
  //------------------------------------------------------------

  var legendOpen = 0;

  var useScroll = false,
      scrollEnabled = true,
      scrollOffset = 0,
      overflowHandler = function(d) { return; };

  //============================================================

  function legend(selection) {

    selection.each(function(data) {

      var container = d3.select(this),
          containerWidth = width,
          containerHeight = height,
          keyWidths = [],
          legendHeight = 0,
          dropdownHeight = 0,
          type = '',
          inline = position === 'start' ? true : false,
          rtl = direction === 'rtl' ? true : false;

      if (!data || !data.length || !data.filter(function(d) { return !d.values || d.values.length; }).length) {
        return legend;
      }

      enabled = true;

      type = !data[0].type || data[0].type === 'bar' ? 'bar' : 'line';
      align = rtl ? align === 'left' ? 'right' : 'left' : align;

      //------------------------------------------------------------
      // Setup containers and skeleton of legend

      var wrap = container.selectAll('g.nv-chart-legend').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nv-chart-legend');

      wrapEnter.append('defs')
        .append('clipPath').attr('id', 'nv-edge-clip-' + id)
        .append('rect');

      var defs = wrap.select('defs');
      var clip = wrap.select('#nv-edge-clip-' + id + ' rect');

      wrapEnter
        .append('rect')
          .attr('class', 'nv-legend-background');
      var back = wrap.select('.nv-legend-background');
      var backFilter = nv.utils.dropShadow('legend_back_' + id, defs, {blur: 2});

      wrapEnter
        .append('text').attr('class', 'nv-legend-link');
      var link = wrap.select('.nv-legend-link');

      wrapEnter
        .append('g').attr('class', 'nv-legend-mask')
        .append('g').attr('class', 'nv-legend');
      var mask = wrap.select('.nv-legend-mask');
      var g = wrap.select('g.nv-legend');
      g .attr('transform', 'translate(0,0)');

      var series = g.selectAll('.nv-series')
            .data(function(d) { return d; }, function(d) { return d.key; });
      var seriesEnter = series.enter().append('g').attr('class', 'nv-series');
      series.exit().remove();

      clip
        .attr('x', 0.5)
        .attr('y', 0.5)
        .attr('width', 0)
        .attr('height', 0);

      back
        .attr('x', 0.5)
        .attr('y', 0.5)
        .attr('width', 0)
        .attr('height', 0)
        .style('opacity', 0)
        .style('pointer-events', 'all')
        .on('click', function(d, i) {
          d3.event.stopPropagation();
        });

      link
        .text(legendOpen === 1 ? legend.strings().close : legend.strings().open)
        .attr('text-anchor', align === 'left' ? rtl ? 'end' : 'start' : rtl ? 'start' : 'end')
        .attr('dy', '.36em')
        .attr('dx', 0)
        .style('opacity', 0)
        .on('click', function(d, i) {
          dispatch.toggleMenu(d, i);
        });

      seriesEnter
        .on('mouseover', function(d, i) {
          dispatch.legendMouseover(d, i);  //TODO: Make consistent with other event objects
        })
        .on('mouseout', function(d, i) {
          dispatch.legendMouseout(d, i);
        })
        .on('click', function(d, i) {
          dispatch.legendClick(d, i);
          d3.event.stopPropagation();
          d3.event.preventDefault();
        });

      if (type === 'bar') {

        seriesEnter.append('rect')
          .attr('x', -radius - 2)
          .attr('y', -radius - 2)
          .attr('width', radius * 2 + 4)
          .attr('height', radius * 2 + 4)
          .style('fill', '#FFE')
          .style('opacity', 0.1);

        seriesEnter.append('circle')
          .attr('r', radius)
          .style('stroke-width', 2);

        series.selectAll('circle')
          .attr('class', function(d, i) {
            return classes(d, d.hasOwnProperty('series') ? d.series : i);
          })
          .attr('fill', function(d, i) {
            return color(d, d.hasOwnProperty('series') ? d.series : i);
          })
          .attr('stroke', function(d, i) {
            return color(d, d.hasOwnProperty('series') ? d.series : i);
          });

        seriesEnter.append('text')
          .attr('dy', inline ? '.36em' : '.71em');
        series.select('text')
          .text(getKey);

      } else {

        seriesEnter.append('circle')
          .attr('r', function(d, i) {
            return d.type === 'dash' ? 0 : radius;
          })
          .style('stroke-width', 2);
        seriesEnter.append('line')
          .attr('x0', 0)
          .attr('y0', 0)
          .attr('y1', 0)
          .style('stroke-width', '4px');
        seriesEnter.append('circle')
          .attr('r', function(d, i) {
            return d.type === 'dash' ? 0 : radius;
          })
          .style('stroke-width', 2);

        series.select('line')
          .attr('class', function(d, i) {
            return classes(d, d.hasOwnProperty('series') ? d.series : i);
          })
          .attr('stroke', function(d, i) {
            return color(d, d.hasOwnProperty('series') ? d.series : i);
          });

        series.selectAll('circle')
          .attr('class', function(d, i) {
            return classes(d, d.hasOwnProperty('series') ? d.series : i);
          })
          .attr('fill', function(d, i) {
            return color(d, d.hasOwnProperty('series') ? d.series : i);
          })
          .attr('stroke', function(d, i) {
            return color(d, d.hasOwnProperty('series') ? d.series : i);
          });

        seriesEnter.append('text')
          .attr('dy', inline ? '.36em' : '.71em')
          .attr('dx', 0);
        series.select('text')
          .text(getKey)
          .attr('text-anchor', position);

      }

      series.classed('disabled', function(d) {
        return d.disabled;
      });

      //------------------------------------------------------------

      //TODO: add ability to add key to legend
      //var label = g.append('text').text('Probability:').attr('class','nv-series-label').attr('transform','translate(0,0)');

      // store legend label widths
      legend.calculateWidth = function() {

        var padding = gutter + (inline ? diameter + textGap : 0);
        keyWidths = [];

        g.style('display', 'inline');

        series.select('text').each(function(d, i) {
          var textWidth = d3.select(this).node().getBBox().width;
          keyWidths.push(Math.max(Math.floor(textWidth) + gutter, 50));
        });

        legend.width(d3.sum(keyWidths) + keyWidths.length * padding - gutter);

        return legend.width();
      };

      legend.getLineHeight = function() {
        g.style('display', 'inline');
        var lineHeightBB = Math.floor(series.select('text').node().getBBox().height);
        return lineHeightBB;
      };

      legend.arrange = function(containerWidth) {

        if (keyWidths.length === 0) {
          this.calculateWidth();
        }

        function keyWidth(i) {
          return keyWidths[i] + gutter + (inline ? diameter + textGap : 0);
        }

        var keys = keyWidths.length,
            rows = 1,
            cols = keys,
            columnWidths = [],
            keyPositions = [],
            maxWidth = containerWidth - margin.left - margin.right,
            maxRowWidth = 0,
            minRowWidth = 0,
            lineSpacing = spacing * (inline ? 1 : 0.6),
            textHeight = this.getLineHeight(),
            lineHeight = diameter + (inline ? 0 : textHeight) + lineSpacing,
            menuMargin = {top: 7, right: 7, bottom: 7, left: 7}, // account for stroke width
            xpos = 0,
            ypos = 0,
            i,
            mod,
            shift;

        if (equalColumns) {

          //keep decreasing the number of keys per row until
          //legend width is less than the available width
          while (cols > 0) {
            columnWidths = [];

            for (i = 0; i < keys; i += 1) {
              if (keyWidth(i) > (columnWidths[i % cols] || 0)) {
                columnWidths[i % cols] = keyWidth(i);
              }
            }

            if (d3.sum(columnWidths) - gutter < maxWidth) {
              break;
            }
            cols -= 1;
          }
          cols = cols || 1;

          rows = Math.ceil(keys / cols);
          maxRowWidth = d3.sum(columnWidths) - gutter;

          for (i = 0; i < keys; i += 1) {
            mod = i % cols;

            if (inline) {
              if (mod === 0) {
                xpos = rtl ? maxRowWidth : 0;
              } else {
                xpos += columnWidths[mod - 1] * (rtl ? -1 : 1);
              }
            } else {
              if (mod === 0) {
                xpos = (rtl ? maxRowWidth : 0) + (columnWidths[mod] - gutter) / 2 * (rtl ? -1 : 1);
              } else {
                xpos += (columnWidths[mod - 1] + columnWidths[mod]) / 2 * (rtl ? -1 : 1);
              }
            }

            ypos = Math.floor(i / cols) * lineHeight;
            keyPositions[i] = {x: xpos, y: ypos};
          }

        } else {

          if (rtl) {

            xpos = maxWidth;

            for (i = 0; i < keys; i += 1) {
              if (xpos - keyWidth(i) + gutter < 0) {
                maxRowWidth = Math.max(maxRowWidth, keyWidth(i) - gutter);
                xpos = maxWidth;
                if (i) {
                  rows += 1;
                }
              }
              if (xpos - keyWidth(i) + gutter > maxRowWidth) {
                maxRowWidth = xpos - keyWidth(i) + gutter;
              }
              keyPositions[i] = {x: xpos, y: (rows - 1) * (lineSpacing + diameter)};
              xpos -= keyWidth(i);
            }

          } else {

            xpos = 0;

            for (i = 0; i < keys; i += 1) {
              if (i && xpos + keyWidth(i) - gutter > maxWidth) {
                xpos = 0;
                rows += 1;
              }
              if (xpos + keyWidth(i) - gutter > maxRowWidth) {
                maxRowWidth = xpos + keyWidth(i) - gutter;
              }
              keyPositions[i] = {x: xpos, y: (rows - 1) * (lineSpacing + diameter)};
              xpos += keyWidth(i);
            }

          }

        }

        if (!showMenu && (showAll || rows <= rowsCount)) {

          legendOpen = 0;
          collapsed = false;
          useScroll = false;

          legend
            .width(margin.left + maxRowWidth + margin.right)
            .height(margin.top + rows * lineHeight - lineSpacing + margin.bottom);

          switch (align) {
            case 'left':
              shift = 0;
              break;
            case 'center':
              shift = (containerWidth - legend.width()) / 2;
              break;
            case 'right':
              shift = 0;
              break;
          }

          clip
            .attr('y', 0)
            .attr('width', legend.width())
            .attr('height', legend.height());

          back
            .attr('x', shift)
            .attr('width', legend.width())
            .attr('height', legend.height())
            .attr('rx', 0)
            .attr('ry', 0)
            .attr('filter', 'none')
            .style('display', 'inline')
            .style('opacity', 0);

          mask
            .attr('clip-path', 'none')
            .attr('transform', function(d, i) {
              var xpos = shift + margin.left + (inline ? radius * (rtl ? -1 : 1) : 0),
                  ypos = margin.top + menuMargin.top;
              return 'translate(' + xpos + ',' + ypos + ')';
            });

          g
            .style('opacity', 1)
            .style('display', 'inline');

          series
            .attr('transform', function(d, i) {
              var pos = keyPositions[i];
              return 'translate(' + pos.x + ',' + pos.y + ')';
            });

          series.selectAll('rect')
            .attr('x', -radius - gutter / 2)
            .attr('y', -radius - lineSpacing / 2)
            .attr('width', function(d, i) {
              // var index = d.series % columnWidths.length;
              // return columnWidths[index];
              return keyWidths[d.series];
            })
            .attr('height', radius * 2 + lineSpacing);
          series.selectAll('text')
            .attr('text-anchor', position)
            .attr('dy', inline ? '.36em' : '.71em')
            .attr('transform', function(d, i) {
              var xpos = inline ? (radius + textGap) * (rtl ? -1 : 1) : 0,
                  ypos = inline ? 0 : radius + 3;
              return 'translate(' + xpos + ',' + ypos + ')';
            });
          series.selectAll('circle')
            .attr('transform', function(d, i) {
              var xpos = inline || type === 'bar' ? 0 : radius * 3 * (i ? 1 : -1);
              return 'translate(' + xpos + ',0)';
            });
          series.selectAll('line')
            .attr('x1', function(d, i) {
              return d.type === 'dash' ? radius * 8 : radius * 4;
            })
            .attr('transform', function(d, i) {
              var xpos = radius * (d.type === 'dash' ? -4 : -2);
              return 'translate(' + xpos + ',0)';
            })
            .style('stroke-dasharray', function(d, i) {
              return d.type === 'dash' ? '8, 8' : '0,0';
            });

        } else {

          collapsed = true;
          useScroll = true;

          legend
            .width(menuMargin.left + d3.max(keyWidths) - gutter + diameter + textGap + menuMargin.right)
            .height(margin.top + diameter + margin.top); //don't use bottom here because we want vertical centering

          legendHeight = menuMargin.top + diameter * keys + spacing * (keys - 1) + menuMargin.bottom;
          dropdownHeight = Math.min(containerHeight - legend.height(), legendHeight);

          clip
            .attr('x', 0.5 - menuMargin.top - radius)
            .attr('y', 0.5 - menuMargin.top - radius)
            .attr('width', legend.width())
            .attr('height', dropdownHeight);

          back
            .attr('x', 0.5)
            .attr('y', 0.5 + legend.height())
            .attr('width', legend.width())
            .attr('height', dropdownHeight)
            .attr('rx', 2)
            .attr('ry', 2)
            .attr('filter', backFilter)
            .style('opacity', legendOpen * 0.9)
            .style('display', legendOpen ? 'inline' : 'none');

          link
            .attr('transform', function(d, i) {
              var xpos = align === 'left' ? 0.5 : 0.5 + legend.width(),
                  ypos = margin.top + radius;
              return 'translate(' + xpos + ',' + ypos + ')';
            })
            .style('opacity', 1);

          mask
            .attr('clip-path', 'url(#nv-edge-clip-' + id + ')')
            .attr('transform', function(d, i) {
              var xpos = menuMargin.left + radius,
                  ypos = legend.height() + menuMargin.top + radius;
              return 'translate(' + xpos + ',' + ypos + ')';
            });

          g
            .style('opacity', legendOpen)
            .style('display', legendOpen ? 'inline' : 'none')
            .attr('transform', function(d, i) {
              var xpos = rtl ? d3.max(keyWidths) - gutter - diameter : 0;
              return 'translate(' + xpos + ',0)';
            });

          series
            .attr('transform', function(d, i) {
              var ypos = i * (diameter + spacing);
              return 'translate(0,' + ypos + ')';
            });
          series
            .selectAll('circle')
              .attr('transform', '');
          series
            .selectAll('line')
              .attr('x1', 16)
              .attr('transform', 'translate(-8,0)') //TODO: why is this hard coded?
              .style('stroke-dasharray', 'inherit');
          series
            .selectAll('text')
              .attr('text-anchor', 'start')
              .attr('dy', '.36em')
              .attr('transform', function(d, i) {
                var xpos = (radius + textGap) * (rtl ? -1 : 1);
                return 'translate(' + xpos + ',0)';
            });

        }
        //------------------------------------------------------------
        // Enable scrolling
        if (scrollEnabled) {
          var diff = dropdownHeight - legendHeight;

          var assignScrollEvents = function(enable) {
            var pan = enable ? panLegend : null;
            var zoom = d3.behavior.zoom()
                  .on('zoom', pan);
            var drag = d3.behavior.drag()
                  .origin(function(d) { return d; })
                  .on('drag', pan);

            back.call(zoom);
            g.call(zoom);

            back.call(drag);
            g.call(drag);
          };

          var panLegend = function() {
            var distance = 0,
                overflowDistance = 0,
                translate = '',
                x = 0,
                y = 0;

            // don't fire on events other than zoom and drag
            // we need click for handling legend toggle
            if (d3.event) {
              if (d3.event.type === 'zoom') {
                x = d3.event.sourceEvent.deltaX || 0;
                y = d3.event.sourceEvent.deltaY || 0;
                distance = (Math.abs(x) > Math.abs(y) ? x : y) * -1;
              } else if (d3.event.type === 'drag') {
                x = d3.event.dx || 0;
                y = d3.event.dy || 0;
                distance = y;
              } else if (d3.event.type !== 'click') {
                return 0;
              }
              overflowDistance = (Math.abs(y) > Math.abs(x) ? y : 0);
            }

            // reset value defined in panMultibar();
            scrollOffset = Math.min(Math.max(scrollOffset + distance, diff), -1);
            translate = 'translate(0,' + scrollOffset + ')';

            if (scrollOffset + distance > 0 || scrollOffset + distance < diff) {
              overflowHandler(overflowDistance);
            }

            g.attr('transform', translate);
          };

          assignScrollEvents(useScroll);
        }

      };

      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      function displayMenu() {
        back
          .style('opacity', legendOpen * 0.9)
          .style('display', legendOpen ? 'inline' : 'none');
        g
          .style('opacity', legendOpen)
          .style('display', legendOpen ? 'inline' : 'none');
        link
          .text(legendOpen === 1 ? legend.strings().close : legend.strings().open);
      }

      dispatch.on('toggleMenu', function(d) {
        d3.event.stopPropagation();
        legendOpen = 1 - legendOpen;
        displayMenu();
      });

      dispatch.on('closeMenu', function(d) {
        if (legendOpen === 1) {
          legendOpen = 0;
          displayMenu();
        }
      });

    });

    return legend;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  legend.dispatch = dispatch;

  legend.margin = function(_) {
    if (!arguments.length) { return margin; }
    margin.top    = typeof _.top    !== 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  !== 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom !== 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   !== 'undefined' ? _.left   : margin.left;
    return legend;
  };

  legend.width = function(_) {
    if (!arguments.length) {
      return width;
    }
    width = Math.round(_);
    return legend;
  };

  legend.height = function(_) {
    if (!arguments.length) {
      return height;
    }
    height = Math.round(_);
    return legend;
  };

  legend.id = function(_) {
    if (!arguments.length) {
      return id;
    }
    id = _;
    return legend;
  };

  legend.key = function(_) {
    if (!arguments.length) {
      return getKey;
    }
    getKey = _;
    return legend;
  };

  legend.color = function(_) {
    if (!arguments.length) {
      return color;
    }
    color = nv.utils.getColor(_);
    return legend;
  };

  legend.classes = function(_) {
    if (!arguments.length) {
      return classes;
    }
    classes = _;
    return legend;
  };

  legend.align = function(_) {
    if (!arguments.length) {
      return align;
    }
    align = _;
    return legend;
  };

  legend.position = function(_) {
    if (!arguments.length) {
      return position;
    }
    position = _;
    return legend;
  };

  legend.showAll = function(_) {
    if (!arguments.length) { return showAll; }
    showAll = _;
    return legend;
  };

  legend.showMenu = function(_) {
    if (!arguments.length) { return showMenu; }
    showMenu = _;
    return legend;
  };

  legend.collapsed = function(_) {
    return collapsed;
  };

  legend.rowsCount = function(_) {
    if (!arguments.length) {
      return rowsCount;
    }
    rowsCount = _;
    return legend;
  };

  legend.spacing = function(_) {
    if (!arguments.length) {
      return spacing;
    }
    spacing = _;
    return legend;
  };

  legend.gutter = function(_) {
    if (!arguments.length) {
      return gutter;
    }
    gutter = _;
    return legend;
  };

  legend.radius = function(_) {
    if (!arguments.length) {
      return radius;
    }
    radius = _;
    return legend;
  };

  legend.strings = function(_) {
    if (!arguments.length) {
      return strings;
    }
    strings = _;
    return legend;
  };

  legend.equalColumns = function(_) {
    if (!arguments.length) {
      return equalColumns;
    }
    equalColumns = _;
    return legend;
  };

  legend.enabled = function(_) {
    if (!arguments.length) {
      return enabled;
    }
    enabled = _;
    return legend;
  };

  legend.direction = function(_) {
    if (!arguments.length) {
      return direction;
    }
    direction = _;
    return legend;
  };

  //============================================================


  return legend;
};

nv.models.scroll = function() {

  //============================================================
  // Public Variables
  //------------------------------------------------------------

  var id,
      margin = {},
      vertical,
      width,
      height,
      minDimension,
      panHandler,
      overflowHandler,
      enable;

  //============================================================

  function scroll(g, gEnter, scrollWrap, xAxis) {

      var defs = g.select('defs'),
          defsEnter = gEnter.select('defs'),
          scrollMask,
          scrollTarget,
          xAxisWrap = scrollWrap.select('.nv-x.nv-axis'),
          barsWrap = scrollWrap.select('.nv-barsWrap'),
          backShadows,
          foreShadows;

      var scrollOffset = 0;

      scroll.init = function(offset, overflow) {

        scrollOffset = offset;
        overflowHandler = overflow;

        this.gradients(enable);
        this.mask(enable);
        this.scrollTarget(enable);
        this.backShadows(enable);
        this.foreShadows(enable);

        this.assignEvents(enable);

        this.resize(enable);
      };

      scroll.pan = function(diff) {
        var distance = 0,
            overflowDistance = 0,
            translate = '',
            x = 0,
            y = 0;

        // don't fire on events other than zoom and drag
        // we need click for handling legend toggle
        if (d3.event) {
          if (d3.event.type === 'zoom') {
            x = d3.event.sourceEvent.deltaX || 0;
            y = d3.event.sourceEvent.deltaY || 0;
            distance = (Math.abs(x) > Math.abs(y) ? x : y) * -1;
          } else if (d3.event.type === 'drag') {
            x = d3.event.dx || 0;
            y = d3.event.dy || 0;
            distance = vertical ? x : y;
          } else if (d3.event.type !== 'click') {
            return 0;
          }
          overflowDistance = (Math.abs(y) > Math.abs(x) ? y : 0);
        }

        // reset value defined in panMultibar();
        scrollOffset = Math.min(Math.max(scrollOffset + distance, diff), -1);
        translate = 'translate(' + (vertical ? scrollOffset + ',0' : '0,' + scrollOffset) + ')';

        if (scrollOffset + distance > 0 || scrollOffset + distance < diff) {
          overflowHandler(overflowDistance);
        }

        foreShadows
          .attr('transform', translate);
        barsWrap
          .attr('transform', translate);
        xAxisWrap.select('.nv-wrap.nv-axis')
          .attr('transform', translate);

        return scrollOffset;
      };

      scroll.assignEvents = function(enable) {
        var pan = enable ? panHandler : null;
        var zoom = d3.behavior.zoom()
              .on('zoom', pan);
        var drag = d3.behavior.drag()
              .origin(function(d) { return d; })
              .on('drag', pan);

        scrollWrap
          .call(zoom);
        scrollTarget
          .call(zoom);

        scrollWrap
          .call(drag);
        scrollTarget
          .call(drag);
      };

      scroll.resize = function(enable) {

        if (!enable) {
          return;
        }
        var labelOffset = xAxis.labelThickness() + xAxis.tickPadding() / 2,
            v = vertical,
            x = v ? margin.left : labelOffset,
            y = margin.top,
            scrollWidth = width + (v ? 0 : margin[xAxis.orient()] - labelOffset),
            scrollHeight = height + (v ? margin[xAxis.orient()] - labelOffset : 0),
            dim = v ? 'height' : 'width',
            val = v ? scrollHeight : scrollWidth;

        scrollMask
          .attr('x', v ? 0 : -margin.left)
          .attr('y', 0)
          .attr('width', width + (v ? 0 : margin.left))
          .attr('height', height + (v ? margin.bottom : 0));

        scrollTarget
          .attr('x', x)
          .attr('y', y)
          .attr('width', scrollWidth)
          .attr('height', scrollHeight);

        backShadows.select('.nv-back-shadow-prev')
          .attr('x', x)
          .attr('y', y)
          .attr(dim, val);

        backShadows.select('.nv-back-shadow-more')
          .attr('x', x + (v ? width - 5 : 1))
          .attr('y', y + (v ? 0 : height - 4))
          .attr(dim, val);

        foreShadows.select('.nv-fore-shadow-prev')
          .attr('x', x + (v ? 1 : 0))
          .attr('y', y + (v ? 0 : 1))
          .attr(dim, val);

        foreShadows.select('.nv-fore-shadow-more')
          .attr('x', x + (v ? minDimension - 20 : 0))
          .attr('y', y + (v ? 0 : minDimension - 19))
          .attr(dim, val);
      };

      /* Background gradients */
      scroll.gradients = function(enable) {
        defsEnter
          .append('linearGradient')
          .attr('class', 'nv-scroll-gradient')
          .attr('id', 'nv-back-gradient-prev-' + id);
        var bgpEnter = defsEnter.select('#nv-back-gradient-prev-' + id);

        defsEnter
          .append('linearGradient')
          .attr('class', 'nv-scroll-gradient')
          .attr('id', 'nv-back-gradient-more-' + id);
        var bgmEnter = defsEnter.select('#nv-back-gradient-more-' + id);

        /* Foreground gradients */
        defsEnter
          .append('linearGradient')
          .attr('class', 'nv-scroll-gradient')
          .attr('id', 'nv-fore-gradient-prev-' + id);
        var fgpEnter = defsEnter.select('#nv-fore-gradient-prev-' + id);

        defsEnter
          .append('linearGradient')
          .attr('class', 'nv-scroll-gradient')
          .attr('id', 'nv-fore-gradient-more-' + id);
        var fgmEnter = defsEnter.select('#nv-fore-gradient-more-' + id);

        defs.selectAll('.nv-scroll-gradient')
          .attr('gradientUnits', 'objectBoundingBox')
          .attr('x1', 0)
          .attr('y1', 0)
          .attr('x2', vertical ? 1 : 0)
          .attr('y2', vertical ? 0 : 1);

        bgpEnter
          .append('stop')
          .attr('stop-color', '#000')
          .attr('stop-opacity', '0.3')
          .attr('offset', 0);
        bgpEnter
          .append('stop')
          .attr('stop-color', '#FFF')
          .attr('stop-opacity', '0')
          .attr('offset', 1);
        bgmEnter
          .append('stop')
          .attr('stop-color', '#FFF')
          .attr('stop-opacity', '0')
          .attr('offset', 0);
        bgmEnter
          .append('stop')
          .attr('stop-color', '#000')
          .attr('stop-opacity', '0.3')
          .attr('offset', 1);

        fgpEnter
          .append('stop')
          .attr('stop-color', '#FFF')
          .attr('stop-opacity', '1')
          .attr('offset', 0);
        fgpEnter
          .append('stop')
          .attr('stop-color', '#FFF')
          .attr('stop-opacity', '0')
          .attr('offset', 1);
        fgmEnter
          .append('stop')
          .attr('stop-color', '#FFF')
          .attr('stop-opacity', '0')
          .attr('offset', 0);
        fgmEnter
          .append('stop')
          .attr('stop-color', '#FFF')
          .attr('stop-opacity', '1')
          .attr('offset', 1);
      };

      scroll.mask = function(enable) {
        defsEnter.append('clipPath')
          .attr('class', 'nv-scroll-mask')
          .attr('id', 'nv-edge-clip-' + id)
          .append('rect');

        scrollMask = defs.select('.nv-scroll-mask rect');

        scrollWrap.attr('clip-path', enable ? 'url(#nv-edge-clip-' + id + ')' : '');
      };

      scroll.scrollTarget = function(enable) {
        gEnter.select('.nv-scroll-background')
          .append('rect')
          .attr('class', 'nv-scroll-target')
          //.attr('fill', '#FFF');
          .attr('fill', 'transparent');

        scrollTarget = g.select('.nv-scroll-target');
      };

      /* Background shadow rectangles */
      scroll.backShadows = function(enable) {
        var shadowWrap = gEnter.select('.nv-scroll-background')
              .append('g')
              .attr('class', 'nv-back-shadow-wrap');

        shadowWrap
          .append('rect')
          .attr('class', 'nv-back-shadow-prev');
        shadowWrap
          .append('rect')
          .attr('class', 'nv-back-shadow-more');

        backShadows = g.select('.nv-back-shadow-wrap');

        if (enable) {
          var dimension = vertical ? 'width' : 'height';

          backShadows.select('rect.nv-back-shadow-prev')
            .attr('fill', 'url(#nv-back-gradient-prev-' + id + ')')
            .attr(dimension, 7);

          backShadows.select('rect.nv-back-shadow-more')
            .attr('fill', 'url(#nv-back-gradient-more-' + id + ')')
            .attr(dimension, 7);
        } else {
          backShadows.selectAll('rect').attr('fill', 'transparent');
        }
      };

      /* Foreground shadow rectangles */
      scroll.foreShadows = function(enable) {
        var shadowWrap = gEnter.select('.nv-scroll-background')
              .insert('g')
              .attr('class', 'nv-fore-shadow-wrap');

        shadowWrap
          .append('rect')
          .attr('class', 'nv-fore-shadow-prev');
        shadowWrap
          .append('rect')
          .attr('class', 'nv-fore-shadow-more');

        foreShadows = g.select('.nv-fore-shadow-wrap');

        if (enable) {
          var dimension = vertical ? 'width' : 'height';

          foreShadows.select('rect.nv-fore-shadow-prev')
            .attr('fill', 'url(#nv-fore-gradient-prev-' + id + ')')
            .attr(dimension, 20);

          foreShadows.select('rect.nv-fore-shadow-more')
            .attr('fill', 'url(#nv-fore-gradient-more-' + id + ')')
            .attr(dimension, 20);
        } else {
          foreShadows.selectAll('rect').attr('fill', 'transparent');
        }
      };

    return scroll;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  scroll.id = function(_) {
    if (!arguments.length) {
      return id;
    }
    id = _;
    return scroll;
  };

  scroll.margin = function(_) {
    if (!arguments.length) {
      return margin;
    }
    margin.top    = typeof _.top    != 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  != 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom != 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   != 'undefined' ? _.left   : margin.left;
    return scroll;
  };

  scroll.width = function(_) {
    if (!arguments.length) {
      return width;
    }
    width = _;
    return scroll;
  };

  scroll.height = function(_) {
    if (!arguments.length) {
      return height;
    }
    height = _;
    return scroll;
  };

  scroll.vertical = function(_) {
    if (!arguments.length) {
      return vertical;
    }
    vertical = _;
    return scroll;
  };

  scroll.minDimension = function(_) {
    if (!arguments.length) {
      return minDimension;
    }
    minDimension = _;
    return scroll;
  };

  scroll.panHandler = function(_) {
    if (!arguments.length) {
      return panHandler;
    }
    panHandler = d3.functor(_);
    return scroll;
  };

  scroll.enable = function(_) {
    if (!arguments.length) {
      return enable;
    }
    enable = _;
    return scroll;
  };

  //============================================================

  return scroll;
};

nv.models.scatter = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin       = {top: 0, right: 0, bottom: 0, left: 0}
    , width        = 960
    , height       = 500
    , color        = function(d, i) { return nv.utils.defaultColor()(d, d.series); } // chooses color
    , fill         = color
    , classes      = function (d,i) { return 'nv-group nv-series-' + d.series; }
    , id           = Math.floor(Math.random() * 100000) //Create semi-unique ID incase user doesn't select one
    , x            = d3.scale.linear()
    , y            = d3.scale.linear()
    , z            = d3.scale.linear() //linear because d3.svg.shape.size is treated as area
    , getX         = function(d) { return d.x } // accessor to get the x value
    , getY         = function(d) { return d.y } // accessor to get the y value
    , getSize      = function(d) { return d.size || 1} // accessor to get the point size
    , getShape     = function(d) { return d.shape || 'circle' } // accessor to get point shape
    , onlyCircles  = true // Set to false to use shapes
    , forceX       = [] // List of numbers to Force into the X scale (ie. 0, or a max / min, etc.)
    , forceY       = [] // List of numbers to Force into the Y scale
    , forceSize    = [] // List of numbers to Force into the Size scale
    , interactive  = true // If true, plots a voronoi overlay for advanced point intersection
    , pointActive  = function(d) { return !d.notActive } // any points that return false will be filtered out
    , padData      = false // If true, adds half a data points width to front and back, for lining up a line chart with a bar chart
    , padDataOuter = .1 //outerPadding to imitate ordinal scale outer padding
    , clipEdge     = false // if true, masks points within x and y scale
    , clipVoronoi  = true // if true, masks each point with a circle... can turn off to slightly increase performance
    , clipRadius   = function() { return 10 } // function to get the radius for voronoi point clips
    , xDomain      = null // Override x domain (skips the calculation from data)
    , yDomain      = null // Override y domain
    , sizeDomain   = null // Override point size domain
    , sizeRange    = [16, 256]
    , singlePoint  = false
    , dispatch     = d3.dispatch('elementClick', 'elementMouseover', 'elementMouseout', 'elementMousemove')
    , useVoronoi   = true
    , nice = false
    ;

  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var x0, y0, z0 // used to store previous scales
    , timeoutID
    , needsUpdate = false // Flag for when the points are visually updating, but the interactive layer is behind, to disable tooltips
    ;

  //============================================================


  function chart(selection) {
    selection.each(function(data) {
      var availableWidth = width - margin.left - margin.right,
          availableHeight = height - margin.top - margin.bottom,
          container = d3.select(this);

      //add series index to each data point for reference
      data = data.map(function(series, i) {
        series.values = series.values.map(function(point) {
          point.series = i;
          return point;
        });
        return series;
      });

      //------------------------------------------------------------
      // Setup Scales

      // remap and flatten the data for use in calculating the scales' domains
      var seriesData = (xDomain && yDomain && sizeDomain) ? [] : // if we know xDomain and yDomain and sizeDomain, no need to calculate.... if Size is constant remember to set sizeDomain to speed up performance
            d3.merge(
              data.map(function(d) {
                return d.values.map(function(d,i) {
                  return { x: getX(d,i), y: getY(d,i), size: getSize(d,i) }
                })
              })
            );

      x   .domain(xDomain || d3.extent(seriesData.map(function(d) { return d.x }).concat(forceX)))

      if (padData && data[0])
        if (padDataOuter !== 0) {
          // adjust range to line up with value bars
          x.range([
            (availableWidth * padDataOuter + availableWidth) / (2 *data[0].values.length),
            availableWidth - availableWidth * (1 + padDataOuter) / (2 * data[0].values.length)
          ]);
        } else {
          // shift range so that largest bubble doesn't cover scales
          x.range([
            0 + Math.sqrt(sizeRange[1]/Math.PI),
            availableWidth - Math.sqrt(sizeRange[1]/Math.PI)
          ]);
        }
        //x.range([availableWidth * .5 / data[0].values.length, availableWidth * (data[0].values.length - .5)  / data[0].values.length ]);
      else
        x.range([0, availableWidth]);

      y   .domain(yDomain || d3.extent(seriesData.map(function(d) { return d.y }).concat(forceY)))
          .range([availableHeight, 0]);
      if (nice) {
        y.nice();
      }

      z   .domain(sizeDomain || d3.extent(seriesData.map(function(d) { return d.size }).concat(forceSize)))
          .range(sizeRange);

      // If scale's domain don't have a range, slightly adjust to make one... so a chart can show a single data point
      if (x.domain()[0] === x.domain()[1] || y.domain()[0] === y.domain()[1]) singlePoint = true;
      if (x.domain()[0] === x.domain()[1])
        x.domain()[0] ?
            x.domain([x.domain()[0] - x.domain()[0] * 0.01, x.domain()[1] + x.domain()[1] * 0.01])
          : x.domain([-1,1]);

      if (y.domain()[0] === y.domain()[1])
        y.domain()[0] ?
            y.domain([y.domain()[0] + y.domain()[0] * 0.01, y.domain()[1] - y.domain()[1] * 0.01])
          : y.domain([-1,1]);

      if (z.domain().length < 2) {
        z.domain([0, z.domain()]);
      }

      x0 = x0 || x;
      y0 = y0 || y;
      z0 = z0 || z;

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-scatter').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-scatter nv-chart-' + id + (singlePoint ? ' nv-single-point' : ''));
      var defsEnter = wrapEnter.append('defs');
      var gEnter = wrapEnter.append('g');
      var g = wrap.select('g');

      //set up the gradient constructor function
      chart.gradient = function(d,i) {
        return nv.utils.colorRadialGradient( d, id+'-'+i, {x:0.5, y:0.5, r:0.5, s:0, u:'objectBoundingBox'}, color(d,i), wrap.select('defs') );
      };

      gEnter.append('g').attr('class', 'nv-groups');
      gEnter.append('g').attr('class', 'nv-point-paths');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------


      defsEnter.append('clipPath')
          .attr('id', 'nv-edge-clip-' + id)
        .append('rect');

      wrap.select('#nv-edge-clip-' + id + ' rect')
          .attr('width', availableWidth)
          .attr('height', availableHeight);

      g   .attr('clip-path', clipEdge ? 'url(#nv-edge-clip-' + id + ')' : '');


      function updateInteractiveLayer() {

        if (!interactive) return false;

        var eventElements;

        var vertices = d3.merge(data.map(function(group, groupIndex) {
            return group.values
              .map(function(point, pointIndex) {
                // *Adding noise to make duplicates very unlikely
                // **Injecting series and point index for reference
                return [x(getX(point,pointIndex)) * (Math.random() / 1e12 + 1)  , y(getY(point,pointIndex)) * (Math.random() / 1e12 + 1), groupIndex, pointIndex, point]; //temp hack to add noise untill I think of a better way so there are no duplicates
              })
              .filter(function(pointArray, pointIndex) {
                return pointActive(pointArray[4], pointIndex); // Issue #237.. move filter to after map, so pointIndex is correct!
              })
          })
        );

        function buildEventObject(e, d, i, j) {
          return {
              series: data[j],
              point: data[j].values[i],
              pointIndex: i,
              seriesIndex: j,
              pos: [e.offsetX, e.offsetY],
              id: id,
              e: e
            };
        }

        //inject series and point index for reference into voronoi
        if (useVoronoi === true) {
          if (clipVoronoi) {
            var pointClipsEnter = wrap.select('defs').selectAll('.nv-point-clips')
                .data([id])
              .enter();

            pointClipsEnter.append('clipPath')
                  .attr('class', 'nv-point-clips')
                  .attr('id', 'nv-points-clip-' + id);

            var pointClips = wrap.select('#nv-points-clip-' + id).selectAll('circle')
                .data(vertices);
            pointClips.enter().append('circle')
                .attr('r', clipRadius);
            pointClips.exit().remove();
            pointClips
                .attr('cx', function(d) { return d[0] })
                .attr('cy', function(d) { return d[1] });

            wrap.select('.nv-point-paths')
                .attr('clip-path', 'url(#nv-points-clip-' + id + ')');
          }

          if (vertices.length < 3) {
            // Issue #283 - Adding 2 dummy points to the voronoi b/c voronoi requires min 3 points to work
            vertices.push([x.range()[0] - 20, y.range()[0] - 20, null, null]);
            vertices.push([x.range()[1] + 20, y.range()[1] + 20, null, null]);
            vertices.push([x.range()[0] - 20, y.range()[0] + 20, null, null]);
            vertices.push([x.range()[1] + 20, y.range()[1] - 20, null, null]);
          }

          var bounds = d3.geom.polygon([
              [-10, -10],
              [-10, height + 10],
              [width + 10, height + 10],
              [width + 10, -10]
          ]);

          var voronoi = d3.geom.voronoi(vertices).map(function(d, i) {
              return {
                'data': bounds.clip(d),
                'series': vertices[i][2],
                'point': vertices[i][3]
              };
            }).filter(function(d) { return d.series !== null; });

          var pointPaths = wrap.select('.nv-point-paths').selectAll('path')
              .data(voronoi);
          pointPaths.enter().append('path')
              .attr('class', function(d, i) { return 'nv-path-' + i; });
          pointPaths.exit().remove();
          pointPaths
              .attr('d', function(d) { return 'M' + d.data.join('L') + 'Z'; });


          pointPaths
              .on('click', function(d) {
                if (needsUpdate) return 0;
                dispatch.elementClick(buildEventObject(d3.event, d, d.point, d.series));
              })
              .on('mouseover', function(d) {
                if (needsUpdate) return 0;
                dispatch.elementMouseover(buildEventObject(d3.event, d, d.point, d.series));
              })
              .on('mouseout', function(d, i) {
                if (needsUpdate) return 0;
                dispatch.elementMouseout(buildEventObject(d3.event, d, d.point, d.series));
              })
              .on('mousemove', function(d, i) {
                dispatch.elementMousemove(buildEventObject(d3.event, d, d.point, d.series));
              });
        } else {
          // add event handlers to points instead voronoi paths
          wrap.select('.nv-groups').selectAll('.nv-group')
            .selectAll('.nv-point')
              //.data(dataWithPoints)
              .style('pointer-events', 'auto') // recativate events, disabled by css
              .on('click', function(d, i) {
                if (needsUpdate || !data[d.series]) return 0; //check if this is a dummy point
                dispatch.elementClick(buildEventObject(d3.event, d, i, d.series));
              })
              .on('mouseover', function(d, i) {
                if (needsUpdate || !data[d.series]) return 0; //check if this is a dummy point
                dispatch.elementMouseover(buildEventObject(d3.event, d, i, d.series));
              })
              .on('mouseout', function(d, i) {
                if (needsUpdate || !data[d.series]) return 0; //check if this is a dummy point
                dispatch.elementMouseout(buildEventObject(d3.event, d, i, d.series));
              })
              .on('mousemove', function(d, i) {
                dispatch.elementMousemove(buildEventObject(d3.event, d, i, d.series));
              });
        }

        needsUpdate = false;
      }

      needsUpdate = true;

      var groups = wrap.select('.nv-groups').selectAll('.nv-group')
          .data(function(d) { return d }, function(d) { return d.key });
      groups.enter().append('g')
          .style('stroke-opacity', 1e-6)
          .style('fill-opacity', 1e-6);
      d3.transition(groups.exit())
          .style('stroke-opacity', 1e-6)
          .style('fill-opacity', 1e-6)
          .remove();
      groups
          .attr('class', function(d,i) { return classes(d,d.series); })
          .attr('fill', function(d,i) { return fill(d,d.series); })
          .attr('stroke', function(d,i) { return fill(d, d.series); })
          .classed('hover', function(d) { return d.hover; });
      d3.transition(groups)
          .style('stroke-opacity', 1)
          .style('fill-opacity', .5);


      if (onlyCircles) {

        var points = groups.selectAll('circle.nv-point')
            .data(function(d) { return d.values });
        points.enter().append('circle')
            .attr('cx', function(d,i) { return x0(getX(d,i)) })
            .attr('cy', function(d,i) { return y0(getY(d,i)) })
            .attr('r', function(d,i) { return Math.sqrt(z(getSize(d,i))/Math.PI) });
        points.exit().remove();
        d3.transition(groups.exit().selectAll('path.nv-point'))
            .attr('cx', function(d,i) { return x(getX(d,i)) })
            .attr('cy', function(d,i) { return y(getY(d,i)) })
            .remove();
        points.attr('class', function(d,i) { return 'nv-point nv-point-' + i });
        d3.transition(points)
            .attr('cx', function(d,i) { return x(getX(d,i)) })
            .attr('cy', function(d,i) { return y(getY(d,i)) })
            .attr('r', function(d,i) { return Math.sqrt(z(getSize(d,i))/Math.PI) });

      } else {

        var points = groups.selectAll('path.nv-point')
            .data(function(d) { return d.values });
        points.enter().append('path')
            .attr('transform', function(d,i) {
              return 'translate(' + x0(getX(d,i)) + ',' + y0(getY(d,i)) + ')'
            })
            .attr('d',
              d3.svg.symbol()
                .type(getShape)
                .size(function(d,i) { return z(getSize(d,i)) })
            );
        points.exit().remove();
        d3.transition(groups.exit().selectAll('path.nv-point'))
            .attr('transform', function(d,i) {
              return 'translate(' + x(getX(d,i)) + ',' + y(getY(d,i)) + ')'
            })
            .remove();
        points.attr('class', function(d,i) { return 'nv-point nv-point-' + i });
        d3.transition(points)
            .attr('transform', function(d,i) {
              //nv.log(d,i,getX(d,i), x(getX(d,i)));
              return 'translate(' + x(getX(d,i)) + ',' + y(getY(d,i)) + ')'
            })
            .attr('d',
              d3.svg.symbol()
                .type(getShape)
                .size(function(d,i) { return z(getSize(d,i)) })
            );
      }


      // Delay updating the invisible interactive layer for smoother animation
      clearTimeout(timeoutID); // stop repeat calls to updateInteractiveLayer
      timeoutID = setTimeout(updateInteractiveLayer, 300);
      //updateInteractiveLayer();

      //store old scales for use in transitions on update
      x0 = x.copy();
      y0 = y.copy();
      z0 = z.copy();

    });

    return chart;
  }


  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  dispatch.on('elementMouseover.point', function(d) {
    if (interactive)
      d3.select('.nv-chart-' + id + ' .nv-series-' + d.seriesIndex + ' .nv-point-' + d.pointIndex)
          .classed('hover', true);
  });

  dispatch.on('elementMouseout.point', function(d) {
    if (interactive)
      d3.select('.nv-chart-' + id + ' .nv-series-' + d.seriesIndex + ' .nv-point-' + d.pointIndex)
          .classed('hover', false);
  });

  //============================================================


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  chart.dispatch = dispatch;

  chart.color = function(_) {
    if (!arguments.length) return color;
    color = _;
    return chart;
  };
  chart.fill = function(_) {
    if (!arguments.length) return fill;
    fill = _;
    return chart;
  };
  chart.classes = function(_) {
    if (!arguments.length) return classes;
    classes = _;
    return chart;
  };
  chart.gradient = function(_) {
    if (!arguments.length) return gradient;
    gradient = _;
    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) return getX;
    getX = d3.functor(_);
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) return getY;
    getY = d3.functor(_);
    return chart;
  };

  chart.size = function(_) {
    if (!arguments.length) return getSize;
    getSize = d3.functor(_);
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin.top    = typeof _.top    != 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  != 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom != 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   != 'undefined' ? _.left   : margin.left;
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

  chart.xScale = function(_) {
    if (!arguments.length) return x;
    x = _;
    return chart;
  };

  chart.yScale = function(_) {
    if (!arguments.length) return y;
    y = _;
    return chart;
  };

  chart.zScale = function(_) {
    if (!arguments.length) return z;
    z = _;
    return chart;
  };

  chart.xDomain = function(_) {
    if (!arguments.length) return xDomain;
    xDomain = _;
    return chart;
  };

  chart.yDomain = function(_) {
    if (!arguments.length) return yDomain;
    yDomain = _;
    return chart;
  };

  chart.sizeDomain = function(_) {
    if (!arguments.length) return sizeDomain;
    sizeDomain = _;
    return chart;
  };

  chart.sizeRange = function(_) {
    if (!arguments.length) return sizeRange;
    sizeRange = _;
    return chart;
  };

  chart.forceX = function(_) {
    if (!arguments.length) return forceX;
    forceX = _;
    return chart;
  };

  chart.forceY = function(_) {
    if (!arguments.length) return forceY;
    forceY = _;
    return chart;
  };

  chart.forceSize = function(_) {
    if (!arguments.length) return forceSize;
    forceSize = _;
    return chart;
  };

  chart.interactive = function(_) {
    if (!arguments.length) return interactive;
    interactive = _;
    return chart;
  };

  chart.pointActive = function(_) {
    if (!arguments.length) return pointActive;
    pointActive = _;
    return chart;
  };

  chart.padData = function(_) {
    if (!arguments.length) return padData;
    padData = _;
    return chart;
  };

  chart.padDataOuter = function(_) {
    if (!arguments.length) return padDataOuter;
    padDataOuter = _;
    return chart;
  };

  chart.clipEdge = function(_) {
    if (!arguments.length) return clipEdge;
    clipEdge = _;
    return chart;
  };

  chart.clipVoronoi= function(_) {
    if (!arguments.length) return clipVoronoi;
    clipVoronoi = _;
    return chart;
  };

  chart.useVoronoi= function(_) {
    if (!arguments.length) return useVoronoi;
    useVoronoi = _;
    if (useVoronoi === false) {
        clipVoronoi = false;
    }
    return chart;
  };

  chart.clipRadius = function(_) {
    if (!arguments.length) return clipRadius;
    clipRadius = _;
    return chart;
  };

  chart.shape = function(_) {
    if (!arguments.length) return getShape;
    getShape = _;
    return chart;
  };

  chart.onlyCircles = function(_) {
    if (!arguments.length) return onlyCircles;
    onlyCircles = _;
    return chart;
  };

  chart.id = function(_) {
    if (!arguments.length) return id;
    id = _;
    return chart;
  };

  chart.singlePoint = function(_) {
    if (!arguments.length) return singlePoint;
    singlePoint = _;
    return chart;
  };

  chart.nice = function(_) {
    if (!arguments.length) {
      return nice;
    }
    nice = _;
    return chart;
  };

  //============================================================


  return chart;
}
nv.models.bubbleChart = function () {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 10, right: 10, bottom: 10, left: 10},
      width = null,
      height = null,
      showTitle = false,
      showControls = false,
      showLegend = true,
      direction = 'ltr',
      getX = function (d) { return d.x; },
      getY = function (d) { return d.y; },
      forceY = [0], // 0 is forced by default.. this makes sense for the majority of bar graphs... user can always do chart.forceY([]) to remove
      xDomain,
      yDomain,
      delay = 200,
      groupBy = function (d) { return d.y; },
      filterBy = function (d) { return d.y; },
      clipEdge = false, // if true, masks lines within x and y scale
      seriesLength = 0,
      reduceYTicks = false, // if false a tick will show for every data point
      bubbleClick = function (e) { return; },
      format = d3.time.format("%Y-%m-%d"),
      tooltip = null,
      tooltips = true,
      tooltipContent = function (key, x, y, e, graph) {
        return '<h3>' + key + '</h3>' +
               '<p>' +  y + ' on ' + x + '</p>';
      },
      x,
      y,
      state = {},
      strings = {
        legend: {close: 'Hide legend', open: 'Show legend'},
        controls: {close: 'Hide controls', open: 'Show controls'},
        noData: 'No Data Available.'
      },
      dispatch = d3.dispatch('chartClick', 'tooltipShow', 'tooltipHide', 'tooltipMove', 'stateChange', 'changeState');

  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var scatter = nv.models.scatter()
        .padData(true)
        .padDataOuter(0)
        .size(function (d){ return d.y; })
        .sizeRange([256,1024])
        .singlePoint(true),
      xAxis = nv.models.axis()
        .orient('bottom')
        .tickSize(0)
        .tickPadding(4)
        .highlightZero(false)
        .showMaxMin(false)
        .ticks(d3.time.months, 1)
        .tickFormat(function (d) {
          return d3.time.format('%b')(new Date(d));
        }),
      yAxis = nv.models.axis()
        .orient('left')
        .tickPadding(7)
        .highlightZero(false)
        .showMaxMin(false),
      legend = nv.models.legend()
        .align('center')
        .key(function(d) { return d.key + '%'; });

  var showTooltip = function (e, offsetElement, properties) {
    var left = e.pos[0],
        top = e.pos[1],
        x = e.point.x,
        y = e.point.y,
        content = tooltipContent(e.series.key, x, y, e, chart);

    tooltip = nv.tooltip.show([left, top], content, e.value < 0 ? 'n' : 's', null, offsetElement);
  };

  //============================================================

  function chart(selection) {

    selection.each(function (chartData) {

      var properties = chartData.properties,
          data = chartData.data,
          container = d3.select(this),
          that = this;

      chart.update = function () {
        container.transition().call(chart);
      };

      chart.container = this;

      if (!data || !data.length) {
        return chart;
      }

      //------------------------------------------------------------
      // Process data

      // Calculate the x-axis ticks
      function getTimeTicks(data) {
        function daysInMonth(date) {
          return 32 - new Date(date.getFullYear(), date.getMonth(), 32).getDate();
        }
        var timeExtent =
              d3.extent(
                d3.merge(
                  data.map(function (d) {
                    return d.values.map(function (d, i) {
                      return d3.time.format("%Y-%m-%d").parse(getX(d));
                    });
                  })
                )
              );
        var timeRange =
              d3.time.month.range(
                d3.time.month.floor(timeExtent[0]),
                d3.time.month.ceil(timeExtent[1])
              );
        var timeTicks =
              timeRange.map(function (d) {
                return d3.time.day.offset(d3.time.month.floor(d), daysInMonth(d) / 2 - 1);
              });
        return timeTicks;
      }

      // Group data by groupBy function to prep data for calculating y-axis groups
      // and y scale value for points
      function getGroupTicks(data) {

        var groupedData = d3.nest()
              .key(groupBy)
              .entries(data);

        // Calculate y scale parameters
        var gHeight = 1000 / groupedData.length,
            gOffset = gHeight * 0.25,
            gDomain = [0,1],
            gRange = [0,1],
            gScale = d3.scale.linear().domain(gDomain).range(gRange),
            yValues = [],
            total = 0;

        // Calculate total for each data group and
        // point y value
        groupedData
          .map(function (s, i) {
            s.total = 0;

            s.values = s.values.sort(function (a, b) {
                return b.y < a.y ? -1 : b.y > a.y ? 1 : 0;
              })
              .map(function (p) {
                s.total += p.y;
                return p;
              });

            s.group = i;
            return s;
          })
          .sort(function (a, b) {
            return a.total < b.total ? -1 : a.total > b.total ? 1 : 0;
          })
          .map(function (s, i) {
            total += s.total;

            gDomain = d3.extent(s.values.map(function (p){ return p.y; }));
            gRange = [gHeight * i + gOffset, gHeight * (i + 1) - gOffset];
            gScale.domain(gDomain).range(gRange);

            s.values = s.values
              .map(function (p) {
                p.group = s.group;
                p.opportunity = p.y;
                p.y = gScale(p.opportunity);
                return p;
              });

            yValues.push({y: d3.min(s.values.map(function (p){ return p.y; })), key: s.key});

            return s;
          });

        return yValues;
      }

      //set state.disabled
      state.disabled = data.map(function (d) { return !!d.disabled; });

      // Now that group calculations are done,
      // group the data by filter so that legend filters
      var filteredData = d3.nest()
            .key(filterBy)
            .entries(data);

      //add series index to each data point for reference
      filteredData = filteredData
        .sort(function (a, b){
          //sort legend by key
          return parseInt(a.key, 10) < parseInt(b.key, 10) ? -1 : parseInt(a.key, 10) > parseInt(b.key, 10) ? 1 : 0;
        })
        .map(function (d, i) {
          d.series = i;
          d.classes = d.values[0].classes;
          d.color = d.values[0].color;
          return d;
        });

      var timeExtent = d3.extent(
            d3.merge(
              filteredData.map(function (d) {
                return d.values.map(function (d, i) {
                  return d3.time.format("%Y-%m-%d").parse(d.x);
                });
              })
            )
          );

      var xD = [
        d3.time.month.floor(timeExtent[0]),
        d3.time.day.offset(d3.time.month.ceil(timeExtent[1]), -1)
      ];

      var yValues = getGroupTicks(data);

      var yD = d3.extent(
            d3.merge(
              filteredData.map(function (d) {
                return d.values.map(function (d, i) {
                  return getY(d,i);
                });
              })
            ).concat(forceY)
          );

      //------------------------------------------------------------
      // Setup Scales

      x = scatter.xScale();
      y = scatter.yScale();

      xAxis
        .scale(x);
      yAxis
        .scale(y);

      chart.render = function () {

        var width = width  || parseInt(container.style('width'), 10 || 960),
            height = height || parseInt(container.style('height'), 10 || 400),
            availableWidth = width - margin.left - margin.right,
            availableHeight = height - margin.top - margin.bottom,
            innerWidth = availableWidth,
            innerHeight = availableHeight,
            innerMargin = {top: 0, right: 0, bottom: 0, left: 0},
            maxBubbleSize = Math.sqrt(scatter.sizeRange()[1] / Math.PI);

        //------------------------------------------------------------
        // Display No Data message if there's nothing to show.

        if (!data || !data.length) {
          var noDataText = container.selectAll('.nv-noData').data([chart.strings().noData]);

          noDataText.enter().append('text')
            .attr('class', 'nvd3 nv-noData')
            .attr('dy', '-.7em')
            .style('text-anchor', 'middle');

          noDataText
            .attr('x', margin.left + availableWidth / 2)
            .attr('y', margin.top + availableHeight / 2)
            .text(function (d) {
              return d;
            });

          return chart;
        } else {
          container.selectAll('.nv-noData').remove();
        }

        //------------------------------------------------------------
        // Setup containers and skeleton of chart

        var wrap = container.selectAll('g.nv-wrap.nv-bubbleChart').data([filteredData]),
            gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-bubbleChart').append('g'),
            g = wrap.select('g').attr('class', 'nv-chartWrap');

        gEnter.append('rect').attr('class', 'nv-background')
          .attr('x', -margin.left)
          .attr('y', -margin.top)
          .attr('fill', '#FFF');

        g.select('.nv-background')
          .attr('width', availableWidth + margin.left + margin.right)
          .attr('height', availableHeight + margin.top + margin.bottom);

        gEnter.append('g').attr('class', 'nv-titleWrap');
        var titleWrap = g.select('.nv-titleWrap');
        gEnter.append('g').attr('class', 'nv-x nv-axis');
        var xAxisWrap = g.select('.nv-x.nv-axis');
        gEnter.append('g').attr('class', 'nv-y nv-axis');
        var yAxisWrap = g.select('.nv-y.nv-axis');
        gEnter.append('g').attr('class', 'nv-bubblesWrap');
        var bubblesWrap = g.select('.nv-bubblesWrap');
        gEnter.append('g').attr('class', 'nv-legendWrap');
        var legendWrap = g.select('.nv-legendWrap');

        wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

        //------------------------------------------------------------
        // Title & Legend

        var titleBBox = {width: 0, height: 0};
        titleWrap.select('.nv-title').remove();

        if (showTitle && properties.title) {
          titleWrap
            .append('text')
              .attr('class', 'nv-title')
              .attr('x', direction === 'rtl' ? availableWidth : 0)
              .attr('y', 0)
              .attr('dy', '.75em')
              .attr('text-anchor', 'start')
              .text(properties.title)
              .attr('stroke', 'none')
              .attr('fill', 'black');

          titleBBox = nv.utils.getTextBBox(g.select('.nv-title'));

          innerMargin.top += titleBBox.height + 12;
        }

        if (showLegend) {
          legend
            .id('legend_' + chart.id())
            .strings(chart.strings().legend)
            .align('center')
            .height(availableHeight - innerMargin.top);
          legendWrap
            .datum(filteredData)
            .call(legend);

          legend
            .arrange(availableWidth);

          var legendLinkBBox = nv.utils.getTextBBox(legendWrap.select('.nv-legend-link')),
              legendSpace = availableWidth - titleBBox.width - 6,
              legendTop = showTitle && legend.collapsed() && legendSpace > legendLinkBBox.width ? true : false,
              xpos = direction === 'rtl' || !legend.collapsed() ? 0 : availableWidth - legend.width(),
              ypos = titleBBox.height;
          if (legendTop) {
            ypos = titleBBox.height - legend.height() / 2 - legendLinkBBox.height / 2;
          } else if (!showTitle) {
            ypos = - legend.margin().top;
          }

          legendWrap
            .attr('transform', 'translate(' + xpos + ',' + ypos + ')');

          innerMargin.top += legendTop ? 0 : legend.height() - 12;
        }

        // Recalc inner margins
        innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

        //------------------------------------------------------------
        // Main Chart Components
        // Initial calls

        // X-Axis

        xAxis
          .tickValues(getTimeTicks(filteredData));

        xAxisWrap
          .call(xAxis);

        innerMargin[xAxis.orient()] += xAxis.height();
        innerMargin.top += maxBubbleSize;
        innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

        // Y-Axis
        yAxis
          .ticks(yValues.length)
          .tickValues(yValues.map(function (d, i) {
            return yValues[i].y;
          }))
          .tickFormat(function (d, i) {
            return yValues[i].key;
          });

        yAxisWrap
          .call(yAxis);

        innerMargin[yAxis.orient()] += yAxis.width();
        innerWidth = availableWidth - innerMargin.left - innerMargin.right;

        scatter
          .xDomain(xD)
          .yDomain(yD)
          .width(innerWidth)
          .height(innerHeight)
          .id(chart.id());

        bubblesWrap
          .datum(filteredData.filter(function (d) {
            return !d.disabled;
          }))
          .attr('transform', 'translate(' + innerMargin.left + ',' + innerMargin.top + ')')
          .transition().duration(chart.delay())
            .call(scatter);

        //------------------------------------------------------------
        // Main Chart Components
        // Recall to set final sizes

        xAxisWrap
          .attr('transform', 'translate(' + innerMargin.left + ',' + (xAxis.orient() === 'bottom' ? innerHeight + innerMargin.top : innerMargin.top) + ')')
          .transition()
            .call(xAxis);

        yAxis
          .tickSize(-innerWidth, 0);

        yAxisWrap
          .attr('transform', 'translate(' + (yAxis.orient() === 'left' ? innerMargin.left : innerMargin.left + innerWidth) + ',' + innerMargin.top + ')')
          .transition()
            .call(yAxis);

      };

      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      legend.dispatch.on('legendClick', function (d, i) {
        d.disabled = !d.disabled;

        if (!data.filter(function (d) {
          return !d.disabled;
        }).length) {
          data.map(function (d) {
            d.disabled = false;
            g.selectAll('.nv-series').classed('disabled', false);
            return d;
          });
        }

        state.disabled = data.map(function (d) {
          return !!d.disabled;
        });
        dispatch.stateChange(state);

        container.transition().call(chart.render);
      });

      dispatch.on('tooltipShow', function (e) {
        if (tooltips) {
          showTooltip(e, that.parentNode);
        }
      });

      dispatch.on('tooltipHide', function () {
        if (tooltips) {
          nv.tooltip.cleanup();
        }
      });

      dispatch.on('tooltipMove', function (e) {
        if (tooltip) {
          nv.tooltip.position(that.parentNode, tooltip, e.pos);
        }
      });

      // Update chart from a state object passed to event handler
      dispatch.on('changeState', function (e) {
        if (typeof e.disabled !== 'undefined') {
          data.forEach(function (series,i) {
            series.disabled = e.disabled[i];
          });
          state.disabled = e.disabled;
        }

        container.transition().call(chart);
      });

      dispatch.on('chartClick', function() {
        dispatch.tooltipHide();
        if (legend.enabled()) {
          legend.dispatch.closeMenu();
        }
      });

      scatter.dispatch.on('elementClick', function(eo) {
        dispatch.chartClick();
        bubbleClick(eo);
      });

      chart.render();
    });

    return chart;
  }

  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  scatter.dispatch.on('elementMouseover.tooltip', function (e) {
    dispatch.tooltipShow(e);
  });

  scatter.dispatch.on('elementMouseout.tooltip', function (e) {
    dispatch.tooltipHide(e);
  });

  scatter.dispatch.on('elementMousemove.tooltip', function (e) {
    dispatch.tooltipMove(e);
  });


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.scatter = scatter;
  chart.legend = legend;
  chart.xAxis = xAxis;
  chart.yAxis = yAxis;

  d3.rebind(chart, scatter, 'id', 'x', 'y', 'xScale', 'yScale', 'xDomain', 'yDomain', 'forceX', 'forceY', 'clipEdge', 'delay', 'color', 'fill', 'classes', 'gradient');
  d3.rebind(chart, scatter, 'size', 'zScale', 'sizeDomain', 'forceSize', 'interactive', 'clipVoronoi', 'clipRadius');
  d3.rebind(chart, xAxis, 'rotateTicks', 'reduceXTicks', 'staggerTicks', 'wrapTicks');

  chart.colorData = function(_) {
    var type = arguments[0],
        params = arguments[1] || {};
    var color = function(d, i) {
          return nv.utils.defaultColor()(d, d.series);
        };
    var classes = function(d, i) {
          return 'nv-group nv-series-' + d.series;
        };

    switch (type) {
      case 'graduated':
        color = function(d, i) {
          return d3.interpolateHsl(d3.rgb(params.c1), d3.rgb(params.c2))(d.series / params.l);
        };
        break;
      case 'class':
        color = function() {
          return 'inherit';
        };
        classes = function(d, i) {
          var iClass = (d.series * (params.step || 1)) % 14;
          iClass = (iClass > 9 ? '' : '0') + iClass;
          return 'nv-group nv-series-' + d.series + ' nv-fill' + iClass;
        };
        break;
      case 'data':
        color = function(d, i) {
          return d.classes ? 'inherit' : d.color || nv.utils.defaultColor()(d, d.series);
        };
        classes = function(d, i) {
          return 'nv-group nv-series-' + d.series + (d.classes ? ' ' + d.classes : '');
        };
        break;
    }

    var fill = (!params.gradient) ? color : function(d, i) {
      return scatter.gradient(d, d.series);
    };

    scatter.color(color);
    scatter.fill(fill);
    scatter.classes(classes);

    legend.color(color);
    legend.classes(classes);

    return chart;
  };

  chart.margin = function (_) {
    if (!arguments.length) {
      return margin;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        margin[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.width = function (_) {
    if (!arguments.length) {
      return width;
    }
    width = _;
    return chart;
  };

  chart.height = function (_) {
    if (!arguments.length) {
      return height;
    }
    height = _;
    return chart;
  };

  chart.showTitle = function (_) {
    if (!arguments.length) {
      return showTitle;
    }
    showTitle = _;
    return chart;
  };

  chart.showLegend = function (_) {
    if (!arguments.length) {
      return showLegend;
    }
    showLegend = _;
    return chart;
  };

  chart.tooltip = function (_) {
    if (!arguments.length) {
      return tooltip;
    }
    tooltip = _;
    return chart;
  };

  chart.tooltips = function (_) {
    if (!arguments.length) {
      return tooltips;
    }
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function (_) {
    if (!arguments.length) {
      return tooltipContent;
    }
    tooltipContent = _;
    return chart;
  };

  chart.state = function (_) {
    if (!arguments.length) {
      return state;
    }
    state = _;
    return chart;
  };

  chart.delay = function(_) {
    if (!arguments.length) {
      return delay;
    }
    delay = _;
    return chart;
  };

  chart.bubbleClick = function (_) {
    if (!arguments.length) {
      return bubbleClick;
    }
    bubbleClick = _;
    return chart;
  };

  chart.groupBy = function (_) {
    if (!arguments.length) {
      return groupBy;
    }
    groupBy = _;
    return chart;
  };

  chart.filterBy = function (_) {
    if (!arguments.length) {
      return filterBy;
    }
    filterBy = _;
    return chart;
  };

  chart.colorFill = function (_) {
    return chart;
  };

  chart.strings = function (_) {
    if (!arguments.length) {
      return strings;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        strings[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.direction = function(_) {
    if (!arguments.length) {
      return direction;
    }
    direction = _;
    yAxis.direction(_);
    legend.direction(_);
    return chart;
  };

  //============================================================

  return chart;
};

nv.models.funnel = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 0, right: 0, bottom: 0, left: 0},
      width = 960,
      height = 500,
      r = 0.3, // ratio of width to height (or slope)
      y = d3.scale.linear(),
      id = Math.floor(Math.random() * 10000), //Create semi-unique ID in case user doesn't select one
      getX = function(d) { return d.x; },
      getY = function(d) { return d.y; },
      getH = function(d) { return d.height; },
      getV = function(d) { return d.value; },
      forceY = [0], // 0 is forced by default.. this makes sense for the majority of bar graphs... user can always do chart.forceY([]) to remove
      clipEdge = true,
      yDomain,
      delay = 0,
      wrapLabels = true,
      minLabelWidth = 75,
      durationMs = 0,
      fmtValueLabel = function(d) { return d.label || d.value || d; },
      color = function(d, i) { return nv.utils.defaultColor()(d, d.series); },
      fill = color,
      classes = function(d, i) { return 'nv-group nv-series-' + d.series; },
      dispatch = d3.dispatch('chartClick', 'elementClick', 'elementDblClick', 'elementMouseover', 'elementMouseout', 'elementMousemove');


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  // These values are preserved between renderings
  var calculatedWidth = 0,
      calculatedHeight = 0,
      calculatedCenter = 0;

  //============================================================
  // Update chart

  function chart(selection) {
    selection.each(function(data) {
      var availableWidth = width - margin.left - margin.right,
          availableHeight = height - margin.top - margin.bottom,
          container = d3.select(this),

          labelGap = 5,
          labelSpace = 5,
          labelOffset = 0,
          funnelTotal = 0,
          funnelOffset = 0;

      // Add series index to each data point for reference
      data.map(function(series, i) {
        series.values = series.values.map(function(point) {
          point.series = i;
          // if value is undefined, not a legitimate 0 value, use point.y
          if (typeof point.value == 'undefined') {
            point.value = getY(point);
          }
          // count total of funnel
          funnelTotal += parseFloat(point.value);
          return point;
        });
        return series;
      });

      //------------------------------------------------------------
      // Setup scales

      function calcDimensions() {
        calculatedWidth = calcWidth(funnelOffset);
        calculatedHeight = calcHeight();
        calculatedCenter = calcCenter(funnelOffset);
      }

      function calcScales() {
        var funnelArea = areaTrapezoid(calculatedHeight, calculatedWidth),
            funnelBase = calculatedWidth - 2 * r * calculatedHeight,
            funnelShift = 0,
            funnelMinHeight = 24;

        //------------------------------------------------------------
        // Adjust points to compensate for parallax of slice
        // by increasing height relative to area of funnel

        data.map(function(series, i) {
          series.values = series.values.map(function(point) {
            point.height = 0;
            if (funnelTotal > 0) {
              point.height = heightTrapezoid(funnelArea * point.value / funnelTotal, funnelBase);
            }
            if (point.height < funnelMinHeight / 2) {
              funnelShift += point.height - funnelMinHeight / 2;
              point.height = funnelMinHeight / 2;
            } else if (funnelShift < 0 && point.height + funnelShift > funnelMinHeight / 2) {
              point.height += funnelShift;
              funnelShift = 0;
            }
            funnelBase += 2 * r * point.height;
            return point;
          });
          return series;
        });

        data = d3.layout.stack()
                    .offset('zero')
                    .values(function(d) { return d.values; })
                    .y(getH)(data);

        // Remap and flatten the data for use in calculating the scales' domains
        var seriesData = (yDomain) ? [] : // if we know yDomain, no need to calculate
              d3.extent(d3.merge(data.map(function(d) {
                return d.values.map(function(d, i) {
                  return getH(d, i) + d.y0;
                });
              })).concat(forceY));

        y .domain(yDomain || seriesData)
          .range([calculatedHeight, 0]);
      }

      calcDimensions();
      calcScales();

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-funnel').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-funnel');
      var defsEnter = wrapEnter.append('defs');
      var gEnter = wrapEnter.append('g');
      var g = wrap.select('g');

      //set up the gradient constructor function
      chart.gradient = function(d, i, p) {
        return nv.utils.colorLinearGradient(d, id + '-' + i, p, color(d, i), wrap.select('defs'));
      };

      gEnter.append('g').attr('class', 'nv-groups');
      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------
      // Clip path

      defsEnter.append('clipPath')
        .attr('id', 'nv-edge-clip-' + id)
          .append('rect');
      wrap.select('#nv-edge-clip-' + id + ' rect')
        .attr('width', availableWidth + 1)
        .attr('height', availableHeight + 1);
      g.attr('clip-path', clipEdge ? 'url(#nv-edge-clip-' + id + ')' : '');

      //------------------------------------------------------------
      // Append major data series grouping containers

      var groups = wrap.select('.nv-groups').selectAll('.nv-group')
            .data(function(d) { return d; }, function(d) { return d.key; });

      groups.enter().append('g')
        .style('stroke-opacity', 1e-6)
        .style('fill-opacity', 1e-6);

      groups
        .attr('class', classes)
        .attr('fill', fill)
        .classed('hover', function(d) { return d.hover; })
        .classed('nv-active', function(d) { return d.active === 'active'; })
        .classed('nv-inactive', function(d) { return d.active === 'inactive'; })
        .style({'stroke': '#FFFFFF', 'stroke-width': 2});

      groups.transition().duration(durationMs)
          .style('stroke-opacity', 1)
          .style('fill-opacity', 1);

      groups.exit().transition().duration(durationMs)
        .selectAll('polygon.nv-bar')
        .delay(function(d, i) { return i * delay / data[0].values.length; })
          .attr('points', function(d) {
            return pointsTrapezoid(d, 0, calculatedWidth);
          })
          .style('stroke-opacity', 1e-6)
          .style('fill-opacity', 1e-6)
          .remove();

      groups.exit().transition().duration(durationMs)
        .selectAll('g.nv-label-value')
        .delay(function(d, i) { return i * delay / data[0].values.length; })
          .attr('y', 0)
          .attr('transform', 'translate(' + calculatedCenter + ',0)')
          .style('stroke-opacity', 1e-6)
          .style('fill-opacity', 1e-6)
          .remove();

      //------------------------------------------------------------
      // Append polygons for funnel

      var funs = groups.selectAll('polygon.nv-bar')
            .data(function(d) {
              return d.values;
            });

      funs.exit().remove();

      funs.enter()
        .append('polygon')
          .attr('class', 'nv-bar')
          .attr('points', function(d) {
            return pointsTrapezoid(d, 0, calculatedWidth);
          })
          .on('mouseover', function(d, i) {
            d3.select(this).classed('hover', true);
            var eo = buildEventObject(d3.event, d, i);
            dispatch.elementMouseover(eo);
          })
          .on('mousemove', function(d, i) {
            var eo = buildEventObject(d3.event, d, i);
            dispatch.elementMousemove(eo);
          })
          .on('mouseout', function(d, i) {
            d3.select(this).classed('hover', false);
            dispatch.elementMouseout();
          })
          .on('click', function(d, i) {
            d3.event.stopPropagation();
            var eo = buildEventObject(d3.event, d, i);
            dispatch.elementClick(eo);
          })
          .on('dblclick', function(d, i) {
            d3.event.stopPropagation();
            var eo = buildEventObject(d3.event, d, i);
            dispatch.elementDblClick(eo);
          });

      function buildEventObject(e, d, i) {
        return {
            value: getV(d, i),
            point: d,
            id: id,
            series: data[d.series],
            pos: [e.offsetX, e.offsetY],
            pointIndex: i,
            seriesIndex: d.series,
            e: e
          };
      }

      //------------------------------------------------------------
      // Append containers for labels

      var labels = groups.selectAll('.nv-label-value')
            .data(function(d) { return d.values; });

      labels.enter()
        .append('g')
          .attr('class', 'nv-label-value')
          .attr('transform', 'translate(' + calculatedCenter + ',0)');

      var sideLabels = labels.filter('.nv-label-side');

      //------------------------------------------------------------
      // Update funnel labels

      function renderFunnelLabels() {
        // Remove responsive label elements
        labels.selectAll('text').remove();

        // Append label text and wrap if needed
        labels.append('text')
          .text(fmtKey)
            .call(fmtLabel, 'nv-label', 0.85, '11px', 'middle', fmtFill);

        labels.select('.nv-label')
          .call(
            handleLabel,
            (wrapLabels ? wrapLabel : ellipsifyLabel),
            calcFunnelWidthAtSliceMidpoint,
            function(txt, dy) {
              fmtLabel(txt, 'nv-label', dy, '11px', 'middle', fmtFill);
            }
          );

        // Append value and count text
        labels.append('text')
          .text(fmtValueLabel)
            .call(fmtLabel, 'nv-value', 0.85, '15px', 'middle', fmtFill);

        labels.select('.nv-value')
          .append('tspan')
            .text(fmtCount);

        labels
          .call(positionValue);

        // Position labels and identify side labels
        labels
          .call(calcFunnelLabelDimensions);

        labels
          .classed('nv-label-side', function(d) { return d.tooTall || d.tooWide; });
      }

      //------------------------------------------------------------
      // Update side labels

      function renderSideLabels() {
        // Remove all responsive elements
        sideLabels = labels.filter('.nv-label-side');
        labels.selectAll('polyline').remove();
        sideLabels.selectAll('.nv-label').remove();

        // Position side labels
        sideLabels.append('text')
          .text(fmtKey)
            .call(fmtLabel, 'nv-label', 0.85, '11px', 'start', '#555');

        sideLabels.select('.nv-label')
          .call(
            handleLabel,
            (wrapLabels ? wrapLabel : ellipsifyLabel),
            (wrapLabels ? calcSideWidth : maxSideLabelWidth),
            function(txt, dy) {
              fmtLabel(txt, 'nv-label', dy, '11px', 'start', '#555');
            }
          );

        sideLabels
          .call(positionValue);

        sideLabels.select('.nv-value')
          .style({'font-size': '15px', 'text-anchor': 'start', 'fill': '#555'});

        sideLabels
          .call(calcSideLabelDimensions);

        // Reflow side label vertical position to prevent overlap
        // Top to bottom

        var d0 = 0;

        sideLabels.reverse().each(function(d, i) {
            if (!d0) {
              d.labelBottom = d.labelTop + d.labelHeight + labelSpace;
              d0 = d.labelBottom;
              return;
            }

            d.labelTop = Math.max(d0, d.labelTop);
            d.labelBottom = d.labelTop + d.labelHeight + labelSpace;
            d0 = d.labelBottom;
          });

        sideLabels.reverse();

        // And then...
        // Bottom to top
        if (d0 && d0 - labelSpace > d3.max(y.range())) {

          d0 = 0;

          sideLabels.each(function(d, i) {
              if (!d0) {
                d.labelBottom = d3.max(y.range()) - 1;
                d.labelTop = d.labelBottom - d.labelHeight;
                d0 = d.labelTop;
                return;
              }

              d.labelBottom = Math.min(d0, d.labelBottom);
              d.labelTop = d.labelBottom - d.labelHeight - labelSpace;
              d0 = d.labelTop;
            });

          if (d0 < 0) {
            sideLabels.each(function(d, i) {
                d.labelTop -= d0;
                d.labelBottom -= d0;
              });
          }
        }

        d0 = 0;

        //------------------------------------------------------------
        // Recalculate funnel offset based on side label dimensions

        sideLabels
          .call(calcOffsets);
      }

      //------------------------------------------------------------
      // Calculate the width and position of labels which
      // determines the funnel offset dimension

      function renderLabels() {
        renderFunnelLabels();
        renderSideLabels();
      }

      renderLabels();
      calcDimensions();

      // Calls twice since the first call may create a funnel offset
      // which decreases the funnel width which impacts label position

      calcScales();
      renderLabels();
      calcDimensions();

      calcScales();
      renderLabels();
      calcDimensions();

      //------------------------------------------------------------
      // Reposition responsive elements

      funs
        .attr('points', function(d) {
          var scalar = d.active && d.active === 'active' ? 1.05 : 1;
          return pointsTrapezoid(d, 1, calculatedWidth * scalar);
        });

      labels
        .attr('transform', function(d) {
          var xTrans = d.tooTall ? 0 : calculatedCenter,
              yTrans = d.tooTall ? 0 : d.labelTop;
          return 'translate(' + xTrans + ',' + yTrans + ')';
        });

      sideLabels
        .attr('transform', function(d) {
          return 'translate(' + labelOffset + ',' + d.labelTop + ')';
        });

      sideLabels
        .append('polyline')
          .attr('class', 'nv-label-leader')
          .style({'fill-opacity': 0, 'stroke': '#999', 'stroke-width': 1, 'stroke-opacity': 0.5});

      sideLabels.reverse();
      sideLabels.selectAll('polyline')
        .call(pointsLeader);
      sideLabels.reverse();

      //------------------------------------------------------------
      // Utility functions

      // TODO: use scales instead of ratio algebra
      // var funnelScale = d3.scale.linear()
      //       .domain([w / 2, minimum])
      //       .range([0, maxy1*thenscalethistopreventminimumfrompassing]);

      function wrapLabel(d, lbl, fnWidth, fmtLabel) {
        var text = lbl.text(),
            dy = parseFloat(lbl.attr('dy')),
            word,
            words = text.split(/\s+/).reverse(),
            line = [],
            lineNumber = 0,
            maxWidth = fnWidth(d, 0),
            parent = d3.select(lbl.node().parentNode);

        lbl.text(null);

        while (word = words.pop()) {
          line.push(word);
          lbl.text(line.join(' '));

          if (lbl.node().getComputedTextLength() > maxWidth && line.length > 1) {
            line.pop();
            lbl.text(line.join(' '));
            line = [word];
            lbl = parent.append('text');
            lbl.text(word)
              .call(fmtLabel, ++lineNumber * 1.1 + dy);
          }
        }
      }

      function handleLabel(lbls, fnFormat, fnWidth, fmtLabel) {
        lbls.each(function(d) {
          var lbl = d3.select(this);
          fnFormat(d, lbl, fnWidth, fmtLabel);
        });
      }

      function ellipsifyLabel(d, lbl, fnWidth, fmtLabel) {
        var text = lbl.text(),
            dy = parseFloat(lbl.attr('dy')),
            maxWidth = fnWidth(d);

        lbl.text(nv.utils.stringEllipsify(text, container, maxWidth))
          .call(fmtLabel, dy);
      }

      function maxSideLabelWidth(d) {
        // overall width of container minus the width of funnel top
        // or minLabelWidth, which ever is greater
        // this is also now as funnelOffset (maybe)
        var twenty = Math.max(availableWidth - availableHeight / 1.1, minLabelWidth),
            // bottom of slice
            sliceBottom = y(d.y0),
            // x component of slope F at y
            base = sliceBottom * r,
            // total width at bottom of slice
            maxWidth = twenty + base,
            // height of sloped leader
            leaderHeight = Math.abs(d.labelBottom - sliceBottom),
            // width of the angled leader
            leaderWidth = leaderHeight * r,
            // total width of leader
            leaderTotal = labelGap + leaderWidth + labelGap + labelGap,
            // this is the distance from end of label plus spacing to F
            iOffset = maxWidth - leaderTotal;

        return Math.max(iOffset, minLabelWidth);
      }

      function pointsTrapezoid(d, h, w) {
        //MATH: don't delete
        // v = 1/2 * h * (b + b + 2*r*h);
        // 2v = h * (b + b + 2*r*h);
        // 2v = h * (2*b + 2*r*h);
        // 2v = 2*b*h + 2*r*h*h;
        // v = b*h + r*h*h;
        // v - b*h - r*h*h = 0;
        // v/r - b*h/r - h*h = 0;
        // b/r*h + h*h + b/r/2*b/r/2 = v/r + b/r/2*b/r/2;
        // h*h + b/r*h + b/r/2*b/r/2 = v/r + b/r/2*b/r/2;
        // (h + b/r/2)(h + b/r/2) = v/r + b/r/2*b/r/2;
        // h + b/r/2 = Math.sqrt(v/r + b/r/2*b/r/2);
        // h  = Math.abs(Math.sqrt(v/r + b/r/2*b/r/2)) - b/r/2;
        var y0 = y(d.y0),
            y1 = y(d.y0 + d.y),
            w0 = w / 2 - r * y0,
            w1 = w / 2 - r * y1,
            c = calculatedCenter;

        return (
          (c - w0) + ',' + (y0 * h) + ' ' +
          (c - w1) + ',' + (y1 * h) + ' ' +
          (c + w1) + ',' + (y1 * h) + ' ' +
          (c + w0) + ',' + (y0 * h)
        );
      }

      function heightTrapezoid(a, b) {
        var x = b / r / 2;
        return Math.abs(Math.sqrt(a / r + x * x)) - x;
      }

      function areaTrapezoid(h, w) {
        return h * (w - h * r);
      }

      function calcWidth(offset) {
        return Math.round(Math.max(Math.min(availableHeight / 1.1, availableWidth - offset), 40));
      }

      function calcHeight() {
        // MATH: don't delete
        // h = 666.666
        // w = 600
        // m = 200
        // at what height is m = 200
        // w = h * 0.3 = 666 * 0.3 = 200
        // maxheight = ((w - m) / 2) / 0.3 = (w - m) / 0.6 = h
        // (600 - 200) / 0.6 = 400 / 0.6 = 666
        return Math.min(calculatedWidth * 1.1, (calculatedWidth - calculatedWidth * r) / (2 * r));
      }

      function calcCenter(offset) {
        return calculatedWidth / 2 + offset;
      }

      function calcFunnelWidthAtSliceMidpoint(d) {
        var b = calculatedWidth,
            v = y(d.y0 + d.y1 / 2); // mid point of slice
        return b - v * r * 2;
      }

      function calcSideWidth(d, offset) {
        var b = Math.max((availableWidth - calculatedWidth) / 2, offset),
            v = y(d.y0 + d.y1); // top of slice
        return b + v * r;
      }

      function calcLabelBBox(lbl) {
        return d3.select(lbl).node().getBBox();
      }

      function calcFunnelLabelDimensions(lbls) {
        lbls.each(function(d) {
          var bbox = calcLabelBBox(this);

          d.labelHeight = bbox.height;
          d.labelWidth = bbox.width;
          d.labelTop = y(d.y0 + d.y / 2) - d.labelHeight / 2;
          d.labelBottom = d.labelTop + d.labelHeight + labelSpace;
          d.y1 = d.y - d.labelHeight;
          d.tooWide = d.labelWidth > calcFunnelWidthAtSliceMidpoint(d);
          d.tooTall = d.labelHeight > d.height - 4;
        });
      }

      function calcSideLabelDimensions(lbls) {
        lbls.each(function(d) {
          var bbox = calcLabelBBox(this);
          d.labelHeight = bbox.height;
          d.labelWidth = bbox.width;
          d.labelTop = y(d.y0 + d.y);
          d.labelBottom = d.labelTop + d.labelHeight + labelSpace;
        });
      }

      function pointsLeader(polylines, i) {
        var c = polylines.length;
        polylines.each(function(d, i, j) {
          d.y1 = 0;
          var // previous label
              p = j ? d3.select(polylines[j - 1][i]).data()[0] : null,
              // next label
              n = j < c - 1 ? d3.select(polylines[j + 1][i]).data()[0] : null,
              // label height
              h = Math.round(d.labelHeight) + 0.5,
              // slice bottom
              t = Math.round(y(d.y0) - d.labelTop) - 0.5,
              // previous width
              wp = p ? p.labelWidth - (d.labelBottom - p.labelBottom) * r : 0,
              // current width
              wc = d.labelWidth,
              // next width
              wn = n && h < t ? n.labelWidth : 0,
              // final width
              w = Math.round(Math.max(wp, wc, wn)) + labelGap,
              // funnel edge
              f = Math.round(calcSideWidth(d, funnelOffset)) - labelOffset - labelGap,
              // polyline points
              p = 0 + ',' + h + ' ' +
                 w + ',' + h + ' ' +
                 (w + Math.abs(h - t) * r) + ',' + t + ' ' +
                 f + ',' + t;
          d.labelWidth = w;
          d3.select(this).attr('points', p);
        });
      }

      function calcOffsets(lbls) {
        var sideWidth = (availableWidth - calculatedWidth) / 2, // natural width of side
            offset = 0;

        lbls.each(function(d) {

          var // bottom of slice
              sliceBottom = y(d.y0),
              // is slice below or above label bottom
              scalar = d.labelBottom >= sliceBottom ? 1 : 0,
              // the width of the angled leader
              // from bottom right of label to bottom of slice
              leaderSlope = Math.abs(d.labelBottom + labelGap - sliceBottom) * r,
              // this is the x component of slope F at y
              base = sliceBottom * r,
              // this is the distance from end of label plus spacing to F
              iOffset = d.labelWidth + leaderSlope + labelGap * 3 - base;

          // if this label sticks out past F
          if (iOffset >= offset) {
            // this is the minimum distance for F
            // has to be away from the left edge of labels
            offset = iOffset;
          }
        });

        // how far from chart edge is label left edge
        offset = Math.round(offset * 10) / 10;

        // there are three states:
        if (offset <= 0) {
        // 1. no label sticks out past F
          labelOffset = sideWidth;
          funnelOffset = sideWidth;
        } else if (offset > 0 && offset < sideWidth) {
        // 2. iOffset is > 0 but < sideWidth
          labelOffset = sideWidth - offset;
          funnelOffset = sideWidth;
        } else {
        // 3. iOffset is >= sideWidth
          labelOffset = 0;
          funnelOffset = offset;
        }
      }

      function fmtFill(d, i, j) {
        var backColor = d3.select(this.parentNode).style('fill'),
            textColor = nv.utils.getTextContrast(backColor, i);
        return textColor;
      }

      function fmtKey(d) {
        return data[d.series].key;
      }

      function fmtCount(d) {
        var i = data[d.series].count;
        return i ? ' (' + i + ')' : '';
      }

      function fmtDirection(d) {
        var m = nv.utils.isRTLChar(d.slice(-1)),
            dir = m ? 'rtl' : 'ltr';
        return 'ltr';
      }

      function fmtLabel(txt, classes, dy, fontSize, anchor, fill) {
        txt
          .attr('x', 0)
          .attr('y', 0)
          .attr('dy', dy + 'em')
          .attr('class', classes)
          .attr('direction', function() {
            return fmtDirection(txt.text());
          })
          .style('pointer-events', 'none')
          .style('text-anchor', anchor)
          .style('font-size', fontSize)
          .style('fill', fill);
      }

      function positionValue(lbls) {
        lbls.each(function(d) {
          var lbl = d3.select(this),
              cnt = lbl.selectAll('.nv-label')[0].length + 1,
              dy = .85 * cnt + 'em';

          lbl.select('.nv-value')
            .attr('dy', dy);
        });
      }

    });

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  chart.dispatch = dispatch;

  chart.color = function(_) {
    if (!arguments.length) return color;
    color = _;
    return chart;
  };
  chart.fill = function(_) {
    if (!arguments.length) return fill;
    fill = _;
    return chart;
  };
  chart.classes = function(_) {
    if (!arguments.length) return classes;
    classes = _;
    return chart;
  };
  chart.gradient = function(_) {
    if (!arguments.length) return gradient;
    gradient = _;
    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) return getX;
    getX = _;
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) return getY;
    getY = _;
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

  chart.xScale = function(_) {
    if (!arguments.length) return x;
    x = _;
    return chart;
  };

  chart.yScale = function(_) {
    if (!arguments.length) return y;
    y = _;
    return chart;
  };

  chart.yDomain = function(_) {
    if (!arguments.length) return yDomain;
    yDomain = _;
    return chart;
  };

  chart.forceY = function(_) {
    if (!arguments.length) return forceY;
    forceY = _;
    return chart;
  };

  chart.id = function(_) {
    if (!arguments.length) return id;
    id = _;
    return chart;
  };

  chart.delay = function(_) {
    if (!arguments.length) return delay;
    delay = _;
    return chart;
  };

  chart.clipEdge = function(_) {
    if (!arguments.length) return clipEdge;
    clipEdge = _;
    return chart;
  };

  chart.fmtValueLabel = function(_) {
    if (!arguments.length) return fmtValueLabel;
    fmtValueLabel = d3.functor(_);
    return chart;
  };

  chart.wrapLabels = function(_) {
    if (!arguments.length) return wrapLabels;
    wrapLabels = _;
    return chart;
  };

  chart.minLabelWidth = function(_) {
    if (!arguments.length) return minLabelWidth;
    minLabelWidth = _;
    return chart;
  };

  //============================================================

  return chart;
}

nv.models.funnelChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 10, right: 10, bottom: 10, left: 10},
      width = null,
      height = null,
      showTitle = false,
      showLegend = true,
      direction = 'ltr',
      tooltip = null,
      tooltips = true,
      tooltipContent = function(key, x, y, e, graph) {
        return '<h3>' + key + ' - ' + x + '</h3>' +
               '<p>' + y + '</p>';
      },
      x,
      y,
      durationMs = 0,
      state = {},
      strings = {
        legend: {close: 'Hide legend', open: 'Show legend'},
        controls: {close: 'Hide controls', open: 'Show controls'},
        noData: 'No Data Available.'
      },
      dispatch = d3.dispatch('chartClick', 'elementClick', 'tooltipShow', 'tooltipHide', 'tooltipMove', 'stateChange', 'changeState');

  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var funnel = nv.models.funnel(),
      legend = nv.models.legend()
        .align('center'),
      yScale = d3.scale.linear();

  var showTooltip = function(e, offsetElement, properties) {
    var xVal = 0;
    // defense against the dark divide-by-zero arts
    if (properties.total > 0) {
      xVal = (e.point.value * 100 / properties.total).toFixed(1);
    }
    var left = e.pos[0],
        top = e.pos[1],
        x = xVal,
        y = e.point.value,
        content = tooltipContent(e.series.key, x, y, e, chart);
    tooltip = nv.tooltip.show([left, top], content, e.value < 0 ? 'n' : 's', null, offsetElement);
  };

  var seriesClick = function(data, e, chart) {
    return;
  };

  //============================================================

  function chart(selection) {

    selection.each(function(chartData) {

      var properties = chartData.properties,
          data = chartData.data,
          container = d3.select(this),
          that = this,
          availableWidth = (width || parseInt(container.style('width'), 10) || 960) - margin.left - margin.right,
          availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom,
          innerWidth = availableWidth,
          innerHeight = availableHeight,
          innerMargin = {top: 0, right: 0, bottom: 0, left: 0},
          minSliceHeight = 30;

      chart.update = function() {
        container.transition().duration(durationMs).call(chart);
      };

      chart.dataSeriesActivate = function(eo) {
        var series = eo.series;

        series.active = (!series.active || series.active === 'inactive') ? 'active' : 'inactive';
        series.values[0].active = series.active;

        // if you have activated a data series, inactivate the rest
        if (series.active === 'active') {
          data.filter(function(d) {
            return d.active !== 'active';
          }).map(function(d) {
            d.values[0].active = 'inactive';
            d.active = 'inactive';
            return d;
          });
        }

        // if there are no active data series, inactivate them all
        if (!data.filter(function(d) {
          return d.active === 'active';
        }).length) {
          data.map(function(d) {
            d.active = '';
            d.values[0].active = '';
            container.selectAll('.nv-series').classed('nv-inactive', false);
            return d;
          });
        }

        container.call(chart);
      };

      chart.container = this;

      //------------------------------------------------------------
      // Display No Data message if there's nothing to show.

      if (!data || !data.length || !data.filter(function(d) {return d.values.length; }).length) {
        displayNoData();
        return chart;
      }

      //------------------------------------------------------------
      // Process data
      // add series index to each data point for reference
      data.map(function(d, i) {
        d.series = i;
        d.values.map(function(v) {
          v.series = d.series;
        });
        d.total = d3.sum(d.values, function(d, i) {
          return funnel.y()(d, i);
        });
        if (!d.total) {
          d.disabled = true;
        }
        return d;
      });

      // only sum enabled series
      var funnelData = data
            .filter(function(d, i) {
              return !d.disabled;
            });

      if (!funnelData.length) {
        funnelData = [{values: []}];
      }

      var totalAmount = d3.sum(funnelData, function(d) {
              return d.total;
            });
      var totalCount = d3.sum(funnelData, function(d) {
              return d.count;
            });

      //set state.disabled
      state.disabled = data.map(function(d) { return !!d.disabled; });

      //------------------------------------------------------------
      // Setup Scales

      y = funnel.yScale(); //see below

      //------------------------------------------------------------
      // Display No Data message if there's nothing to show.

      if (!totalAmount) {
        displayNoData();
        return chart;
      } else {
        container.selectAll('.nv-noData').remove();
      }

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-funnelChart').data([funnelData]),
          gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-funnelChart').append('g'),
          g = wrap.select('g').attr('class', 'nv-chartWrap');

      gEnter.append('rect').attr('class', 'nv-background')
        .attr('x', -margin.left)
        .attr('y', -margin.top)
        .attr('fill', '#FFF');

      g.select('.nv-background')
        .attr('width', availableWidth + margin.left + margin.right)
        .attr('height', availableHeight + margin.top + margin.bottom);

      gEnter.append('g').attr('class', 'nv-titleWrap');
      var titleWrap = g.select('.nv-titleWrap');
      gEnter.append('g').attr('class', 'nv-funnelWrap');
      var funnelWrap = g.select('.nv-funnelWrap');
      gEnter.append('g').attr('class', 'nv-legendWrap');
      var legendWrap = g.select('.nv-legendWrap');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------
      // Title & Legend

      var titleBBox = {width: 0, height: 0};
      titleWrap.select('.nv-title').remove();

      if (showTitle && properties.title) {
        titleWrap
          .append('text')
            .attr('class', 'nv-title')
            .attr('x', direction === 'rtl' ? availableWidth : 0)
            .attr('y', 0)
            .attr('dy', '.75em')
            .attr('text-anchor', 'start')
            .text(properties.title)
            .attr('stroke', 'none')
            .attr('fill', 'black');

        titleBBox = nv.utils.getTextBBox(g.select('.nv-title'));

        innerMargin.top += titleBBox.height + 12;
      }

      if (showLegend) {
        legend
          .id('legend_' + chart.id())
          .strings(chart.strings().legend)
          .align('center')
          .height(availableHeight - innerMargin.top);
        legendWrap
          .datum(data)
          .call(legend);
        legend
          .arrange(availableWidth);

        var legendLinkBBox = nv.utils.getTextBBox(legendWrap.select('.nv-legend-link')),
            legendSpace = availableWidth - titleBBox.width - 6,
            legendTop = showTitle && legend.collapsed() && legendSpace > legendLinkBBox.width ? true : false,
            xpos = direction === 'rtl' || !legend.collapsed() ? 0 : availableWidth - legend.width(),
            ypos = titleBBox.height;
        if (legendTop) {
          ypos = titleBBox.height - legend.height() / 2 - legendLinkBBox.height / 2;
        } else if (!showTitle) {
          ypos = - legend.margin().top;
        }

        legendWrap
          .attr('transform', 'translate(' + xpos + ',' + ypos + ')');

        innerMargin.top += legendTop ? 0 : legend.height() - 12;
      }

      // Recalc inner margins
      innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

      //------------------------------------------------------------
      // Main Chart Component(s)

      funnel
        .width(innerWidth)
        .height(innerHeight);

      funnelWrap
        .datum(funnelData)
        .attr('transform', 'translate(' + innerMargin.left + ',' + innerMargin.top + ')')
          .call(funnel);

      //------------------------------------------------------------
      // Setup Scales (again, not sure why it has to be here and not above?)

      var tickValues = resetScale(yScale, funnelData);

      function resetScale(scale, data) {
        var series1 = [[0]];
        var series2 = data.filter(function(d) {
                return !d.disabled;
              })
              .map(function(d) {
                return d.values.map(function(d, i) {
                  return d.y0 + d.y;
                });
              });
        var tickValues = d3.merge(series1.concat(series2));

        yScale
          .domain(tickValues)
          .range(tickValues.map(function(d) { return y(d); }));

        return tickValues;
      }

      function displayNoData() {
        container.select('.nvd3.nv-wrap').remove();
        var noDataText = container.selectAll('.nv-noData').data([chart.strings().noData]);

        noDataText.enter().append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle');

        noDataText
          .attr('x', margin.left + availableWidth / 2)
          .attr('y', margin.top + availableHeight / 2)
          .text(function(d) {
            return d;
          });
      }

      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      legend.dispatch.on('legendClick', function(d, i) {
        d.disabled = !d.disabled;

        if (!funnelData.filter(function(d) { return !d.disabled; }).length) {
          funnelData.map(function(d) {
            d.disabled = false;
            wrap.selectAll('.nv-series').classed('disabled', false);
            return d;
          });
        }

        state.disabled = funnelData.map(function(d) { return !!d.disabled; });
        dispatch.stateChange(state);

        container.transition().duration(durationMs).call(chart);
      });

      dispatch.on('tooltipShow', function(eo) {
        if (tooltips) {
          showTooltip(eo, that.parentNode, properties);
        }
      });

      dispatch.on('tooltipMove', function(eo) {
        if (tooltip) {
          nv.tooltip.position(that.parentNode, tooltip, eo.pos);
        }
      });

      dispatch.on('tooltipHide', function() {
        if (tooltips) {
          nv.tooltip.cleanup();
        }
      });

      // Update chart from a state object passed to event handler
      dispatch.on('changeState', function(eo) {
        if (typeof eo.disabled !== 'undefined') {
          funnelData.forEach(function(series, i) {
            series.disabled = eo.disabled[i];
          });
          state.disabled = eo.disabled;
        }

        container.transition().duration(durationMs).call(chart);
      });

      dispatch.on('chartClick', function() {
        if (legend.enabled()) {
          legend.dispatch.closeMenu();
        }
      });

      funnel.dispatch.on('elementClick', function(eo) {
        dispatch.chartClick();
        seriesClick(data, eo, chart);
      });

    });

    return chart;
  }

  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  funnel.dispatch.on('elementMouseover.tooltip', function(eo) {
    dispatch.tooltipShow(eo);
  });

  funnel.dispatch.on('elementMousemove.tooltip', function(eo) {
    dispatch.tooltipMove(eo);
  });

  funnel.dispatch.on('elementMouseout.tooltip', function() {
    dispatch.tooltipHide();
  });


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.funnel = funnel;
  chart.legend = legend;

  d3.rebind(chart, funnel, 'id', 'x', 'y', 'xDomain', 'yDomain', 'forceX', 'forceY', 'color', 'fill', 'classes', 'gradient');
  d3.rebind(chart, funnel, 'fmtValueLabel', 'clipEdge', 'delay', 'wrapLabels', 'minLabelWidth');

  chart.colorData = function(_) {
    var type = arguments[0],
        params = arguments[1] || {};
    var color = function(d, i) {
          return nv.utils.defaultColor()(d, d.series);
        };
    var classes = function(d, i) {
          return 'nv-group nv-series-' + d.series;
        };

    switch (type) {
      case 'graduated':
        color = function(d, i) {
          return d3.interpolateHsl(d3.rgb(params.c1), d3.rgb(params.c2))(d.series / params.l);
        };
        break;
      case 'class':
        color = function() {
          return 'inherit';
        };
        classes = function(d, i) {
          var iClass = (d.series * (params.step || 1)) % 14;
          iClass = (iClass > 9 ? '' : '0') + iClass;
          return 'nv-group nv-series-' + d.series + ' nv-fill' + iClass;
        };
        break;
      case 'data':
        color = function(d, i) {
          return d.classes ? 'inherit' : d.color || nv.utils.defaultColor()(d, d.series);
        };
        classes = function(d, i) {
          return 'nv-group nv-series-' + d.series + (d.classes ? ' ' + d.classes : '');
        };
        break;
    }

    var fill = (!params.gradient) ? color : function(d, i) {
      var p = {orientation: params.orientation || 'vertical', position: params.position || 'middle'};
      return funnel.gradient(d, d.series, p);
    };

    funnel.color(color);
    funnel.fill(fill);
    funnel.classes(classes);

    legend.color(color);
    legend.classes(classes);

    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) { return getX; }
    getX = _;
    funnelWrap.x(_);
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) { return getY; }
    getY = _;
    funnel.y(_);
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) {
      return margin;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        margin[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) {
      return width;
    }
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) {
      return height;
    }
    height = _;
    return chart;
  };

  chart.showTitle = function(_) {
    if (!arguments.length) {
      return showTitle;
    }
    showTitle = _;
    return chart;
  };

  chart.showLegend = function(_) {
    if (!arguments.length) {
      return showLegend;
    }
    showLegend = _;
    return chart;
  };

  chart.tooltip = function(_) {
    if (!arguments.length) {
      return tooltip;
    }
    tooltip = _;
    return chart;
  };

  chart.tooltips = function(_) {
    if (!arguments.length) {
      return tooltips;
    }
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function(_) {
    if (!arguments.length) {
      return tooltipContent;
    }
    tooltipContent = _;
    return chart;
  };

  chart.state = function(_) {
    if (!arguments.length) {
      return state;
    }
    state = _;
    return chart;
  };

  chart.strings = function(_) {
    if (!arguments.length) {
      return strings;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        strings[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.seriesClick = function(_) {
    if (!arguments.length) {
      return seriesClick;
    }
    seriesClick = _;
    return chart;
  };

  chart.direction = function(_) {
    if (!arguments.length) {
      return direction;
    }
    direction = _;
    legend.direction(_);
    return chart;
  };

  //============================================================

  return chart;
};

nv.models.gauge = function() {
  /* original inspiration for this chart type is at http://bl.ocks.org/3202712 */
  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 0, right: 0, bottom: 0, left: 0}
    , width = null
    , height = null
    , clipEdge = true
    , getValues = function(d) { return d.values; }
    , getX = function(d) { return d.key; }
    , getY = function(d) { return d.y; }
    , id = Math.floor(Math.random() * 10000) //Create semi-unique ID in case user doesn't select one
    , labelFormat = d3.format(',g')
    , valueFormat = d3.format(',.f')
    , showLabels = true
    , showPointer = true
    , color = function (d, i) { return nv.utils.defaultColor()(d, d.series); }
    , fill = color
    , classes = function (d,i) { return 'nv-arc-path nv-series-' + d.series; }
    , dispatch = d3.dispatch('chartClick', 'elementClick', 'elementDblClick', 'elementMouseover', 'elementMouseout', 'elementMousemove')
  ;

  var ringWidth = 50
    , pointerWidth = 5
    , pointerTailLength = 5
    , pointerHeadLength = 90
    , minValue = 0
    , maxValue = 10
    , minAngle = -90
    , maxAngle = 90
    , transitionMs = 750
    , labelInset = 10
  ;

  //============================================================

  //colorScale = d3.scale.linear().domain([0, .5, 1].map(d3.interpolate(min, max))).range(["green", "yellow", "red"]);

  function chart(selection)
  {
    selection.each(

    function(chartData) {

      var properties = chartData.properties
        , data = chartData.data;

        var availableWidth = width - margin.left - margin.right
          , availableHeight = height - margin.top - margin.bottom
          , radius =  Math.min( (availableWidth/2), availableHeight ) / (  (100+labelInset)/100  )
          , container = d3.select(this)
          , range = maxAngle - minAngle
          , scale = d3.scale.linear().range([0,1]).domain([minValue, maxValue])
          , previousTick = 0
          , arcData = data.map( function(d,i){
              var rtn = {
                  key:d.key
                , series:d.series
                , y0:previousTick
                , y1:d.y
                , color:d.color
                , classes:d.classes
              };
              previousTick = d.y;
              return rtn;
            })
          , labelData = [0].concat( data.map( function(d){ return d.y; } ) )
          , prop = function(d){ return d*radius/100; }
          , pointerValue = properties.values[0].t
          ;

        //------------------------------------------------------------
        // Setup containers and skeleton of chart

        var wrap = container.selectAll('g.nv-wrap.nv-gauge').data([data]);
        var wrapEnter = wrap.enter().append('g').attr('class','nvd3 nv-wrap nv-gauge');
        var defsEnter = wrapEnter.append('defs');
        var gEnter = wrapEnter.append('g');
        var g = wrap.select('g');

        //set up the gradient constructor function
        chart.gradient = function(d,i) {
          return nv.utils.colorRadialGradient( d, id+'-'+i, {x:0, y:0, r:radius, s:ringWidth/100, u:'userSpaceOnUse'}, color(d,i), wrap.select('defs') );
        };

        gEnter.append('g').attr('class', 'nv-arc-group');
        gEnter.append('g').attr('class', 'nv-labels');
        gEnter.append('g').attr('class', 'nv-pointer');
        gEnter.append('g').attr('class', 'nv-odometer');

        wrap.attr('transform', 'translate('+ (margin.left/2 + margin.right/2 + prop(labelInset)) +','+ (margin.top + prop(labelInset)) +')');
        //g.select('.nv-arc-gauge').attr('transform', 'translate('+ availableWidth/2 +','+ availableHeight/2 +')');

        //------------------------------------------------------------

        // defsEnter.append('clipPath')
        //     .attr('id', 'nv-edge-clip-' + id)
        //   .append('rect');
        // wrap.select('#nv-edge-clip-' + id + ' rect')
        //     .attr('width', availableWidth)
        //     .attr('height', availableHeight);
        // g.attr('clip-path', clipEdge ? 'url(#nv-edge-clip-' + id + ')' : '');

        //------------------------------------------------------------
        // Gauge arcs
        var arc = d3.svg.arc()
          .innerRadius( prop(ringWidth) )
          .outerRadius( radius )
          .startAngle(function(d, i) {
            return deg2rad( newAngle(d.y0) );
          })
          .endAngle(function(d, i) {
            return deg2rad( newAngle(d.y1) );
          });

        var ag = g.select('.nv-arc-group')
            .attr('transform', centerTx);

        ag.selectAll('.nv-arc-path')
            .data(arcData)
          .enter().append('path')
            .attr('class', 'nv-arc-path')
            .attr('stroke', '#ffffff')
            .attr('stroke-width', 3)
            .attr('d', arc)
            .on('mouseover', function(d,i){
              d3.select(this).classed('hover', true);
              dispatch.elementMouseover({
                  point: d,
                  pointIndex: i,
                  pos: [d3.event.offsetX, d3.event.offsetY],
                  id: id
              });
            })
            .on('mouseout', function(d,i){
              d3.select(this).classed('hover', false);
              dispatch.elementMouseout({
                  point: d,
                  index: i,
                  id: id
              });
            })
            .on('mousemove', function(d,i){
              dispatch.elementMousemove({
                point: d,
                pointIndex: i,
                pos: [d3.event.offsetX, d3.event.offsetY],
                id: id
              });
            })
            .on('click', function(d,i) {
              dispatch.elementClick({
                  point: d,
                  index: i,
                  pos: d3.event,
                  id: id
              });
              d3.event.stopPropagation();
            })
            .on('dblclick', function(d,i) {
              dispatch.elementDblClick({
                  point: d,
                  index: i,
                  pos: d3.event,
                  id: id
              });
              d3.event.stopPropagation();
            });

        ag.selectAll('.nv-arc-path').transition().duration(10)
            .attr('class', classes)
            .attr('fill', fill)
            .attr('d', arc);

        //------------------------------------------------------------
        // Gauge labels
        var lg = g.select('.nv-labels')
            .attr('transform', centerTx);

        lg.selectAll('text').transition().duration(0)
            .attr('transform', function(d) {
              return 'rotate('+ newAngle(d) +') translate(0,'+ (-radius-prop(1.5)) +')';
            })
            .style('font-size', prop(0.6)+'em');

        lg.selectAll('text')
            .data(labelData)
          .enter().append('text')
            .attr('transform', function(d) {
              return 'rotate('+ newAngle(d) +') translate(0,'+ (-radius-prop(1.5)) +')';
            })
            .text(labelFormat)
            .style('text-anchor', 'middle')
            .style('font-size', prop(0.6)+'em');

        if (showPointer) {
          //------------------------------------------------------------
          // Gauge pointer
          var pointerData = [
                [ Math.round(prop(pointerWidth)/2),    0 ],
                [ 0, -Math.round(prop(pointerHeadLength))],
                [ -(Math.round(prop(pointerWidth)/2)), 0 ],
                [ 0, Math.round(prop(pointerWidth)) ],
                [ Math.round(prop(pointerWidth)/2),    0 ]
              ];

          var pg = g.select('.nv-pointer')
              .attr('transform', centerTx);

          pg.selectAll('path').transition().duration(120)
            .attr('d', d3.svg.line().interpolate('monotone'));

          var pointer = pg.selectAll('path')
              .data([pointerData])
            .enter().append('path')
              .attr('d', d3.svg.line().interpolate('monotone')/*function(d) { return pointerLine(d) +'Z';}*/ )
              .attr('transform', 'rotate('+ minAngle +')');

          setGaugePointer(pointerValue);

          //------------------------------------------------------------
          // Odometer readout
          g.selectAll('.nv-odom').remove();

          g.select('.nv-odomText').transition().duration(0)
              .style('font-size', prop(0.7)+'em');

          g.select('.nv-odometer')
            .append('text')
              .attr('class', 'nv-odom nv-odomText')
              .attr('x', 0)
              .attr('y', 0 )
              .attr('text-anchor', 'middle')
              .text( valueFormat(pointerValue) )
              .style('stroke', 'none')
              .style('fill', 'black')
              .style('font-size', prop(0.7)+'em')
            ;

          var bbox = g.select('.nv-odomText').node().getBBox();

          g.select('.nv-odometer')
            .insert('path','.nv-odomText')
            .attr('class', 'nv-odom nv-odomBox')
            .attr("d",
              nv.utils.roundedRectangle(
                -bbox.width/2, -bbox.height+prop(1.5), bbox.width+prop(4), bbox.height+prop(2), prop(2)
              )
            )
            .attr('fill', '#eeffff')
            .attr('stroke','black')
            .attr('stroke-width','2px')
            .attr('opacity',0.8)
          ;

          g.select('.nv-odometer')
              .attr('transform', 'translate('+ radius +','+ ( margin.top + prop(70) + bbox.width ) +')');

        } else {
          g.select('.nv-odometer').select('.nv-odomText').remove();
          g.select('.nv-odometer').select('.nv-odomBox').remove();
          g.select('.nv-pointer').selectAll('path').remove();
        }

        //------------------------------------------------------------
        // private functions
        function setGaugePointer(d) {
          pointer.transition()
            .duration(transitionMs)
            .ease('elastic')
            .attr('transform', 'rotate('+ newAngle(d) +')');
        }

        function deg2rad(deg) {
          return deg * Math.PI/180;
        }

        function newAngle(d) {
          return minAngle + ( scale(d) * range );
        }

        // Center translation
        function centerTx() {
          return 'translate('+ radius +','+ radius +')';
        }

        chart.setGaugePointer = setGaugePointer;

      }

    );

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  chart.dispatch = dispatch;

  chart.color = function(_) {
    if (!arguments.length) return color;
    color = _;
    return chart;
  };
  chart.fill = function(_) {
    if (!arguments.length) return fill;
    fill = _;
    return chart;
  };
  chart.classes = function(_) {
    if (!arguments.length) return classes;
    classes = _;
    return chart;
  };
  chart.gradient = function(_) {
    if (!arguments.length) return gradient;
    gradient = _;
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

  chart.values = function(_) {
    if (!arguments.length) return getValues;
    getValues = _;
    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) return getX;
    getX = _;
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) return getY;
    getY = d3.functor(_);
    return chart;
  };

  chart.showLabels = function(_) {
    if (!arguments.length) return showLabels;
    showLabels = _;
    return chart;
  };

  chart.id = function(_) {
    if (!arguments.length) return id;
    id = _;
    return chart;
  };

  chart.valueFormat = function(_) {
    if (!arguments.length) return valueFormat;
    valueFormat = _;
    return chart;
  };

  chart.labelThreshold = function(_) {
    if (!arguments.length) return labelThreshold;
    labelThreshold = _;
    return chart;
  };

  // GAUGE
  chart.ringWidth = function(_) {
    if (!arguments.length) return ringWidth;
    ringWidth = _;
    return chart;
  };
  chart.pointerWidth = function(_) {
    if (!arguments.length) return pointerWidth;
    pointerWidth = _;
    return chart;
  };
  chart.pointerTailLength = function(_) {
    if (!arguments.length) return pointerTailLength;
    pointerTailLength = _;
    return chart;
  };
  chart.pointerHeadLength = function(_) {
    if (!arguments.length) return pointerHeadLength;
    pointerHeadLength = _;
    return chart;
  };
  chart.minValue = function(_) {
    if (!arguments.length) return minValue;
    minValue = _;
    return chart;
  };
  chart.maxValue = function(_) {
    if (!arguments.length) return maxValue;
    maxValue = _;
    return chart;
  };
  chart.minAngle = function(_) {
    if (!arguments.length) return minAngle;
    minAngle = _;
    return chart;
  };
  chart.maxAngle = function(_) {
    if (!arguments.length) return maxAngle;
    maxAngle = _;
    return chart;
  };
  chart.transitionMs = function(_) {
    if (!arguments.length) return transitionMs;
    transitionMs = _;
    return chart;
  };
  chart.labelFormat = function(_) {
    if (!arguments.length) return labelFormat;
    labelFormat = _;
    return chart;
  };
  chart.labelInset = function(_) {
    if (!arguments.length) return labelInset;
    labelInset = _;
    return chart;
  };
  chart.setPointer = function(_) {
    if (!arguments.length) return chart.setGaugePointer;
    chart.setGaugePointer(_);
    return chart;
  };
  chart.isRendered = function(_) {
    return (svg !== undefined);
  };
  chart.showPointer = function(_) {
    if (!arguments.length) return showPointer;
    showPointer = _;
    return chart;
  };

  //============================================================

  return chart;
}
nv.models.gaugeChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 10, right: 10, bottom: 10, left: 10},
      width = null,
      height = null,
      showTitle = false,
      showLegend = true,
      direction = 'ltr',
      tooltip = null,
      tooltips = true,
      tooltipContent = function(key, y, e, graph) {
        return '<h3>' + key + '</h3>' +
               '<p>' + y + '</p>';
      },
      x,
      y, //can be accessed via chart.yScale()
      strings = {
        legend: {close: 'Hide legend', open: 'Show legend'},
        controls: {close: 'Hide controls', open: 'Show controls'},
        noData: 'No Data Available.'
      },
      dispatch = d3.dispatch('chartClick', 'tooltipShow', 'tooltipHide', 'tooltipMove');

  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var gauge = nv.models.gauge(),
      legend = nv.models.legend()
        .align('center');

  var showTooltip = function(e, offsetElement) {
    var left = e.pos[0],
        top = e.pos[1],
        y = gauge.valueFormat()((e.point.y1 - e.point.y0)),
        content = tooltipContent(e.point.key, y, e, chart);

    tooltip = nv.tooltip.show([left, top], content, null, null, offsetElement);
  };

  //============================================================

  function chart(selection) {

    selection.each(function(chartData) {

      var properties = chartData.properties,
          data = chartData.data,
          container = d3.select(this),
          that = this,
          availableWidth = (width || parseInt(container.style('width'), 10) || 960) - margin.left - margin.right,
          availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom,
          innerWidth = availableWidth,
          innerHeight = availableHeight,
          innerMargin = {top: 0, right: 0, bottom: 0, left: 0};

      chart.update = function() {
        container.transition().call(chart);
      };

      chart.container = this;

      //------------------------------------------------------------
      // Display No Data message if there's nothing to show.

      if (!data || !data.length) {
        var noDataText = container.selectAll('.nv-noData').data([chart.strings().noData]);

        noDataText.enter().append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle');

        noDataText
          .attr('x', margin.left + availableWidth / 2)
          .attr('y', margin.top + availableHeight / 2)
          .text(function(d) {
            return d;
          });

        return chart;
      } else {
        container.selectAll('.nv-noData').remove();
      }

      //------------------------------------------------------------
      // Process data
      //add series index to each data point for reference
      data.map(function(d, i) {
        d.series = i;
      });

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-gaugeChart').data([data]),
          gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-gaugeChart').append('g'),
          g = wrap.select('g').attr('class', 'nv-chartWrap');

      gEnter.append('rect').attr('class', 'nv-background')
        .attr('x', -margin.left)
        .attr('y', -margin.top)
        .attr('fill', '#FFF');

      g.select('.nv-background')
        .attr('width', availableWidth + margin.left + margin.right)
        .attr('height', availableHeight + margin.top + margin.bottom);

      gEnter.append('g').attr('class', 'nv-titleWrap');
      var titleWrap = g.select('.nv-titleWrap');
      gEnter.append('g').attr('class', 'nv-gaugeWrap');
      var gaugeWrap = g.select('.nv-gaugeWrap');
      gEnter.append('g').attr('class', 'nv-legendWrap');
      var legendWrap = g.select('.nv-legendWrap');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------
      // Title & Legend

      var titleBBox = {width: 0, height: 0};
      titleWrap.select('.nv-title').remove();

      if (showTitle && properties.title) {
        titleWrap
          .append('text')
            .attr('class', 'nv-title')
            .attr('x', direction === 'rtl' ? availableWidth : 0)
            .attr('y', 0)
            .attr('dy', '.75em')
            .attr('text-anchor', 'start')
            .text(properties.title)
            .attr('stroke', 'none')
            .attr('fill', 'black');

        titleBBox = nv.utils.getTextBBox(g.select('.nv-title'));

        innerMargin.top += titleBBox.height + 12;
      }

      var legendLinkBBox = {width: 0, height: 0};

      if (showLegend) {
        legend
          .id('legend_' + chart.id())
          .strings(chart.strings().legend)
          .align('center')
          .height(availableHeight - innerMargin.top);
        legendWrap
          .datum(data)
          .call(legend);

        legend
          .arrange(availableWidth);

        var legendLinkBBox = nv.utils.getTextBBox(legendWrap.select('.nv-legend-link')),
            legendSpace = availableWidth - titleBBox.width - 6,
            legendTop = showTitle && legend.collapsed() && legendSpace > legendLinkBBox.width ? true : false,
            xpos = direction === 'rtl' || !legend.collapsed() ? 0 : availableWidth - legend.width(),
            ypos = titleBBox.height;
        if (legendTop) {
          ypos = titleBBox.height - legend.height() / 2 - legendLinkBBox.height / 2;
        } else if (!showTitle) {
          ypos = - legend.margin().top;
        }

        legendWrap
          .attr('transform', 'translate(' + xpos + ',' + ypos + ')');

        innerMargin.top += legendTop ? 0 : legend.height() - 12;
      }

      // Recalc inner margins
      innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

      //------------------------------------------------------------
      // Main Chart Component(s)

      gauge
        .width(innerWidth)
        .height(innerHeight);

      gaugeWrap
        .datum(chartData)
        .attr('transform', 'translate(' + innerMargin.left + ',' + innerMargin.top + ')')
        .transition()
          .call(gauge);

      //gauge.setPointer(properties.value);

      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      dispatch.on('tooltipShow', function(eo) {
        if (tooltips) {
          showTooltip(eo, that.parentNode);
        }
      });

      dispatch.on('tooltipMove', function(eo) {
        if (tooltip) {
          nv.tooltip.position(that.parentNode, tooltip, eo.pos);
        }
      });

      dispatch.on('tooltipHide', function() {
        if (tooltips) {
          nv.tooltip.cleanup();
        }
      });

      dispatch.on('chartClick', function() {
        if (legend.enabled()) {
          legend.dispatch.closeMenu();
        }
      });

    });

    return chart;
  }

  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  gauge.dispatch.on('elementMouseover.tooltip', function(eo) {
    dispatch.tooltipShow(eo);
  });

  gauge.dispatch.on('elementMousemove.tooltip', function(eo) {
    dispatch.tooltipMove(eo);
  });

  gauge.dispatch.on('elementMouseout.tooltip', function() {
    dispatch.tooltipHide();
  });

  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.gauge = gauge;
  chart.legend = legend;

  d3.rebind(chart, gauge, 'id', 'x', 'y', 'color', 'fill', 'classes', 'gradient');
  d3.rebind(chart, gauge, 'valueFormat', 'values', 'showLabels', 'showPointer', 'setPointer', 'ringWidth', 'labelThreshold', 'maxValue', 'minValue', 'transitionMs');

  chart.colorData = function(_) {
    var type = arguments[0],
        params = arguments[1] || {};
    var color = function(d, i) {
          return nv.utils.defaultColor()(d, d.series);
        };
    var classes = function(d, i) {
          return 'nv-arc-path nv-series-' + d.series;
        };

    switch (type) {
      case 'graduated':
        color = function(d, i) {
          return d3.interpolateHsl(d3.rgb(params.c1), d3.rgb(params.c2))(d.series / params.l);
        };
        break;
      case 'class':
        color = function() {
          return 'inherit';
        };
        classes = function(d, i) {
          var iClass = (d.series * (params.step || 1)) % 14;
          iClass = (iClass > 9 ? '' : '0') + iClass;
          return 'nv-arc-path nv-series-' + d.series + ' nv-fill' + iClass;
        };
        break;
      case 'data':
        color = function(d, i) {
          return d.classes ? 'inherit' : d.color || nv.utils.defaultColor()(d, d.series);
        };
        classes = function(d, i) {
          return 'nv-arc-path nv-series-' + d.series + (d.classes ? ' ' + d.classes : '');
        };
        break;
    }

    var fill = (!params.gradient) ? color : function(d, i) {
      return gauge.gradient(d, d.series);
    };

    gauge.color(color);
    gauge.fill(fill);
    gauge.classes(classes);

    legend.color(color);
    legend.classes(classes);

    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) {
      return margin;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        margin[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) {
      return width;
    }
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) {
      return height;
    }
    height = _;
    return chart;
  };

  chart.showTitle = function(_) {
    if (!arguments.length) {
      return showTitle;
    }
    showTitle = _;
    return chart;
  };

  chart.showLegend = function(_) {
    if (!arguments.length) {
      return showLegend;
    }
    showLegend = _;
    return chart;
  };

  chart.tooltip = function(_) {
    if (!arguments.length) {
      return tooltip;
    }
    tooltip = _;
    return chart;
  };

  chart.tooltips = function(_) {
    if (!arguments.length) {
      return tooltips;
    }
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function(_) {
    if (!arguments.length) {
      return tooltipContent;
    }
    tooltipContent = _;
    return chart;
  };

  chart.strings = function(_) {
    if (!arguments.length) {
      return strings;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        strings[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.direction = function(_) {
    if (!arguments.length) {
      return direction;
    }
    direction = _;
    legend.direction(_);
    return chart;
  };

  //============================================================

  return chart;
};

nv.models.line = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var scatter = nv.models.scatter()
    ;

  var margin = {top: 0, right: 0, bottom: 0, left: 0}
    , width = 960
    , height = 500
    , getX = function(d) { return d.x; } // accessor to get the x value from a data point
    , getY = function(d) { return d.y; } // accessor to get the y value from a data point
    , defined = function(d,i) { return !isNaN(getY(d,i)) && getY(d,i) !== null; } // allows a line to be not continuous when it is not defined
    , isArea = function(d) { return (d && d.area) || false; } // decides if a line is an area or just a line
    , clipEdge = false // if true, masks lines within x and y scale
    , x //can be accessed via chart.xScale()
    , y //can be accessed via chart.yScale()
    , delay = 200
    , interpolate = "linear" // controls the line interpolation
    , color = function (d, i) { return nv.utils.defaultColor()(d, d.series); }
    , fill = color
    , classes = function (d,i) { return 'nv-group nv-series-' + d.series; }
    ;

  scatter
    .size(16) // default size
    .sizeDomain([16,256]) //set to speed up calculation, needs to be unset if there is a custom size accessor
    ;

  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var x0, y0 //used to store previous scales
      ;

  //============================================================


  function chart(selection) {
    selection.each(function(data) {
      var availableWidth = width - margin.left - margin.right,
          availableHeight = height - margin.top - margin.bottom,
          container = d3.select(this);

      //------------------------------------------------------------
      // Setup Scales

      x = scatter.xScale();
      y = scatter.yScale();

      x0 = x0 || x;
      y0 = y0 || y;

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-line').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-line');
      var defsEnter = wrapEnter.append('defs');
      var gEnter = wrapEnter.append('g');
      var g = wrap.select('g');

      //set up the gradient constructor function
      chart.gradient = function(d,i,p) {
        return nv.utils.colorLinearGradient( d, chart.id() + '-' + i, p, color(d,i), wrap.select('defs') );
      };

      gEnter.append('g').attr('class', 'nv-groups');
      gEnter.append('g').attr('class', 'nv-scatterWrap');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------

      scatter
        .width(availableWidth)
        .height(availableHeight);

      var scatterWrap = wrap.select('.nv-scatterWrap');
          //.datum(data); // Data automatically trickles down from the wrap

      d3.transition(scatterWrap).call(scatter);


      defsEnter.append('clipPath')
          .attr('id', 'nv-edge-clip-' + scatter.id())
        .append('rect');

      wrap.select('#nv-edge-clip-' + scatter.id() + ' rect')
          .attr('width', availableWidth)
          .attr('height', availableHeight);

      g   .attr('clip-path', clipEdge ? 'url(#nv-edge-clip-' + scatter.id() + ')' : '');
      scatterWrap
          .attr('clip-path', clipEdge ? 'url(#nv-edge-clip-' + scatter.id() + ')' : '');


      var groups = wrap.select('.nv-groups').selectAll('.nv-group')
          .data(function(d) { return d; }, function(d) { return d.key; });
      groups.enter().append('g')
          .style('stroke-opacity', 1e-6)
          .style('fill-opacity', 1e-6);
      d3.transition(groups.exit())
          .style('stroke-opacity', 1e-6)
          .style('fill-opacity', 1e-6)
          .remove();
      groups
          .classed('hover', function(d) { return d.hover; })
          .attr('class', classes)
          .attr('fill', color)
          .attr('stroke', color);
      d3.transition(groups)
          .style('stroke-opacity', 1)
          .style('fill-opacity', 0.5);


      var areaPaths = groups.selectAll('path.nv-area')
          .data(function(d) { return isArea(d) ? [d] : []; }); // this is done differently than lines because I need to check if series is an area
      areaPaths.enter().append('path')
          .attr('class', 'nv-area')
          .attr('d', function(d) {
            return d3.svg.area()
                .interpolate(interpolate)
                .defined(defined)
                .x(function(d,i) { return x0(getX(d,i)); })
                .y0(function(d,i) { return y0(getY(d,i)); })
                .y1(function(d,i) { return y0( y.domain()[0] <= 0 ? y.domain()[1] >= 0 ? 0 : y.domain()[1] : y.domain()[0] ); })
                //.y1(function(d,i) { return y0(0) }) //assuming 0 is within y domain.. may need to tweak this
                .apply(this, [d.values]);
          });
      areaPaths.exit().remove();

      d3.transition(groups.exit().selectAll('path.nv-area'))
          .attr('d', function(d) {
            return d3.svg.area()
                .interpolate(interpolate)
                .defined(defined)
                .x(function(d,i) { return x0(getX(d,i)); })
                .y0(function(d,i) { return y0(getY(d,i)); })
                .y1(function(d,i) { return y0( y.domain()[0] <= 0 ? y.domain()[1] >= 0 ? 0 : y.domain()[1] : y.domain()[0] ); })
                //.y1(function(d,i) { return y0(0) }) //assuming 0 is within y domain.. may need to tweak this
                .apply(this, [d.values]);
          });
      d3.transition(areaPaths)
          .attr('d', function(d) {
            return d3.svg.area()
                .interpolate(interpolate)
                .defined(defined)
                .x(function(d,i) { return x(getX(d,i)); })
                .y0(function(d,i) { return y(getY(d,i)); })
                .y1(function(d,i) { return y0( y.domain()[0] <= 0 ? y.domain()[1] >= 0 ? 0 : y.domain()[1] : y.domain()[0] ); })
                //.y1(function(d,i) { return y0(0) }) //assuming 0 is within y domain.. may need to tweak this
                .apply(this, [d.values]);
          });


      var linePaths = groups.selectAll('path.nv-line')
          .data(function(d) { return [d.values]; });
      linePaths.enter().append('path')
          .attr('class', 'nv-line')
          .attr('d',
            d3.svg.line()
              .interpolate(interpolate)
              .defined(defined)
              .x(function(d,i) { return x0(getX(d,i)); })
              .y(function(d,i) { return y0(getY(d,i)); })
          );
      d3.transition(groups.exit().selectAll('path.nv-line'))
          .attr('d',
            d3.svg.line()
              .interpolate(interpolate)
              .defined(defined)
              .x(function(d,i) { return x0(getX(d,i)); })
              .y(function(d,i) { return y0(getY(d,i)); })
          );
      d3.transition(linePaths)
          .attr('d',
            d3.svg.line()
              .interpolate(interpolate)
              .defined(defined)
              .x(function(d,i) { return x(getX(d,i)); })
              .y(function(d,i) { return y(getY(d,i)); })
          );


      //store old scales for use in transitions on update
      x0 = x.copy();
      y0 = y.copy();

    });

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  chart.dispatch = scatter.dispatch;
  chart.scatter = scatter;

  d3.rebind(chart, scatter, 'id', 'interactive', 'size', 'xScale', 'yScale', 'zScale', 'xDomain', 'yDomain', 'sizeDomain', 'forceX', 'forceY', 'forceSize', 'useVoronoi', 'clipVoronoi', 'clipRadius', 'padData', 'nice');

  chart.color = function(_) {
    if (!arguments.length) { return color; }
    color = _;
    scatter.color(color);
    return chart;
  };
  chart.fill = function(_) {
    if (!arguments.length) { return fill; }
    fill = _;
    scatter.fill(fill);
    return chart;
  };
  chart.classes = function(_) {
    if (!arguments.length) { return classes; }
    classes = _;
    scatter.classes(classes);
    return chart;
  };
  chart.gradient = function(_) {
    if (!arguments.length) { return gradient; }
    gradient = _;
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) { return margin; }
    margin.top    = typeof _.top    != 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  != 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom != 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   != 'undefined' ? _.left   : margin.left;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) { return width; }
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) { return height; }
    height = _;
    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) { return getX; }
    getX = _;
    scatter.x(_);
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) { return getY; }
    getY = _;
    scatter.y(_);
    return chart;
  };

  chart.delay = function(_) {
    if (!arguments.length) { return delay; }
    delay = _;
    return chart;
  };

  chart.clipEdge = function(_) {
    if (!arguments.length) { return clipEdge; }
    clipEdge = _;
    return chart;
  };

  chart.interpolate = function(_) {
    if (!arguments.length) { return interpolate; }
    interpolate = _;
    return chart;
  };

  chart.defined = function(_) {
    if (!arguments.length) { return defined; }
    defined = _;
    return chart;
  };

  chart.isArea = function(_) {
    if (!arguments.length) { return isArea; }
    isArea = d3.functor(_);
    return chart;
  };

  //============================================================


  return chart;
};
nv.models.lineChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 10, right: 10, bottom: 10, left: 10},
      width = null,
      height = null,
      showTitle = false,
      showControls = false,
      showLegend = true,
      direction = 'ltr',
      tooltip = null,
      tooltips = true,
      tooltipContent = function(key, x, y, e, graph) {
        return '<h3>' + key + '</h3>' +
               '<p>' + y + ' on ' + x + '</p>';
      },
      x,
      y,
      state = {},
      strings = {
        legend: {close: 'Hide legend', open: 'Show legend'},
        controls: {close: 'Hide controls', open: 'Show controls'},
        noData: 'No Data Available.'
      },
      dispatch = d3.dispatch('chartClick', 'tooltipShow', 'tooltipHide', 'tooltipMove', 'stateChange', 'changeState');

  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var lines = nv.models.line()
        .clipEdge(true),
      xAxis = nv.models.axis()
        .orient('bottom')
        .tickPadding(4)
        .highlightZero(false)
        .showMaxMin(false)
        .tickFormat(function(d) { return d; }),
      yAxis = nv.models.axis()
        .orient('left')
        .tickPadding(4)
        .tickFormat(d3.format('s')),
      legend = nv.models.legend()
        .align('right'),
      controls = nv.models.legend()
        .align('left')
        .color(['#444']);

  var showTooltip = function(e, offsetElement) {
    var left = e.pos[0],
        top = e.pos[1],
        x = xAxis.tickFormat()(lines.x()(e.point, e.pointIndex)),
        y = yAxis.tickFormat()(lines.y()(e.point, e.pointIndex)),
        content = tooltipContent(e.series.key, x, y, e, chart);

    tooltip = nv.tooltip.show([left, top], content, null, null, offsetElement);
  };

  //============================================================

  function chart(selection) {

    selection.each(function(chartData) {

      var properties = chartData.properties,
          data = chartData.data,
          container = d3.select(this),
          that = this,
          availableWidth = (width || parseInt(container.style('width'), 10) || 960) - margin.left - margin.right,
          availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom,
          innerWidth = availableWidth,
          innerHeight = availableHeight,
          innerMargin = {top: 0, right: 0, bottom: 0, left: 0},
          maxControlsWidth = 0,
          maxLegendWidth = 0,
          widthRatio = 0,
          controlsHeight = 0,
          legendHeight = 0;

      chart.update = function() {
        container.transition().duration(chart.delay()).call(chart);
      };

      chart.container = this;

      //------------------------------------------------------------
      // Display No Data message if there's nothing to show.

      if (!data || !data.length || !data.filter(function(d) {return d.values.length; }).length) {
        displayNoData();
        return chart;
      }

      //------------------------------------------------------------
      // Process data

      //add series index to each data point for reference
      data.map(function(d, i) {
        d.series = i;
        d.total = d3.sum(d.values, function(d, i) {
          return lines.y()(d, i);
        });
        if (!d.total) {
          d.disabled = true;
        }
      });

      var dataLines = data.filter(function(d) {
              return !d.disabled;
            });
      dataLines = dataLines.length ? dataLines : [{values: []}];

      var totalAmount = d3.sum(dataLines, function(d) {
              return d.total;
            });

      //set state.disabled
      state.disabled = data.map(function(d) { return !!d.disabled; });
      state.interpolate = lines.interpolate();
      state.isArea = !lines.isArea();

      var controlsData = [
        { key: 'Linear', disabled: lines.interpolate() !== 'linear' },
        { key: 'Basis', disabled: lines.interpolate() !== 'basis' },
        { key: 'Monotone', disabled: lines.interpolate() !== 'monotone' },
        { key: 'Cardinal', disabled: lines.interpolate() !== 'cardinal' },
        { key: 'Line', disabled: lines.isArea()() === true },
        { key: 'Area', disabled: lines.isArea()() === false }
      ];

      //------------------------------------------------------------
      // Setup Scales

      x = lines.xScale();
      y = lines.yScale();

      xAxis
        .scale(x);
      yAxis
        .scale(y);

      //------------------------------------------------------------
      // Display No Data message if there's nothing to show.

      if (!totalAmount) {
        displayNoData();
        return chart;
      } else {
        container.selectAll('.nv-noData').remove();
      }

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-lineChart').data([data]),
          gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-lineChart').append('g'),
          g = wrap.select('g').attr('class', 'nv-chartWrap');

      gEnter.append('rect').attr('class', 'nv-background')
        .attr('x', -margin.left)
        .attr('y', -margin.top)
        .attr('fill', '#FFF');

      g.select('.nv-background')
        .attr('width', availableWidth + margin.left + margin.right)
        .attr('height', availableHeight + margin.top + margin.bottom);

      gEnter.append('g').attr('class', 'nv-titleWrap');
      var titleWrap = g.select('.nv-titleWrap');
      gEnter.append('g').attr('class', 'nv-x nv-axis');
      var xAxisWrap = g.select('.nv-x.nv-axis');
      gEnter.append('g').attr('class', 'nv-y nv-axis');
      var yAxisWrap = g.select('.nv-y.nv-axis');
      gEnter.append('g').attr('class', 'nv-linesWrap');
      var linesWrap = g.select('.nv-linesWrap');
      gEnter.append('g').attr('class', 'nv-controlsWrap');
      var controlsWrap = g.select('.nv-controlsWrap');
      gEnter.append('g').attr('class', 'nv-legendWrap');
      var legendWrap = g.select('.nv-legendWrap');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------
      // Title & Legend & Controls

      var titleBBox = {width: 0, height: 0};
      titleWrap.select('.nv-title').remove();

      if (showTitle && properties.title) {
        titleWrap
          .append('text')
            .attr('class', 'nv-title')
            .attr('x', direction === 'rtl' ? availableWidth : 0)
            .attr('y', 0)
            .attr('dy', '.75em')
            .attr('text-anchor', 'start')
            .text(properties.title)
            .attr('stroke', 'none')
            .attr('fill', 'black');

        titleBBox = nv.utils.getTextBBox(g.select('.nv-title'));

        innerMargin.top += titleBBox.height + 12;
      }

      if (showControls) {
        controls
          .id('controls_' + chart.id())
          .strings(chart.strings().controls)
          .align('left')
          .height(availableHeight - innerMargin.top);
        controlsWrap
          .datum(controlsData)
          .call(controls);

        maxControlsWidth = controls.calculateWidth();
      }

      if (showLegend) {
        legend
          .id('legend_' + chart.id())
          .strings(chart.strings().legend)
          .align('right')
          .height(availableHeight - innerMargin.top);
        legendWrap
          .datum(data)
          .call(legend);

        maxLegendWidth = legend.calculateWidth();
      }

      // calculate proportional available space
      widthRatio = availableWidth / (maxControlsWidth + maxLegendWidth);
      maxControlsWidth = Math.floor(maxControlsWidth * widthRatio);
      maxLegendWidth = Math.floor(maxLegendWidth * widthRatio);

      if (showControls) {
        controls
          .arrange(maxControlsWidth);
        maxLegendWidth = availableWidth - controls.width();
      }
      if (showLegend) {
        legend
          .arrange(maxLegendWidth);
        maxControlsWidth = availableWidth - legend.width();
      }

      if (showControls) {
        var xpos = direction === 'rtl' ? availableWidth - controls.width() : 0,
            ypos = showTitle ? titleBBox.height : - legend.margin().top;
        controlsWrap
          .attr('transform', 'translate(' + xpos + ',' + ypos + ')');
        controlsHeight = controls.height();
      }

      if (showLegend) {
        var legendLinkBBox = nv.utils.getTextBBox(legendWrap.select('.nv-legend-link')),
            legendSpace = availableWidth - titleBBox.width - 6,
            legendTop = showTitle && !showControls && legend.collapsed() && legendSpace > legendLinkBBox.width ? true : false,
            xpos = direction === 'rtl' ? 0 : availableWidth - legend.width(),
            ypos = titleBBox.height;
        if (legendTop) {
          ypos = titleBBox.height - legend.height() / 2 - legendLinkBBox.height / 2;
        } else if (!showTitle) {
          ypos = - legend.margin().top;
        }
        legendWrap
          .attr('transform', 'translate(' + xpos + ',' + ypos + ')');
        legendHeight = legendTop ? 0 : legend.height() - 12;
      }

      // Recalc inner margins based on legend and control height
      innerMargin.top += Math.max(controlsHeight, legendHeight);
      innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

      //------------------------------------------------------------
      // Main Chart Component(s)

      lines
        .width(innerWidth)
        .height(innerHeight)
        .id(chart.id());
      linesWrap
        .datum(dataLines)
        .call(lines);

      //------------------------------------------------------------
      // Setup Axes

      //------------------------------------------------------------
      // X-Axis

      xAxisWrap
        .call(xAxis);

      innerMargin[xAxis.orient()] += xAxis.height();
      innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

      //------------------------------------------------------------
      // Y-Axis

      yAxisWrap
        .call(yAxis);

      innerMargin[yAxis.orient()] += yAxis.width();
      innerWidth = availableWidth - innerMargin.left - innerMargin.right;

      //------------------------------------------------------------
      // Main Chart Components
      // Recall to set final size

      lines
        .width(innerWidth)
        .height(innerHeight);

      linesWrap
        .attr('transform', 'translate(' + innerMargin.left + ',' + innerMargin.top + ')')
        .transition().duration(chart.delay())
          .call(lines);

      xAxis
        .ticks(innerWidth / 100)
        .tickSize(-innerHeight, 0);

      xAxisWrap
        .attr('transform', 'translate(' + innerMargin.left + ',' + (xAxis.orient() === 'bottom' ? innerHeight + innerMargin.top : innerMargin.top) + ')')
        .transition()
          .call(xAxis);

      yAxis
        .ticks(innerHeight / 36)
        .tickSize(-innerWidth, 0);

      yAxisWrap
        .attr('transform', 'translate(' + (yAxis.orient() === 'left' ? innerMargin.left : innerMargin.left + innerWidth) + ',' + innerMargin.top + ')')
        .transition()
          .call(yAxis);

      function displayNoData() {
        container.select('.nvd3.nv-wrap').remove();
        var noDataText = container.selectAll('.nv-noData').data([chart.strings().noData]);

        noDataText.enter().append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle');

        noDataText
          .attr('x', margin.left + availableWidth / 2)
          .attr('y', margin.top + availableHeight / 2)
          .text(function(d) {
            return d;
          });
      }

      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      legend.dispatch.on('legendClick', function(d, i) {
        d.disabled = !d.disabled;

        if (!data.filter(function(d) { return !d.disabled; }).length) {
          data.map(function(d) {
            d.disabled = false;
            g.selectAll('.nv-series').classed('disabled', false);
            return d;
          });
        }

        state.disabled = data.map(function(d) { return !!d.disabled; });
        dispatch.stateChange(state);

        container.transition().duration(chart.delay()).call(chart);
      });

      controls.dispatch.on('legendClick', function(d, i) {
        if (!d.disabled) {
          return;
        }
        controlsData = controlsData.map(function(s) {
          s.disabled = true;
          return s;
        });
        d.disabled = false;

        switch (d.key) {
          case 'Basis':
            lines.interpolate('basis');
            break;
          case 'Linear':
            lines.interpolate('linear');
            break;
          case 'Monotone':
            lines.interpolate('monotone');
            break;
          case 'Cardinal':
            lines.interpolate('cardinal');
            break;
          case 'Line':
            lines.isArea(false);
            break;
          case 'Area':
            lines.isArea(true);
            break;
        }

        state.interpolate = lines.interpolate();
        state.isArea = lines.isArea();
        dispatch.stateChange(state);

        container.transition().duration(chart.delay()).call(chart);
      });

      dispatch.on('tooltipShow', function(eo) {
        if (tooltips) {
          showTooltip(eo, that.parentNode);
        }
      });

      dispatch.on('tooltipMove', function(eo) {
        if (tooltip) {
          nv.tooltip.position(that.parentNode, tooltip, eo.pos, 's');
        }
      });

      dispatch.on('tooltipHide', function() {
        if (tooltips) {
          nv.tooltip.cleanup();
        }
      });

      // Update chart from a state object passed to event handler
      dispatch.on('changeState', function(e) {
        if (typeof e.disabled !== 'undefined') {
          data.forEach(function(series, i) {
            series.disabled = e.disabled[i];
          });
          state.disabled = e.disabled;
        }

        if (typeof e.interpolate !== 'undefined') {
          lines.interpolate(e.interpolate);
          state.interpolate = e.interpolate;
        }

        if (typeof e.isArea !== 'undefined') {
          lines.isArea(e.isArea);
          state.isArea = e.isArea;
        }

        container.transition().duration(chart.delay()).call(chart);
      });

      dispatch.on('chartClick', function() {
        if (controls.enabled()) {
          controls.dispatch.closeMenu();
        }
        if (legend.enabled()) {
          legend.dispatch.closeMenu();
        }
      });

    });

    return chart;
  }

  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  lines.dispatch.on('elementMouseover.tooltip', function(eo) {
    dispatch.tooltipShow(eo);
  });

  lines.dispatch.on('elementMousemove.tooltip', function(eo) {
    dispatch.tooltipMove(eo);
  });

  lines.dispatch.on('elementMouseout.tooltip', function() {
    dispatch.tooltipHide();
  });

  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.lines = lines;
  chart.legend = legend;
  chart.controls = controls;
  chart.xAxis = xAxis;
  chart.yAxis = yAxis;

  d3.rebind(chart, lines, 'id', 'x', 'y', 'xScale', 'yScale', 'xDomain', 'yDomain', 'forceX', 'forceY', 'clipEdge', 'delay', 'color', 'fill', 'classes', 'gradient');
  d3.rebind(chart, lines, 'defined', 'isArea', 'interpolate', 'size', 'clipVoronoi', 'useVoronoi', 'interactive', 'nice');
  d3.rebind(chart, xAxis, 'rotateTicks', 'reduceXTicks', 'staggerTicks', 'wrapTicks');

  chart.colorData = function(_) {
    var type = arguments[0],
        params = arguments[1] || {};
    var color = function(d, i) {
          return nv.utils.defaultColor()(d, d.series);
        };
    var classes = function(d, i) {
          return 'nv-group nv-series-' + d.series;
        };

    switch (type) {
      case 'graduated':
        color = function(d, i) {
          return d3.interpolateHsl(d3.rgb(params.c1), d3.rgb(params.c2))(d.series / params.l);
        };
        break;
      case 'class':
        color = function() {
          return 'inherit';
        };
        classes = function(d, i) {
          var iClass = (d.series * (params.step || 1)) % 14;
          iClass = (iClass > 9 ? '' : '0') + iClass;
          return 'nv-group nv-series-' + d.series + ' nv-fill' + iClass + ' nv-stroke' + iClass;
        };
        break;
      case 'data':
        color = function(d, i) {
          return d.color || nv.utils.defaultColor()(d, d.series);
        };
        classes = function(d, i) {
          return 'nv-group nv-series-' + d.series + (d.classes ? ' ' + d.classes : '');
        };
        break;
    }

    var fill = (!params.gradient) ? color : function(d, i) {
      var p = {orientation: params.orientation || 'horizontal', position: params.position || 'base'};
      return lines.gradient(d, d.series, p);
    };

    lines.color(color);
    lines.fill(fill);
    lines.classes(classes);

    legend.color(color);
    legend.classes(classes);

    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) {
      return margin;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        margin[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) {
      return width;
    }
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) {
      return height;
    }
    height = _;
    return chart;
  };

  chart.showTitle = function(_) {
    if (!arguments.length) {
      return showTitle;
    }
    showTitle = _;
    return chart;
  };

  chart.showControls = function(_) {
    if (!arguments.length) {
      return showControls;
    }
    showControls = _;
    return chart;
  };

  chart.showLegend = function(_) {
    if (!arguments.length) {
      return showLegend;
    }
    showLegend = _;
    return chart;
  };

  chart.tooltip = function(_) {
    if (!arguments.length) {
      return tooltip;
    }
    tooltip = _;
    return chart;
  };

  chart.tooltips = function(_) {
    if (!arguments.length) {
      return tooltips;
    }
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function(_) {
    if (!arguments.length) {
      return tooltipContent;
    }
    tooltipContent = _;
    return chart;
  };

  chart.state = function(_) {
    if (!arguments.length) {
      return state;
    }
    state = _;
    return chart;
  };

  chart.strings = function(_) {
    if (!arguments.length) {
      return strings;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        strings[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.direction = function(_) {
    if (!arguments.length) {
      return direction;
    }
    direction = _;
    yAxis.direction(_);
    legend.direction(_);
    controls.direction(_);
    return chart;
  };

  //============================================================

  return chart;
};

nv.models.lineWithFocusChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var lines = nv.models.line()
    , lines2 = nv.models.line()
    , xAxis = nv.models.axis()
    , yAxis = nv.models.axis()
    , x2Axis = nv.models.axis()
    , y2Axis = nv.models.axis()
    , legend = nv.models.legend()
    , brush = d3.svg.brush()
    ;

  var margin = {top: 30, right: 30, bottom: 30, left: 60}
    , margin2 = {top: 0, right: 30, bottom: 20, left: 60}
    , color = nv.utils.defaultColor()
    , width = null
    , height = null
    , height2 = 100
    , x
    , y
    , x2
    , y2
    , showLegend = true
    , brushExtent = null
    , tooltips = true
    , tooltip = function(key, x, y, e, graph) {
        return '<h3>' + key + '</h3>' +
               '<p>' +  y + ' at ' + x + '</p>'
      }
    , noData = "No Data Available."
    , dispatch = d3.dispatch('tooltipShow', 'tooltipHide', 'brush')
    ;

  lines
    .clipEdge(true)
    ;
  lines2
    .interactive(false)
    ;
  xAxis
    .orient('bottom')
    .tickPadding(5)
    ;
  yAxis
    .orient('left')
    ;
  x2Axis
    .orient('bottom')
    .tickPadding(5)
    ;
  y2Axis
    .orient('left')
    ;
  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var showTooltip = function(e, offsetElement) {
    var left = e.pos[0] + ( offsetElement.offsetLeft || 0 ),
        top = e.pos[1] + ( offsetElement.offsetTop || 0),
        x = xAxis.tickFormat()(lines.x()(e.point, e.pointIndex)),
        y = yAxis.tickFormat()(lines.y()(e.point, e.pointIndex)),
        content = tooltip(e.series.key, x, y, e, chart);

    nv.tooltip.show([left, top], content, null, null, offsetElement);
  };

  //============================================================


  function chart(selection) {
    selection.each(function(data) {
      var container = d3.select(this),
          that = this;

      var availableWidth = (width  || parseInt(container.style('width')) || 960)
                             - margin.left - margin.right,
          availableHeight1 = (height || parseInt(container.style('height')) || 400)
                             - margin.top - margin.bottom - height2,
          availableHeight2 = height2 - margin2.top - margin2.bottom;

      chart.update = function() { chart(selection) };
      chart.container = this;


      //------------------------------------------------------------
      // Display No Data message if there's nothing to show.

      if (!data || !data.length || !data.filter(function(d) { return d.values.length }).length) {
        var noDataText = container.selectAll('.nv-noData').data([noData]);

        noDataText.enter().append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle');

        noDataText
          .attr('x', margin.left + availableWidth / 2)
          .attr('y', margin.top + availableHeight1 / 2)
          .text(function(d) { return d });

        return chart;
      } else {
        container.selectAll('.nv-noData').remove();
      }

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup Scales

      x = lines.xScale();
      y = lines.yScale();
      x2 = lines2.xScale();
      y2 = lines2.yScale();

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-lineWithFocusChart').data([data]);
      var gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-lineWithFocusChart').append('g');
      var g = wrap.select('g');

      gEnter.append('g').attr('class', 'nv-legendWrap');

      var focusEnter = gEnter.append('g').attr('class', 'nv-focus');
      focusEnter.append('g').attr('class', 'nv-x nv-axis');
      focusEnter.append('g').attr('class', 'nv-y nv-axis');
      focusEnter.append('g').attr('class', 'nv-linesWrap');

      var contextEnter = gEnter.append('g').attr('class', 'nv-context');
      contextEnter.append('g').attr('class', 'nv-x nv-axis');
      contextEnter.append('g').attr('class', 'nv-y nv-axis');
      contextEnter.append('g').attr('class', 'nv-linesWrap');
      contextEnter.append('g').attr('class', 'nv-brushBackground');
      contextEnter.append('g').attr('class', 'nv-x nv-brush');

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Legend

      if (showLegend) {

        legend
          .id('legend_' + chart.id())
          .margin({top: 10, right: 10, bottom: 10, left: 10})
          .align('right')
          .height(availableHeight1 - margin.top);

        g.select('.nv-legendWrap')
            .datum(data)
            .call(legend);

        legend.arrange(availableWidth);

        if ( margin.top != legend.height()) {
          margin.top = legend.height();
          availableHeight1 = (height || parseInt(container.style('height')) || 400)
                             - margin.top - margin.bottom - height2;
        }

        g.select('.nv-legendWrap')
            .attr('transform', 'translate(0,' + (-margin.top) +')')
      }

      //------------------------------------------------------------


      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');


      //------------------------------------------------------------
      // Main Chart Component(s)

      lines
        .width(availableWidth)
        .height(availableHeight1)
        .color(
          data
            .map(function(d,i) {
              return d.color || color(d, i);
            })
            .filter(function(d,i) {
              return !data[i].disabled;
          })
        );

      lines2
        .defined(lines.defined())
        .width(availableWidth)
        .height(availableHeight2)
        .color(
          data
            .map(function(d,i) {
              return d.color || color(d, i);
            })
            .filter(function(d,i) {
              return !data[i].disabled;
          })
        );

      g.select('.nv-context')
          .attr('transform', 'translate(0,' + ( availableHeight1 + margin.bottom + margin2.top) + ')')

      var contextLinesWrap = g.select('.nv-context .nv-linesWrap')
          .datum(data.filter(function(d) { return !d.disabled }))

      d3.transition(contextLinesWrap).call(lines2);

      //------------------------------------------------------------


      /*
      var focusLinesWrap = g.select('.nv-focus .nv-linesWrap')
          .datum(data.filter(function(d) { return !d.disabled }))

      d3.transition(focusLinesWrap).call(lines);
     */


      //------------------------------------------------------------
      // Setup Main (Focus) Axes

      xAxis
        .scale(x)
        .ticks( availableWidth / 100 )
        .tickSize(-availableHeight1, 0);

      yAxis
        .scale(y)
        .ticks( availableHeight1 / 36 )
        .tickSize( -availableWidth, 0);

      g.select('.nv-focus .nv-x.nv-axis')
          .attr('transform', 'translate(0,' + availableHeight1 + ')');

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup Brush

      brush
        .x(x2)
        .on('brush', onBrush);

      if (brushExtent) brush.extent(brushExtent);

      var brushBG = g.select('.nv-brushBackground').selectAll('g')
          .data([brushExtent || brush.extent()])

      var brushBGenter = brushBG.enter()
          .append('g');

      brushBGenter.append('rect')
          .attr('class', 'left')
          .attr('x', 0)
          .attr('y', 0)
          .attr('height', availableHeight2);

      brushBGenter.append('rect')
          .attr('class', 'right')
          .attr('x', 0)
          .attr('y', 0)
          .attr('height', availableHeight2);

      gBrush = g.select('.nv-x.nv-brush')
          .call(brush);
      gBrush.selectAll('rect')
          //.attr('y', -5)
          .attr('height', availableHeight2);
      gBrush.selectAll('.resize').append('path').attr('d', resizePath);

      onBrush();

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup Secondary (Context) Axes

      x2Axis
        .scale(x2)
        .ticks( availableWidth / 100 )
        .tickSize(-availableHeight2, 0);

      g.select('.nv-context .nv-x.nv-axis')
          .attr('transform', 'translate(0,' + y2.range()[0] + ')');
      d3.transition(g.select('.nv-context .nv-x.nv-axis'))
          .call(x2Axis);


      y2Axis
        .scale(y2)
        .ticks( availableHeight2 / 36 )
        .tickSize( -availableWidth, 0);

      d3.transition(g.select('.nv-context .nv-y.nv-axis'))
          .call(y2Axis);

      g.select('.nv-context .nv-x.nv-axis')
          .attr('transform', 'translate(0,' + y2.range()[0] + ')');

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
        if (tooltips) showTooltip(e, that.parentNode);
      });

      //============================================================


      //============================================================
      // Functions
      //------------------------------------------------------------

      // Taken from crossfilter (http://square.github.com/crossfilter/)
      function resizePath(d) {
        var e = +(d == 'e'),
            x = e ? 1 : -1,
            y = availableHeight2 / 3;
        return 'M' + (.5 * x) + ',' + y
            + 'A6,6 0 0 ' + e + ' ' + (6.5 * x) + ',' + (y + 6)
            + 'V' + (2 * y - 6)
            + 'A6,6 0 0 ' + e + ' ' + (.5 * x) + ',' + (2 * y)
            + 'Z'
            + 'M' + (2.5 * x) + ',' + (y + 8)
            + 'V' + (2 * y - 8)
            + 'M' + (4.5 * x) + ',' + (y + 8)
            + 'V' + (2 * y - 8);
      }


      function updateBrushBG() {
        if (!brush.empty()) brush.extent(brushExtent);
        brushBG
            .data([brush.empty() ? x2.domain() : brushExtent])
            .each(function(d,i) {
              var leftWidth = x2(d[0]) - x.range()[0],
                  rightWidth = x.range()[1] - x2(d[1]);
              d3.select(this).select('.left')
                .attr('width',  leftWidth < 0 ? 0 : leftWidth);

              d3.select(this).select('.right')
                .attr('x', x2(d[1]))
                .attr('width', rightWidth < 0 ? 0 : rightWidth);
            });
      }


      function onBrush() {
        brushExtent = brush.empty() ? null : brush.extent();
        extent = brush.empty() ? x2.domain() : brush.extent();


        dispatch.brush({extent: extent, brush: brush});


        updateBrushBG();

        // Update Main (Focus)
        var focusLinesWrap = g.select('.nv-focus .nv-linesWrap')
            .datum(
              data
                .filter(function(d) { return !d.disabled })
                .map(function(d,i) {
                  return {
                    key: d.key,
                    values: d.values.filter(function(d,i) {
                      return lines.x()(d,i) >= extent[0] && lines.x()(d,i) <= extent[1];
                    })
                  }
                })
            );
        d3.transition(focusLinesWrap).call(lines);


        // Update Main (Focus) Axes
        d3.transition(g.select('.nv-focus .nv-x.nv-axis'))
            .call(xAxis);
        d3.transition(g.select('.nv-focus .nv-y.nv-axis'))
            .call(yAxis);
      }

      //============================================================


    });

    return chart;
  }


  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  lines.dispatch.on('elementMouseover.tooltip', function(e) {
    e.pos = [e.pos[0] +  margin.left, e.pos[1] + margin.top];
    dispatch.tooltipShow(e);
  });

  lines.dispatch.on('elementMouseout.tooltip', function(e) {
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
  chart.lines = lines;
  chart.lines2 = lines2;
  chart.xAxis = xAxis;
  chart.yAxis = yAxis;
  chart.x2Axis = x2Axis;
  chart.y2Axis = y2Axis;

  d3.rebind(chart, lines, 'defined', 'isArea', 'size', 'xDomain', 'yDomain', 'forceX', 'forceY', 'interactive', 'clipEdge', 'clipVoronoi', 'id');

  chart.x = function(_) {
    if (!arguments.length) return lines.x;
    lines.x(_);
    lines2.x(_);
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) return lines.y;
    lines.y(_);
    lines2.y(_);
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin.top    = typeof _.top    != 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  != 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom != 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   != 'undefined' ? _.left   : margin.left;
    return chart;
  };

  chart.margin2 = function(_) {
    if (!arguments.length) return margin2;
    margin2 = _;
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

  chart.height2 = function(_) {
    if (!arguments.length) return height2;
    height2 = _;
    return chart;
  };

  chart.color = function(_) {
    if (!arguments.length) return color;
    color =nv.utils.getColor(_);
    legend.color(color);
    return chart;
  };

  chart.showLegend = function(_) {
    if (!arguments.length) return showLegend;
    showLegend = _;
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

  chart.interpolate = function(_) {
    if (!arguments.length) return lines.interpolate();
    lines.interpolate(_);
    lines2.interpolate(_);
    return chart;
  };

  chart.noData = function(_) {
    if (!arguments.length) return noData;
    noData = _;
    return chart;
  };

  // Chart has multiple similar Axes, to prevent code duplication, probably need to link all axis functions manually like below
  chart.xTickFormat = function(_) {
    if (!arguments.length) return xAxis.tickFormat();
    xAxis.tickFormat(_);
    x2Axis.tickFormat(_);
    return chart;
  };

  chart.yTickFormat = function(_) {
    if (!arguments.length) return yAxis.tickFormat();
    yAxis.tickFormat(_);
    y2Axis.tickFormat(_);
    return chart;
  };

  //============================================================


  return chart;
}
nv.models.multiBar = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 0, right: 0, bottom: 0, left: 0},
      width = 960,
      height = 500,
      x = d3.scale.ordinal(),
      y = d3.scale.linear(),
      id = Math.floor(Math.random() * 10000), //Create semi-unique ID in case user doesn't select one
      getX = function(d) { return d.x; },
      getY = function(d) { return d.y; },
      forceY = [0], // 0 is forced by default.. this makes sense for the majority of bar graphs... user can always do chart.forceY([]) to remove
      stacked = false,
      barColor = null, // adding the ability to set the color for each rather than the whole group
      disabled, // used in conjunction with barColor to communicate to multiBarChart what series are disabled
      clipEdge = true,
      showValues = false,
      valueFormat = d3.format(',.2s'),
      withLine = false,
      vertical = true,
      baseDimension = 60,
      direction = 'ltr',
      delay = 200,
      xDomain,
      yDomain,
      nice = false,
      color = function(d, i) { return nv.utils.defaultColor()(d, d.series); },
      fill = color,
      barColor = null, // adding the ability to set the color for each rather than the whole group
      classes = function(d, i) { return 'nv-group nv-series-' + d.series; },
      dispatch = d3.dispatch('chartClick', 'elementClick', 'elementDblClick', 'elementMouseover', 'elementMouseout', 'elementMousemove');

  //============================================================

  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var x0,
      y0; //used to store previous scales

  //============================================================

  function chart(selection) {
    selection.each(function(data) {

      baseDimension = stacked ? vertical ? 72 : 30 : 20;

      var container = d3.select(this),
          orientation = vertical ? 'vertical' : 'horizontal',
          availableWidth = width - margin.left - margin.right,
          availableHeight = height - margin.top - margin.bottom,
          maxX = vertical ? availableWidth : availableHeight,
          maxY = vertical ? availableHeight : availableWidth,
          dimX = vertical ? 'width' : 'height',
          dimY = vertical ? 'height' : 'width',
          limDimX = 0,
          limDimY = 0,
          valX = vertical ? 'x' : 'y',
          valY = vertical ? 'y' : 'x',
          valuePadding = 0,
          seriesCount = 0,
          groupCount = 0;

      var barLength = function(d, i) {
        return Math.max(Math.round(Math.abs(y(getY(d, i)) - y(0))), 0);
      };
      var barThickness = function() {
        return x.rangeBand() / (stacked ? 1 : data.length);
      };

      if (stacked) {
        data = d3.layout.stack()
                 .offset('zero')
                 .values(function(d) { return d.values; })
                 .y(getY)(data);
      }

      //------------------------------------------------------------
      // HACK for negative value stacking
      if (stacked) {
        data[0].values.map(function(d, i) {
          var posBase = 0,
              negBase = 0;
          data.map(function(d) {
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
      var seriesData = (xDomain && yDomain) ?
            [] : // if we know xDomain and yDomain, no need to calculate
            d3.merge(data.map(function(d) {
              return d.values.map(function(d, i) {
                return {x: getX(d, i), y: getY(d, i), y0: d.y0, y1: d.y1};
              });
            }));

      groupCount = data[0].values.length;
      seriesCount = data.length;

      chart.resetDimensions = function(w, h) {
        width = w;
        height = h;
        availableWidth = w - margin.left - margin.right;
        availableHeight = h - margin.top - margin.bottom;
        maxX = vertical ? availableWidth : availableHeight;
        maxY = vertical ? availableHeight : availableWidth;
      };

      chart.resetScale = function() {

        availableWidth = width - margin.left - margin.right;
        availableHeight = height - margin.top - margin.bottom;
        limDimX = vertical ? availableWidth : availableHeight;
        limDimY = vertical ? availableHeight : availableWidth;

        var boundsWidth = stacked ? baseDimension : baseDimension * seriesCount + baseDimension,
            gap = baseDimension * (stacked ? 0.25 : 1),
            outerPadding = Math.max(0.25, (maxX - (groupCount * boundsWidth + gap)) / (2 * boundsWidth));

        if (withLine) {
          /*TODO: used in reports to keep bars from being too wide
            breaks pareto chart, so need to update line to adjust x position */
          x .domain(xDomain || seriesData.map(function(d) { return d.x; }))
            .rangeBands([0, maxX], 0.3);
        } else {
          x .domain(xDomain || seriesData.map(function(d) { return d.x; }))
            .rangeRoundBands([0, maxX], 0.25, outerPadding);
        }

        y .domain(yDomain || d3.extent(seriesData.map(function(d) {
            var posOffset = (vertical ? 0 : d.y),
                negOffset = (vertical ? d.y : 0);
            return stacked ? (d.y > 0 ? d.y1 + posOffset : d.y1 + negOffset) : d.y;
          }).concat(forceY)))
          .range(vertical ? [availableHeight, 0] : [0, availableWidth]);
        if (nice) {
          y.nice();
        }

        x0 = x0 || x;
        y0 = y0 || y;

        // var expandDomain = y.invert(y(0) + (vertical ? -4 : 4));
        // y.domain(
        //   y.domain().map(function(d, i) {
        //     return d += expandDomain * (d < 0 ? -1 : d > 1 ? 1 : 0);
        //   })
        // );

        //------------------------------------------------------------
        // recalculate y.range if show values
        if (!stacked && (showValues === 'top' || showValues === true)) {
          valuePadding = nv.utils.maxStringSetLength(
              seriesData.map(function(d) { return d.y; }),
              container,
              valueFormat
            );
          valuePadding += 8;
          if (vertical) {
            y.range([
              maxY - (y.domain()[0] < 0 ? valuePadding : 0),
                         y.domain()[1] > 0 ? valuePadding : 0
            ]);
          } else {
            y.range([
                         y.domain()[0] < 0 ? valuePadding : 0,
              maxY - (y.domain()[1] > 0 ? valuePadding : 0)
            ]);
          }
        }
      };

      chart.resetScale();


      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('.nvd3.nv-wrap').data([data]);
      var wrapEnter = wrap.enter().append('g');
      var defsEnter = wrapEnter.append('defs');
      var gEnter = wrapEnter.append('g');
      var g = wrap.select('g');

      wrap.attr('class', 'nvd3 nv-wrap nv-multibar' + (vertical ? '' : 'Horizontal'));

      //set up the gradient constructor function
      chart.gradient = function(d, i, p) {
        return nv.utils.colorLinearGradient(d, id + '-' + i, p, color(d, i), wrap.select('defs'));
      };

      gEnter.append('g').attr('class', 'nv-groups');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------

      if (clipEdge) {
        defsEnter.append('clipPath')
          .attr('id', 'nv-edge-clip-' + id)
          .append('rect');
        wrap.select('#nv-edge-clip-' + id + ' rect')
          .attr('width', availableWidth)
          .attr('height', availableHeight);
      }
      g .attr('clip-path', clipEdge ? 'url(#nv-edge-clip-' + id + ')' : '');

      //------------------------------------------------------------

      var groups = wrap.select('.nv-groups').selectAll('.nv-group')
            .data(function(d) { return d; }, function(d) { return d.key; });

      groups.enter().append('g')
        .style('stroke-opacity', 1e-6)
        .style('fill-opacity', 1e-6);
      groups.exit()
        .style('stroke-opacity', 1e-6)
        .style('fill-opacity', 1e-6)
          .selectAll('g.nv-bar')
            .attr('y', function(d) {
              return stacked ? y0(d.y0) : y0(0);
            })
            .attr(dimX, 0)
            .remove();

      groups
        .attr('class', classes)
        .attr('fill', fill)
        .classed('hover', function(d) { return d.hover; })
        .classed('nv-active', function(d) { return d.active === 'active'; })
        .classed('nv-inactive', function(d) { return d.active === 'inactive'; })
        .style({'stroke-opacity': 1, 'fill-opacity': 1});

      var bars = groups.selectAll('g.nv-bar')
            .data(
              function(d) {
                return d.values;
              }, function(d, i) {
                return d.series + '-' + d.x;
              }
            );

      bars.exit().remove();

      var barsEnter = bars.enter().append('g')
            .attr('class', function(d, i) {
              return getY(d, i) < 0 ? 'nv-bar negative' : 'nv-bar positive';
            })
            .attr('transform', function(d, i, j) {
              var trans = {
                x: stacked ? 0 : (j * barThickness()) + x(getX(d, i)),
                y: y0(stacked ? d.y0 : 0)
              };
              return 'translate(' + trans[valX] + ',' + trans[valY] + ')';
            });

      barsEnter.append('rect')
        .attr(dimX, 0)
        .attr(dimY, 0); //x.rangeBand() / (stacked ? 1 : data.length)

      function buildEventObject(e, d, i, j) {
        return {
            value: getY(d, i),
            point: d,
            series: data[j],
            pointIndex: i,
            seriesIndex: j,
            pos: [e.offsetX, e.offsetY],
            id: id,
            e: e
          };
      }

      bars
        .on('mouseover', function(d, i, j) { //TODO: figure out why j works above, but not here
          d3.select(this).classed('hover', true);
          var eo = buildEventObject(d3.event, d, i, j);
          dispatch.elementMouseover(eo);
        })
        .on('mousemove', function(d, i, j) {
          var eo = buildEventObject(d3.event, d, i, j);
          dispatch.elementMousemove(eo);
        })
        .on('mouseout', function(d, i, j) {
          d3.select(this).classed('hover', false);
          dispatch.elementMouseout();
        })
        .on('click', function(d, i, j) {
          d3.event.stopPropagation();
          var eo = buildEventObject(d3.event, d, i, j);
          dispatch.elementClick(eo);
        })
        .on('dblclick', function(d, i, j) {
          d3.event.stopPropagation();
          // I have no clue why this was here
          // pos = [
          //     x(getX(d, i)) + (x.rangeBand() * (stacked ? data.length / 2 : j + 0.5) / data.length),
          //     y(getY(d, i) + (stacked ? d.y0 : 0))
          //   ];
          var eo = buildEventObject(d3.event, d, i, j);
          dispatch.elementDblClick(eo);
        });


      if (stacked) {
        bars
          .attr('transform', function(d, i) {
            var xScalar = (d.active && d.active === 'active') ? x.rangeBand() * 0.1 : 0;
            var trans = {
              x: Math.round(x(getX(d, i)) - xScalar),
              y: Math.round(y(d.y1))
            };
            return 'translate(' + trans[valX] + ',' + trans[valY] + ')';
          })
          .select('rect')
            .attr(valY, function(d, i) {
              return vertical ?
                        getY(d, i) < 0 ?  2 : -2 :
                        getY(d, i) < 0 ? -2 :  2 ;
            })
            .attr(valX, 0)
            .attr(dimY, barLength)
            .attr(dimX, function(d, i) {
              var xScalar = (d.active && d.active === 'active') ? 1.2 : 1.0;
              return x.rangeBand() * xScalar;
            });
      } else {
        bars
          .attr('transform', function(d, i, j) {
            var xScalar = (d.active && d.active === 'active') ? x.rangeBand() * 0.1 / data.length : 0;
            var trans = {
              x: Math.round((j * barThickness() + x(getX(d, i))) - xScalar),
              y: Math.round(getY(d, i) < 0 ? (vertical ? y(0) : y(getY(d, i))) : (vertical ? y(getY(d, i)) : y(0)))
            };
            return 'translate(' + trans[valX] + ',' + trans[valY] + ')';
          })
          .select('rect')
            .attr(valY, function(d, i) {
              return vertical ?
                        getY(d, i) < 0 ?  2 : -2 :
                        getY(d, i) < 0 ? -2 :  2 ;
            })
            .attr(valX, 0)
            .attr(dimY, barLength)
            .attr(dimX, function(d, i) {
              var xScalar = (d.active && d.active === 'active') ? 1.2 : 1.0;
              return Math.round(x.rangeBand() * xScalar / data.length);
            });
      }

      //begin, middle, end, top
      barsEnter.append('text')
        .attr('class', 'nv-label-value');
      var barText = bars.select('.nv-label-value');
      barText
        .text(function(d, i) {
          return showValues ? valueFormat(getY(d, i)) : '';
        })
        .attr('fill-opacity', 0)
        .attr('stroke-opacity-opacity', 0);

      if (showValues) {

        var valueBox = barText[0][0].getBBox(),
            valueDim = vertical ? valueBox.height : valueBox.width;

        // KEEP: to be used in case you want rect behind text
        //var labelBoxWidth = x.rangeBand() - 8;
        // barsEnter.append('rect')
        //   .attr('class', 'nv-label-box')
        //   .attr('x', 4)
        //   .attr('y', 1)
        //   .attr('width', labelBoxWidth)
        //   .attr('height', 14)
        //   .attr('rx', 2)
        //   .attr('ry', 2)
        //   .style('fill', '#FFF')
        //   .style('stroke-width', 0)
        //   .style('fill-opacity', 0.3);

        barText
          .attr('text-anchor', function(d, i) {
            var anchor = 'middle',
                negative = getY(d, i) < 0;
            if (vertical && stacked) {
              anchor = 'middle';
            } else {
              switch (showValues) {
                case 'start':
                  anchor = negative ? 'end' : 'start';
                  break;
                case 'middle':
                  anchor = 'middle';
                  break;
                case 'end':
                  anchor = negative ? 'start' : 'end';
                  break;
                case 'top':
                default:
                  anchor = vertical ?
                    negative ? 'end' : 'start' :
                    stacked ?
                      negative ? 'start' : 'end' :
                      negative ? 'end' : 'start';
                  break;
              }
              anchor = direction === 'rtl' && anchor !== 'middle' ? anchor === 'start' ? 'end' : 'start' : anchor;
            }
            return anchor;
          })
          .attr('dy', function(d, i) {
            var dy = 0,
                negative = getY(d, i) < 0;
            if (stacked && vertical) {
              switch (showValues) {
                case 'start':
                  dy = negative ? '0.71em' : 0;
                  break;
                case 'middle':
                  dy = '0.35em';
                  break;
                case 'top':
                case 'end':
                default:
                  dy = negative ? 0 : '0.71em';
                  break;
              }
            } else {
              dy = '0.35em';
            }
            return dy;
          })
          .attr('x', function(d, i) {
            var offset = 0,
                negative = getY(d, i) < 0;
            if (vertical && stacked) {
              offset = barThickness() / 2;
            } else {
              switch (showValues) {
                case 'start':
                  offset = barLength(d, i) * (negative ? 0 : -1) + 6 * (negative ? -1 : 1);
                  break;
                case 'middle':
                  offset = -barLength(d, i) / 2;
                  break;
                case 'end':
                  offset = barLength(d, i) * (negative ? -1 : 0) + 4 * (negative ? 1 : -1);
                  break;
                case 'top':
                default:
                  offset = barLength(d, i) * (negative ? -1 : 0) + 4 * (negative ? -1 : 1) * (stacked ? -1 : 1);
                  break;
              }
              if (!vertical) {
                offset += barLength(d, i);
              }
            }
            return offset;
          })
          .attr('y', function(d, i) {
            var offset = 0,
                negative = getY(d, i) < 0;
            if (vertical && stacked) {
              switch (showValues) {
                case 'start':
                  offset = barLength(d, i) * (negative ? 0 : 1) + 6 * (negative ? 1 : -1);
                  break;
                case 'middle':
                  offset = barLength(d, i) / 2 + (negative ? 1 : -1);
                  break;
                case 'end':
                case 'top':
                default:
                  offset = barLength(d, i) * (negative ? 1 : 0) + 4 * (negative ? -1 : 1);
                  break;
              }
            } else {
              offset = barThickness() / 2;
            }
            return offset;
          })
          .attr('transform', 'rotate(' + (!stacked && vertical ? -90 : 0) + ' 0,0)')
          .style('fill', function(d, i, j) {
            if (!stacked && (showValues === 'top' || showValues === true)) {
              return '#000';
            }
            var backColor = d3.select(this.previousSibling).style('fill'),
                textColor = nv.utils.getTextContrast(backColor, i);
            return textColor;
          })
          .attr('fill-opacity', function(d, i) {
            var barHeight = barLength(d, i);
            return getY(d, i) === 0 || barHeight < valueDim ? 0 : 1;
          })
          .attr('stroke-opacity', function(d, i) {
            var barHeight = barLength(d, i);
            return getY(d, i) === 0 || barHeight < valueDim ? 0 : 1;
          });

        // KEEP: to be used in case you want rect behind text
        // bars.selectAll('text').each(function(d, i) {
        //       var width = this.getBBox().width + 20;
        //       if (width > labelBoxWidth) {
        //         labelBoxWidth = width;
        //       }
        //     });
        // bars.selectAll('rect').each(function(d, i) {
        //       d3.select(this)
        //         .attr('width', labelBoxWidth)
        //         .attr('x', -labelBoxWidth / 2);
        //     });
      }

      // TODO: fix way of passing in a custom color function
      // if (barColor) {
      //   if (!disabled) {
      //     disabled = data.map(function() { return true; });
      //   }
      //   bars
      //     //.style('fill', barColor)
      //     //.style('stroke', barColor)
      //     //.style('fill', function(d,i,j) { return d3.rgb(barColor(d,i)).darker(j).toString(); })
      //     //.style('stroke', function(d,i,j) { return d3.rgb(barColor(d,i)).darker(j).toString(); })
      //     .style('fill', function(d, i, j) {
      //       return d3.rgb(barColor(d, i))
      //                .darker(disabled.map(function(d, i) { return i; })
      //                .filter(function(d, i) { return !disabled[i]; })[j])
      //                .toString();
      //     })
      //     .style('stroke', function(d, i, j) {
      //       return d3.rgb(barColor(d, i))
      //                .darker(disabled.map(function(d, i) { return i; })
      //                .filter(function(d, i) { return !disabled[i]; })[j])
      //                .toString();
      //     });
      // }

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

  chart.color = function(_) {
    if (!arguments.length) {
      return color;
    }
    color = _;
    return chart;
  };
  chart.fill = function(_) {
    if (!arguments.length) {
      return fill;
    }
    fill = _;
    return chart;
  };
  chart.classes = function(_) {
    if (!arguments.length) {
      return classes;
    }
    classes = _;
    return chart;
  };
  chart.gradient = function(_) {
    if (!arguments.length) {
      return gradient;
    }
    gradient = _;
    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) {
      return getX;
    }
    getX = _;
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) {
      return getY;
    }
    getY = _;
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) {
      return margin;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        margin[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) {
      return width;
    }
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) {
      return height;
    }
    height = _;
    return chart;
  };

  chart.xScale = function(_) {
    if (!arguments.length) {
      return x;
    }
    x = _;
    return chart;
  };

  chart.yScale = function(_) {
    if (!arguments.length) {
      return y;
    }
    y = _;
    return chart;
  };

  chart.xDomain = function(_) {
    if (!arguments.length) {
      return xDomain;
    }
    xDomain = _;
    return chart;
  };

  chart.yDomain = function(_) {
    if (!arguments.length) {
      return yDomain;
    }
    yDomain = _;
    return chart;
  };

  chart.forceY = function(_) {
    if (!arguments.length) {
      return forceY;
    }
    forceY = _;
    return chart;
  };

  chart.stacked = function(_) {
    if (!arguments.length) {
      return stacked;
    }
    stacked = _;
    return chart;
  };

  chart.clipEdge = function(_) {
    if (!arguments.length) {
      return clipEdge;
    }
    clipEdge = _;
    return chart;
  };

  chart.barColor = function(_) {
    if (!arguments.length) {
      return barColor;
    }
    barColor = nv.utils.getColor(_);
    return chart;
  };

  chart.disabled = function(_) {
    if (!arguments.length) {
      return disabled;
    }
    disabled = _;
    return chart;
  };

  chart.id = function(_) {
    if (!arguments.length) {
      return id;
    }
    id = _;
    return chart;
  };

  chart.delay = function(_) {
    if (!arguments.length) {
      return delay;
    }
    delay = _;
    return chart;
  };

  chart.showValues = function(_) {
    if (!arguments.length) {
      return showValues;
    }
    showValues = _;
    return chart;
  };

  chart.valueFormat = function(_) {
    if (!arguments.length) {
      return valueFormat;
    }
    valueFormat = _;
    return chart;
  };

  chart.withLine = function(_) {
    if (!arguments.length) {
      return withLine;
    }
    withLine = _;
    return chart;
  };

  chart.vertical = function(_) {
    if (!arguments.length) {
      return vertical;
    }
    vertical = _;
    return chart;
  };

  chart.baseDimension = function(_) {
    if (!arguments.length) {
      return baseDimension;
    }
    baseDimension = _;
    return chart;
  };

  chart.direction = function(_) {
    if (!arguments.length) {
      return direction;
    }
    direction = _;
    return chart;
  };

  chart.nice = function(_) {
    if (!arguments.length) {
      return nice;
    }
    nice = _;
    return chart;
  };

  //============================================================

  return chart;
};
nv.models.multiBarChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var vertical = true,
      margin = {top: 10, right: 10, bottom: 10, left: 10},
      width = null,
      height = null,
      showTitle = false,
      showControls = false,
      showLegend = true,
      direction = 'ltr',
      tooltip = null,
      tooltips = true,
      scrollEnabled = true,
      overflowHandler = function(d) { return; },
      x,
      y,
      state = {},
      strings = {
        legend: {close: 'Hide legend', open: 'Show legend'},
        controls: {close: 'Hide controls', open: 'Show controls'},
        noData: 'No Data Available.'
      },
      hideEmptyGroups = true,
      dispatch = d3.dispatch('chartClick', 'elementClick', 'tooltipShow', 'tooltipHide', 'tooltipMove', 'stateChange', 'changeState');

  //============================================================
  // Private Variables
  //------------------------------------------------------------

  // Scroll variables
  var useScroll = false,
      scrollOffset = 0;

  var multibar = nv.models.multiBar()
        .stacked(false),
      xAxis = nv.models.axis()
        .tickSize(0)
        .tickPadding(4)
        .highlightZero(false)
        .showMaxMin(false)
        .tickFormat(function(d) { return d; }),
      yAxis = nv.models.axis()
        .tickPadding(4)
        .tickFormat(d3.format('s')),
      legend = nv.models.legend(),
      controls = nv.models.legend()
        .color(['#444']),
      scroll = nv.models.scroll();

  var tooltipContent = function(key, x, y, e, graph) {
    return '<h3>' + key + '</h3>' +
           '<p>' + y + ' on ' + x + '</p>';
  };

  var showTooltip = function(e, offsetElement, groupTotals) {
    var left = e.pos[0],
        top = e.pos[1],
        x = (groupTotals) ?
              (e.point.y * 100 / groupTotals[e.pointIndex].t).toFixed(1) :
              xAxis.tickFormat()(multibar.x()(e.point, e.pointIndex)),
        y = yAxis.tickFormat()(multibar.y()(e.point, e.pointIndex)),
        content = tooltipContent(e.series.key, x, y, e, chart),
        gravity = e.value < 0 ?
          vertical ? 'n' : 'e' :
          vertical ? 's' : 'w';

    tooltip = nv.tooltip.show([left, top], content, gravity, null, offsetElement);
  };

  var seriesClick = function(data, e, chart) {
    return;
  };

  //============================================================

  function chart(selection) {

    selection.each(function(chartData) {

      var that = this,
          className = vertical ? 'multibarChart' : 'multiBarHorizontalChart';

      var properties = chartData ? chartData.properties : {},
          data = chartData ? chartData.data : null,
          groupLabels = [],
          groupTotals = [],
          totalAmount = 0,
          dataBars = [],
          seriesCount = 0,
          groupCount = 0;

      // Chart layout
      var container = d3.select(this),
          availableWidth = (width || parseInt(container.style('width'), 10) || 960) - margin.left - margin.right,
          availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom,
          innerWidth = innerWidth || availableWidth,
          innerHeight = innerHeight || availableHeight,
          innerMargin = {top: 0, right: 0, bottom: 0, left: 0},
          trans = '';

      // Legend variables
      var maxControlsWidth = 0,
          maxLegendWidth = 0,
          widthRatio = 0,
          controlsHeight = 0,
          legendHeight = 0;

      // Scroll variables
      var minDimension = 0,
          boundsWidth = 0,
          baseDimension = multibar.stacked() ? vertical ? 60 : 30 : 20,
          gap = 0;

      chart.update = function() {
        container.transition().call(chart);
      };

      chart.dataSeriesActivate = function(eo) {
        var series = eo.series;

        series.active = (!series.active || series.active === 'inactive') ? 'active' : 'inactive';
        series.values.map(function(d) {
          d.active = series.active;
        });

        // if you have activated a data series, inactivate the rest
        if (series.active === 'active') {
          data.filter(function(d) {
            return d.active !== 'active';
          }).map(function(d) {
            d.active = 'inactive';
            d.values.map(function(d) {
              d.active = 'inactive';
            });
            return d;
          });
        }

        // if there are no active data series, activate them all
        if (!data.filter(function(d) {
          return d.active === 'active';
        }).length) {
          data.map(function(d) {
            d.active = '';
            d.values.map(function(d) {
              d.active = '';
            });
            container.selectAll('.nv-series').classed('nv-inactive', false);
            return d;
          });
        }

        container.call(chart);
      };

      chart.container = this;

      //------------------------------------------------------------
      // Display No Data message if there's nothing to show.

      if (!data || !data.length || !data.filter(function(d) { return d.values.length; }).length) {
        displayNoData();
        return chart;
      }

      //------------------------------------------------------------
      // Process data

      // set title display option
      showTitle = showTitle && properties.title;

      var controlsData = [
        { key: 'Grouped', disabled: state.stacked },
        { key: 'Stacked', disabled: !state.stacked }
      ];

      //make sure untrimmed values array exists
      if (hideEmptyGroups) {
        data.map(function(d) {
          if (!d._values) {
            d._values = d.values;
          }
          return d;
        });
      }

      // add series index to each data point for reference
      // and disable data series if total is zero
      data.map(function(d, i) {
        d.series = i;
        d.total = d3.sum(d.values, function(d) {
          return d.y;
        });
        if (!d.total) {
          d.disabled = true;
        }
      });

      // update groupTotal amounts based on enabled data series
      groupTotals = properties.values
        .map(function(d, i) {
          d.t = d3.sum(
            // only sum enabled series
            data
              .map(function(m, j) {
                if (m.disabled) {
                  return 0;
                }
                return (hideEmptyGroups ? m._values : m.values)
                  .filter(function(v, k) {
                    return multibar.x()(v, k) === d.group;
                  })
                  .map(function(v, k) {
                    return multibar.y()(v, k);
                  });
              })
          );
          return d;
        });

      totalAmount = d3.sum(groupTotals, function(d) { return d.t; });

      // build a trimmed array for active group only labels
      groupLabels = properties.labels
        .filter(function(d, i) {
          return hideEmptyGroups ? groupTotals[i].t !== 0 : true;
        })
        .map(function(d) {
          return d.l;
        });

      dataBars = data
        .filter(function(d, i) {
          return !d.disabled && (!d.type || d.type === 'bar');
        });

      if (hideEmptyGroups) {
        // build a discrete array of data values for the multibar
        // based on enabled data series
        dataBars
          .map(function(d, i) {
            //reset series values to exlcude values for
            //groups that have a sum of zero
            d.values = d._values
              .filter(function(d, i) {
                return groupTotals[i].t !== 0;
              })
              .map(function(m, j) {
                return {
                  "series": d.series,
                  "x": (j + 1),
                  "y": m.y,
                  "y0": m.y0,
                  "active": typeof d.active !== 'undefined' ? d.active : ''
                };
              });
            return d;
          });
      }

      //------------------------------------------------------------
      // Display No Data message if there's nothing to show.

      if (!totalAmount) {
        displayNoData();
        return chart;
      } else {
        container.selectAll('.nv-noData').remove();
      }

      // safety array
      if (!dataBars.length) {
        dataBars = [{values: []}];
      }

      // set state.disabled
      state.disabled = data.map(function(d) { return !!d.disabled; });
      state.stacked = multibar.stacked();

      groupCount = groupLabels.length;
      seriesCount = dataBars.length;

      // for stacked, baseDimension is width of bar plus 1/4 of bar for gap
      // for grouped, baseDimension is width of bar plus width of one bar for gap
      boundsWidth = state.stacked ? baseDimension : baseDimension * seriesCount + baseDimension,
      gap = baseDimension * (state.stacked ? 0.25 : 1);
      minDimension = groupCount * boundsWidth + gap;
      useScroll = (minDimension > (vertical ? innerWidth : innerHeight));

      //------------------------------------------------------------
      // Setup Scales and Axes

      x = multibar.xScale();
      y = multibar.yScale();

      xAxis
        .orient(vertical ? 'bottom' : 'left')
        .scale(x)
        .tickFormat(function(d, i) {
          // Set xAxis to use trimmed array rather than data
          return groupLabels[i] || 'undefined';
        });

      yAxis
        .orient(vertical ? 'left' : 'bottom')
        .scale(y);

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('.nvd3.nv-wrap').data([data]),
          gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap').append('g'),
          g = wrap.select('g').attr('class', 'nv-chartWrap');
      wrap
        .attr('class', 'nvd3 nv-wrap nv-' + className);

      /* Clipping box for scroll */
      gEnter.append('defs');

      /* Container for scroll elements */
      gEnter.append('g').attr('class', 'nv-scroll-background');

      gEnter.append('g').attr('class', 'nv-titleWrap');
      var titleWrap = g.select('.nv-titleWrap');

      gEnter.append('g').attr('class', 'nv-y nv-axis');
      var yAxisWrap = g.select('.nv-y.nv-axis');

      /* Append scroll group with chart mask */
      gEnter.append('g').attr('class', 'nv-scroll-wrap');
      var scrollWrap = g.select('.nv-scroll-wrap');

      gEnter.select('.nv-scroll-wrap').append('g')
        .attr('class', 'nv-x nv-axis');
      var xAxisWrap = g.select('.nv-x.nv-axis');

      gEnter.select('.nv-scroll-wrap').append('g')
        .attr('class', 'nv-barsWrap');
      var barsWrap = g.select('.nv-barsWrap');

      gEnter.append('g').attr('class', 'nv-controlsWrap');
      var controlsWrap = g.select('.nv-controlsWrap');
      gEnter.append('g').attr('class', 'nv-legendWrap');
      var legendWrap = g.select('.nv-legendWrap');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------
      // Title & Legend & Controls

      var titleBBox = {width: 0, height: 0};
      titleWrap.select('.nv-title').remove();

      if (showTitle) {

        titleWrap
          .append('text')
            .attr('class', 'nv-title')
            .attr('x', direction === 'rtl' ? availableWidth : 0)
            .attr('y', 0)
            .attr('dy', '.75em')
            .attr('text-anchor', 'start')
            .text(properties.title)
            .attr('stroke', 'none')
            .attr('fill', 'black');

        titleBBox = nv.utils.getTextBBox(g.select('.nv-title'));

        innerMargin.top += titleBBox.height + 12;
      }

      if (showControls) {
        controls
          .id('controls_' + chart.id())
          .strings(chart.strings().controls)
          .align('left')
          .height(availableHeight - innerMargin.top);
        controlsWrap
          .datum(controlsData)
          .call(controls);

        maxControlsWidth = controls.calculateWidth();
      }

      if (showLegend) {
        if (multibar.barColor()) {
          data.forEach(function(series, i) {
            series.color = d3.rgb('#ccc').darker(i * 1.5).toString();
          });
        }

        legend
          .id('legend_' + chart.id())
          .strings(chart.strings().legend)
          .align('right')
          .height(availableHeight - innerMargin.top);
        legendWrap
          .datum(data)
          .call(legend);

        maxLegendWidth = legend.calculateWidth();
      }

      // calculate proportional available space
      widthRatio = availableWidth / (maxControlsWidth + maxLegendWidth);
      maxControlsWidth = Math.floor(maxControlsWidth * widthRatio);
      maxLegendWidth = Math.floor(maxLegendWidth * widthRatio);

      if (showControls) {
        controls
          .arrange(maxControlsWidth);
        maxLegendWidth = availableWidth - controls.width();
      }
      if (showLegend) {
        legend
          .arrange(maxLegendWidth);
        maxControlsWidth = availableWidth - legend.width();
      }

      if (showControls) {
        var xpos = direction === 'rtl' ? availableWidth - controls.width() : 0,
            ypos = showTitle ? titleBBox.height : - legend.margin().top;
        controlsWrap
          .attr('transform', 'translate(' + xpos + ',' + ypos + ')');
        controlsHeight = controls.height();
      }

      if (showLegend) {
        var legendLinkBBox = nv.utils.getTextBBox(legendWrap.select('.nv-legend-link')),
            legendSpace = availableWidth - titleBBox.width - 6,
            legendTop = showTitle && !showControls && legend.collapsed() && legendSpace > legendLinkBBox.width ? true : false,
            xpos = direction === 'rtl' ? 0 : availableWidth - legend.width(),
            ypos = titleBBox.height;
        if (legendTop) {
          ypos = titleBBox.height - legend.height() / 2 - legendLinkBBox.height / 2;
        } else if (!showTitle) {
          ypos = - legend.margin().top;
        }

        legendWrap
          .attr('transform', 'translate(' + xpos + ',' + ypos + ')');

        legendHeight = legendTop ? 0 : legend.height() - 12;
      }

      // Recalc inner margins based on legend and control height
      innerMargin.top += Math.max(controlsHeight, legendHeight);
      innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

      //------------------------------------------------------------
      // Main Chart Component(s)

      function getDimension(d) {
        if (d === 'width') {
          return vertical && scrollEnabled ? Math.max(innerWidth, minDimension) : innerWidth;
        } else if (d === 'height') {
          return !vertical && scrollEnabled ? Math.max(innerHeight, minDimension) : innerHeight;
        } else {
          return 0;
        }
      }

      function displayNoData() {
        container.select('.nvd3.nv-wrap').remove();
        var noDataText = container.selectAll('.nv-noData').data([chart.strings().noData]);

        noDataText.enter().append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle');

        noDataText
          .attr('x', margin.left + availableWidth / 2)
          .attr('y', margin.top + availableHeight / 2)
          .text(function(d) {
            return d;
          });
      }

      multibar
        .vertical(vertical)
        .baseDimension(baseDimension)
        .disabled(data.map(function(series) { return series.disabled; }))
        .width(getDimension('width'))
        .height(getDimension('height'))
        .clipEdge(false);
      barsWrap
        .data([dataBars])
        .call(multibar);

      //------------------------------------------------------------
      // Setup Axes

      // Y-Axis
      yAxisWrap
        .call(yAxis);

      innerMargin[yAxis.orient()] += vertical ? yAxis.width() : yAxis.height();
      innerWidth = availableWidth - innerMargin.left - innerMargin.right;
      innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

      // Recalc chart dimensions and scales based on new inner dimensions
      multibar.resetDimensions(getDimension('width'), getDimension('height'));
      multibar.resetScale();

      // X-Axis
      xAxisWrap
        .call(xAxis);

      innerMargin[xAxis.orient()] += vertical ? xAxis.height() : xAxis.width();
      innerWidth = availableWidth - innerMargin.left - innerMargin.right;
      innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

      multibar.resetDimensions(getDimension('width'), getDimension('height'));
      multibar.resetScale();

      //------------------------------------------------------------
      // Main Chart Components
      // Recall to set final sizes

      scrollWrap
        .attr('transform', 'translate(' + innerMargin.left + ',' + innerMargin.top + ')');

      barsWrap
        .transition()
          .call(multibar);

      trans = 'translate(';
      trans += vertical ? 0 : (xAxis.orient() === 'left' ? 0 : innerWidth);
      trans += ',';
      trans += vertical ? (xAxis.orient() === 'bottom' ? innerHeight : 0) : 0;
      trans += ')';

      xAxisWrap
        .attr('transform', trans)
        .transition()
          .call(xAxis);

      xAxisWrap.select('.nv-axislabel')
        .attr('x', (vertical ? innerWidth : -innerHeight) / 2);

      trans = 'translate(';
      trans += innerMargin.left + (vertical ? (yAxis.orient() === 'left' ? 0 : innerWidth) : 0);
      trans += ',';
      trans += innerMargin.top + (vertical ? 0 : (yAxis.orient() === 'bottom' ? innerHeight : 0));
      trans += ')';

      yAxis
        //.ticks(innerHeight / 36)
        .tickSize((vertical ? -innerWidth : -innerHeight), 0);

      yAxisWrap
        .attr('transform', trans)
        .transition()
          .call(yAxis);


      //------------------------------------------------------------
      // Enable scrolling
      if (scrollEnabled) {
        var diff = (vertical ? innerWidth : innerHeight) - minDimension,
            panMultibar = function() {
              if (d3.event && d3.event.type === 'click') {
                return;
              }
              dispatch.tooltipHide(d3.event);
              scrollOffset = scroll.pan(diff);
              xAxisWrap.select('.nv-axislabel')
                .attr('x', (vertical ? innerWidth - scrollOffset * 2 : scrollOffset * 2 - innerHeight) / 2);
            };

        scroll
          .id(chart.id())
          .enable(useScroll)
          .vertical(vertical)
          .width(innerWidth)
          .height(innerHeight)
          .margin(innerMargin)
          .minDimension(minDimension)
          .panHandler(panMultibar);

        scroll(g, gEnter, scrollWrap, xAxis);

        scroll.init(scrollOffset, overflowHandler);

        // initial call to zoom in case of scrolled bars on window resize
        scroll.panHandler()();
      }

      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      legend.dispatch.on('legendClick', function(d, i) {
        d.disabled = !d.disabled;

        if (hideEmptyGroups) {
          data.map(function(m, j) {
            m._values.map(function(v, k) {
              v.disabled = (k === i ? d.disabled : v.disabled ? true : false);
              return v;
            });
            return m;
          });
        }

        if (!data.filter(function(d) { return !d.disabled; }).length) {
          data.map(function(d) {
            d.disabled = false;
            g.selectAll('.nv-series').classed('disabled', false);
            return d;
          });
        }

        state.disabled = data.map(function(d) { return !!d.disabled; });
        dispatch.stateChange(state);

        container.transition().call(chart);
      });

      controls.dispatch.on('legendClick', function(d, i) {

        //if the option is currently enabled (i.e., selected)
        if (!d.disabled) {
          return;
        }

        //set the controls all to false
        controlsData = controlsData.map(function(s) {
          s.disabled = true;
          return s;
        });
        //activate the the selected control option
        d.disabled = false;

        switch (d.key) {
          case 'Grouped':
            multibar.stacked(false);
            break;
          case 'Stacked':
            multibar.stacked(true);
            break;
        }

        state.stacked = multibar.stacked();
        dispatch.stateChange(state);

        container.transition().call(chart);
      });

      dispatch.on('tooltipShow', function(eo) {
        if (tooltips) {
          showTooltip(eo, that.parentNode, groupTotals);
        }
      });

      dispatch.on('tooltipMove', function(eo) {
        if (tooltip) {
          nv.tooltip.position(that.parentNode, tooltip, eo.pos, vertical ? 's' : 'w');
        }
      });

      dispatch.on('tooltipHide', function() {
        if (tooltips) {
          nv.tooltip.cleanup();
        }
      });

      // Update chart from a state object passed to event handler
      dispatch.on('changeState', function(e) {
        if (typeof eo.disabled !== 'undefined') {
          data.forEach(function(series, i) {
            series.disabled = eo.disabled[i];
          });
          state.disabled = eo.disabled;
        }

        if (typeof eo.stacked !== 'undefined') {
          multibar.stacked(eo.stacked);
          state.stacked = eo.stacked;
        }

        container.transition().call(chart);
      });

      dispatch.on('chartClick', function() {
        if (controls.enabled()) {
          controls.dispatch.closeMenu();
        }
        if (legend.enabled()) {
          legend.dispatch.closeMenu();
        }
      });

      multibar.dispatch.on('elementClick', function(eo) {
        dispatch.chartClick();
        seriesClick(data, eo, chart);
      });

    });

    return chart;
  }

  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  multibar.dispatch.on('elementMouseover.tooltip', function(eo) {
    dispatch.tooltipShow(eo);
  });

  multibar.dispatch.on('elementMousemove.tooltip', function(eo) {
    dispatch.tooltipMove(eo);
  });

  multibar.dispatch.on('elementMouseout.tooltip', function() {
    dispatch.tooltipHide();
  });

  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.multibar = multibar;
  chart.legend = legend;
  chart.controls = controls;
  chart.xAxis = xAxis;
  chart.yAxis = yAxis;

  d3.rebind(chart, multibar, 'id', 'x', 'y', 'xScale', 'yScale', 'xDomain', 'yDomain', 'forceX', 'forceY', 'clipEdge', 'delay', 'color', 'fill', 'classes', 'gradient');
  d3.rebind(chart, multibar, 'stacked', 'showValues', 'valueFormat', 'nice');
  d3.rebind(chart, xAxis, 'rotateTicks', 'reduceXTicks', 'staggerTicks', 'wrapTicks');

  chart.colorData = function(_) {
    var type = arguments[0],
        params = arguments[1] || {};
    var color = function(d, i) {
          return nv.utils.defaultColor()(d, d.series);
        };
    var classes = function(d, i) {
          return 'nv-group nv-series-' + d.series;
        };

    switch (type) {
      case 'graduated':
        color = function(d, i) {
          return d3.interpolateHsl(d3.rgb(params.c1), d3.rgb(params.c2))(d.series / params.l);
        };
        break;
      case 'class':
        color = function() {
          return 'inherit';
        };
        classes = function(d, i) {
          var iClass = (d.series * (params.step || 1)) % 14;
          iClass = (iClass > 9 ? '' : '0') + iClass;
          return 'nv-group nv-series-' + d.series + ' nv-fill' + iClass;
        };
        break;
      case 'data':
        color = function(d, i) {
          return d.classes ? 'inherit' : d.color || nv.utils.defaultColor()(d, d.series);
        };
        classes = function(d, i) {
          return 'nv-group nv-series-' + d.series + (d.classes ? ' ' + d.classes : '');
        };
        break;
    }

    var fill = (!params.gradient) ? color : function(d, i) {
      var p = {orientation: params.orientation || (vertical ? 'vertical' : 'horizontal'), position: params.position || 'middle'};
      return multibar.gradient(d, d.series, p);
    };

    multibar.color(color);
    multibar.fill(fill);
    multibar.classes(classes);

    legend.color(color);
    legend.classes(classes);

    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) {
      return margin;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        margin[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.vertical = function(_) {
    if (!arguments.length) {
      return vertical;
    }
    vertical = _;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) {
      return width;
    }
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) {
      return height;
    }
    height = _;
    return chart;
  };

  chart.showTitle = function(_) {
    if (!arguments.length) {
      return showTitle;
    }
    showTitle = _;
    return chart;
  };

  chart.showControls = function(_) {
    if (!arguments.length) {
      return showControls;
    }
    showControls = _;
    return chart;
  };

  chart.showLegend = function(_) {
    if (!arguments.length) {
      return showLegend;
    }
    showLegend = _;
    return chart;
  };

  chart.tooltip = function(_) {
    if (!arguments.length) {
      return tooltip;
    }
    tooltip = _;
    return chart;
  };

  chart.tooltips = function(_) {
    if (!arguments.length) {
      return tooltips;
    }
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function(_) {
    if (!arguments.length) {
      return tooltipContent;
    }
    tooltipContent = _;
    return chart;
  };

  chart.state = function(_) {
    if (!arguments.length) {
      return state;
    }
    state = _;
    return chart;
  };

  chart.strings = function(_) {
    if (!arguments.length) {
      return strings;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        strings[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.allowScroll = function(_) {
    if (!arguments.length) {
      return scrollEnabled;
    }
    scrollEnabled = _;
    return chart;
  };

  chart.overflowHandler = function(_) {
    if (!arguments.length) {
      return overflowHandler;
    }
    overflowHandler = d3.functor(_);
    return chart;
  };

  chart.seriesClick = function(_) {
    if (!arguments.length) {
      return seriesClick;
    }
    seriesClick = _;
    return chart;
  };

  chart.hideEmptyGroups = function(_) {
    if (!arguments.length) {
      return hideEmptyGroups;
    }
    hideEmptyGroups = _;
    return chart;
  };

  chart.direction = function(_) {
    if (!arguments.length) {
      return direction;
    }
    direction = _;
    multibar.direction(_);
    xAxis.direction(_);
    yAxis.direction(_);
    legend.direction(_);
    controls.direction(_);
    return chart;
  };


  //============================================================

  return chart;
};
nv.models.paretoChart = function() {
    //'use strict';
    //============================================================
    // Public Variables with Default Settings
    //------------------------------------------------------------

    var margin = {top: 10, right: 10, bottom: 10, left: 10},
        width = null,
        height = null,
        getX = function(d) { return d.x; },
        getY = function(d) { return d.y; },
        showTitle = false,
        showLegend = true,
        tooltip = null,
        tooltips = true,
        direction = 'ltr',
        tooltipBar = function(key, x, y, e, graph) {
            return '<p><b>' + key + '</b></p>' +
                '<p><b>' + y + '</b></p>' +
                '<p><b>' + x + '%</b></p>';
        },
        tooltipLine = function(key, x, y, e, graph) {
            return '<p><p>' + key + ': <b>' + y + '</b></p>';
        },
        tooltipQuota = function(key, x, y, e, graph) {
            return '<p>' + e.key + ': <b>' + y + '</b></p>';
        },
        yAxisTickFormat = function(d) {
            return d3.format(',.2s')(d);
        },
        quotaTickFormat = function(d) {
            return d3.format(',.3s')(d);
        },
        x,
        y,
        strings = {
            barlegend: {close: 'Hide bar legend', open: 'Show bar legend'},
            linelegend: {close: 'Hide line legend', open: 'Show line legend'},
            controls: {close: 'Hide controls', open: 'Show controls'},
            noData: 'No Data Available.'
        },
        dispatch = d3.dispatch('chartClick', 'tooltipShow', 'tooltipHide', 'tooltipMove');

    //============================================================
    // Private Variables
    //------------------------------------------------------------

    var multibar = nv.models.multiBar()
            .stacked(true)
            .clipEdge(false)
            .withLine(true)
            .nice(true),
        lines1 = nv.models.line()
            .color(function(d, i) { return '#FFF'; })
            .fill(function(d, i) { return '#FFF'; })
            .useVoronoi(false)
            .nice(true),
        lines2 = nv.models.line()
            .useVoronoi(false)
            .color('data')
            .nice(true),
        xAxis = nv.models.axis()
            .orient('bottom')
            .tickSize(0)
            .tickPadding(4)
            .wrapTicks(true)
            .highlightZero(false)
            .showMaxMin(false)
            .tickFormat(function(d) { return d; }),
        yAxis = nv.models.axis()
            .orient('left')
            .tickPadding(7)
            .showMaxMin(false),
        barLegend = nv.models.legend()
            .align('left')
            .position('middle'),
        lineLegend = nv.models.legend()
            .align('right')
            .position('middle');

    var showTooltip = function(e, offsetElement, dataGroup) {
        var left = e.pos[0],
            top = e.pos[1],
            per = (e.point.y * 100 / dataGroup[e.pointIndex].t).toFixed(1),
            amt = yAxis.tickFormat()(lines2.y()(e.point, e.pointIndex)),
            content = (e.series.type === 'bar' ? tooltipBar(e.series.key, per, amt, e, chart) : tooltipLine(e.series.key, per, amt, e, chart));

        tooltip = nv.tooltip.show([left, top], content, 's', null, offsetElement);
    };

    var showQuotaTooltip = function(e, offsetElement) {
        var left = e.pos[0],
            top = e.pos[1],
            amt = d3.format(',.2s')(e.val),
            content = tooltipQuota(e.key, 0, amt, e, chart);

        tooltip = nv.tooltip.show([left, top], content, 's', null, offsetElement);
    };

    var barClick = function(data, eo, chart, container) {
        return;
    };

    var getAbsoluteXY = function(element) {
        var viewportElement = document.documentElement,
            box = element.getBoundingClientRect(),
            scrollLeft = viewportElement.scrollLeft + document.body.scrollLeft,
            scrollTop = viewportElement.scrollTop + document.body.scrollTop,
            x = box.left + scrollLeft,
            y = box.top + scrollTop;

        return {'x': x, 'y': y};
    };

    //============================================================

    function chart(selection) {

        selection.each(function(chartData) {

            var properties = chartData.properties,
                data = chartData.data,
                container = d3.select(this),
                that = this,
                availableWidth = (width || parseInt(container.style('width'), 10) || 960) - margin.left - margin.right,
                availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom,
                innerWidth = availableWidth,
                innerHeight = availableHeight,
                innerMargin = {top: 0, right: 0, bottom: 0, left: 0},
                maxBarLegendWidth = 0,
                maxLineLegendWidth = 0,
                widthRatio = 0;

            chart.update = function() {
                container.call(chart);
            };

            chart.container = this;

            //------------------------------------------------------------
            // Display No Data message if there's nothing to show.

            if (!data || !data.length || !data.filter(function(d) {
                return d.values.length;
            }).length) {
                var noDataText = container.selectAll('.nv-noData').data([chart.strings().noData]);

                noDataText.enter().append('text')
                    .attr('class', 'nvd3 nv-noData')
                    .attr('dy', '-.7em')
                    .style('text-anchor', 'middle');

                noDataText
                    .attr('x', margin.left + availableWidth / 2)
                    .attr('y', margin.top + availableHeight / 2)
                    .text(function(d) {
                        return d;
                    });

                return chart;
            } else {
                container.selectAll('.nv-noData').remove();
            }

            //------------------------------------------------------------
            // Process data

            var dataBars = data.filter(function(d) {
                    return !d.disabled && (!d.type || d.type === 'bar');
                }),
                dataLines = data.filter(function(d) {
                    return !d.disabled && d.type === 'line';
                }).map(function(lineData) {
                        if (!multibar.stacked()) {
                            lineData.values = lineData.valuesOrig.map(function(v, i) {
                                return {'series': v.series, 'x': (v.x + v.series * 0.25 - i * 0.25), 'y': v.y};
                            });
                        } else {
                            lineData.values.map(function(v) {
                                v.y = 0;
                            });
                            dataBars
                                .map(function(v, i) {
                                    v.values.map(function(v, i) {
                                        lineData.values[i].y += v.y;
                                    });
                                });
                            lineData.values.map(function(v, i) {
                                if (i > 0) {
                                    v.y += lineData.values[i - 1].y;
                                }
                            });
                        }
                        return lineData;
                    }),
                dataGroup = properties.groupData,
                quotaValue = properties.quota || 0,
                quotaLabel = properties.quotaLabel || '',
                targetQuotaValue = properties.targetQuota || 0,
                targetQuotaLabel = properties.targetQuotaLabel || '';

            dataBars = dataBars.length ? dataBars : [{values: []}];
            dataLines = dataLines.length ? dataLines : [{values: []}];

            // line legend data
            var lineLegendData = [{'key': quotaLabel, 'type': 'dash', 'color': '#444', 'values': {'series': 0, 'x': 0, 'y': 0}}];
            if (targetQuotaValue > 0) {
                lineLegendData.push({'key': targetQuotaLabel, 'type': 'dash', 'color': '#777', 'values': {'series': 0, 'x': 0, 'y': 0}});
            }

            var seriesX = data.filter(function(d) {
                return !d.disabled;
            }).map(function(d) {
                    return d.valuesOrig.map(function(d, i) {
                        return getX(d, i);
                    });
                });

            var seriesY = data.map(function(d) {
                return d.valuesOrig.map(function(d, i) {
                    return getY(d, i);
                });
            });

            //------------------------------------------------------------
            // Setup Scales

            x = multibar.xScale();
            y = multibar.yScale();

            xAxis
                .scale(x);
            yAxis
                .scale(y)
                .tickFormat(yAxisTickFormat);

            if (dataGroup.length) {
                xAxis
                    .tickFormat(function(d, i) {
                        return dataGroup[i] ? dataGroup[i].l : 'asfd';
                    });
            }

            //------------------------------------------------------------
            // Setup containers and skeleton of chart

            var wrap = container.selectAll('g.nv-wrap.nv-multiBarWithLegend').data([data]),
                gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-multiBarWithLegend').append('g'),
                g = wrap.select('g').attr('class', 'nv-chartWrap');

            gEnter.append('rect').attr('class', 'nv-background')
                .attr('x', -margin.left)
                .attr('y', -margin.top)
                .attr('width', availableWidth + margin.left + margin.right)
                .attr('height', availableHeight + margin.top + margin.bottom)
                .attr('fill', '#FFF');

            gEnter.append('g').attr('class', 'nv-titleWrap');
            var titleWrap = g.select('.nv-titleWrap');
            gEnter.append('g').attr('class', 'nv-x nv-axis');
            var xAxisWrap = g.select('.nv-x.nv-axis');
            gEnter.append('g').attr('class', 'nv-y nv-axis');
            var yAxisWrap = g.select('.nv-y.nv-axis');
            gEnter.append('g').attr('class', 'nv-barsWrap');
            var barsWrap = g.select('.nv-barsWrap');
            gEnter.append('g').attr('class', 'nv-quotaWrap');
            var quotaWrap = g.select('.nv-quotaWrap');

            gEnter.append('g').attr('class', 'nv-linesWrap1');
            var linesWrap1 = g.select('.nv-linesWrap1');
            gEnter.append('g').attr('class', 'nv-linesWrap2');
            var linesWrap2 = g.select('.nv-linesWrap2');

            gEnter.append('g').attr('class', 'nv-legendWrap nv-barLegend');
            var barLegendWrap = g.select('.nv-legendWrap.nv-barLegend');
            gEnter.append('g').attr('class', 'nv-legendWrap nv-lineLegend');
            var lineLegendWrap = g.select('.nv-legendWrap.nv-lineLegend');

            wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

            //------------------------------------------------------------
            // Title & Legend

            if (showTitle && properties.title) {
                titleWrap.select('.nv-title').remove();

                titleWrap
                    .append('text')
                    .text(properties.title)
                    .attr('class', 'nv-title')
                    .attr('x', direction === 'rtl' ? availableWidth : 0)
                    .attr('y', 0)
                    .attr('dy', '.75em')
                    .attr('text-anchor', 'start')
                    .attr('stroke', 'none')
                    .attr('fill', 'black');

                innerMargin.top += parseInt(g.select('.nv-title').node().getBoundingClientRect().height / 1.15, 10) +
                    parseInt(g.select('.nv-title').style('margin-top'), 10) +
                    parseInt(g.select('.nv-title').style('margin-bottom'), 10);
            }

            if (showLegend) {

                // bar series legend
                barLegend
                    .id('barlegend_' + chart.id())
                    .strings(chart.strings().barlegend)
                    .align('left')
                    .height(availableHeight - innerMargin.top);
                barLegendWrap
                    .datum(
                        data.filter(function(d) {
                            return d.type === 'bar';
                        })
                    )
                    .call(barLegend);

                maxBarLegendWidth = barLegend.calculateWidth();

                // line series legend
                lineLegend
                    .id('linelegend_' + chart.id())
                    .strings(chart.strings().linelegend)
                    .align('right')
                    .height(availableHeight - innerMargin.top);
                lineLegendWrap
                    .datum(
                        data.filter(function(d) {
                            return d.type === 'line';
                        }).concat(lineLegendData)
                    )
                    .call(lineLegend);

                maxLineLegendWidth = lineLegend.calculateWidth();

                // calculate proportional available space
                widthRatio = availableWidth / (maxBarLegendWidth + maxLineLegendWidth);

                barLegend
                    .arrange(Math.floor(widthRatio * maxBarLegendWidth));

                lineLegend
                    .arrange(Math.floor(widthRatio * maxLineLegendWidth));
                //.arrange(Math.floor(availableWidth - barLegend.width()));


                barLegendWrap
                    .attr('transform', 'translate(' + (direction === 'rtl' ? availableWidth - barLegend.width() : 0) + ',' + innerMargin.top + ')');
                lineLegendWrap
                    .attr('transform', 'translate(' + (direction === 'rtl' ? 0 : availableWidth - lineLegend.width()) + ',' + innerMargin.top + ')');
            }

            //------------------------------------------------------------
            // Recalculate inner margins based on legend size

            innerMargin.top += Math.max(barLegend.height(), lineLegend.height()) + 4;
            innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

            //------------------------------------------------------------
            // Initial call of Main Chart Components

            var lx = x.domain(d3.merge(seriesX)).rangeBands([0, availableWidth - margin.left - margin.right], 0.3),
                ly = Math.max(d3.max(d3.merge(seriesY)), quotaValue, targetQuotaValue || 0),
                forceY = Math.ceil(ly * 0.1) * 10,
                lOffset = lx(1) + lx.rangeBand() / (multibar.stacked() || dataLines.length === 1 ? 2 : 4);

            // Main Bar Chart
            multibar
                .width(innerWidth)
                .height(innerHeight)
                .forceY([0, forceY])
                .id(chart.id());
            barsWrap
                .datum(dataBars)
                .call(multibar);

            // Main Line Chart
            lines1
                .margin({top: 0, right: lOffset, bottom: 0, left: lOffset})
                .width(innerWidth)
                .height(innerHeight)
                .forceY([0, forceY])
                .useVoronoi(false)
                .id('outline_' + chart.id());
            lines2
                .margin({top: 0, right: lOffset, bottom: 0, left: lOffset})
                .width(innerWidth)
                .height(innerHeight)
                .forceY([0, forceY])
                .useVoronoi(false)
                .size(function() { return Math.pow(6, 2) * Math.PI; })
                .id('foreground_' + chart.id());
            linesWrap1
                .datum(dataLines)
                .call(lines1);
            linesWrap2
                .datum(dataLines)
                .call(lines2);

            // Axis
            xAxisWrap
                .call(xAxis);
            yAxisWrap
                .style('opacity', dataBars.length ? 1 : 0)
                .call(yAxis);

            //------------------------------------------------------------
            // Calculate intial dimensions based on first Axis call

            innerWidth = availableWidth - innerMargin.left - innerMargin.right - yAxis.width();
            innerHeight = availableHeight - innerMargin.top - innerMargin.bottom - xAxis.height();

            //------------------------------------------------------------
            // Recall Main Chart and Axis

            multibar
                .width(innerWidth)
                .height(innerHeight);
            barsWrap
                .call(multibar);
            xAxisWrap
                .call(xAxis);
            yAxisWrap
                .call(yAxis);

            //------------------------------------------------------------
            // Recalculate final dimensions based on new Axis size

            innerMargin[yAxis.orient()] += yAxis.width();
            innerWidth = availableWidth - innerMargin.left - innerMargin.right;
            innerMargin[xAxis.orient()] += xAxis.height();
            innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

            //------------------------------------------------------------
            // Recall Main Chart Components based on final dimensions

            multibar
                .width(innerWidth)
                .height(innerHeight);

            barsWrap
                .attr('transform', 'translate(' + innerMargin.left + ',' + innerMargin.top + ')')
                .call(multibar);

            lines1
                .width(innerWidth)
                .height(innerHeight);
            lines2
                .width(innerWidth)
                .height(innerHeight);

            linesWrap1
                .attr('transform', 'translate(' + innerMargin.left + ',' + innerMargin.top + ')')
                .call(lines1);
            linesWrap2
                .attr('transform', 'translate(' + innerMargin.left + ',' + innerMargin.top + ')')
                .call(lines2);

            quotaWrap
                .attr('transform', 'translate(' + innerMargin.left + ',' + innerMargin.top + ')');

            xAxisWrap
                .attr('transform', 'translate(' + innerMargin.left + ',' + (xAxis.orient() === 'bottom' ? innerHeight + innerMargin.top : innerMargin.top) + ')')
                .call(xAxis);

            yAxis
                .ticks(innerHeight / 100)
                .tickSize(-innerWidth, 0);

            yAxisWrap
                .attr('transform', 'translate(' + (yAxis.orient() === 'left' ? innerMargin.left : innerMargin.left + innerWidth) + ',' + innerMargin.top + ')')
                .call(yAxis);

            //------------------------------------------------------------
            // Quota Line

            quotaWrap.selectAll('line').remove();
            yAxisWrap.selectAll('text.nv-quotaValue').remove();
            yAxisWrap.selectAll('text.nv-targetQuotaValue').remove();
            var tickTextHeight = 14;

            // Target Quota Line
            if (targetQuotaValue > 0) {

                quotaWrap.append('line')
                    .attr('class', 'nv-quotaLineTarget')
                    .attr('x1', 0)
                    .attr('y1', 0)
                    .attr('x2', innerWidth)
                    .attr('y2', 0)
                    .attr('transform', 'translate(0,' + y(targetQuotaValue) + ')')
                    .style('stroke-dasharray', '8, 8');

                quotaWrap.append('line')
                    .datum({key: targetQuotaLabel, val: targetQuotaValue})
                    .attr('class', 'nv-quotaLineTarget nv-quotaLineBackground')
                    .attr('x1', 0)
                    .attr('y1', 0)
                    .attr('x2', innerWidth)
                    .attr('y2', 0)
                    .attr('transform', 'translate(0,' + y(targetQuotaValue) + ')');

                // Target Quota line label
                yAxisWrap.append('text')
                    .text(chart.quotaTickFormat()(targetQuotaValue))
                    .attr('class', 'nv-targetQuotaValue')
                    .attr('dy', '.36em')
                    .attr('dx', '0')
                    .attr('text-anchor', direction === 'rtl' ? 'start' : 'end')
                    .attr('transform', 'translate(' + -yAxis.tickPadding() + ',' + y(targetQuotaValue) + ')');

                tickTextHeight = Math.round(parseInt(g.select('text.nv-targetQuotaValue').node().getBoundingClientRect().height, 10) / 1.15);
                //check if tick lines overlap quota values, if so, hide the values that overlap
                yAxisWrap.selectAll('g.tick')
                    .each(function(d, i) {
                        if (y(d) <= y(targetQuotaValue) + tickTextHeight && y(d) >= y(targetQuotaValue) - tickTextHeight) {
                            d3.select(this).select('text').style('opacity', 0);
                            d3.select(this).select('line').style('opacity', 0);
                        }
                    });
                if (yAxis.showMaxMin) {
                    yAxisWrap.selectAll('g.nv-axisMaxMin')
                        .each(function(d, i) {
                            if (Math.abs(y(d) - y(targetQuotaValue)) <= tickTextHeight) {
                                d3.select(this).select('text').style('opacity', 0);
                            }
                        });
                }
            }


            if (quotaValue > 0) {

                quotaWrap.append('line')
                    .attr('class', 'nv-quotaLine')
                    .attr('x1', 0)
                    .attr('y1', 0)
                    .attr('x2', innerWidth)
                    .attr('y2', 0)
                    .attr('transform', 'translate(0,' + y(quotaValue) + ')')
                    .style('stroke-dasharray', '8, 8');

                quotaWrap.append('line')
                    .datum({key: quotaLabel, val: quotaValue})
                    .attr('class', 'nv-quotaLine nv-quotaLineBackground')
                    .attr('x1', 0)
                    .attr('y1', 0)
                    .attr('x2', innerWidth)
                    .attr('y2', 0)
                    .attr('transform', 'translate(0,' + y(quotaValue) + ')');

                // Quota line label
                yAxisWrap.append('text')
                    .text(chart.quotaTickFormat()(quotaValue))
                    .attr('class', 'nv-quotaValue')
                    .attr('dy', '.36em')
                    .attr('dx', '0')
                    .attr('text-anchor', direction === 'rtl' ? 'start' : 'end')
                    .attr('transform', 'translate(' + -yAxis.tickPadding() + ',' + y(quotaValue) + ')');

                tickTextHeight = Math.round(parseInt(g.select('text.nv-quotaValue').node().getBoundingClientRect().height, 10) / 1.15);
                //check if tick lines overlap quota values, if so, hide the values that overlap
                yAxisWrap.selectAll('g.tick')
                    .each(function(d, i) {
                        if (y(d) <= y(quotaValue) + tickTextHeight && y(d) >= y(quotaValue) - tickTextHeight) {
                            d3.select(this).select('line').style('opacity', 0);
                            d3.select(this).select('text').style('opacity', 0);
                        }
                    });


                if (yAxis.showMaxMin) {
                    yAxisWrap.selectAll('g.nv-axisMaxMin')
                        .each(function(d, i) {
                            if (Math.abs(y(d) - y(quotaValue)) <= tickTextHeight) {
                                d3.select(this).select('text').style('opacity', 0);
                            }
                        });
                }

                // if there is a quota and an adjusted quota
                // check to see if the adjusted collides
                if (targetQuotaValue > 0) {
                    if (Math.abs(y(quotaValue) - y(targetQuotaValue)) <= tickTextHeight) {
                        yAxisWrap.select('.nv-targetQuotaValue').style('opacity', 0);
                    }
                }

            }

            quotaWrap.selectAll('line.nv-quotaLineBackground')
                .on('mouseover', function(d) {
                    if (tooltips) {
                        var eo = {
                            pos: [d3.event.offsetX, d3.event.offsetY],
                            val: d.val,
                            key: d.key
                        };
                        showQuotaTooltip(eo, that.parentNode);
                    }
                })
                .on('mouseout', function() {
                    dispatch.tooltipHide();
                })
                .on('mousemove', function() {
                    dispatch.tooltipMove({
                        pos: [d3.event.offsetX, d3.event.offsetY]
                    });
                });

            //============================================================
            // Event Handling/Dispatching (in chart's scope)
            //------------------------------------------------------------

            barLegend.dispatch.on('legendClick', function(d, i) {
                var selectedSeries = d.series;
                //swap bar disabled
                d.disabled = !d.disabled;
                //swap line disabled for same series
                if (!chart.stacked()) {
                    data.filter(function(d) {
                        return d.series === selectedSeries && d.type === 'line';
                    }).map(function(d) {
                            d.disabled = !d.disabled;
                            return d;
                        });
                }
                // if there are no enabled data series, enable them all
                if (!data.filter(function(d) {
                    return !d.disabled && d.type === 'bar';
                }).length) {
                    data.map(function(d) {
                        d.disabled = false;
                        g.selectAll('.nv-series').classed('disabled', false);
                        return d;
                    });
                }
                container.call(chart);
            });

            dispatch.on('tooltipShow', function(eo) {
                if (tooltips) {
                    showTooltip(eo, that.parentNode, dataGroup);
                }
            });

            dispatch.on('tooltipMove', function(eo) {
                if (tooltip) {
                    nv.tooltip.position(that.parentNode, tooltip, eo.pos, 's');
                }
            });

            dispatch.on('tooltipHide', function() {
                if (tooltips) {
                    nv.tooltip.cleanup();
                }
            });

            dispatch.on('chartClick', function() {
                if (barLegend.enabled()) {
                    barLegend.dispatch.closeMenu();
                }
                if (lineLegend.enabled()) {
                    lineLegend.dispatch.closeMenu();
                }
            });

            multibar.dispatch.on('elementClick', function(eo) {
                dispatch.chartClick();
                barClick(data, eo, chart, container);
            });

        });

        return chart;
    }

    //============================================================
    // Event Handling/Dispatching (out of chart's scope)
    //------------------------------------------------------------

    lines2.dispatch.on('elementMouseover.tooltip', function(eo) {
        dispatch.tooltipShow(eo);
    });

    lines2.dispatch.on('elementMousemove', function(eo) {
        dispatch.tooltipMove(eo);
    });

    lines2.dispatch.on('elementMouseout.tooltip', function() {
        dispatch.tooltipHide();
    });

    multibar.dispatch.on('elementMouseover.tooltip', function(eo) {
        dispatch.tooltipShow(eo);
    });

    multibar.dispatch.on('elementMousemove', function(eo) {
        dispatch.tooltipMove(eo);
    });

    multibar.dispatch.on('elementMouseout.tooltip', function() {
        dispatch.tooltipHide();
    });


    //============================================================
    // Expose Public Variables
    //------------------------------------------------------------

    // expose chart's sub-components
    chart.dispatch = dispatch;
    chart.lines1 = lines1;
    chart.lines2 = lines2;
    chart.multibar = multibar;
    chart.barLegend = barLegend;
    chart.lineLegend = lineLegend;
    chart.xAxis = xAxis;
    chart.yAxis = yAxis;

    d3.rebind(chart, multibar, 'id', 'x', 'y', 'xScale', 'yScale', 'xDomain', 'yDomain', 'forceX', 'forceY', 'clipEdge', 'color', 'fill', 'classes', 'gradient');
    d3.rebind(chart, multibar, 'stacked', 'showValues', 'valueFormat', 'nice');
    d3.rebind(chart, xAxis, 'rotateTicks', 'reduceXTicks', 'staggerTicks', 'wrapTicks');

    chart.colorData = function(_) {
        var type = arguments[0],
            params = arguments[1] || {};
        var barColor = function(d, i) {
            return nv.utils.defaultColor()(d, d.series);
        };
        var barClasses = function(d, i) {
            return 'nv-group nv-series-' + d.series;
        };
        var lineColor = function(d, i) {
            var p = params.lineColor ? params.lineColor : {
                c1: '#1A8221',
                c2: '#62B464',
                l: 1
            };
            return d.color || d3.interpolateHsl(d3.rgb(p.c1), d3.rgb(p.c2))(d.series / 1);
        };
        var lineClasses = function(d, i) {
            return 'nv-group nv-series-' + d.series;
        };

        switch (type) {
            case 'graduated':
                barColor = function(d, i) {
                    return d3.interpolateHsl(d3.rgb(params.barColor.c1), d3.rgb(params.barColor.c2))(d.series / params.barColor.l);
                };
                break;
            case 'class':
                barColor = function() {
                    return 'inherit';
                };
                barClasses = function(d, i) {
                    var iClass = (d.series * (params.step || 1)) % 14;
                    iClass = (iClass > 9 ? '' : '0') + iClass;
                    return 'nv-group nv-series-' + d.series + ' nv-fill' + iClass;
                };
                lineClasses = function(d, i) {
                    var iClass = (d.series * (params.step || 1)) % 14;
                    iClass = (iClass > 9 ? '' : '0') + iClass;
                    return 'nv-group nv-series-' + d.series + ' nv-fill' + iClass + ' nv-stroke' + iClass;
                };
                break;
            case 'data':
                barColor = function(d, i) {
                    return d.classes ? 'inherit' : d.color || nv.utils.defaultColor()(d, d.series);
                };
                barClasses = function(d, i) {
                    return 'nv-group nv-series-' + d.series + (d.classes ? ' ' + d.classes : '');
                };
                lineClasses = function(d, i) {
                    return 'nv-group nv-series-' + d.series + (d.classes ? ' ' + d.classes : '');
                };
                break;
        }

        var barFill = (!params.gradient) ? barColor : function(d, i) {
            var p = {orientation: params.orientation || 'vertical', position: params.position || 'middle'};
            return multibar.gradient(d, d.series, p);
        };

        multibar.color(barColor);
        multibar.fill(barFill);
        multibar.classes(barClasses);

        lines2.color(lineColor);
        lines2.fill(lineColor);
        lines2.classes(lineClasses);

        barLegend.color(barColor);
        barLegend.classes(barClasses);

        lineLegend.color(lineColor);
        lineLegend.classes(lineClasses);

        return chart;
    };

    chart.x = function(_) {
        if (!arguments.length) {
            return getX;
        }
        getX = _;
        lines.x(_);
        multibar.x(_);
        return chart;
    };

    chart.y = function(_) {
        if (!arguments.length) {
            return getY;
        }
        getY = _;
        lines.y(_);
        multibar.y(_);
        return chart;
    };

    chart.margin = function(_) {
        if (!arguments.length) {
            return margin;
        }
        for (var prop in _) {
            if (_.hasOwnProperty(prop)) {
                margin[prop] = _[prop];
            }
        }
        return chart;
    };

    chart.width = function(_) {
        if (!arguments.length) {
            return width;
        }
        width = _;
        return chart;
    };

    chart.height = function(_) {
        if (!arguments.length) {
            return height;
        }
        height = _;
        return chart;
    };

    chart.showTitle = function(_) {
        if (!arguments.length) {
            return showTitle;
        }
        showTitle = _;
        return chart;
    };

    chart.showLegend = function(_) {
        if (!arguments.length) {
            return showLegend;
        }
        showLegend = _;
        return chart;
    };

    chart.showControls = function(_) {
        if (!arguments.length) {
            return false;
        }
        return chart;
    };

    chart.tooltipBar = function(_) {
        if (!arguments.length) {
            return tooltipBar;
        }
        tooltipBar = _;
        return chart;
    };

    chart.tooltipLine = function(_) {
        if (!arguments.length) {
            return tooltipLine;
        }
        tooltipLine = _;
        return chart;
    };

    chart.tooltipQuota = function(_) {
        if (!arguments.length) {
            return tooltipQuota;
        }
        tooltipQuota = _;
        return chart;
    };

    chart.tooltip = function(_) {
        if (!arguments.length) {
            return tooltip;
        }
        tooltip = _;
        return chart;
    };

    chart.tooltips = function(_) {
        if (!arguments.length) {
            return tooltips;
        }
        tooltips = _;
        return chart;
    };

    chart.tooltipContent = function(_) {
        if (!arguments.length) {
            return tooltipContent;
        }
        tooltipContent = _;
        return chart;
    };

    chart.barClick = function(_) {
        if (!arguments.length) {
            return barClick;
        }
        barClick = _;
        return chart;
    };

    chart.colorFill = function(_) {
        return chart;
    };

    chart.yAxisTickFormat = function(_) {
        if (!arguments.length) {
            return yAxisTickFormat;
        }
        yAxisTickFormat = _;
        return chart;
    };

    chart.quotaTickFormat = function(_) {
        if (!arguments.length) {
            return quotaTickFormat;
        }
        quotaTickFormat = _;
        return chart;
    };

    chart.strings = function(_) {
        if (!arguments.length) {
            return strings;
        }
        for (var prop in _) {
            if (_.hasOwnProperty(prop)) {
                strings[prop] = _[prop];
            }
        }
        return chart;
    };

    chart.direction = function(_) {
        if (!arguments.length) {
            return direction;
        }
        direction = _;
        multibar.direction(_);
        yAxis.direction(_);
        barLegend.direction(_);
        lineLegend.direction(_);
        return chart;
    };

    //============================================================

    return chart;
};
nv.models.pie = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 0, right: 0, bottom: 0, left: 0},
      width = 500,
      height = 500,
      getValues = function(d) { return d; },
      getX = function(d) { return d.key; },
      getY = function(d) { return d.value; },
      getDescription = function(d) { return d.description; },
      id = Math.floor(Math.random() * 10000), //Create semi-unique ID in case user doesn't select one
      valueFormat = d3.format(',.2f'),
      showLabels = true,
      showLeaders = true,
      pieLabelsOutside = true,
      donutLabelsOutside = true,
      labelThreshold = 0.01, //if slice percentage is under this, don't show label
      donut = false,
      hole = false,
      holeFormat = function(holeWrap, data) {
        var wrap = holeWrap.selectAll('.nv-hole-container').data(data),
            wrapEnter = wrap.enter().append('g').attr('class', 'nv-hole-container');
        wrapEnter.append('text')
          .text(data)
          .attr('class', 'nv-pie-hole-value')
          .attr('dy', '.35em')
          .attr('text-anchor', 'middle')
          .style('font-size', '50px');
        wrap.exit().remove();
      },
      labelSunbeamLayout = false,
      leaderLength = 20,
      textOffset = 5,
      arcDegrees = 360,
      rotateDegrees = 0,
      startAngle = function(d) {
        // DNR (Math): simplify d.startAngle - ((rotateDegrees * Math.PI / 180) * (360 / arcDegrees)) * (arcDegrees / 360);
        return d.startAngle * arcDegrees / 360 - rotateDegrees * Math.PI / 180;
      },
      endAngle = function(d) {
        return d.endAngle * arcDegrees / 360 - rotateDegrees * Math.PI / 180;
      },
      donutRatio = 0.447,
      minRadius = 75,
      maxRadius = 250,
      fixedRadius = function(chart) { return null; },
      durationMs = 0,
      direction = 'ltr',
      color = function(d, i) { return nv.utils.defaultColor()(d, d.series); },
      fill = color,
      classes = function(d, i) { return 'nv-slice nv-series-' + d.series; },
      dispatch = d3.dispatch('chartClick', 'elementClick', 'elementDblClick', 'elementMouseover', 'elementMouseout', 'elementMousemove');

  //============================================================


  function chart(selection) {
    selection.each(function(data) {

      var availableWidth = width - margin.left - margin.right,
          availableHeight = height - margin.top - margin.bottom,
          container = d3.select(this);

      // Setup the Pie chart and choose the data element
      var pie = d3.layout.pie()
            .sort(null)
            .value(function(d) { return d.disabled ? 0 : getY(d); });

      //------------------------------------------------------------
      // recalculate width and height based on label length
      var labelLengths = [],
          doLabels = showLabels && pieLabelsOutside ? true : false;
      if (doLabels) {
        labelLengths = nv.utils.stringSetLengths(
            data.map(function(d) { return d.key; }),
            container,
            function(d) { return d; }
          );
      }

      //------------------------------------------------------------
      // Setup containers and skeleton of chart
      var wrap = container.selectAll('.nv-wrap.nv-pie').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-pie nv-chart-' + id);
      var defsEnter = wrapEnter.append('defs');
      var gEnter = wrapEnter.append('g');
      var g = wrap.select('g');

      //set up the gradient constructor function
      chart.gradient = function(d, i) {
        var params = {x: 0, y: 0, r: pieRadius, s: (donut ? (donutRatio * 100) + '%' : '0%'), u: 'userSpaceOnUse'};
        return nv.utils.colorRadialGradient(d, id + '-' + i, params, color(d, i), wrap.select('defs'));
      };

      gEnter.append('g').attr('class', 'nv-pie');
      var pieWrap = g.select('.nv-pie');
      gEnter.append('g').attr('class', 'nv-holeWrap');
      var holeWrap = g.select('.nv-holeWrap');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');
      pieWrap.attr('transform', 'translate(' + (availableWidth / 2) + ',' + (availableHeight / 2) + ')');

      //------------------------------------------------------------

      container
        .on('click', function(d, i) {
          dispatch.chartClick({
            data: d,
            index: i,
            pos: d3.event,
            id: id
          });
        });

      var slices = wrap.select('.nv-pie').selectAll('.nv-slice')
            .data(pie);

      slices.exit().remove();

      var ae = slices.enter().append('g')
            .on('mouseover', function(d, i) {
              d3.select(this).classed('hover', true);
              var eo = buildEventObject(d3.event, d, i);
              dispatch.elementMouseover(eo);
            })
            .on('mousemove', function(d, i) {
              var eo = buildEventObject(d3.event, d, i);
              dispatch.elementMousemove(eo);
            })
            .on('mouseout', function(d, i) {
              d3.select(this).classed('hover', false);
              dispatch.elementMouseout();
            })
            .on('click', function(d, i) {
              d3.event.stopPropagation();
              var eo = buildEventObject(d3.event, d, i);
              dispatch.elementClick(eo);
            })
            .on('dblclick', function(d, i) {
              d3.event.stopPropagation();
              var eo = buildEventObject(d3.event, d, i);
              dispatch.elementDblClick(eo);
            });

          ae.append('path')
              .style('stroke', '#ffffff')
              .style('stroke-width', 2)
              .style('stroke-opacity', 0)
              .each(function(d, i) {
                this._current = d;
              });

          ae.append('g')
              .attr('transform', 'translate(0,0)')
              .attr('class', 'nv-label');

          ae.select('.nv-label')
              .append('rect')
              .style('fill-opacity', 0)
              .style('stroke-opacity', 0);
          ae.select('.nv-label')
              .append('text')
              .style('fill-opacity', 0);

          ae.append('polyline')
              .attr('class', 'nv-label-leader')
              .style('stroke-opacity', 0);


      // UPDATE
      //------------------------------------------------------------

      var maxWidthRadius = availableWidth / 2,
          maxHeightRadius = availableHeight / 2,
          extWidths = [],
          extHeights = [],
          verticalShift = 0,
          verticalReduction = doLabels ? 5 : 0,
          horizontalShift = 0,
          horizontalReduction = leaderLength + textOffset,
          verticalDifferential = 0,
          horizontalDifferential = 0;

      slices.select('path').call(calcScalars, maxWidthRadius, maxHeightRadius);

      // Donut Hole Text
      holeWrap.call(holeFormat, hole ? [hole] : []);

      if (hole) {
        var heightHoleHalf = holeWrap.node().getBBox().height * 0.30,
            heightPieHalf = Math.abs(maxHeightRadius * d3.min(extHeights)),
            holeOffset = Math.round(heightHoleHalf - heightPieHalf);

        if (holeOffset > 0) {
          verticalReduction += holeOffset;
          verticalShift -= holeOffset / 2;
        }
      }

      var offsetHorizontal = availableWidth / 2,
          offsetVertical = availableHeight / 2;

      //first adjust the leaderLength to be proportional to radius
      if (doLabels) {
        leaderLength = Math.max(Math.min(Math.min(calcMaxRadius()) / 12, 20), 10);
      }

      if (fixedRadius(chart)) {
        minRadius = fixedRadius(chart);
        maxRadius = fixedRadius(chart);
      }

      var labelRadius = Math.min(Math.max(calcMaxRadius(), minRadius), maxRadius),
          pieRadius = labelRadius - (doLabels ? leaderLength : 0);

      offsetVertical += ((d3.max(extHeights) - d3.min(extHeights)) / 2 + d3.min(extHeights)) * ((labelRadius + verticalShift) / offsetVertical);
      offsetHorizontal += ((d3.max(extWidths) - d3.min(extWidths)) / 2 - d3.max(extWidths)) * (labelRadius / offsetHorizontal);

      offsetVertical += verticalShift / 2;

      pieWrap
        .attr('transform', 'translate(' + offsetHorizontal + ',' + offsetVertical + ')');
      holeWrap
        .attr('transform', 'translate(' + offsetHorizontal + ',' + offsetVertical + ')');

      var pieArc = d3.svg.arc()
            .innerRadius(donut ? pieRadius * donutRatio : 0)
            .outerRadius(pieRadius)
            .startAngle(startAngle)
            .endAngle(endAngle);

      var labelArc = d3.svg.arc()
            .innerRadius(0)
            .outerRadius(pieRadius)
            .startAngle(startAngle)
            .endAngle(endAngle);

      if (pieLabelsOutside) {
        if (!donut || donutLabelsOutside) {
          labelArc
            .innerRadius(labelRadius)
            .outerRadius(labelRadius);
        } else {
          labelArc
            .outerRadius(pieRadius * donutRatio);
        }
      }

      slices
        .classed('nv-active', function(d) { return d.data.active === 'active'; })
        .classed('nv-inactive', function(d) { return d.data.active === 'inactive'; })
        .attr('class', function(d) { return classes(d.data, d.data.series); })
        .attr('fill', function(d) { return fill(d.data, d.data.series); });

      // removed d3 transition in MACAROON-133 because
      // there is a "Maximum call stack size exceeded at Date.toString" error
      // in PMSE that stops d3 from calling transitions
      // this may be a logger issue or some recursion somewhere in PMSE
      // slices.select('path').transition().duration(durationMs)
      //   .attr('d', arc)
      //   .attrTween('d', arcTween);

      slices.select('path')
        .attr('d', pieArc)
        .style('stroke-opacity', function(d) {
          return startAngle(d) === endAngle(d) ? 0 : 1;
        });

      if (showLabels) {
        // This does the normal label
        slices.select('.nv-label')
          .attr('transform', function(d) {
            if (labelSunbeamLayout) {
              d.outerRadius = pieRadius + 10; // Set Outer Coordinate
              d.innerRadius = pieRadius + 15; // Set Inner Coordinate
              var rotateAngle = (startAngle(d) + endAngle(d)) / 2 * (180 / Math.PI);
              rotateAngle += 90 * alignedRight(d, labelArc);
              return 'translate(' + labelArc.centroid(d) + ') rotate(' + rotateAngle + ')';
            } else {
              var labelsPosition = labelArc.centroid(d),
                  leadOffset = showLeaders ? (leaderLength + textOffset) * alignedRight(d, labelArc) : 0;
              return 'translate(' + [labelsPosition[0] + leadOffset, labelsPosition[1]] + ')';
            }
          });

        slices.select('.nv-label text')
          .text(function(d) {
            return labelOpacity(d) ? getX(d.data) : '';
          })
          .attr('dy', '.35em')
          .style('fill', '#555')
          .style('fill-opacity', labelOpacity)
          .style('text-anchor', function(d) {
            //center the text on it's origin or begin/end if orthogonal aligned
            //labelSunbeamLayout ? ((d.startAngle + d.endAngle) / 2 < Math.PI ? 'start' : 'end') : 'middle'
            if (!pieLabelsOutside) {
              return 'middle';
            }
            var anchor = alignedRight(d, labelArc) === 1 ? 'start' : 'end';
            if (direction === 'rtl') {
              anchor === 'start' ? 'end' : 'start';
            }
            return anchor;
          });

        slices
          .each(function(d, i) {
            if (labelLengths[i] > minRadius || labelRadius === minRadius) {
              var theta = (startAngle(d) + endAngle(d)) / 2,
                  sin = Math.abs(Math.sin(theta)),
                  bW = labelRadius * sin + leaderLength + textOffset + labelLengths[i],
                  rW = (availableWidth / 2 - offsetHorizontal) + availableWidth / 2 - bW;

              if (rW < 0) {
                var label = nv.utils.stringEllipsify(d.data.key, container, labelLengths[i] + rW);
                d3.select(this).select('text').text(label);
              }
            }
          });

        if (!pieLabelsOutside) {
          slices.select('.nv-label')
            .each(function(d) {
              if (!labelOpacity(d)) {
                return;
              }
              var slice = d3.select(this),
                  textBox = slice.select('text').node().getBBox();
              slice.select('rect')
                .attr('rx', 3)
                .attr('ry', 3)
                .attr('width', textBox.width + 10)
                .attr('height', textBox.height + 10)
                .attr('transform', function() {
                  return 'translate(' + [textBox.x - 5, textBox.y - 5] + ')';
                })
                .style('fill', '#fff')
                .style('fill-opacity', labelOpacity);
            });
        } else if (showLeaders) {
          slices.select('.nv-label-leader')
            .attr('points', function(d) {
              if (!labelOpacity(d)) {
                return '0,0';
              }
              var outerArc = d3.svg.arc()
                    .innerRadius(pieRadius)
                    .outerRadius(pieRadius)
                    .startAngle(startAngle)
                    .endAngle(endAngle);
              var leadOffset = showLeaders ? leaderLength * alignedRight(d, outerArc) : 0,
                  outerArcPoints = outerArc.centroid(d),
                  labelArcPoints = labelArc.centroid(d),
                  leadArcPoints = [labelArcPoints[0] + leadOffset, labelArcPoints[1]];
              return outerArcPoints + ' ' + labelArcPoints + ' ' + leadArcPoints;
            })
            .style('stroke', '#aaa')
            .style('fill', 'none')
            .style('stroke-opacity', labelOpacity);
        }
      } else {
        slices.select('.nv-label-leader').style('stroke-opacity', 0);
        slices.select('.nv-label rect').style('fill-opacity', 0);
        slices.select('.nv-label text').style('fill-opacity', 0);
      }

      // Utility Methods
      //------------------------------------------------------------

      function buildEventObject(e, d, i) {
        return {
            label: getX(d.data),
            value: getY(d.data),
            point: d.data,
            pointIndex: i,
            pos: [e.offsetX, e.offsetY],
            id: id,
            e: e
          };
      }

      // calculate max and min height of slice vertices
      function calcScalars(slices, maxWidth, maxHeight) {
        var widths = [],
            heights = [],
            Pi = Math.PI;

        slices.each(function(d, i) {
          var aStart = (startAngle(d) + 2 * Pi) % (2 * Pi),
              aEnd = (endAngle(d) + 2 * Pi) % (2 * Pi),

              wStart = Math.round(Math.sin(aStart) * 10000) / 10000,
              wEnd = Math.round(Math.sin(aEnd) * 10000) / 10000,
              hStart = Math.round(Math.cos(aStart) * 10000) / 10000,
              hEnd = Math.round(Math.cos(aEnd) * 10000) / 10000;

          // North
          if (startAngle(d) <= 0 && endAngle(d) >= 0) {
            heights.push(maxHeight);
            if (donut) {
              heights.push(maxHeight * donutRatio);
            }
          }
          // East
          if (aStart <= Pi / 2 && (aEnd >= Pi / 2 || aEnd === 0)) {
            widths.push(maxWidth);
            if (donut) {
              widths.push(maxWidth * donutRatio);
            }
          }
          // South
          if (aStart <= Pi && (aEnd >= Pi || aEnd === 0)) {
            heights.push(-maxHeight);
            if (donut) {
              heights.push(-maxHeight * donutRatio);
            }
          }
          // West
          if (aStart <= 3 * Pi / 2 && (aEnd >= 3 * Pi / 2 || aEnd === 0)) {
            widths.push(-maxWidth);
            if (donut) {
              widths.push(-maxWidth * donutRatio);
            }
          }

          widths.push(maxWidth * wStart);
          widths.push(maxWidth * wEnd);
          if (donut) {
            widths.push(maxWidth * donutRatio * wStart);
            widths.push(maxWidth * donutRatio * wEnd);
          } else {
            widths.push(0);
          }

          heights.push(maxHeight * hStart);
          heights.push(maxHeight * hEnd);
          if (donut) {
            heights.push(maxHeight * donutRatio * hStart);
            heights.push(maxHeight * donutRatio * hEnd);
          } else {
            heights.push(0);
          }
        });

        extWidths = d3.extent(widths);
        extHeights = d3.extent(heights);

        // scale up height radius to fill extents
        maxWidthRadius *= availableWidth / (d3.max(extWidths) - d3.min(extWidths));
        maxHeightRadius *= availableHeight / (d3.max(extHeights) - d3.min(extHeights));
      }

      // reduce width radius for width of labels
      function calcMaxRadius() {
        var widthRadius = [maxWidthRadius],
            heightRadius = [maxHeightRadius + leaderLength];

        slices.select('path').each(function(d, i) {
          if (!labelOpacity(d)) {
            return;
          }

          var theta = (startAngle(d) + endAngle(d)) / 2,
              sin = d3.round(Math.sin(theta), 5),
              cos = d3.round(Math.cos(theta), 5),
              bW = maxWidthRadius - horizontalReduction - labelLengths[i],
              bH = maxHeightRadius - verticalReduction,
              rW = sin ? bW / sin : bW, //don't divide by zero, fool
              rH = cos ? bH / cos : bH;

          widthRadius.push(rW);
          heightRadius.push(rH);
        });

        verticalDifferential = d3.max(heightRadius) + d3.min(heightRadius);
        horizontalDifferential = d3.max(widthRadius) + d3.min(widthRadius);

        var radius = d3.min(widthRadius.concat(heightRadius).concat([]), function(d) { return Math.abs(d); });

        return radius;
      }

      function labelOpacity(d) {
        var percent = (endAngle(d) - startAngle(d)) / (2 * Math.PI);
        return percent > labelThreshold ? 1 : 0;
      }

      function alignedRight(d, arc) {
        var circ = Math.PI * 2,
            midArc = ((startAngle(d) + endAngle(d)) / 2 + circ) % circ;
        return midArc > 0 && midArc < Math.PI ? 1 : -1;
      }

      function arcTween(d) {
        if (!donut) {
          d.innerRadius = 0;
        }
        var i = d3.interpolate(this._current, d);
        this._current = i(0);

        return function(t) {
          var iData = i(t);
          return pieArc(iData);
        };
      }

      function tweenPie(b) {
        b.innerRadius = 0;
        var i = d3.interpolate({startAngle: 0, endAngle: 0}, b);
        return function(t) {
          return pieArc(i(t));
        };
      }

    });

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  chart.dispatch = dispatch;

  chart.color = function(_) {
    if (!arguments.length) {
      return color;
    }
    color = _;
    return chart;
  };
  chart.fill = function(_) {
    if (!arguments.length) {
      return fill;
    }
    fill = _;
    return chart;
  };
  chart.classes = function(_) {
    if (!arguments.length) {
      return classes;
    }
    classes = _;
    return chart;
  };
  chart.gradient = function(_) {
    if (!arguments.length) {
      return gradient;
    }
    gradient = _;
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) {
      return margin;
    }
    margin.top    = typeof _.top    != 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  != 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom != 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   != 'undefined' ? _.left   : margin.left;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) {
      return width;
    }
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) {
      return height;
    }
    height = _;
    return chart;
  };

  chart.values = function(_) {
    if (!arguments.length) {
      return getValues;
    }
    getValues = _;
    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) {
      return getX;
    }
    getX = _;
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) {
      return getY;
    }
    getY = d3.functor(_);
    return chart;
  };

  chart.description = function(_) {
    if (!arguments.length) {
      return getDescription;
    }
    getDescription = _;
    return chart;
  };

  chart.showLabels = function(_) {
    if (!arguments.length) {
      return showLabels;
    }
    showLabels = _;
    return chart;
  };

  chart.labelSunbeamLayout = function(_) {
    if (!arguments.length) {
      return labelSunbeamLayout;
    }
    labelSunbeamLayout = _;
    return chart;
  };

  chart.donutLabelsOutside = function(_) {
    if (!arguments.length) {
      return donutLabelsOutside;
    }
    donutLabelsOutside = _;
    return chart;
  };

  chart.pieLabelsOutside = function(_) {
    if (!arguments.length) {
      return pieLabelsOutside;
    }
    pieLabelsOutside = _;
    return chart;
  };

  chart.showLeaders = function(_) {
    if (!arguments.length) {
      return showLeaders;
    }
    showLeaders = _;
    return chart;
  };

  chart.donut = function(_) {
    if (!arguments.length) {
      return donut;
    }
    donut = _;
    return chart;
  };

  chart.hole = function(_) {
    if (!arguments.length) {
      return hole;
    }
    hole = _;
    return chart;
  };

  chart.holeFormat = function(_) {
    if (!arguments.length) {
      return holeFormat;
    }
    holeFormat = d3.functor(_);
    return chart;
  };

  chart.donutRatio = function(_) {
    if (!arguments.length) {
      return donutRatio;
    }
    donutRatio = _;
    return chart;
  };

  chart.startAngle = function(_) {
    if (!arguments.length) {
      return startAngle;
    }
    startAngle = _;
    return chart;
  };

  chart.endAngle = function(_) {
    if (!arguments.length) {
      return endAngle;
    }
    endAngle = _;
    return chart;
  };

  chart.id = function(_) {
    if (!arguments.length) {
      return id;
    }
    id = _;
    return chart;
  };

  chart.valueFormat = function(_) {
    if (!arguments.length) {
      return valueFormat;
    }
    valueFormat = _;
    return chart;
  };

  chart.labelThreshold = function(_) {
    if (!arguments.length) {
      return labelThreshold;
    }
    labelThreshold = _;
    return chart;
  };

  chart.direction = function(_) {
    if (!arguments.length) {
      return direction;
    }
    direction = _;
    return chart;
  };

  chart.arcDegrees = function(_) {
    if (!arguments.length) {
      return arcDegrees;
    }
    arcDegrees = Math.max(Math.min(_, 360), 1);
    return chart;
  };

  chart.rotateDegrees = function(_) {
    if (!arguments.length) {
      return rotateDegrees;
    }
    rotateDegrees = _ % 360;
    return chart;
  };

  chart.minRadius = function(_) {
    if (!arguments.length) {
      return minRadius;
    }
    minRadius = _;
    return chart;
  };

  chart.maxRadius = function(_) {
    if (!arguments.length) {
      return maxRadius;
    }
    maxRadius = _;
    return chart;
  };

  chart.fixedRadius = function(_) {
    if (!arguments.length) {
      return fixedRadius;
    }
    fixedRadius = d3.functor(_);
    return chart;
  };

  //============================================================

  return chart;
}
nv.models.pieChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 10, right: 10, bottom: 10, left: 10},
      width = null,
      height = null,
      showTitle = false,
      showLegend = true,
      direction = 'ltr',
      tooltip = null,
      durationMs = 0,
      tooltips = true,
      tooltipContent = function(key, x, y, e, graph) {
        return '<h3>' + key + ' - ' + x + '</h3>' +
               '<p>' + y + '</p>';
      },
      state = {},
      strings = {
        legend: {close: 'Hide legend', open: 'Show legend'},
        controls: {close: 'Hide controls', open: 'Show controls'},
        noData: 'No Data Available.'
      },
      dispatch = d3.dispatch('chartClick', 'elementClick', 'tooltipShow', 'tooltipHide', 'tooltipMove', 'stateChange', 'changeState');

  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var pie = nv.models.pie(),
      legend = nv.models.legend()
        .align('center');

  var showTooltip = function(e, offsetElement, total) {
    var left = e.pos[0],
        top = e.pos[1],
        x = (pie.y()(e.point) * 100 / total).toFixed(1),
        y = pie.valueFormat()(pie.y()(e.point)),
        content = tooltipContent(e.point.key, x, y, e, chart);

    tooltip = nv.tooltip.show([left, top], content, null, null, offsetElement);
  };

  var seriesClick = function(data, e, chart) {
    return;
  };

  //============================================================

  function chart(selection) {

    selection.each(function(chartData) {

      var properties = chartData.properties,
          data = chartData.data,
          container = d3.select(this),
          that = this,
          availableWidth = (width || parseInt(container.style('width'), 10) || 960) - margin.left - margin.right,
          availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom,
          total = d3.sum(data.map(function(d) { return d.value; })),
          innerWidth = availableWidth,
          innerHeight = availableHeight,
          innerMargin = {top: 0, right: 0, bottom: 0, left: 0};

      chart.update = function() {
        container.transition().duration(durationMs).call(chart);
      };

      chart.dataSeriesActivate = function(eo) {
        var series = eo.point;

        series.active = (!series.active || series.active === 'inactive') ? 'active' : 'inactive';

        // if you have activated a data series, inactivate the rest
        if (series.active === 'active') {
          data.filter(function(d) {
            return d.active !== 'active';
          }).map(function(d) {
            d.active = 'inactive';
            return d;
          });
        }

        // if there are no active data series, inactivate them all
        if (!data.filter(function(d) {
          return d.active === 'active';
        }).length) {
          data.map(function(d) {
            d.active = '';
            container.selectAll('.nv-series').classed('nv-inactive', false);
            return d;
          });
        }

        container.call(chart);
      };

      chart.container = this;

      //------------------------------------------------------------
      // Display No Data message if there's nothing to show.
      if (!data || !data.length) {
        displayNoData();
        return chart;
      }

      //------------------------------------------------------------
      // Process data
      //add series index to each data point for reference
      var pieData = data.map(function(d, i) {
            d.series = i;
            if (!d.value) {
              d.disabled = true;
            }
            return d;
          });

      var totalAmount = d3.sum(
            // only sum enabled series
            pieData
              .filter(function(d, i) {
                return !d.disabled;
              })
              .map(function(d, i) {
                return d.value;
              })
          );

      //set state.disabled
      state.disabled = pieData.map(function(d) { return !!d.disabled; });

      //------------------------------------------------------------
      // Display No Data message if there's nothing to show.

      if (!totalAmount) {
        displayNoData();
        return chart;
      } else {
        container.selectAll('.nv-noData').remove();
      }

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-pieChart').data([pieData]),
          gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-pieChart').append('g'),
          g = wrap.select('g').attr('class', 'nv-chartWrap');

      gEnter.append('rect').attr('class', 'nv-background')
        .attr('x', -margin.left)
        .attr('y', -margin.top)
        .attr('fill', '#FFF');

      g.select('.nv-background')
        .attr('width', availableWidth + margin.left + margin.right)
        .attr('height', availableHeight + margin.top + margin.bottom);

      gEnter.append('g').attr('class', 'nv-titleWrap');
      var titleWrap = g.select('.nv-titleWrap');
      gEnter.append('g').attr('class', 'nv-pieWrap');
      var pieWrap = g.select('.nv-pieWrap');
      gEnter.append('g').attr('class', 'nv-legendWrap');
      var legendWrap = g.select('.nv-legendWrap');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------
      // Title & Legend

      var titleBBox = {width: 0, height: 0};
      titleWrap.select('.nv-title').remove();

      if (showTitle && properties.title) {
        titleWrap
          .append('text')
            .attr('class', 'nv-title')
            .attr('x', direction === 'rtl' ? availableWidth : 0)
            .attr('y', 0)
            .attr('dy', '.75em')
            .attr('text-anchor', 'start')
            .text(properties.title)
            .attr('stroke', 'none')
            .attr('fill', 'black');

        titleBBox = nv.utils.getTextBBox(g.select('.nv-title'));

        innerMargin.top += titleBBox.height + 12;
      }

      if (showLegend) {
        legend
          .id('legend_' + chart.id())
          .strings(chart.strings().legend)
          .align('center')
          .height(availableHeight - innerMargin.top);
        legendWrap
          .datum(pieData)
          .call(legend);
        legend
          .arrange(availableWidth);

        var legendLinkBBox = nv.utils.getTextBBox(legendWrap.select('.nv-legend-link')),
            legendSpace = availableWidth - titleBBox.width - 6,
            legendTop = showTitle && legend.collapsed() && legendSpace > legendLinkBBox.width ? true : false,
            xpos = direction === 'rtl' || !legend.collapsed() ? 0 : availableWidth - legend.width(),
            ypos = titleBBox.height;
        if (legendTop) {
          ypos = titleBBox.height - legend.height() / 2 - legendLinkBBox.height / 2;
        } else if (!showTitle) {
          ypos = - legend.margin().top;
        }

        legendWrap
          .attr('transform', 'translate(' + xpos + ',' + ypos + ')');

        innerMargin.top += legendTop ? 0 : legend.height() - 12;
      }

      // Recalc inner margins
      innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;
      innerWidth = availableWidth - innerMargin.left - innerMargin.right;

      //------------------------------------------------------------
      // Main Chart Component(s)

      pie
        .width(innerWidth)
        .height(innerHeight);

      pieWrap
        .datum(pieData.filter(function(d) { return !d.disabled; }))
        .attr('transform', 'translate(' + innerMargin.left + ',' + innerMargin.top + ')')
        .transition().duration(durationMs)
          .call(pie);

      function displayNoData() {
        container.select('.nvd3.nv-wrap').remove();
        var noDataText = container.selectAll('.nv-noData').data([chart.strings().noData]);

        noDataText.enter().append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle');

        noDataText
          .attr('x', margin.left + availableWidth / 2)
          .attr('y', margin.top + availableHeight / 2)
          .text(function(d) {
            return d;
          });
      }

      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      legend.dispatch.on('legendClick', function(d, i) {
        d.disabled = !d.disabled;

        if (!pie.values()(pieData).filter(function(d) { return !d.disabled; }).length) {
          pie.values()(data).map(function(d) {
            d.disabled = false;
            wrap.selectAll('.nv-series').classed('disabled', false);
            return d;
          });
        }

        state.disabled = pieData.map(function(d) { return !!d.disabled; });
        dispatch.stateChange(state);

        container.transition().duration(durationMs).call(chart);
      });

      dispatch.on('tooltipShow', function(eo) {
        if (tooltips) {
          showTooltip(eo, that.parentNode, total);
        }
      });

      dispatch.on('tooltipMove', function(eo) {
        if (tooltip) {
          nv.tooltip.position(that.parentNode, tooltip, eo.pos);
        }
      });

      dispatch.on('tooltipHide', function() {
        if (tooltips) {
          nv.tooltip.cleanup();
        }
      });

      // Update chart from a state object passed to event handler
      dispatch.on('changeState', function(eo) {
        if (typeof eo.disabled !== 'undefined') {
          pieData.forEach(function(series, i) {
            series.disabled = eo.disabled[i];
          });
          state.disabled = eo.disabled;
        }

        container.transition().duration(durationMs).call(chart);
      });

      dispatch.on('chartClick', function() {
        if (legend.enabled()) {
          legend.dispatch.closeMenu();
        }
      });

      pie.dispatch.on('elementClick', function(eo) {
        dispatch.chartClick();
        seriesClick(data, eo, chart);
      });

    });

    return chart;
  }

  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  pie.dispatch.on('elementMouseover.tooltip', function(eo) {
    dispatch.tooltipShow(eo);
  });

  pie.dispatch.on('elementMousemove.tooltip', function(eo) {
    dispatch.tooltipMove(eo);
  });

  pie.dispatch.on('elementMouseout.tooltip', function() {
    dispatch.tooltipHide();
  });

  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.pie = pie;
  chart.legend = legend;

  d3.rebind(chart, pie, 'id', 'x', 'y', 'color', 'fill', 'classes', 'gradient');
  d3.rebind(chart, pie, 'valueFormat', 'values', 'description', 'showLabels', 'showLeaders', 'donutLabelsOutside', 'pieLabelsOutside', 'labelThreshold');
  d3.rebind(chart, pie, 'arcDegrees', 'rotateDegrees', 'minRadius', 'maxRadius', 'fixedRadius', 'startAngle', 'endAngle', 'donut', 'hole', 'holeFormat', 'donutRatio');

  chart.colorData = function(_) {
    var type = arguments[0],
        params = arguments[1] || {};
    var color = function(d, i) {
          return nv.utils.defaultColor()(d, d.series);
        };
    var classes = function(d, i) {
          return 'nv-slice nv-series-' + d.series;
        };

    switch (type) {
      case 'graduated':
        color = function(d, i) {
          return d3.interpolateHsl(d3.rgb(params.c1), d3.rgb(params.c2))(d.series / params.l);
        };
        break;
      case 'class':
        color = function() {
          return 'inherit';
        };
        classes = function(d, i) {
          var iClass = (d.series * (params.step || 1)) % 14;
          iClass = (iClass > 9 ? '' : '0') + iClass;
          return 'nv-slice nv-series-' + d.series + ' nv-fill' + iClass;
        };
        break;
      case 'data':
        color = function(d, i) {
          return d.classes ? 'inherit' : d.color || nv.utils.defaultColor()(d, d.series);
        };
        classes = function(d, i) {
          return 'nv-slice nv-series-' + d.series + (d.classes ? ' ' + d.classes : '');
        };
        break;
    }

    var fill = (!params.gradient) ? color : function(d, i) {
      return pie.gradient(d, d.series);
    };

    pie.color(color);
    pie.fill(fill);
    pie.classes(classes);

    legend.color(color);
    legend.classes(classes);

    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) {
      return margin;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        margin[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) {
      return width;
    }
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) {
      return height;
    }
    height = _;
    return chart;
  };

  chart.showTitle = function(_) {
    if (!arguments.length) {
      return showTitle;
    }
    showTitle = _;
    return chart;
  };

  chart.showLegend = function(_) {
    if (!arguments.length) {
      return showLegend;
    }
    showLegend = _;
    return chart;
  };

  chart.tooltip = function(_) {
    if (!arguments.length) {
      return tooltip;
    }
    tooltip = _;
    return chart;
  };

  chart.tooltips = function(_) {
    if (!arguments.length) {
      return tooltips;
    }
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function(_) {
    if (!arguments.length) {
      return tooltipContent;
    }
    tooltipContent = _;
    return chart;
  };

  chart.state = function(_) {
    if (!arguments.length) {
      return state;
    }
    state = _;
    return chart;
  };

  chart.colorFill = function(_) {
    return chart;
  };

  chart.strings = function(_) {
    if (!arguments.length) {
      return strings;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        strings[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.seriesClick = function(_) {
    if (!arguments.length) {
      return seriesClick;
    }
    seriesClick = _;
    return chart;
  };

  chart.direction = function(_) {
    if (!arguments.length) {
      return direction;
    }
    direction = _;
    pie.direction(_);
    legend.direction(_);
    return chart;
  };

  //============================================================

  return chart;
};

nv.models.sparkline = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 2, right: 0, bottom: 2, left: 0}
    , width = 400
    , height = 32
    , animate = true
    , x = d3.scale.linear()
    , y = d3.scale.linear()
    , getX = function(d) { return d.x }
    , getY = function(d) { return d.y }
    , color = nv.utils.getColor(['#000'])
    , xDomain
    , yDomain
    ;

  //============================================================


  function chart(selection) {
    selection.each(function(data) {
      var availableWidth = width - margin.left - margin.right,
          availableHeight = height - margin.top - margin.bottom,
          container = d3.select(this);


      //------------------------------------------------------------
      // Setup Scales

      x   .domain(xDomain || d3.extent(data, getX ))
          .range([0, availableWidth]);

      y   .domain(yDomain || d3.extent(data, getY ))
          .range([availableHeight, 0]);

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-sparkline').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-sparkline');
      var gEnter = wrapEnter.append('g');
      var g = wrap.select('g');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')')

      //------------------------------------------------------------


      var paths = wrap.selectAll('path')
          .data(function(d) { return [d] });
      paths.enter().append('path');
      paths.exit().remove();
      paths
          .style('stroke', function(d,i) { return d.color || color(d, i) })
          .attr('d', d3.svg.line()
            .x(function(d,i) { return x(getX(d,i)) })
            .y(function(d,i) { return y(getY(d,i)) })
          );


      // TODO: Add CURRENT data point (Need Min, Mac, Current / Most recent)
      var points = wrap.selectAll('circle.nv-point')
          .data(function(data) {
              var yValues = data.map(function(d, i) { return getY(d,i); });
              function pointIndex(index) {
                  if (index != -1) {
	              var result = data[index];
                      result.pointIndex = index;
                      return result;
                  } else {
                      return null;
                  }
              }
              var maxPoint = pointIndex(yValues.lastIndexOf(y.domain()[1])),
                  minPoint = pointIndex(yValues.indexOf(y.domain()[0])),
                  currentPoint = pointIndex(yValues.length - 1);
              return [minPoint, maxPoint, currentPoint].filter(function (d) {return d != null;});
          });
      points.enter().append('circle');
      points.exit().remove();
      points
          .attr('cx', function(d,i) { return x(getX(d,d.pointIndex)) })
          .attr('cy', function(d,i) { return y(getY(d,d.pointIndex)) })
          .attr('r', 2)
          .attr('class', function(d,i) {
            return getX(d, d.pointIndex) == x.domain()[1] ? 'nv-point nv-currentValue' :
                   getY(d, d.pointIndex) == y.domain()[0] ? 'nv-point nv-minValue' : 'nv-point nv-maxValue'
          });
    });

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin.top    = typeof _.top    != 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  != 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom != 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   != 'undefined' ? _.left   : margin.left;
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

  chart.x = function(_) {
    if (!arguments.length) return getX;
    getX = d3.functor(_);
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) return getY;
    getY = d3.functor(_);
    return chart;
  };

  chart.xScale = function(_) {
    if (!arguments.length) return x;
    x = _;
    return chart;
  };

  chart.yScale = function(_) {
    if (!arguments.length) return y;
    y = _;
    return chart;
  };

  chart.xDomain = function(_) {
    if (!arguments.length) return xDomain;
    xDomain = _;
    return chart;
  };

  chart.yDomain = function(_) {
    if (!arguments.length) return yDomain;
    yDomain = _;
    return chart;
  };

  chart.animate = function(_) {
    if (!arguments.length) return animate;
    animate = _;
    return chart;
  };

  chart.color = function(_) {
    if (!arguments.length) return color;
    color = nv.utils.getColor(_);
    return chart;
  };

  //============================================================


  return chart;
}

nv.models.sparklinePlus = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var sparkline = nv.models.sparkline();

  var margin = {top: 15, right: 100, bottom: 10, left: 50}
    , width = null
    , height = null
    , x
    , y
    , index = []
    , paused = false
    , xTickFormat = d3.format(',r')
    , yTickFormat = d3.format(',.2f')
    , showValue = true
    , alignValue = true
    , rightAlignValue = false
    , noData = "No Data Available."
    ;

  //============================================================


  function chart(selection) {
    selection.each(function(data) {
      var container = d3.select(this);

      var availableWidth = (width  || parseInt(container.style('width')) || 960)
                             - margin.left - margin.right,
          availableHeight = (height || parseInt(container.style('height')) || 400)
                             - margin.top - margin.bottom;

      var currentValue = sparkline.y()(data[data.length-1], data.length-1);

      chart.update = function() { chart(selection) };
      chart.container = this;


      //------------------------------------------------------------
      // Display No Data message if there's nothing to show.

      if (!data || !data.length) {
        var noDataText = container.selectAll('.nv-noData').data([noData]);

        noDataText.enter().append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle');

        noDataText
          .attr('x', margin.left + availableWidth / 2)
          .attr('y', margin.top + availableHeight / 2)
          .text(function(d) { return d });

        return chart;
      } else {
        container.selectAll('.nv-noData').remove();
      }

      //------------------------------------------------------------



      //------------------------------------------------------------
      // Setup Scales

      x = sparkline.xScale();
      y = sparkline.yScale();

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-sparklineplus').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-sparklineplus');
      var gEnter = wrapEnter.append('g');
      var g = wrap.select('g');

      gEnter.append('g').attr('class', 'nv-sparklineWrap');
      gEnter.append('g').attr('class', 'nv-valueWrap');
      gEnter.append('g').attr('class', 'nv-hoverArea');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Main Chart Component(s)

      var sparklineWrap = g.select('.nv-sparklineWrap');

      sparkline
        .width(availableWidth)
        .height(availableHeight);

      sparklineWrap
          .call(sparkline);

      //------------------------------------------------------------


      var valueWrap = g.select('.nv-valueWrap');

      var value = valueWrap.selectAll('.nv-currentValue')
          .data([currentValue]);

      value.enter().append('text').attr('class', 'nv-currentValue')
          .attr('dx', rightAlignValue ? -8 : 8)
          .attr('dy', '.9em')
          .style('text-anchor', rightAlignValue ? 'end' : 'start');

      value
          .attr('x', availableWidth + (rightAlignValue ? margin.right : 0))
          .attr('y', alignValue ? function(d) { return y(d) } : 0)
          .style('fill', sparkline.color()(data[data.length-1], data.length-1))
          .text(yTickFormat(currentValue));



      gEnter.select('.nv-hoverArea').append('rect')
          .on('mousemove', sparklineHover)
          .on('click', function() { paused = !paused })
          .on('mouseout', function() { index = []; updateValueLine(); });
          //.on('mouseout', function() { index = null; updateValueLine(); });

      g.select('.nv-hoverArea rect')
          .attr('transform', function(d) { return 'translate(' + -margin.left + ',' + -margin.top + ')' })
          .attr('width', availableWidth + margin.left + margin.right)
          .attr('height', availableHeight + margin.top);



      function updateValueLine() { //index is currently global (within the chart), may or may not keep it that way
        if (paused) return;

        var hoverValue = g.selectAll('.nv-hoverValue').data(index)

        var hoverEnter = hoverValue.enter()
          .append('g').attr('class', 'nv-hoverValue')
            .style('stroke-opacity', 0)
            .style('fill-opacity', 0);

        hoverValue.exit()
          .transition().duration(250)
            .style('stroke-opacity', 0)
            .style('fill-opacity', 0)
            .remove();

        hoverValue
            .attr('transform', function(d) { return 'translate(' + x(sparkline.x()(data[d],d)) + ',0)' })
          .transition().duration(250)
            .style('stroke-opacity', 1)
            .style('fill-opacity', 1);

        if (!index.length) return;

        hoverEnter.append('line')
            .attr('x1', 0)
            .attr('y1', -margin.top)
            .attr('x2', 0)
            .attr('y2', availableHeight);


        hoverEnter.append('text').attr('class', 'nv-xValue')
            .attr('x', -6)
            .attr('y', -margin.top)
            .attr('text-anchor', 'end')
            .attr('dy', '.9em')


        g.select('.nv-hoverValue .nv-xValue')
            .text(xTickFormat(sparkline.x()(data[index[0]], index[0])));

        hoverEnter.append('text').attr('class', 'nv-yValue')
            .attr('x', 6)
            .attr('y', -margin.top)
            .attr('text-anchor', 'start')
            .attr('dy', '.9em')

        g.select('.nv-hoverValue .nv-yValue')
            .text(yTickFormat(sparkline.y()(data[index[0]], index[0])));

      }


      function sparklineHover() {
        if (paused) return;

        var pos = d3.mouse(this)[0] - margin.left;

        function getClosestIndex(data, x) {
          var distance = Math.abs(sparkline.x()(data[0], 0) - x);
          var closestIndex = 0;
          for (var i = 0; i < data.length; i++){
            if (Math.abs(sparkline.x()(data[i], i) - x) < distance) {
              distance = Math.abs(sparkline.x()(data[i], i) - x);
              closestIndex = i;
            }
          }
          return closestIndex;
        }

        index = [getClosestIndex(data, Math.round(x.invert(pos)))];

        updateValueLine();
      }

    });

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.sparkline = sparkline;

  d3.rebind(chart, sparkline, 'x', 'y', 'xScale', 'yScale', 'color');

  chart.margin = function(_) {
    if (!arguments.length) return margin;
    margin.top    = typeof _.top    != 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  != 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom != 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   != 'undefined' ? _.left   : margin.left;
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

  chart.xTickFormat = function(_) {
    if (!arguments.length) return xTickFormat;
    xTickFormat = _;
    return chart;
  };

  chart.yTickFormat = function(_) {
    if (!arguments.length) return yTickFormat;
    yTickFormat = _;
    return chart;
  };

  chart.showValue = function(_) {
    if (!arguments.length) return showValue;
    showValue = _;
    return chart;
  };

  chart.alignValue = function(_) {
    if (!arguments.length) return alignValue;
    alignValue = _;
    return chart;
  };

  chart.rightAlignValue = function(_) {
    if (!arguments.length) return rightAlignValue;
    rightAlignValue = _;
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

nv.models.stackedArea = function () {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 0, right: 0, bottom: 0, left: 0}
    , width = 960
    , height = 500
    , getX = function (d) { return d.x; } // accessor to get the x value from a data point
    , getY = function (d) { return d.y; } // accessor to get the y value from a data point
    , style = 'stack'
    , offset = 'zero'
    , order = 'default'
    , interpolate = 'linear'  // controls the line interpolation
    , clipEdge = false // if true, masks lines within x and y scale
    , x //can be accessed via chart.xScale()
    , y //can be accessed via chart.yScale()
    , delay = 200
    , scatter = nv.models.scatter()
    , color = function (d, i) { return nv.utils.defaultColor()(d, d.series); }
    , fill = color
    , classes = function (d,i) { return 'nv-area nv-area-' + d.series; }
    , dispatch =  d3.dispatch('tooltipShow', 'tooltipHide', 'tooltipMove', 'areaClick', 'areaMouseover', 'areaMouseout', 'areaMousemove')
    ;

  scatter
    .size(2.2) // default size
    .sizeDomain([2.2]) // all the same size by default
    ;

  /************************************
   * offset:
   *   'wiggle' (stream)
   *   'zero' (stacked)
   *   'expand' (normalize to 100%)
   *   'silhouette' (simple centered)
   *
   * order:
   *   'inside-out' (stream)
   *   'default' (input order)
   ************************************/

  //============================================================


  function chart(selection) {
    selection.each(function (data) {
      var availableWidth = width - margin.left - margin.right,
          availableHeight = height - margin.top - margin.bottom,
          container = d3.select(this);

      //------------------------------------------------------------
      // Setup Scales

      x = scatter.xScale();
      y = scatter.yScale();

      //------------------------------------------------------------


      // Injecting point index into each point because d3.layout.stack().out does not give index
      // ***Also storing getY(d,i) as stackedY so that it can be set to 0 if series is disabled
      data = data.map(function (aseries, i) {
        aseries.values = aseries.values.map(function (d, j) {
          d.index = j;
          d.stackedY = aseries.disabled ? 0 : getY(d,j);
          return d;
        });
        return aseries;
      });


      data = d3.layout.stack()
        .order(order)
        .offset(offset)
        .values(function (d) { return d.values; })  //TODO: make values customizeable in EVERY model in this fashion
        .x(getX)
        .y(function (d) { return d.stackedY; })
        .out(function (d, y0, y) {
          d.display = {
            y: y,
            y0: y0
          };
        })
        (data);


      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-stackedarea').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-stackedarea');
      var defsEnter = wrapEnter.append('defs');
      var gEnter = wrapEnter.append('g');
      var g = wrap.select('g');

      //set up the gradient constructor function
      chart.gradient = function (d,i,p) {
        return nv.utils.colorLinearGradient( d, chart.id() +'-'+ i, p, color(d,i), wrap.select('defs') );
      };

      gEnter.append('g').attr('class', 'nv-areaWrap');
      gEnter.append('g').attr('class', 'nv-scatterWrap');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------

      scatter
        .width(availableWidth)
        .height(availableHeight)
        .x(getX)
        .y(function (d) { return d.display.y + d.display.y0; })
        .forceY([0]);


      var scatterWrap = g.select('.nv-scatterWrap')
          .datum(data.filter(function (d) { return !d.disabled; }));

      //d3.transition(scatterWrap).call(scatter);
      scatterWrap.call(scatter);


      defsEnter.append('clipPath')
          .attr('id', 'nv-edge-clip-' + chart.id())
        .append('rect');

      wrap.select('#nv-edge-clip-' + chart.id() + ' rect')
          .attr('width', availableWidth)
          .attr('height', availableHeight);

      g   .attr('clip-path', clipEdge ? 'url(#nv-edge-clip-' + chart.id() + ')' : '');


      var area = d3.svg.area()
          .x(function (d,i)  { return x(getX(d,i)); })
          .y0(function (d) { return y(d.display.y0); })
          .y1(function (d) { return y(d.display.y + d.display.y0); })
          .interpolate(interpolate);

      var zeroArea = d3.svg.area()
          .x(function (d,i)  { return x(getX(d,i)); })
          .y0(function (d) { return y(d.display.y0); })
          .y1(function (d) { return y(d.display.y0); });


      var path = g.select('.nv-areaWrap').selectAll('path.nv-area')
          .data(function (d) { return d; });
          //.data(function (d) { return d }, function (d) { return d.key });
      path.enter().append('path')
			//.attr('class', function (d,i) { return 'nv-area nv-area-' + i })
          .on('mouseover', function (d,i) {
            d3.select(this).classed('hover', true);
            dispatch.areaMouseover({
              point: d,
              series: d.key,
              pos: [d3.event.offsetX, d3.event.offsetY],
              seriesIndex: i
            });
            g.select('.nv-chart-' + chart.id() + ' .nv-area-' + i).classed('hover', true);
          })
          .on('mouseout', function (d,i) {
            d3.select(this).classed('hover', false);
            dispatch.areaMouseout({
              point: d,
              series: d.key,
              pos: [d3.event.offsetX, d3.event.offsetY],
              seriesIndex: i
            });
            g.select('.nv-chart-' + chart.id() + ' .nv-area-' + i).classed('hover', false);
          })
          .on('mousemove', function (d,i){
            dispatch.areaMousemove({
              point: d,
              pointIndex: i,
              pos: [d3.event.offsetX, d3.event.offsetY],
              seriesIndex: i
            });
          })
          .on('click', function (d,i) {
            d3.select(this).classed('hover', false);
            dispatch.areaClick({
              point: d,
              series: d.key,
              pos: [d3.event.offsetX, d3.event.offsetY],
              seriesIndex: i
            });
          });
      //d3.transition(path.exit())
      path.exit()
          .attr('d', function (d,i) { return zeroArea(d.values,i); })
          .remove();
      path
          .attr('class', classes)
          .attr('fill', color)
          .attr('stroke', color);
      //d3.transition(path)
      path
          .attr('d', function (d,i) { return area(d.values,i); });


      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      scatter.dispatch.on('elementMouseover.area', function (e) {
        g.select('.nv-chart-' + chart.id() + ' .nv-area-' + e.seriesIndex).classed('hover', true);
      });
      scatter.dispatch.on('elementMouseout.area', function (e) {
        g.select('.nv-chart-' + chart.id() + ' .nv-area-' + e.seriesIndex).classed('hover', false);
      });
      scatter.dispatch.on('elementClick.area', function (e) {
        dispatch.areaClick(e);
      });

      //============================================================

    });

    return chart;
  }


  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  scatter.dispatch.on('elementMouseover.tooltip', function (e) {
    e.pos = [e.pos[0] + margin.left, e.pos[1] + margin.top];
    dispatch.tooltipShow(e);
  });
  scatter.dispatch.on('elementMouseout.tooltip', function (e) {
    dispatch.tooltipHide(e);
  });

  //============================================================


  //============================================================
  // Global getters and setters
  //------------------------------------------------------------

  chart.dispatch = dispatch;
  chart.scatter = scatter;

  d3.rebind(chart, scatter, 'interactive', 'size', 'id', 'xScale', 'yScale', 'zScale', 'xDomain', 'yDomain', 'sizeDomain', 'forceX', 'forceY', 'forceSize', 'clipVoronoi', 'useVoronoi', 'clipRadius');

  chart.color = function (_) {
    if (!arguments.length) { return color; }
    color = _;
    scatter.color(color);
    return chart;
  };
  chart.fill = function (_) {
    if (!arguments.length) { return fill; }
    fill = _;
    scatter.fill(fill);
    return chart;
  };
  chart.classes = function (_) {
    if (!arguments.length) { return classes; }
    classes = _;
    scatter.classes(classes);
    return chart;
  };
  chart.gradient = function (_) {
    if (!arguments.length) { return gradient; }
    gradient = _;
    return chart;
  };

  chart.margin = function (_) {
    if (!arguments.length) { return margin; }
    margin.top    = typeof _.top    != 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  != 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom != 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   != 'undefined' ? _.left   : margin.left;
    return chart;
  };

  chart.width = function (_) {
    if (!arguments.length) { return width; }
    width = _;
    return chart;
  };

  chart.height = function (_) {
    if (!arguments.length) { return height; }
    height = _;
    return chart;
  };

  chart.x = function (_) {
    if (!arguments.length) { return getX; }
    getX = _;
    scatter.x(_);
    return chart;
  };

  chart.y = function (_) {
    if (!arguments.length) { return getY; }
    getY = _;
    scatter.y(_);
    return chart;
  };

  chart.delay = function (_) {
    if (!arguments.length) { return delay; }
    delay = _;
    return chart;
  };

  chart.clipEdge = function (_) {
    if (!arguments.length) { return clipEdge; }
    clipEdge = _;
    return chart;
  };

  chart.offset = function (_) {
    if (!arguments.length) { return offset; }
    offset = _;
    return chart;
  };

  chart.order = function (_) {
    if (!arguments.length) { return order; }
    order = _;
    return chart;
  };

  //shortcut for offset + order
  chart.style = function (_) {
    if (!arguments.length) { return style; }
    style = _;

    switch (style) {
      case 'stack':
        chart.offset('zero');
        chart.order('default');
        break;
      case 'stream':
        chart.offset('wiggle');
        chart.order('inside-out');
        break;
      case 'stream-center':
          chart.offset('silhouette');
          chart.order('inside-out');
          break;
      case 'expand':
        chart.offset('expand');
        chart.order('default');
        break;
    }

    return chart;
  };

  chart.interpolate = function (_) {
    if (!arguments.length) { return interpolate; }
    interpolate = _;
    return interpolate;
  };

  //============================================================

  return chart;
};
nv.models.stackedAreaChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 10, right: 10, bottom: 10, left: 10},
      width = null,
      height = null,
      showTitle = false,
      showControls = false,
      showLegend = true,
      direction = 'ltr',
      tooltip = null,
      tooltips = true,
      tooltipContent = function (key, x, y, e, graph) {
        return '<h3>' + key + '</h3>';
      },
      x,
      y,
      yAxisTickFormat = d3.format(',.2f'),
      state = {},
      strings = {
        legend: {close: 'Hide legend', open: 'Show legend'},
        controls: {close: 'Hide controls', open: 'Show controls'},
        noData: 'No Data Available.'
      },
      dispatch = d3.dispatch('chartClick', 'tooltipShow', 'tooltipHide', 'tooltipMove', 'stateChange', 'changeState');

  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var stacked = nv.models.stackedArea()
        .clipEdge(true),
      xAxis = nv.models.axis()
        .orient('bottom')
        .tickPadding(4)
        .highlightZero(false)
        .showMaxMin(false)
        .tickFormat(function (d) { return d; }),
      yAxis = nv.models.axis()
        .orient('left')
        .tickPadding(4)
        .tickFormat(stacked.offset() === 'expand' ? d3.format('%') : yAxisTickFormat),
      legend = nv.models.legend()
        .align('right'),
      controls = nv.models.legend()
        .align('left')
        .color(['#444']);

  stacked.scatter
    .pointActive(function (d) {
      return !!Math.round(stacked.y()(d) * 100);
    });

  var showTooltip = function (e, offsetElement) {
    var left = e.pos[0],
        top = e.pos[1],
        content = tooltipContent(e.series, e, chart);

    tooltip = nv.tooltip.show([left, top], content, null, null, offsetElement);
  };

  //============================================================

  function chart(selection) {

    selection.each(function (chartData) {

      var properties = chartData.properties,
          data = chartData.data,
          container = d3.select(this),
          that = this,
          availableWidth = (width || parseInt(container.style('width'), 10) || 960) - margin.left - margin.right,
          availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom,
          innerWidth = availableWidth,
          innerHeight = availableHeight,
          innerMargin = {top: 0, right: 0, bottom: 0, left: 0},
          maxControlsWidth = 0,
          maxLegendWidth = 0,
          widthRatio = 0,
          controlsHeight = 0,
          legendHeight = 0;

      chart.update = function () {
        container.transition().duration(chart.delay()).call(chart);
      };

      chart.container = this;

      //------------------------------------------------------------
      // Display No Data message if there's nothing to show.

      if (!data || !data.length || !data.filter(function (d) {
        return d.values.length;
      }).length) {
        var noDataText = container.selectAll('.nv-noData').data([chart.strings().noData]);

        noDataText.enter().append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle');

        noDataText
          .attr('x', margin.left + availableWidth / 2)
          .attr('y', margin.top + availableHeight / 2)
          .text(function (d) {
            return d;
          });

        return chart;
      } else {
        container.selectAll('.nv-noData').remove();
      }

      //------------------------------------------------------------
      // Process data

      //add series index to each data point for reference
      data.map(function (d, i) {
        d.series = i;
      });

      var dataLines = data.filter(function (d) {
            return !d.disabled;
          });
      dataLines = dataLines.length ? dataLines : [{values:[]}];

      //set state.disabled
      state.disabled = data.map(function (d) { return !!d.disabled; });
      state.style = stacked.style();

      var controlsData = [
        { key: 'Stacked', disabled: stacked.offset() !== 'zero' },
        { key: 'Stream', disabled: stacked.offset() !== 'wiggle' },
        { key: 'Expanded', disabled: stacked.offset() !== 'expand' }
      ];

      //------------------------------------------------------------
      // Setup Scales

      x = stacked.xScale();
      y = stacked.yScale();

      xAxis
        .scale(x);
      yAxis
        .scale(y);

      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-stackedAreaChart').data([data]),
          gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-stackedAreaChart').append('g'),
          g = wrap.select('g').attr('class', 'nv-chartWrap');

      gEnter.append('rect').attr('class', 'nv-background')
        .attr('x', -margin.left)
        .attr('y', -margin.top)
        .attr('fill', '#FFF');

      g.select('.nv-background')
        .attr('width', availableWidth + margin.left + margin.right)
        .attr('height', availableHeight + margin.top + margin.bottom);

      gEnter.append('g').attr('class', 'nv-titleWrap');
      var titleWrap = g.select('.nv-titleWrap');
      gEnter.append('g').attr('class', 'nv-x nv-axis');
      var xAxisWrap = g.select('.nv-x.nv-axis');
      gEnter.append('g').attr('class', 'nv-y nv-axis');
      var yAxisWrap = g.select('.nv-y.nv-axis');
      gEnter.append('g').attr('class', 'nv-stackedWrap');
      var stackedWrap = g.select('.nv-stackedWrap');
      gEnter.append('g').attr('class', 'nv-controlsWrap');
      var controlsWrap = g.select('.nv-controlsWrap');
      gEnter.append('g').attr('class', 'nv-legendWrap');
      var legendWrap = g.select('.nv-legendWrap');

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------
      // Title & Legend & Controls

      var titleBBox = {width: 0, height: 0};
      titleWrap.select('.nv-title').remove();

      if (showTitle && properties.title) {
        titleWrap
          .append('text')
            .attr('class', 'nv-title')
            .attr('x', direction === 'rtl' ? availableWidth : 0)
            .attr('y', 0)
            .attr('dy', '.75em')
            .attr('text-anchor', 'start')
            .text(properties.title)
            .attr('stroke', 'none')
            .attr('fill', 'black');

        titleBBox = nv.utils.getTextBBox(g.select('.nv-title'));

        innerMargin.top += titleBBox.height + 12;
      }

      if (showControls) {
        controls
          .id('controls_' + chart.id())
          .strings(chart.strings().controls)
          .align('left')
          .height(availableHeight - innerMargin.top);
        controlsWrap
          .datum(controlsData)
          .call(controls);

        maxControlsWidth = controls.calculateWidth();
      }

      if (showLegend) {
        legend
          .id('legend_' + chart.id())
          .strings(chart.strings().legend)
          .align('right')
          .height(availableHeight - innerMargin.top);
        legendWrap
          .datum(data)
          .call(legend);

        maxLegendWidth = legend.calculateWidth();
      }

      // calculate proportional available space
      widthRatio = availableWidth / (maxControlsWidth + maxLegendWidth);
      maxControlsWidth = Math.floor(maxControlsWidth * widthRatio);
      maxLegendWidth = Math.floor(maxLegendWidth * widthRatio);

      if (showControls) {
        controls
          .arrange(maxControlsWidth);
        maxLegendWidth = availableWidth - controls.width();
      }
      if (showLegend) {
        legend
          .arrange(maxLegendWidth);
        maxControlsWidth = availableWidth - legend.width();
      }

      if (showControls) {
        var xpos = direction === 'rtl' ? availableWidth - controls.width() : 0,
            ypos = showTitle ? titleBBox.height : - legend.margin().top;
        controlsWrap
          .attr('transform', 'translate(' + xpos + ',' + ypos + ')');
        controlsHeight = controls.height();
      }

      if (showLegend) {
        var legendLinkBBox = nv.utils.getTextBBox(legendWrap.select('.nv-legend-link')),
            legendSpace = availableWidth - titleBBox.width - 6,
            legendTop = showTitle && !showControls && legend.collapsed() && legendSpace > legendLinkBBox.width ? true : false,
            xpos = direction === 'rtl' ? 0 : availableWidth - legend.width(),
            ypos = titleBBox.height;
        if (legendTop) {
          ypos = titleBBox.height - legend.height() / 2 - legendLinkBBox.height / 2;
        } else if (!showTitle) {
          ypos = - legend.margin().top;
        }
        legendWrap
          .attr('transform', 'translate(' + xpos + ',' + ypos + ')');
        legendHeight = legendTop ? 0 : legend.height() - 12;
      }

      // Recalc inner margins based on legend and control height
      innerMargin.top += Math.max(controlsHeight, legendHeight);
      innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

      //------------------------------------------------------------
      // Main Chart Component(s)

      stacked
        .width(innerWidth)
        .height(innerHeight)
        .id(chart.id());
      stackedWrap
        .datum(dataLines)
        .call(stacked);

      //------------------------------------------------------------
      // Setup Axes

      //------------------------------------------------------------
      // X-Axis

      xAxisWrap
        .call(xAxis);

      innerMargin[xAxis.orient()] += xAxis.height();
      innerHeight = availableHeight - innerMargin.top - innerMargin.bottom;

      //------------------------------------------------------------
      // Y-Axis

      yAxisWrap
        .call(yAxis);

      innerMargin[yAxis.orient()] += yAxis.width();
      innerWidth = availableWidth - innerMargin.left - innerMargin.right;

      //------------------------------------------------------------
      // Main Chart Components
      // Recall to set final size

      stacked
        .width(innerWidth)
        .height(innerHeight);

      stackedWrap
        .attr('transform', 'translate(' + innerMargin.left + ',' + innerMargin.top + ')')
        .transition().duration(chart.delay())
          .call(stacked);

      xAxis
        .ticks(innerWidth / 100)
        .tickSize(-innerHeight, 0);

      xAxisWrap
        .attr('transform', 'translate(' + innerMargin.left + ',' + (xAxis.orient() === 'bottom' ? innerHeight + innerMargin.top : innerMargin.top) + ')')
        .transition()
          .call(xAxis);

      yAxis
        .ticks(stacked.offset() === 'wiggle' ? 0 : innerHeight / 36)
        .tickSize(-innerWidth, 0);

      yAxisWrap
        .attr('transform', 'translate(' + (yAxis.orient() === 'left' ? innerMargin.left : innerMargin.left + innerWidth) + ',' + innerMargin.top + ')')
        .transition()
          .call(yAxis);

      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      stacked.dispatch.on('areaClick.toggle', function (e) {
        if (data.filter(function (d) { return !d.disabled; }).length === 1) {
          data = data.map(function (d) {
            d.disabled = false;
            return d;
          });
        } else {
          data = data.map(function (d,i) {
            d.disabled = (i !== e.seriesIndex);
            return d;
          });
        }

        state.disabled = data.map(function (d) { return !!d.disabled; });
        dispatch.stateChange(state);

        container.transition().duration(chart.delay()).call(chart);
      });

      legend.dispatch.on('legendClick', function (d, i) {
        d.disabled = !d.disabled;

        if (!data.filter(function (d) { return !d.disabled; }).length) {
          data.map(function (d) {
            d.disabled = false;
            g.selectAll('.nv-series').classed('disabled', false);
            return d;
          });
        }

        state.disabled = data.map(function (d) { return !!d.disabled; });
        dispatch.stateChange(state);

        container.transition().duration(chart.delay()).call(chart);
      });

      controls.dispatch.on('legendClick', function (d, i) {
        if (!d.disabled) {
          return;
        }
        controlsData = controlsData.map(function (s) {
          s.disabled = true;
          return s;
        });
        d.disabled = false;

        switch (d.key) {
          case 'Stacked':
            stacked.style('stack');
            break;
          case 'Stream':
            stacked.style('stream');
            break;
          case 'Expanded':
            stacked.style('expand');
            break;
        }

        state.style = stacked.style();
        dispatch.stateChange(state);

        container.transition().duration(chart.delay()).call(chart);
      });

      dispatch.on('tooltipShow', function(eo) {
        if (tooltips) {
          showTooltip(eo, that.parentNode);
        }
      });

      dispatch.on('tooltipMove', function(eo) {
        if (tooltip) {
          nv.tooltip.position(that.parentNode, tooltip, eo.pos, 's');
        }
      });

      dispatch.on('tooltipHide', function() {
        if (tooltips) {
          nv.tooltip.cleanup();
        }
      });

      // Update chart from a state object passed to event handler
      dispatch.on('changeState', function(e) {
        if (typeof e.disabled !== 'undefined') {
          data.forEach(function(series, i) {
            series.disabled = e.disabled[i];
          });
          state.disabled = e.disabled;
        }

        if (typeof e.style !== 'undefined') {
          stacked.style(e.style);
          state.style = e.style;
        }

        container.transition().duration(chart.delay()).call(chart);
      });

      dispatch.on('chartClick', function() {
        if (controls.enabled()) {
          controls.dispatch.closeMenu();
        }
        if (legend.enabled()) {
          legend.dispatch.closeMenu();
        }
      });

    });

    return chart;
  }

  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  stacked.dispatch.on('areaMouseover.tooltip', function(eo) {
    dispatch.tooltipShow(eo);
  });

  stacked.dispatch.on('areaMousemove.tooltip', function(eo) {
    dispatch.tooltipMove(eo);
  });

  stacked.dispatch.on('areaMouseout.tooltip', function() {
    dispatch.tooltipHide();
  });

  stacked.dispatch.on('tooltipShow', function(e) {
    dispatch.tooltipShow(e);
  });

  stacked.dispatch.on('tooltipHide', function(e) {
    dispatch.tooltipHide(e);
  });


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.stacked = stacked;
  chart.legend = legend;
  chart.controls = controls;
  chart.xAxis = xAxis;
  chart.yAxis = yAxis;

  d3.rebind(chart, stacked, 'id', 'x', 'y', 'xScale', 'yScale', 'xDomain', 'yDomain', 'forceX', 'forceY', 'clipEdge', 'delay', 'color', 'fill', 'classes', 'gradient');
  d3.rebind(chart, stacked, 'size', 'sizeDomain', 'forceSize', 'offset', 'order', 'style', 'interactive', 'useVoronoi', 'clipVoronoi');
  d3.rebind(chart, xAxis, 'rotateTicks', 'reduceXTicks', 'staggerTicks', 'wrapTicks');

  chart.colorData = function(_) {
    var type = arguments[0],
        params = arguments[1] || {};
    var color = function(d, i) {
          return nv.utils.defaultColor()(d, d.series);
        };
    var classes = function(d, i) {
          return 'nv-area nv-area-' + d.series;
        };

    switch (type) {
      case 'graduated':
        color = function(d, i) {
          return d3.interpolateHsl(d3.rgb(params.c1), d3.rgb(params.c2))(d.series / params.l);
        };
        break;
      case 'class':
        color = function() {
          return 'inherit';
        };
        classes = function(d, i) {
          var iClass = (d.series * (params.step || 1)) % 14;
          iClass = (iClass > 9 ? '' : '0') + iClass;
          return 'nv-area nv-area-' + d.series + ' nv-fill' + iClass + ' nv-stroke' + iClass;
        };
        break;
      case 'data':
        color = function(d, i) {
          return d.color || nv.utils.defaultColor()(d, d.series);
        };
        classes = function(d, i) {
          return 'nv-area nv-area-' + d.series + (d.classes ? ' ' + d.classes : '');
        };
        break;
    }

    var fill = (!params.gradient) ? color : function(d, i) {
      var p = {orientation: params.orientation || 'horizontal', position: params.position || 'base'};
      return stacked.gradient(d, d.series, p);
    };

    stacked.color(color);
    stacked.fill(fill);
    stacked.classes(classes);

    legend.color(color);
    legend.classes(classes);

    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) {
      return margin;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        margin[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) {
      return width;
    }
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) {
      return height;
    }
    height = _;
    return chart;
  };

  chart.showTitle = function(_) {
    if (!arguments.length) {
      return showTitle;
    }
    showTitle = _;
    return chart;
  };

  chart.showControls = function(_) {
    if (!arguments.length) {
      return showControls;
    }
    showControls = _;
    return chart;
  };

  chart.showLegend = function(_) {
    if (!arguments.length) {
      return showLegend;
    }
    showLegend = _;
    return chart;
  };

  chart.tooltip = function(_) {
    if (!arguments.length) {
      return tooltip;
    }
    tooltip = _;
    return chart;
  };

  chart.tooltips = function(_) {
    if (!arguments.length) {
      return tooltips;
    }
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function(_) {
    if (!arguments.length) {
      return tooltipContent;
    }
    tooltipContent = _;
    return chart;
  };

  chart.state = function(_) {
    if (!arguments.length) {
      return state;
    }
    state = _;
    return chart;
  };

  yAxis.tickFormat = function(_) {
    if (!arguments.length) {
      return yAxisTickFormat;
    }
    yAxisTickFormat = _;
    return yAxis;
  };

  chart.strings = function(_) {
    if (!arguments.length) {
      return strings;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        strings[prop] = _[prop];
      }
    }
    return chart;
  };

  chart.direction = function(_) {
    if (!arguments.length) {
      return direction;
    }
    direction = _;
    yAxis.direction(_);
    legend.direction(_);
    controls.direction(_);
    return chart;
  };

  //============================================================

  return chart;
};

nv.models.treemap = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 20, right: 0, bottom: 0, left: 0},
      width = 0,
      height = 0,
      x, //can be accessed via chart.xScale()
      y, //can be accessed via chart.yScale()
      id = Math.floor(Math.random() * 10000), //Create semi-unique ID incase user doesn't select one
      getSize = function(d) { return d.size; }, // accessor to get the size value from a data point
      groupBy = function(d) { return d.name; }, // accessor to get the name value from a data point
      clipEdge = true, // if true, masks lines within x and y scale
      groups = [],
      leafClick = function() { return false; },
      color = function(d, i) { return nv.utils.defaultColor()(d, i); },
      fill = color,
      classes = function(d, i) { return 'nv-child'; },
      direction = 'ltr',
      dispatch = d3.dispatch('chartClick', 'elementClick', 'elementDblClick', 'elementMouseover', 'elementMouseout', 'elementMousemove');

  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  //used to store previous scales
  var x0,
      y0;

  //============================================================


  function chart(selection) {
    selection.each(function(chartData) {

      var data = chartData[0];

      //this is for data sets that don't include a colorIndex
      //excludes leaves
      function reduceGroups(d) {
        var i, l;
        if (d.children && groupBy(d) && groups.indexOf(groupBy(d)) === -1) {
          groups.push(groupBy(d));
          l = d.children.length;
          for (i = 0; i < l; i += 1) {
            reduceGroups(d.children[i]);
          }
        }
      }
      reduceGroups(data);

      var availableWidth = width - margin.left - margin.right,
          availableHeight = height - margin.top - margin.bottom,
          container = d3.select(this),
          transitioning;

      x = d3.scale.linear()
            .domain([0, data.dx])
            .range([0, availableWidth]);

      y = d3.scale.linear()
            .domain([0, data.dy])
            .range([0, availableHeight]);

      x0 = x0 || x;
      y0 = y0 || y;

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-treemap').data([data]);
      var wrapEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-treemap');
      var defsEnter = wrapEnter.append('defs');
      var gEnter = wrapEnter.append('g');
      var g = wrap.select('g');

      //set up the gradient constructor function
      chart.gradient = function(d, i, p) {
        var iColor = (d.parent.colorIndex || groups.indexOf(groupBy(d.parent)) || i);
        return nv.utils.colorLinearGradient(d, id + '-' + i, p, color(d, iColor, groups.length), wrap.select('defs'));
      };

      //wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

      //------------------------------------------------------------
      // Clip Path

      defsEnter.append('clipPath')
          .attr('id', 'nv-edge-clip-' + id)
        .append('rect');
      wrap.select('#nv-edge-clip-' + id + ' rect')
          .attr('width', width)
          .attr('height', height);
      g.attr('clip-path', clipEdge ? 'url(#nv-edge-clip-' + id + ')' : '');


      //------------------------------------------------------------
      // Main Chart

      var grandparent = gEnter.append('g').attr('class', 'nv-grandparent');

      grandparent.append('rect')
        //.attr('y', -margin.top)
        .attr('width', width)
        .attr('height', margin.top);

      grandparent.append('text')
        .attr('x', direction === 'rtl' ? width - 6 : 6)
        .attr('y', 6)
        .attr('dy', '.75em');

      display(data);

      function display(d) {

        var treemap = d3.layout.treemap()
              .value(getSize)
              .sort(function(a, b) { return getSize(a) - getSize(b); })
              .round(false);

        layout(d);

        grandparent.datum(d.parent).on('click', transition).select('text').text(name(d));

        var g1 = gEnter.insert('g', '.nv-grandparent')
          .attr('class', 'nv-depth')
          .attr('height', availableHeight)
          .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

        var g = g1.selectAll('g').data(d.children).enter().append('g');

        // Transition for nodes with children.
        g.filter(function(d) { return d.children; })
          .classed('nv-children', true)
          .on('click', transition);

        // Navigate for nodes without children (leaves).
        g.filter(function(d) { return !(d.children); })
          .on('click', leafClick);

        g.on('mouseover', function(d, i) {
            d3.select(this).classed('hover', true);
            dispatch.elementMouseover({
              point: d,
              pointIndex: i,
              pos: [d3.event.offsetX, d3.event.offsetY],
              id: id
            });
          })
          .on('mouseout', function(d, i) {
            d3.select(this).classed('hover', false);
            dispatch.elementMouseout();
          })
          .on('mousemove', function(d, i) {
            dispatch.elementMousemove({
              point: d,
              pointIndex: i,
              pos: [d3.event.offsetX, d3.event.offsetY],
              id: id
            });
          });

        var child_rects = g.selectAll('.nv-child').data(function(d) {
            return d.children || [d];
          }).enter().append('rect')
              .attr('class', classes)
              .attr('fill', function(d, i) {
                var iColor = (d.parent.colorIndex || groups.indexOf(groupBy(d.parent)) || i);
                return this.getAttribute('fill') || fill(d, iColor, groups.length); })
              .call(rect);

        child_rects
          .on('mouseover', function(d, i) {
            d3.select(this).classed('hover', true);
            dispatch.elementMouseover({
                label: groupBy(d),
                value: getSize(d),
                point: d,
                pointIndex: i,
                pos: [d3.event.offsetX, d3.event.offsetY],
                id: id
            });
          })
          .on('mouseout', function(d, i) {
            d3.select(this).classed('hover', false);
            dispatch.elementMouseout();
          });

        g.append('rect')
          .attr('class', 'nv-parent')
          .call(rect);

        g.append('text')
          .attr('dy', '.75em')
          .text(function(d) { return groupBy(d); })
          .call(text);

        function transition(d) {
          dispatch.elementMouseout();
          if (transitioning || !d) { return; }
          transitioning = true;

          var g2 = display(d),
              t1 = g1.transition().duration(750),
              t2 = g2.transition().duration(750);

          // Update the domain only after entering new elements.
          x.domain([d.x, d.x + d.dx]);
          y.domain([d.y, d.y + d.dy]);

          // Enable anti-aliasing during the transition.
          container.style('shape-rendering', null);

          // Draw child nodes on top of parent nodes.
          container.selectAll('.nv-depth').sort(function(a, b) { return a.depth - b.depth; });

          // Fade-in entering text.
          g2.selectAll('text').style('fill-opacity', 0);

          // Transition to the new view.
          t1.selectAll('text').call(text).style('fill-opacity', 0);
          t2.selectAll('text').call(text).style('fill-opacity', 1);
          t1.selectAll('rect').call(rect);
          t2.selectAll('rect').call(rect);

          // Remove the old node when the transition is finished.
          t1.remove().each('end', function() {
            container.style('shape-rendering', 'crispEdges');
            transitioning = false;
          });
        }

        function layout(d) {
          if (d.children) {
            treemap.nodes({children: d.children});
            d.children.forEach(function(c) {
              c.x = d.x + c.x * d.dx;
              c.y = d.y + c.y * d.dy;
              c.dx *= d.dx;
              c.dy *= d.dy;
              c.parent = d;
              layout(c);
            });
          }
        }

        function text(t) {
          t.attr('x', function(d) {
              var xpos = direction === 'rtl' ? x(d.x + d.dx) - x(d.x) - 6 : 6;
              return x(d.x) + xpos;
            })
            .attr('y', function(d) { return y(d.y) + 6; });
        }

        function rect(r) {
          r.attr('x', function(d) { return x(d.x); })
            .attr('y', function(d) { return y(d.y); })
            .attr('width', function(d) { return x(d.x + d.dx) - x(d.x); })
            .attr('height', function(d) { return y(d.y + d.dy) - y(d.y); });
        }

        function name(d) {
          if (d.parent) {
            return name(d.parent) + ' / ' + groupBy(d);
          }
          return groupBy(d);
        }

        return g;
      }

    });

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  chart.dispatch = dispatch;

  chart.color = function(_) {
    if (!arguments.length) { return color; }
    color = _;
    return chart;
  };
  chart.fill = function(_) {
    if (!arguments.length) { return fill; }
    fill = _;
    return chart;
  };
  chart.classes = function(_) {
    if (!arguments.length) { return classes; }
    classes = _;
    return chart;
  };
  chart.gradient = function(_) {
    if (!arguments.length) { return gradient; }
    gradient = _;
    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) { return getX; }
    getX = _;
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) { return getY; }
    getY = _;
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) { return margin; }
    margin.top    = typeof _.top    !== 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  !== 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom !== 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   !== 'undefined' ? _.left   : margin.left;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) { return width; }
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) { return height; }
    height = _;
    return chart;
  };

  chart.xScale = function(_) {
    if (!arguments.length) { return x; }
    x = _;
    return chart;
  };

  chart.yScale = function(_) {
    if (!arguments.length) { return y; }
    y = _;
    return chart;
  };

  chart.xDomain = function(_) {
    if (!arguments.length) { return xDomain; }
    xDomain = _;
    return chart;
  };

  chart.yDomain = function(_) {
    if (!arguments.length) { return yDomain; }
    yDomain = _;
    return chart;
  };

  chart.leafClick = function(_) {
    if (!arguments.length) { return leafClick; }
    leafClick = _;
    return chart;
  };

  chart.getSize = function(_) {
    if (!arguments.length) { return getSize; }
    getSize = _;
    return chart;
  };

  chart.groupBy = function(_) {
    if (!arguments.length) { return groupBy; }
    groupBy = _;
    return chart;
  };

  chart.groups = function(_) {
    if (!arguments.length) { return groups; }
    groups = _;
    return chart;
  };

  chart.id = function(_) {
    if (!arguments.length) { return id; }
    id = _;
    return chart;
  };

  chart.direction = function(_) {
    if (!arguments.length) {
      return direction;
    }
    direction = _;
    return chart;
  };

  //============================================================


  return chart;
};

nv.models.treemapChart = function() {

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  var margin = {top: 0, right: 10, bottom: 10, left: 10},
      width = null,
      height = null,
      showTitle = false,
      showLegend = false,
      direction = 'ltr',
      tooltip = null,
      tooltips = true,
      tooltipContent = function(point) {
        var tt = '<p>Value: <b>' + d3.format(',.2s')(point.value) + '</b></p>' +
          '<p>Name: <b>' + point.name + '</b></p>';
        return tt;
      },
      colorData = 'default',
      //create a clone of the d3 array
      colorArray = d3.scale.category20().range().map( function(d) { return d; }),
      x, //can be accessed via chart.xScale()
      y, //can be accessed via chart.yScale()
      strings = {
        legend: {close: 'Hide legend', open: 'Show legend'},
        controls: {close: 'Hide controls', open: 'Show controls'},
        noData: 'No Data Available.'
      },
      dispatch = d3.dispatch('tooltipShow', 'tooltipHide', 'tooltipMove', 'elementMousemove');


  var treemap = nv.models.treemap(),
      legend = nv.models.legend();

  //============================================================


  //============================================================
  // Private Variables
  //------------------------------------------------------------

  var showTooltip = function(e, offsetElement) {
    var left = e.pos[0],// + ( (offsetElement && offsetElement.offsetLeft) || 0 ),
        top = e.pos[1],// + ( (offsetElement && offsetElement.offsetTop) || 0 ),
        content = tooltipContent(e.point);
    tooltip = nv.tooltip.show([left, top], content, null, null, offsetElement);
  };

  //============================================================


  function chart(selection) {
    selection.each(function(chartData) {

      var data = [chartData];

      var container = d3.select(this),
          that = this;

      var availableWidth = (width || parseInt(container.style('width'), 10) || 960) - margin.left - margin.right,
          availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom;

      chart.update = function() { container.transition().duration(300).call(chart); };
      chart.container = this;

      //------------------------------------------------------------
      // Display noData message if there's nothing to show.

      if (!data || !data.length || !data.filter(function(d) { return d && d.children.length; }).length) {
        container.select('.nvd3.nv-wrap').remove();
        var noDataText = container.selectAll('.nv-noData').data([chart.strings().noData]);

        noDataText.enter().append('text')
          .attr('class', 'nvd3 nv-noData')
          .attr('dy', '-.7em')
          .style('text-anchor', 'middle');

        noDataText
          .attr('x', margin.left + availableWidth / 2)
          .attr('y', margin.top + availableHeight / 2)
          .text(function(d) { return d; });

        return chart;
      } else {
        container.selectAll('.nv-noData').remove();
      }

      //------------------------------------------------------------

      //remove existing colors from default color array, if any
      if (colorData === 'data') {
        removeColors(data[0]);
      }


      //------------------------------------------------------------
      // Setup containers and skeleton of chart

      var wrap = container.selectAll('g.nv-wrap.nv-treemapWithLegend').data(data);
      var gEnter = wrap.enter().append('g').attr('class', 'nvd3 nv-wrap nv-treemapWithLegend').append('g');
      var g = wrap.select('g');

      gEnter.append('rect').attr('class', 'nv-background')
        .attr('x', -margin.left)
        .attr('y', -margin.top)
        .attr('width', availableWidth + margin.left + margin.right)
        .attr('height', availableHeight + margin.top + margin.bottom)
        .attr('fill', '#FFF');

      gEnter.append('g').attr('class', 'nv-treemapWrap');

      //------------------------------------------------------------


      //------------------------------------------------------------
      // Title & Legend

      var titleHeight = 0,
          legendHeight = 0;

      if (showLegend) {
        gEnter.append('g').attr('class', 'nv-legendWrap');

        legend
          .id('legend_' + chart.id())
          .strings(chart.strings().legend)
          .width(availableWidth + margin.left)
          .height(availableHeight);

        g.select('.nv-legendWrap')
          .datum(data)
          .call(legend);

        legendHeight = legend.height() + 10;

        if (margin.top !== legendHeight + titleHeight) {
          margin.top = legendHeight + titleHeight;
          availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom;
        }

        g.select('.nv-legendWrap')
          .attr('transform', 'translate(' + (-margin.left) + ',' + (-margin.top) + ')');
      }

      if (showTitle && properties.title) {
        gEnter.append('g').attr('class', 'nv-titleWrap');

        g.select('.nv-title').remove();

        g.select('.nv-titleWrap')
          .append('text')
            .attr('class', 'nv-title')
            .attr('x', 0)
            .attr('y', 0)
            .attr('text-anchor', 'start')
            .text(properties.title)
            .attr('stroke', 'none')
            .attr('fill', 'black');

        titleHeight = parseInt(g.select('.nv-title').style('height'), 10) +
          parseInt(g.select('.nv-title').style('margin-top'), 10) +
          parseInt(g.select('.nv-title').style('margin-bottom'), 10);

        if (margin.top !== titleHeight + legendHeight) {
          margin.top = titleHeight + legendHeight;
          availableHeight = (height || parseInt(container.style('height'), 10) || 400) - margin.top - margin.bottom;
        }

        g.select('.nv-titleWrap')
          .attr('transform', 'translate(0,' + (-margin.top + parseInt(g.select('.nv-title').style('height'), 10)) + ')');
      }

      //------------------------------------------------------------


      //------------------------------------------------------------

      wrap.attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');


      //------------------------------------------------------------
      // Main Chart Component(s)

      treemap
        .width(availableWidth)
        .height(availableHeight);


      var treemapWrap = g.select('.nv-treemapWrap')
          .datum(data.filter(function(d) { return !d.disabled; }));

      treemapWrap.transition().call(treemap);

      //------------------------------------------------------------



      //============================================================
      // Event Handling/Dispatching (in chart's scope)
      //------------------------------------------------------------

      legend.dispatch.on('legendClick', function(d, i) {
        d.disabled = !d.disabled;

        if (!data.filter(function(d) { return !d.disabled; }).length) {
          data.map(function(d) {
            d.disabled = false;
            wrap.selectAll('.nv-series').classed('disabled', false);
            return d;
          });
        }

        container.transition().duration(300).call(chart);
      });

      dispatch.on('tooltipShow', function(eo) {
        if (tooltips) {
          showTooltip(eo, that.parentNode);
        }
      });

      dispatch.on('tooltipMove', function(eo) {
        if (tooltip) {
          nv.tooltip.position(that.parentNode, tooltip, eo.pos);
        }
      });

      dispatch.on('tooltipHide', function() {
        if (tooltips) {
          nv.tooltip.cleanup();
        }
      });

      //============================================================

      function removeColors(d) {
        var i, l;
        if (d.color && colorArray.indexOf(d.color) !== -1) {
          colorArray.splice(colorArray.indexOf(d.color), 1);
        }
        if (d.children) {
          l = d.children.length;
          for (i = 0; i < l; i += 1) {
            removeColors(d.children[i]);
          }
        }
      }

    });

    return chart;
  }


  //============================================================
  // Event Handling/Dispatching (out of chart's scope)
  //------------------------------------------------------------

  treemap.dispatch.on('elementMouseover', function(eo) {
    eo.pos = [eo.pos[0] + margin.left, eo.pos[1] + margin.top];
    dispatch.tooltipShow(eo);
  });

  treemap.dispatch.on('elementMousemove', function(eo) {
    dispatch.tooltipMove(eo);
  });

  treemap.dispatch.on('elementMouseout', function() {
    dispatch.tooltipHide();
  });

  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  // expose chart's sub-components
  chart.dispatch = dispatch;
  chart.legend = legend;
  chart.treemap = treemap;

  d3.rebind(chart, treemap, 'x', 'y', 'xDomain', 'yDomain', 'forceX', 'forceY', 'clipEdge', 'id', 'delay', 'leafClick', 'getSize', 'getName', 'groups', 'color', 'fill', 'classes', 'gradient', 'direction');

  chart.colorData = function(_) {
    if (!arguments.length) { return colorData; }

    var type = arguments[0],
        params = arguments[1] || {};
    var color = function(d, i) {
          var c = (type === 'data' && d.color) ? {color: d.color} : {};
          return nv.utils.getColor(colorArray)(c, i);
        };
    var classes = function(d, i) {
          return 'nv-child';
        };

    switch (type) {
      case 'graduated':
        color = function(d, i, l) {
          return d3.interpolateHsl(d3.rgb(params.c1), d3.rgb(params.c2))(i / l);
        };
        break;
      case 'class':
        color = function() {
          return 'inherit';
        };
        classes = function(d, i) {
          var iClass = (i * (params.step || 1)) % 14;
          iClass = (iClass > 9 ? '' : '0') + iClass;
          return 'nv-child ' + (d.className || 'nv-fill' + iClass);
        };
        break;
    }

    var fill = (!params.gradient) ? color : function(d, i) {
      var p = {orientation: params.orientation || 'horizontal', position: params.position || 'base'};
      return treemap.gradient(d, i, p);
    };

    treemap.color(color);
    treemap.fill(fill);
    treemap.classes(classes);

    legend.color(color);
    legend.classes(classes);

    colorData = arguments[0];

    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) { return getX; }
    getX = _;
    treemap.x(_);
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) { return getY; }
    getY = _;
    treemap.y(_);
    return chart;
  };

  chart.margin = function(_) {
    if (!arguments.length) { return margin; }
    margin.top    = typeof _.top    !== 'undefined' ? _.top    : margin.top;
    margin.right  = typeof _.right  !== 'undefined' ? _.right  : margin.right;
    margin.bottom = typeof _.bottom !== 'undefined' ? _.bottom : margin.bottom;
    margin.left   = typeof _.left   !== 'undefined' ? _.left   : margin.left;
    return chart;
  };

  chart.width = function(_) {
    if (!arguments.length) { return width; }
    width = _;
    return chart;
  };

  chart.height = function(_) {
    if (!arguments.length) { return height; }
    height = _;
    return chart;
  };

  chart.showTitle = function(_) {
    if (!arguments.length) { return showTitle; }
    showTitle = _;
    return chart;
  };

  chart.showLegend = function(_) {
    if (!arguments.length) { return showLegend; }
    showLegend = _;
    return chart;
  };

  chart.tooltip = function(_) {
    if (!arguments.length) { return tooltip; }
    tooltip = _;
    return chart;
  };

  chart.tooltips = function(_) {
    if (!arguments.length) { return tooltips; }
    tooltips = _;
    return chart;
  };

  chart.tooltipContent = function(_) {
    if (!arguments.length) { return tooltipContent; }
    tooltipContent = _;
    return chart;
  };

  chart.strings = function(_) {
    if (!arguments.length) {
      return strings;
    }
    for (var prop in _) {
      if (_.hasOwnProperty(prop)) {
        strings[prop] = _[prop];
      }
    }
    return chart;
  };

  //============================================================

  return chart;
};

nv.models.tree = function() {

  // issues: 1. zoom slider doesn't zoom on chart center
  // orientation
  // bottom circles

  // all hail, stepheneb
  // https://gist.github.com/1182434
  // http://mbostock.github.com/d3/talk/20111018/tree.html
  // https://groups.google.com/forum/#!topic/d3-js/-qUd_jcyGTw/discussion
  // http://ajaxian.com/archives/foreignobject-hey-youve-got-html-in-my-svg
  // [possible improvements @ http://bl.ocks.org/robschmuecker/7880033]

  //============================================================
  // Public Variables with Default Settings
  //------------------------------------------------------------

  // specific to org chart
  var r = 5.5,
    padding = {'top': 10, 'right': 10, 'bottom': 10, 'left': 10}, // this is the distance from the edges of the svg to the chart,
    duration = 300,
    zoomExtents = {'min': 0.25, 'max': 2},
    nodeSize = {'width': 100, 'height': 50},
    nodeImgPath = '../img/',
    nodeRenderer = function(d) { return '<div class="nv-tree-node"></div>'; },
    zoomCallback = function(d) { return; },
    horizontal = false;

  var id = Math.floor(Math.random() * 10000), //Create semi-unique ID in case user doesn't select one,
    color = function (d, i) { return nv.utils.defaultColor()(d, i); },
    fill = function(d, i) { return color(d,i); },
    gradient = function(d, i) { return color(d,i); },

    setX = function(d, v) { d.x = v; },
    setY = function(d, v) { d.y = v; },
    setX0 = function(d, v) { if (horizontal) { d.y0 = v; } else { d.x0 = v; } },
    setY0 = function(d, v) { if (horizontal) { d.x0 = v; } else { d.y0 = v; } },

    getX = function(d) { return horizontal ? d.y : d.x; },
    getY = function(d) { return horizontal ? d.x : d.y; },
    getX0 = function(d) { return horizontal ? d.y0 : d.x0; },
    getY0 = function(d) { return horizontal ? d.x0 : d.y0; },

    getId = function(d) { return d.id; },

    fillGradient = function(d, i) {
        return nv.utils.colorRadialGradient(d, i, 0, 0, '35%', '35%', color(d, i), wrap.select('defs'));
    },
    useClass = false,
    valueFormat = d3.format(',.2f'),
    showLabels = true,
    dispatch = d3.dispatch('chartClick', 'elementClick', 'elementDblClick', 'elementMouseover', 'elementMouseout');

  //============================================================

  function chart(selection)
  {
    selection.each(function(data) {

      var diagonal = d3.svg.diagonal()
            .projection(function(d) {
              return [getX(d), getY(d)];
            });

      var zoom = null;
      chart.setZoom = function() {
        zoom = d3.behavior.zoom()
                    .scaleExtent([zoomExtents.min, zoomExtents.max])
                    .on('zoom', function() {
                      treeWrapper.attr('transform', 'translate(' + d3.event.translate + ')scale(' + d3.event.scale + ')');
                      zoomCallback(d3.event.scale);
                    });
      };
      chart.setZoom();

      //------------------------------------------------------------
      // Setup svgs and skeleton of chart

      var svg = d3.select(this);
      var availableSize = { // the size of the svg container minus padding
            'width': parseInt(svg.style('width'), 10) - padding.left - padding.right,
            'height': parseInt(svg.style('height'), 10) - padding.top - padding.bottom
          };

      var wrap = svg.selectAll('.nv-wrap').data([1]);
      var wrapEnter = wrap.enter().append('g')
            .attr('class', 'nvd3 nv-wrap nv-treeChart')
            .attr('id', 'nv-chart-' + id);
      wrap.call(zoom);

      wrapEnter.append('defs');
      var defsEnter = wrap.select('defs');

      wrapEnter.append('svg:rect')
            .attr('class', 'nv-chartBackground')
            .attr('width', availableSize.width)
            .attr('height', availableSize.height)
            .attr('transform', 'translate(' + padding.left + ',' + padding.top + ')')
            .style('fill', 'transparent');
      var backg = wrap.select('.nv-chartBackground');

      var gEnter = wrapEnter.append('g')
            .attr('class', 'nv-chartWrap');
      var treeWrapper = wrap.select('.nv-chartWrap');

      gEnter.append('g')
            .attr('class', 'nv-tree');
      var treeChart = wrap.select('.nv-tree');

      // Compute the new tree layout.
      var tree = d3.layout.tree()
            .size(null)
            .nodeSize([(horizontal ? nodeSize.height : nodeSize.width), 1])
            .separation(function separation(a, b) {
              return a.parent == b.parent ? 1 : 1;
            });

      data.x0 = data.x0 || 0;
      data.y0 = data.y0 || 0;

      var _data = data;

      var nodes = null;

      chart.resize = function() {
        chart.reset();

        // the size of the svg container minus padding
        availableSize = {
          'width': parseInt(svg.style('width'), 10) - padding.left - padding.right,
          'height': parseInt(svg.style('height'), 10) - padding.top - padding.bottom
        };

        // the size of the chart itself
        var size = [
              Math.abs(d3.min(nodes, getX)) + Math.abs(d3.max(nodes, getX)) + nodeSize.width,
              Math.abs(d3.min(nodes, getY)) + Math.abs(d3.max(nodes, getY)) + nodeSize.height
            ],

            // initial chart scale to fit chart in container
            xScale = availableSize.width / size[0],
            yScale = availableSize.height / size[1],
            scale = d3.min([xScale, yScale]),

            // initial chart translation to position chart in the center of container
            center = [
              Math.abs(d3.min(nodes, getX)) +
                (xScale < yScale ? 0 : (availableSize.width / scale - size[0]) / 2),
              Math.abs(d3.min(nodes, getY)) +
                (xScale < yScale ? (availableSize.height / scale - size[1]) / 2 : 0)
            ],

            offset = [
              nodeSize.width / (horizontal ? 1 : 2),
              nodeSize.height / (horizontal ? 2 : 1)
            ],

            translate = [
              (center[0] + offset[0]) * scale + padding.left / (horizontal ? 2 : 1),
              (center[1] + offset[1]) * scale + padding.top / (horizontal ? 1 : 2)
            ];

        backg
          .attr('width', availableSize.width)
          .attr('height', availableSize.height);

        treeChart.attr('transform', 'translate(' + translate + ')scale(' + scale + ')');
      };

      chart.orientation = function(orientation) {
        horizontal = (orientation === 'horizontal' || !horizontal ? true : false);
        tree.nodeSize([(horizontal ? nodeSize.height : nodeSize.width), 1]);
        chart.update(_data);
      };

      chart.showall = function() {
        function expandAll(d) {
          if ((d.children && d.children.length) || (d._children && d._children.length)) {
            if (d._children && d._children.length) {
              d.children = d._children;
              d._children = null;
            }
            d.children.forEach(expandAll);
          }
        }
        expandAll(_data);
        chart.update(_data);
      };

      chart.reset = function() {
        chart.setZoom();
        zoom.translate([0, 0]).scale(1);
        wrap.call(zoom);
        treeWrapper.attr('transform', 'translate(' + [0, 0] + ')scale(' + 1 + ')');
      };

      chart.zoomStep = function(step) {
        var level = zoom.scale() + step;
        return this.zoomLevel(level);
      };

      chart.zoomLevel = function(level) {
        var scale = Math.min(Math.max(level, zoomExtents.min), zoomExtents.max),

            prevScale = zoom.scale(),
            prevTrans = zoom.translate(),
            treeBBox = backg.node().getBBox(),

            size = [
              treeBBox.width,
              treeBBox.height
            ],

            offset = [
              (size[0] - size[0] * scale) / 2,
              (size[1] - size[1] * scale) / 2
            ],

            shift = [
              scale * (prevTrans[0] - (size[0] - size[0] * prevScale) / 2) / prevScale,
              scale * (prevTrans[1] - (size[1] - size[1] * prevScale) / 2) / prevScale
            ],

            translate = [
              offset[0] + shift[0],
              offset[1] + shift[1]
            ];

        zoom.translate(translate).scale(scale);
        treeWrapper.attr('transform', 'translate(' + translate + ')scale(' + scale + ')');

        return scale;
      };

      chart.zoomScale = function() {
        return zoom.scale();
      };

      chart.filter = function(node) {
        var __data = {}
          , found = false;

        function findNode(d) {
          if (getId(d) === node) {
            __data = d;
            found = true;
          } else if (!found && d.children) {
            d.children.forEach(findNode);
          }
        }

        // Initialize the display to show a few nodes.
        findNode(data);

        __data.x0 = 0;
        __data.y0 = 0;

        _data = __data;

        chart.update(_data);
      };

      chart.update = function(source) {
        // Click tree node.
        function leafClick(d) {
          toggle(d);
          chart.update(d);
        }

        // Toggle children.
        function toggle(d) {
          if (d.children) {
            d._children = d.children;
            d.children = null;
          } else {
            d.children = d._children;
            d._children = null;
          }
        }

        nodes = tree.nodes(_data);
        var root = nodes[0];

        nodes.forEach(function(d) {
          setY(d, d.depth * 2 * (horizontal ? nodeSize.width : nodeSize.height));
        });

        // Update the nodes…
        var node = treeChart.selectAll('g.nv-card')
              .data(nodes, getId);

        // Enter any new nodes at the parent's previous position.
        var nodeEnter = node.enter().append('svg:g')
              .attr('class', 'nv-card')
              .attr('id', function(d) { return 'nv-card-' + getId(d); })
              .attr('transform', function(d) {
                if (getY(source) === 0) {
                  return 'translate(' + getX(root) + ',' + getY(root) + ')';
                } else {
                  return 'translate(' + getX0(source) + ',' + getY0(source) + ')';
                }
              });

        // node content
        nodeEnter.append('foreignObject').attr('class', 'nv-foreign-object')
            .attr('width', 1)
            .attr('height', 1)
            .attr('x', -1)
            .attr('y', -1)
            .attr('externalResourcesRequired', true)
          .append('xhtml:body')
            .style('font', '14px "Helvetica Neue"')
            .html(nodeRenderer);

        // node circle
        var xcCircle = nodeEnter.append('svg:g').attr('class', 'nv-expcoll')
              .style('opacity', 1e-6)
              .on('click', leafClick);
            xcCircle.append('svg:circle').attr('class', 'nv-circ-back')
              .attr('r', r);
            xcCircle.append('svg:line').attr('class', 'nv-line-vert')
              .attr('x1', 0).attr('y1', 0.5 - r).attr('x2', 0).attr('y2', r - 0.5)
              .style('stroke', '#bbb');
            xcCircle.append('svg:line').attr('class', 'nv-line-hrzn')
              .attr('x1', 0.5 - r).attr('y1', 0).attr('x2', r - 0.5).attr('y2', 0)
              .style('stroke', '#fff');

        //Transition nodes to their new position.
        var nodeUpdate = node.transition()
              .duration(duration)
              .attr('transform', function(d) {
                return 'translate(' + getX(d) + ',' + getY(d) + ')';
              });
            nodeUpdate.select('.nv-expcoll')
              .style('opacity', function(d) {
                return ((d.children && d.children.length) || (d._children && d._children.length)) ? 1 : 0;
              });
            nodeUpdate.select('.nv-circ-back')
              .style('fill', function(d) {
                return (d._children && d._children.length) ? '#777' : (d.children ? '#bbb' : 'none');
              });
            nodeUpdate.select('.nv-line-vert')
              .style('stroke', function(d) {
                return (d._children && d._children.length) ? '#fff' : '#bbb';
              });
            nodeUpdate.selectAll('.nv-foreign-object')
              .attr('width', nodeSize.width)
              .attr('height', nodeSize.height)
              .attr('x', (horizontal ? -nodeSize.width + r : -nodeSize.width / 2))
              .attr('y', (horizontal ? -nodeSize.height / 2 + r : -nodeSize.height + r * 2));

        // Transition exiting nodes to the parent's new position.
        var nodeExit = node.exit().transition()
              .duration(duration)
              .attr('transform', function(d) {
                return 'translate(' + getX(source) + ',' + getY(source) + ')';
              })
              .remove();
            nodeExit.selectAll('.nv-expcoll')
              .style('stroke-opacity', 1e-6);
            nodeExit.selectAll('.nv-foreign-object')
              .attr('width', 1)
              .attr('height', 1)
              .attr('x', -1)
              .attr('y', -1);

        // Update the links
        var link = treeChart.selectAll('path.link')
              .data(tree.links(nodes), function(d) {
                return getId(d.source) + '-' + getId(d.target);
              });

            // Enter any new links at the parent's previous position.
            link.enter().insert('svg:path', 'g')
              .attr('class', 'link')
              .attr('d', function(d) {
                var o = getY(source) === 0 ? {x: source.x, y: source.y} : {x: source.x0, y: source.y0};
                return diagonal({ source: o, target: o });
              });

            // Transition links to their new position.
            link.transition()
              .duration(duration)
              .attr('d', diagonal);

            // Transition exiting nodes to the parent's new position.
            link.exit().transition()
              .duration(duration)
              .attr('d', function(d) {
                var o = { x: source.x, y: source.y };
                return diagonal({ source: o, target: o });
              })
              .remove();

        // Stash the old positions for transition.
        nodes
          .forEach(function(d) {
            setX0(d, getX(d));
            setY0(d, getY(d));
          });

        chart.resize();
      };

      chart.gradient(fillGradient);

      chart.update(_data);

    });

    return chart;
  }


  //============================================================
  // Expose Public Variables
  //------------------------------------------------------------

  chart.dispatch = dispatch;

  chart.color = function(_) {
    if (!arguments.length) return color;
    color = _;
    return chart;
  };
  chart.fill = function(_) {
    if (!arguments.length) return fill;
    fill = _;
    return chart;
  };
  chart.gradient = function(_) {
    if (!arguments.length) return gradient;
    gradient = _;
    return chart;
  };
  chart.useClass = function(_) {
    if (!arguments.length) return useClass;
    useClass = _;
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

  chart.values = function(_) {
    if (!arguments.length) return getValues;
    getValues = _;
    return chart;
  };

  chart.x = function(_) {
    if (!arguments.length) return getX;
    getX = _;
    return chart;
  };

  chart.y = function(_) {
    if (!arguments.length) return getY;
    getY = d3.functor(_);
    return chart;
  };

  chart.showLabels = function(_) {
    if (!arguments.length) return showLabels;
    showLabels = _;
    return chart;
  };

  chart.id = function(_) {
    if (!arguments.length) return id;
    id = _;
    return chart;
  };

  chart.valueFormat = function(_) {
    if (!arguments.length) return valueFormat;
    valueFormat = _;
    return chart;
  };

  chart.labelThreshold = function(_) {
    if (!arguments.length) return labelThreshold;
    labelThreshold = _;
    return chart;
  };

  // ORG

  chart.radius = function(_) {
    if (!arguments.length) return r;
    r = _;
    return chart;
  };

  chart.duration = function(_) {
    if (!arguments.length) return duration;
    duration = _;
    return chart;
  };

  chart.zoomExtents = function(_) {
    if (!arguments.length) return zoomExtents;
    zoomExtents = _;
    return chart;
  };

  chart.zoomCallback = function(_) {
    if (!arguments.length) return zoomCallback;
    zoomCallback = _;
    return chart;
  };

  chart.padding = function(_) {
    if (!arguments.length) return padding;
    padding = _;
    return chart;
  };

  chart.nodeSize = function(_) {
    if (!arguments.length) return nodeSize;
    nodeSize = _;
    return chart;
  };

  chart.nodeImgPath = function(_) {
    if (!arguments.length) return nodeImgPath;
    nodeImgPath = _;
    return chart;
  };

  chart.nodeRenderer = function(_) {
    if (!arguments.length) return nodeRenderer;
    nodeRenderer = _;
    return chart;
  };

  chart.horizontal = function(_) {
    if (!arguments.length) return horizontal;
    horizontal = _;
    return chart;
  };

  chart.getId = function(_) {
    if (!arguments.length) return getId;
    getId = _;
    return chart;
  };
  //============================================================

  return chart;
};
})();