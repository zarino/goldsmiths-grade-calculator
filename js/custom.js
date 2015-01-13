//  Handwritten jQuery code by Zarino Zappia 2010

function IsNumeric(input){
   return (input - 0) == input && input.length > 0;
}

$(function(){

  $('dd input').live('blur', function(){
    if($(this).val() != ''){
      var value = $(this).val($(this).val().replace(/%/g, '')).val();
      if(!IsNumeric(value)){
        $(this).addClass('error');
      } else {
        $(this).removeClass('error');
      }
    }
  });

});
