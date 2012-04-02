(function($) {
	// swipe for top nav
	$('#logo').bind('touchmove', function (e) {
		e.preventDefault();} 
	);
	$('#logo').swipeRight(function () {
		$('html').find('body').addClass('onL');
	});
	$('#logo').swipeLeft(function () {
		$('html').find('body').removeClassClass('onL');
	});
	$('#create').bind('touchmove', function (e) {
		e.preventDefault();}
	);
	$('#create').swipeLeft(function () {
		$('html').find('body').addClass('onR');
	});
	$('#create').swipeRight(function () {
		$('html').find('body').removeClass('onR');
	});
	$('#moduleList').swipeLeft(function () {
		$('html').find('body').removeClass('onL');
	});
	$('#createList').bind('touchmove', function (e) {
		e.preventDefault();}
	);
	$('#createList').swipeRight(function () {
		$('html').find('body').removeClass('onR');
	});
	$('#search').bind('touchmove', function (e) {
		e.preventDefault();
	});
	$('#search').swipeDown(function () {
		$('body').find('#searchForm').toggleClass('hide');
	});	
    // trigger the module menu - this could be a tap function but zepto will not honor return false
    $('.cube').on('click', function () {
	      $('html').find('body').toggleClass('onL');
	      return false;
    });
    // trigger the module menu - this could be a tap function but zepto will not honor return false
    $('.launch').on('click', function () {
		$('html').find('body').toggleClass('onR');
		return false;
    });
	$('article').live('swipeLeft',function () {
		$(this).find('.grip').addClass('on');
		$(this).find('[id^=listing-action] span').removeClass('hide').addClass('on');
	});	
	$('article').live('swipeRight',function () {
	    $(this).find('.grip').removeClass('on');
	    $(this).find('[id^=listing-action] span').addClass('hide').removeClass('on');
	});	
    $('article .grip').live('click', function () {
	    $(this).next('.actions').toggleClass('hide');
	    $(this).toggleClass('on');
    })
    // search toggle
    $('.navbar').find('#search').on('click', function () {
    	$('body').find('#searchForm').toggleClass('hide');
	    return false;
    });
    $('#searchForm').find('.cancel').on('click', function () {
	    $('body').find('#searchForm').toggleClass('hide');
	    return false;
    });
    // fake phone for prototype
    $('#record-action').find('.icon-phone').on('click', function () {
	    $('body').append('<div class="over"><h4>Place a call</h4><p><a href="tel:605-334-2345" class="btn btn-large">Home (605)-334-2345</a></p><p><a class="btn btn-large">Mobile (605)-334-2345</a></p><p><a class="btn btn-large">Office (605)-334-2345</a></p><p><a href="" class="btn btn-inverse btn-large" id="cancel">Cancel</a></p></div>');
	    return false;
    });
    $('.over').find('#cancel').on('click', function () {
		$(this).remove();
		return false;
    });
    $('a[title=Remove]').live('click', function () {
		$(this).closest('article').addClass('deleted').anim({ translateX: window.innerWidth + 'px', opacity: '0'}, .5, 'ease-out');
		setTimeout(rmel,250);
		return false;
    });
  	$('.icon-star-empty, .icon-star').on('click', function () {
	    $(this).toggleClass('icon-star-empty').addClass('icon-star');
	    return false;
  	});
    $('#tour').on('click', function () {
      $(this).remove();
    });
    $('#back .back').on('click', function(){	
		if(history.length<=2) {
			window.location="./";
		}else{
		    window.history.back(-1);		
		}
	});
    $('#listing > article:last-child a.show_more_posts').on('click', function(e){
	    $(this).closest('article').remove();
	    $('#listing').append(more_posts+more_posts+more_posts+more_posts+more_posts+more_posts_link);
		return false;
	});
	var more_posts = '<article><i class="icon-star-empty"></i><div title="Perkin Kleiners"><a href="perkin_kleiners.html">Perkin Kleiners</a> is a <a href="100seat.html">100 seat plan</a> of 75K closing in 20 days at <a href="">quality</a> stage  </div><span id="listing-action-item1"><i class="grip">|||</i><span class="hide actions"><a href="" title="Log"><i class="icon-share icon-md"></i></a><a href="" title="Remove"><i class="icon-trash icon-md"></i></a></span></span></article>',
	    more_posts_link = '<article><div><a class="show_more_posts" href="">Show more posts...</a></div></article>';
	function rmel(){
      $('.deleted').remove();
	}
})(window.Zepto);