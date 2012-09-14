
nv.utils.windowSize = function()
{
    // Sane defaults
    var size = {width: 640, height: 480};

    // Earlier IE uses Doc.body
    if (document.body && document.body.offsetWidth) {
        size.width = document.body.offsetWidth;
        size.height = document.body.offsetHeight;
    }

    // IE can use depending on mode it is in
    if (document.compatMode=='CSS1Compat' &&
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
nv.utils.windowResize = function(fun)
{
  var oldresize = window.onresize;

  window.onresize = function(e) {
    if (typeof oldresize == 'function') oldresize(e);
    fun(e);
  }
}

// Backwards compatible way to implement more d3-like coloring of graphs.
// If passed an array, wrap it in a function which implements the old default
// behaviour
nv.utils.getColor = function(color)
{
    if (!arguments.length) return nv.utils.defaultColor(); //if you pass in nothing, get default colors back

    if( Object.prototype.toString.call( color ) === '[object Array]' )
        return function(d, i) { return d.color || color[i % color.length]; };
    else
        return color;
        //can't really help it if someone passes rubish as color
}

// Default color chooser uses the index of an object as before.
nv.utils.defaultColor = function()
{
    var colors = d3.scale.category20().range();
    return function(d, i) { return d.color || colors[i % colors.length] };
}

//gradient color
nv.utils.colorLinearGradient = function ( d, i, o, c, defs ) {
  var id = 'rg_gradient_'+i
    , grad = defs.select('#'+id);
  if ( !grad.empty() ){
    //grad.attr('r',r);
  }
  else
  {
    nv.utils.createLinearGradient( id, o, defs, [
      { 'offset': '0%','stop-color': d3.rgb(c).darker().toString(),  'stop-opacity': 1 },
      { 'offset': '20%', 'stop-color': d3.rgb(c).toString(), 'stop-opacity': 1 },
      { 'offset': '50%', 'stop-color': d3.rgb(c).brighter().toString(), 'stop-opacity': 1 },
      { 'offset': '80%', 'stop-color': d3.rgb(c).toString(), 'stop-opacity': 1 },
      { 'offset': '100%','stop-color': d3.rgb(c).darker().toString(),  'stop-opacity': 1 }
    ]);
  }
  return 'url(#'+ id +')';
}

// defs:definition container
// id:dynamic id for arc
// radius:outer edge of gradient
// stops: an array of attribute objects
nv.utils.createLinearGradient = function ( id, orientation, defs, stops )
{
  var x2 = orientation === 'horizontal' ? '0%' : '100%'
    , y2 = orientation === 'horizontal' ? '100%' : '0%'
    , grad
    = defs.append('linearGradient')
        .attr('id', id)
        .attr('x1', '0%')
        .attr('y1', '0%')
        .attr('x2', x2 )
        .attr('y2', y2 )
        //.attr('gradientUnits', 'userSpaceOnUse')
        .attr('spreadMethod', 'pad');

  for (var i=0;i<stops.length;i++)
  {
    var attrs = stops[i]
      , stop = grad.append('stop')
    for (var a in attrs)
    {
      if ( attrs.hasOwnProperty(a) ) stop.attr( a, attrs[a] );
    }
  }
}

nv.utils.colorRadialGradient = function ( d, i, x, y, r, s, c, defs ) {
  var id = 'rg_gradient_'+i
    , grad = defs.select('#'+id);
  if ( !grad.empty() ){
    grad.attr('r',r);
  }
  else
  {
    nv.utils.createRadialGradient( id, x, y, r, defs, [
      { 'offset': s, 'stop-color': d3.rgb(c).brighter().toString(), 'stop-opacity': 1 },
      { 'offset': '100%','stop-color': d3.rgb(c).darker().toString(),  'stop-opacity': 1 }
    ]);
  }
  return 'url(#'+ id +')';
}

nv.utils.createRadialGradient = function ( id, x, y, radius, defs, stops )
{
  var grad
    = defs.append('radialGradient')
        .attr('id', id)
        .attr('r', radius)
        .attr('cx', x)
        .attr('cy', y)
        .attr('gradientUnits', 'userSpaceOnUse')
        .attr('spreadMethod', 'pad');

  for (var i=0;i<stops.length;i++)
  {
    var attrs = stops[i]
      , stop = grad.append('stop')
    for (var a in attrs)
    {
      if ( attrs.hasOwnProperty(a) ) stop.attr( a, attrs[a] );
    }
  }
}

// From the PJAX example on d3js.org, while this is not really directly needed
// it's a very cool method for doing pjax, I may expand upon it a little bit,
// open to suggestions on anything that may be useful
nv.utils.pjax = function(links, content)
{
  d3.selectAll(links).on("click", function() {
    history.pushState(this.href, this.textContent, this.href);
    load(this.href);
    d3.event.preventDefault();
  });

  function load(href) {
    d3.html(href, function(fragment) {
      var target = d3.select(content).node();
      target.parentNode.replaceChild(d3.select(fragment).select(content).node(), target);
      nv.utils.pjax(links, content);
    });
  }

  d3.select(window).on("popstate", function() {
    if (d3.event.state) load(d3.event.state);
  });
}

// Creates a rectangle with rounded corners
nv.utils.roundedRectangle = function (x, y, width, height, radius)
{
  return "M" + x + "," + y
       + "h" + (width - radius*2)
       + "a" + radius + "," + radius + " 0 0 1 " + radius + "," + radius
       + "v" + (height - 2 - radius*2)
       + "a" + radius + "," + radius + " 0 0 1 " + -radius + "," + radius
       + "h" + (radius*2 - width)
       + "a" + -radius + "," + radius + " 0 0 1 " + -radius + "," + -radius
       + "v" + ( -height+radius*2 + 2 )
       + "a" + radius + "," + radius + " 0 0 1 " + radius + "," + -radius
       + "z";
}
