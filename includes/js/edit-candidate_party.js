jQuery(document).ready(function($) {
window.send_to_editor_default = window.send_to_editor;
 
$('#party_logo_button').click(function(){
 
  // replace the default send_to_editor handler function with our own
  window.send_to_editor = window.attach_image;
  tb_show('', 'media-upload.php?post_id=1&amp;type=image&amp;TB_iframe=true');
  return false;
 
});

window.attach_image = function(html) {
 
  $('body').append('<div id="temp_image">' + html + '</div>');
 
  var img = $('#temp_image').find('img');
 
  imgurl   = img.attr('src');
  imgclass = img.attr('class');
  imgid    = parseInt(imgclass.replace(/D/g, ''), 10);
 
  $('#party_logo').val(imgid);
 
  $('img#party_logo_img').attr('src', imgurl);
 
  try{tb_remove();}catch(e){};
 
  $('#temp_image').remove();
 
  window.send_to_editor = window.send_to_editor_default;
}
});