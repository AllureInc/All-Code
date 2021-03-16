require ([
    "jquery", 
    "jquery/ui"
], function($){
    $(".faq-accordion").accordion({
        collapsible: true,
        heightStyle: "content",
        active: '',
        animate: 500
    });


    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('.group-title, .faq-accordion').css('display', 'none');
            $(target).show();
            $(this.getAttribute('href')+'+.faq-accordion').show();
            $('html, body').stop().animate({
                scrollTop: target.offset().top-200
            }, 1000);
        }
    }); 
});