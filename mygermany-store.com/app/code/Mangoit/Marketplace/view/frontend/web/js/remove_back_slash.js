require(['jquery', 'jquery/ui'], function($){
   $(document).ready( function() {
      $('.page-footer .footer .nav a').each(function() {
         var url = $(this).attr('href');
         var idx = url.lastIndexOf("/");
         url = url.substring(0, idx) + '' + url.substring(idx+1);
         $(this).attr("href", url);
      });

      $('ul.header.links li a').each(function() {
         var url = $(this).attr('href');
         var idx = url.lastIndexOf("/");
         url = url.substring(0, idx) + '' + url.substring(idx+1);
         $(this).attr("href", url);
      });
   });
});