(function($){
  $(function(){
    $('.button-collapse').sideNav();
    $('.parallax').parallax();
  });
})(jQuery);

function copyToClipboard(element) {
  var $temp = $("<input>");
  $("body").append($temp);
  $temp.val($(element).text()).select();
  document.execCommand("copy");
  $temp.remove();
}
