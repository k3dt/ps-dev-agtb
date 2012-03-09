/*
* Superfish v1.4.8 - jQuery menu widget
* Copyright (c) 2008 Joel Birch
*
* Dual licensed under the MIT and GPL licenses:
* http://www.opensource.org/licenses/mit-license.php
* http://www.gnu.org/licenses/gpl.html
*
* CHANGELOG: http://users.tpg.com.au/j_birch/plugins/superfish/changelog.txt
*/

; (function($) {
    $.fn.superfish = function(op) {

        var sf = $.fn.superfish,
        c = sf.c,
        menuActive = false,
        $arrow = $(['<span class="', c.arrowClass, '"> &#187;</span>'].join('')),
        click = function(evt) {
        	$(".subnav.ddopen").hide();
            var $$ = $(this),
            menu = getMenu($$),
            o = sf.op;
            if (o.firstOnClick && !menuActive && $$.parent()[0] == menu)
            {
                menuActive = true;
                clearTimeout(menu.sfTimer);

                $$.showSuperfishUl().siblings().hideSuperfishUl();
                //return false;
                // prevent redirect to anchor target href
                evt.preventDefault();
            }
        },
        over = function() {
            var $$ = $(this),
            menu = getMenu($$),
            o = sf.op;
            if (!o.firstOnClick || menuActive || $$.parent()[0] != menu)
            {
                clearTimeout(menu.sfTimer);
                $$.showSuperfishUl().siblings().hideSuperfishUl();
            }
        },
        out = function() {
            var $$ = $(this),
            menu = getMenu($$),
            o = sf.op,
            $menu = $(menu);
            clearTimeout(menu.sfTimer);
            menu.sfTimer = $menu.hasClass(sf.defaults['retainClass']) ? null : setTimeout(function() {
                if($menu.hasClass(sf.defaults['retainClass']) === false) {
                    o.retainPath = ($.inArray($$[0], o.$path) > -1);
                    $$.hideSuperfishUl();
                    if (o.$path.length && $$.parents(['li.', o.hoverClass].join('')).length < 1)
                    {
                        over.call(o.$path);
                    }
                    else
                    {
                        menuActive = false;
                    }
                }

            },
            o.delay);
        },
        getMenu = function($menu) {
            var menu = $menu.hasClass(sf.menuClass) ? $menu[0] : $menu.parents(['ul.', c.menuClass, ':first'].join(''))[0];
            if(!menu)
                return $menu[0];
            sf.op = sf.o[menu.serial];
            return menu;
        },
        addArrow = function($a) {
            $a.addClass(c.anchorClass).append($arrow.clone());
        };
        sf.getMenu = getMenu;
        return this.each(function() {
            var s = this.serial = sf.o.length;
            var o = $.extend({},
            sf.defaults, op);
            o.$path =
            $('li.' + o.pathClass, this).slice(0, o.pathLevels).each
            (function() {

                $(this).addClass([o.hoverClass, c.bcClass].join(' '))

                .filter('li:has(ul)').removeClass(o.pathClass);
            });
            sf.o[s] = sf.op = o;

			if(o.firstOnClick){
				$('li:has(ul)', this).not('li:has( > .' + sf.ignoreClass + ')')['click'](click);
			} else {
				$('li:has(ul)', this).not('li:has( > .' + sf.ignoreClass + ')')[($.fn.hoverIntent && !o.disableHI) ? 'hoverIntent' : 'hover'](over, out);
			}
            
            $('li:has(ul)', this)
            .click(click)
            .each(function() {
                if (o.autoArrows) addArrow(
                $('>a:first-child', this));
            })
            .not('.' + c.bcClass)
            .hideSuperfishUl();

            var $a = $('a', this);
            $a.each(function(i) {
                var $li = $a.eq(i).parents('li');

                $a.eq(i).attr("tabindex",-1).focus(function() {
                    over.call($li);
                }).blur(function()
                {
                    out.call($li);
                });
                
                
                if(o.firstOnClick) {
	                $a.eq(i).click(function(event)
	                {
					  event.preventDefault();
					  if ( !$a.eq(i).hasClass("sf-with-ul") || $li.children('ul').size() == 0) {
					    SUGAR.ajaxUI.loadContent(this.href);
					  }
					});
					
					
					$a.eq(i).dblclick(function(event)
	                {
	                    SUGAR.ajaxUI.loadContent(this.href);
					});
                }
				
            });
            o.onInit.call(this);

        }).each(function() {
            var menuClasses = [c.menuClass];
            if (sf.op.dropShadows && !($.browser.msie &&
            $.browser.version <
            7)) menuClasses.push(c.shadowClass);
            $(this).addClass(menuClasses.join(' '));
        });
    };

    var sf = $.fn.superfish;
    sf.o = [];
    sf.op = {};
    sf.counter = 0;
    sf.IE7fix = function() {
        var o = sf.op;
        if ($.browser.msie && $.browser.version > 6 && o.dropShadows &&
        o.animation.opacity != undefined)
        this.toggleClass(sf.c.shadowClass + '-off');
    };
    sf.cssValue = function($css) {
        if(this.length == 0)
            return 0;
        var _val = parseInt(this.css($css).replace("px", ""));
        return (_val) ? _val : 0;
    };
    sf.IEfix = function($ul) {
        if ($.browser.msie && $.browser.version > 6) {
            if($ul) {
                this.each(function(){
                    var $$ = $(this),
                        o = sf.op,
                        _id = $$.attr("ul-child-id") ? $$.attr("ul-child-id") : ($ul.attr('id')) ? $ul.attr('id') : o.megamenuID ? o.megamenuID + ++sf.counter : 'megamenu' + ++sf.counter,
                        _top = $$.position().top + $$.outerHeight() + 1,
                        _left = $$.offset().left - sf.cssValue.call($ul, "border-left-width"),
                        $menu = $('ul.' + sf.c.menuClass + ':visible');
                    if($$.css('position') == 'static') {
                        _left += $$.outerWidth() + sf.cssValue.call($ul, "border-right-width");
                        $ul.addClass('sf-sub-modulelist').on('mouseover', function(){
                                $$.addClass(sf.defaults['retainClass']);
                            }).on('mouseout', function(){
                                $$.removeClass(sf.defaults['retainClass']);
                                $('ul.' + sf.c.menuClass + ':visible').removeClass(sf.defaults['retainClass'])[0].sfTimer = setTimeout(function(){
                                    $$.hideSuperfishUl();
                                    $('ul.' + sf.c.menuClass + ':visible > li').hideSuperfishUl();
                                }, o.delay);
                            });
                    }

                    $('body').append($ul.attr("id", _id).css({
                        top: _top,
                        left:_left,
                        position: 'fixed'
                        }).on('mouseover',function(){
                            var menu = sf.getMenu($menu),
                                o = sf.op;
                            clearTimeout(menu.sfTimer);
                            if( $(menu).hasClass(sf.defaults['retainClass']) === false )
                                $(menu).addClass(sf.defaults['retainClass']);
                        }).on('mouseout', function(){
                            var menu = sf.getMenu($menu),
                                o = sf.op;
                            clearTimeout(menu.sfTimer);
                            menu.sfTimer = setTimeout(function() {
                                $$.hideSuperfishUl();
                                $(menu).removeClass(sf.defaults['retainClass']);
                            }, o.delay)
                        })
                    );
                    $$.attr("ul-child-id", _id);
                });

            } else {
                this.each(function(){
                    var _id = $(this).attr("ul-child-id");
                    $(this).append($("body>#"+_id).off('mouseover mouseout'));
                });
            }
        }
    };
    sf.c = {
        bcClass: 'sf-breadcrumb',
        menuClass: 'sf-js-enabled',
        anchorClass: 'sf-with-ul',
        arrowClass: 'sf-sub-indicator',
        shadowClass: 'sf-shadow'
    };
    sf.defaults = {
        hoverClass: 'sfHover',
        retainClass: 'retainThisItem',
        ignoreClass: 'none',
        pathClass: 'overideThisToUse',
        pathLevels: 8,
        delay: 800,
        animation: {
            opacity: 'show'
        },
        speed: 'normal',
        autoArrows: true,
        dropShadows: true,
        disableHI: false,
        // true disables hoverIntent detection
        onInit: function() {},
        // callback functions
        onBeforeShow: function() {},
        onShow: function() {},
        onHide: function() {},
        firstOnClick: false
        // true - open first level on click (like classic application menu)

    };
    $.fn.extend({
        hideSuperfishUl: function() {
            var o = sf.op,
            not = (o.retainPath === true) ? o.$path: '';
            o.retainPath = false;
            sf.IEfix.call(this);
            var $ul = $(['li.', o.hoverClass].join(''), this).add(this).not
                (not).removeClass(o.hoverClass).find('>ul').hide().css('visibility', 'hidden');
            o.onHide.call($ul);
            return this;
        },
        showSuperfishUl: function() {
            var o = sf.op,
            sh = sf.c.shadowClass + '-off',
            $ul = this.addClass(o.hoverClass).find('>ul:hidden').css('visibility', 'visible');
            sf.IE7fix.call($ul);
            o.onBeforeShow.call($ul);
            sf.IEfix.call(this, $ul);
            $ul.animate(o.animation, o.speed,
            function() {
                sf.IE7fix.call($ul);
                o.onShow.call($ul);
            });
            return this;
        }
    });

})(jQuery);