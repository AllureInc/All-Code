require(['jquery','mage/url'], function($, url){
    // For adding toggle effect on header element
	$( ".filter-options-title" ).off();
    $(".filter-options-title").on("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        self.toggleFilter($(this));
        return false;
    });
	function toggleFilter(el) {
        el.toggleClass('inactive');
        el.toggleClass('active');
        el.parent().closest('div').next().slideToggle();
    }
});