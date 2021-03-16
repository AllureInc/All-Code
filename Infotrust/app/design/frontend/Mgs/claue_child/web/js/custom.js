require([
	'jquery',
	'waypoints',
	'mage/translate'
], function(jQuery){
	(function($) {
		$.fn.appear = function(fn, options) {

			var settings = $.extend({

				//arbitrary data to pass to fn
				data: undefined,

				//call fn only on the first appear?
				one: true,

				// X & Y accuracy
				accX: 0,
				accY: 0

			}, options);

			return this.each(function() {

				var t = $(this);

				//whether the element is currently visible
				t.appeared = false;

				if (!fn) {

					//trigger the custom event
					t.trigger('appear', settings.data);
					return;
				}

				var w = $(window);

				//fires the appear event when appropriate
				var check = function() {

					//is the element hidden?
					if (!t.is(':visible')) {

						//it became hidden
						t.appeared = false;
						return;
					}

					//is the element inside the visible window?
					var a = w.scrollLeft();
					var b = w.scrollTop();
					var o = t.offset();
					var x = o.left;
					var y = o.top;

					var ax = settings.accX;
					var ay = settings.accY;
					var th = t.height();
					var wh = w.height();
					var tw = t.width();
					var ww = w.width();

					if (y + th + ay >= b &&
						y <= b + wh + ay &&
						x + tw + ax >= a &&
						x <= a + ww + ax) {

						//trigger the custom event
						if (!t.appeared) t.trigger('appear', settings.data);

					} else {

						//it scrolled out of view
						t.appeared = false;
					}
				};

				//create a modified fn with some additional logic
				var modifiedFn = function() {

					//mark the element as visible
					t.appeared = true;

					//is this supposed to happen only once?
					if (settings.one) {

						//remove the check
						w.unbind('scroll', check);
						var i = $.inArray(check, $.fn.appear.checks);
						if (i >= 0) $.fn.appear.checks.splice(i, 1);
					}

					//trigger the original fn
					fn.apply(this, arguments);
				};

				//bind the modified fn to the element
				if (settings.one) t.one('appear', settings.data, modifiedFn);
				else t.bind('appear', settings.data, modifiedFn);

				//check whenever the window scrolls
				w.scroll(check);

				//check whenever the dom changes
				$.fn.appear.checks.push(check);

				//check now
				(check)();
			});
		};

		//keep a queue of appearance checks
		$.extend($.fn.appear, {

			checks: [],
			timeout: null,

			//process the queue
			checkAll: function() {
				var length = $.fn.appear.checks.length;
				if (length > 0) while (length--) ($.fn.appear.checks[length])();
			},

			//check the queue asynchronously
			run: function() {
				if ($.fn.appear.timeout) clearTimeout($.fn.appear.timeout);
				$.fn.appear.timeout = setTimeout($.fn.appear.checkAll, 20);
			}
		});

		//run checks when these methods are called
		$.each(['append', 'prepend', 'after', 'before', 'attr',
			'removeAttr', 'addClass', 'removeClass', 'toggleClass',
			'remove', 'css', 'show', 'hide'], function(i, n) {
			var old = $.fn[n];
			if (old) {
				$.fn[n] = function() {
					var r = old.apply(this, arguments);
					$.fn.appear.run();
					return r;
				}
			}
		});
		
		$(document).ready(function(){
			$("[data-appear-animation]").each(function() {
				$(this).addClass("appear-animation");
				if($(window).width() > 767) {
					$(this).appear(function() {

						var delay = ($(this).attr("data-appear-animation-delay") ? $(this).attr("data-appear-animation-delay") : 1);

						if(delay > 1) $(this).css("animation-delay", delay + "ms");
						$(this).addClass($(this).attr("data-appear-animation"));
						$(this).addClass("animated");

						setTimeout(function() {
							$(this).addClass("appear-animation-visible");
						}, delay);

					}, {accX: 0, accY: -150});
				} else {
					$(this).addClass("appear-animation-visible");
				}
			});
			// MEGAMENU JS
			$('.nav-main-menu li.mega-menu-fullwidth.menu-2columns').hover(function(){
				if($(window).width() > 1199){
					var position = $(this).position();
					var widthMenu = $("#mainMenu").width() - position.left;
					if(widthMenu > 500){
						widthMenu = 500;
					}
					$(this).find('ul.dropdown-menu').width(widthMenu);
				}
			});
			
			$('.nav-main-menu .static-menu li > .toggle-menu a').click(function(){
				$(this).toggleClass('active');
				$(this).parent().parent().find('> ul').slideToggle();
			});
			// END MEGAMENU
			
			
			// Responsive header
			$('.action.nav-toggle').click(function(){
				if ($('html').hasClass('nav-open')) {
					$('html').removeClass('nav-open');
					setTimeout(function () {
						$('html').removeClass('nav-before-open');
					}, 300);
				} else {
					$('html').addClass('nav-before-open');
					setTimeout(function () {
						$('html').addClass('nav-open');
					}, 42);
				}
			});
			
			$('.close-nav-button').click(function(){
				$('html').removeClass('nav-open');
				setTimeout(function () {
					$('html').removeClass('nav-before-open');
				}, 300);
			});
			
			// Closed filter fixed
			$(document).on("click","#close-filter",function(e){
				$('.block.filter .filter-title .title').click();		
			});
			
			// Closed minicart 
			$(document).on("click","#close-minicart",function(e){
				$('.table-icon-menu .minicart-wrapper .action.showcart').click();
			});
			
			/* Shipping & Discount Code */
			$('.checkout-extra > .block > .title').click(function(){
				$('.checkout-extra > .block > .title').removeClass('active');
				$('.checkout-extra > .block > .content').removeClass('active');
				$(this).addClass('active');
				$(this).parent().find('> .content').addClass('active');
			});
			
			$(document).on("click",".products-grid .product-item-info .product-top > a",function(e){
				if($(window).width() < 992 && !$('.products-grid .product-item-info').hasClass('disable_hover_effect')){
					if(!$(this).hasClass('active')){
						$('.products-grid .product-item-info .product-top > a.active').removeClass('active');
						event.returnValue = false;
						event.preventDefault();
						$(this).addClass('active');
					}
				}
			});

			/*For phone number validation*/
			$("body.customer-address-form .action.submit.primary").click(function(e) {
				var num = $("input[name=telephone]").val();
				var codenum = '+'+$(".country.active").attr('data-dial-code') || $(".selected-flag").attr('title').split(": ")[1];
				var numLength = codenum.length;
				var num_after_replace = num; 

				if(num.indexOf(' ') >= 0)
				{
				    num = num.substr(num.indexOf(' ')+1);
				    num_after_replace = num;
				} else {
				    num = num.substr(num.indexOf(' ')+1);
				    num_after_replace = num.slice(numLength); 
				}
				num_after_replace = num_after_replace.replace(/\s/g, '');
				if((num_after_replace.length != window.phone_no_digits) || (num_after_replace.charAt(0) == 0)) {
				    var msgText = 'Please enter valid number.';
				    if((num_after_replace.length != window.phone_no_digits) && (num_after_replace.charAt(0) == 0)){
				    	msgText += ' Allowed phone number digit is not matched and phone number cannot start from zero.';
				    }
				    else if(num_after_replace.length != window.phone_no_digits){
				    	msgText += ' Allowed phone number digit is not matched.'; 
				    } else if(num_after_replace.charAt(0) == 0){
				    	msgText += ' Phone number cannot start from zero.';
				    }
				    var htmlText = '<div for="telephone" generated="true" class="mage-error" id="telephone-error">'+$.mage.__(msgText)+'</div>';
				    if($('#telephone-error').length)
				    {
				        $('#telephone-error').text('');
				        $('#telephone-error').html(htmlText);
				        e.preventDefault(e);
				    } 
				    else
				    {
				        $('.telephone').after(htmlText);
				        e.preventDefault(e);
				    }
				} else {
					$("#telephone-error").remove();
				}
		    });

			$("body.customer-address-form input[name=telephone]").change(function(e) {
				var num = $("input[name=telephone]").val();
				var codenum = '+'+$(".country.active").attr('data-dial-code') || $(".selected-flag").attr('title').split(": ")[1];
				var numLength = codenum.length;
				var num_after_replace = num; 

				if(num.indexOf(' ') >= 0)
				{
				    num = num.substr(num.indexOf(' ')+1);
				    num_after_replace = num;
				} else {
				    num = num.substr(num.indexOf(' ')+1);
				    num_after_replace = num.slice(numLength); 
				}
				num_after_replace = num_after_replace.replace(/\s/g, '');
				if((num_after_replace.length != window.phone_no_digits) || (num_after_replace.charAt(0) == 0)) {
				    var msgText = 'Please enter valid number.';
				    if((num_after_replace.length != window.phone_no_digits) && (num_after_replace.charAt(0) == 0)){
				    	msgText += ' Allowed phone number digit is not matched and phone number cannot start from zero.';
				    }
				    else if(num_after_replace.length != window.phone_no_digits){
				    	msgText += ' Allowed phone number digit is not matched.'; 
				    } else if(num_after_replace.charAt(0) == 0){
				    	msgText += ' Phone number cannot start from zero.';
				    }
				    var htmlText = '<div for="telephone" generated="true" class="mage-error" id="telephone-error">'+$.mage.__(msgText)+'</div>';
				    if($('#telephone-error').length)
				    {
				        $('#telephone-error').text('');
				        $('#telephone-error').html(htmlText);
				        e.preventDefault(e);
				    } 
				    else
				    {
				    	$('.telephone').after(htmlText);
				        e.preventDefault(e);
				    }
				} else {
					$("#telephone-error").remove();
				}
			});
		    /*For phone number validation*/

		    /* -- browser check script */
	    	// Opera 8.0+
			var isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
			// Firefox 1.0+
			var isFirefox = typeof InstallTrigger !== 'undefined';
			// Safari 3.0+ "[object HTMLElementConstructor]" 
			var isSafari = /constructor/i.test(window.HTMLElement) || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || (typeof safari !== 'undefined' && window['safari'].pushNotification));
			// Internet Explorer 6-11
			var isIE = /*@cc_on!@*/false || !!document.documentMode;
			// Edge 20+
			var isEdge = !isIE && !!window.StyleMedia;
			// Chrome 1 - 79
			var isChrome = !!window.chrome && (!!window.chrome.webstore || !!window.chrome.runtime);
			// Edge (based on chromium) detection
			var isEdgeChromium = isChrome && (navigator.userAgent.indexOf("Edg") != -1);
			// Blink engine detection
			var isBlink = (isChrome || isOpera) && !!window.CSS;
		    /* Function for making country code non editable */
		    if (isSafari) {
		    	$(document).ready(function() {
			        $("body.customer-address-form input[name=telephone]").keydown(function(event){
					var codenum = '+'+$(".country.active").attr('data-dial-code') || $(".selected-flag").attr('title').split(": ")[1];
					var numLength = codenum.length;
					    if(event.keyCode == 8){
					        this.selectionStart--;
					    }
					    if(this.selectionStart < numLength){
					        this.selectionStart = numLength;
					        event.preventDefault();
					    }
					});
					
					$("body.customer-address-form input[name=telephone]").keyup(function(event){
					   var codenum = '+'+jQuery(".country.active").attr('data-dial-code') || $(".selected-flag").attr('title').split(": ")[1];
					   var numLength = codenum.length;
					    if(this.selectionStart < numLength){
					        this.selectionStart = numLength; 
					        event.preventDefault();
					    }
					});
				});
		    } else {
		   		$(window).load(function() {
			        $("body.customer-address-form input[name=telephone]").keydown(function(event){
					var codenum = '+'+$(".country.active").attr('data-dial-code') || $(".selected-flag").attr('title').split(": ")[1];
					var numLength = codenum.length;
					    if(event.keyCode == 8){
					        this.selectionStart--;
					    }
					    if(this.selectionStart < numLength){
					        this.selectionStart = numLength;
					        event.preventDefault();
					    }
					});
					
					$("body.customer-address-form input[name=telephone]").keyup(function(event){
					   var codenum = '+'+jQuery(".country.active").attr('data-dial-code') || $(".selected-flag").attr('title').split(": ")[1];
					   var numLength = codenum.length;
					    if(this.selectionStart < numLength){
					        this.selectionStart = numLength; 
					        event.preventDefault();
					    }
					});
				});
		    }
		    /* Function for making country code non editable ends */
		});
	})(jQuery);
});

require([
	'jquery', 'mgs_quickview'
], function(jQuery){
	(function($) {
		$(document).ready(function(){
			$('.btn-loadmore').click(function(){
				var el = $(this);
				el.addClass('loading');
				url = $(this).attr('href');
				$.ajax({
					url: url,
					success: function(data,textStatus,jqXHR ){
						el.removeClass('loading');
						var result = $.parseJSON(data);
						if(result.content!=''){
							$('#'+result.element_id).append(result.content);
							$('.mgs-quickview').bind('click', function () {
								var prodUrl = $(this).attr('data-quickview-url');
								if (prodUrl.length) {
									reInitQuickview($,prodUrl);
								}
							});
						}
						
						if(result.url){
							el.attr('href', result.url);
						}else{
							el.remove();
						}
					}
				});
				return false;
			});
		});
		
	})(jQuery);
});

function reInitQuickview($, prodUrl){
	if (!prodUrl.length) {
		return false;
	}
	var url = QUICKVIEW_BASE_URL + 'mgs_quickview/index/updatecart';
	$.magnificPopup.open({
		items: {
			src: prodUrl
		},
		type: 'iframe',
		removalDelay: 300,
		mainClass: 'mfp-fade',
		closeOnBgClick: true,
		preloader: true,
		tLoading: '',
		callbacks: {
			open: function () {
				$('.mfp-preloader').css('display', 'block');
			},
			beforeClose: function () {
				$('[data-block="minicart"]').trigger('contentLoading');
				$.ajax({
					url: url,
					method: "POST"
				});
			},
			close: function () {
				$('.mfp-preloader').css('display', 'none');
			}
		}
	});
}

function setLocation(url) {
    require([
        'jquery'
    ], function (jQuery) {
        (function () {
            window.location.href = url;
        })(jQuery);
    });
}