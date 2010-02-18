$(function(){
$('form#info .slider label').each(function(){
	var labelColor = '#999';
	var restingPosition = '5px';

	// style the label with JS for progressive enhancement
	$(this).css({
		'color' : labelColor,
		 	'position' : 'absolute',
	 		'top' : '6px',
			'left' : restingPosition,
			'display' : 'inline',
    		        'z-index' : '99'
	});

	var inputval = $(this).next().val();

	// grab the label width, then add 5 pixels to it
	var labelwidth = $(this).width();
	var labelmove = labelwidth + 5 +'px';

	// onload, check if a field is filled out, if so, move the label out of the way
	if(inputval !== ''){
		$(this).stop().animate({ 'left':'-'+labelmove }, 1);
	}    	

	// if the input is empty on focus move the label to the left
	// if it's empty on blur, move it back
	$('input, textarea').focus(function(){
		var label = $(this).prev('label');
		var width = $(label).width();
		var adjust = width + 5 + 'px';
		var value = $(this).val();

		if(value == ''){
			label.stop().animate({ 'left':'-'+adjust }, 'fast');
		} else {
			label.css({ 'left':'-'+adjust });
		}
	}).blur(function(){
		var label = $(this).prev('label');
		var value = $(this).val();

		if(value == ''){
			label.stop().animate({ 'left':restingPosition }, 'fast');
		}	

	});
}); // End "each" statement
}); // End loaded jQuery


function validate(form_id,email) {
   var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
   var address = document.forms[form_id].elements[email].value;
   if(reg.test(address) == false) {
      alert('Invalid Email Address');
      return false;
   }
}

var TimeToFade = 1000.0;

function fade(eid)
{
  var element = document.getElementById(eid);
  if(element == null)
    return;
   
  if(element.FadeState == null)
  {
    if(element.style.opacity == null
        || element.style.opacity == ''
        || element.style.opacity == '1')
    {
      element.FadeState = 2;
    }
    else
    {
      element.FadeState = -2;
    }
  }
   
  if(element.FadeState == 1 || element.FadeState == -1)
  {
    element.FadeState = element.FadeState == 1 ? -1 : 1;
    element.FadeTimeLeft = TimeToFade - element.FadeTimeLeft;
  }
  else
  {
    element.FadeState = element.FadeState == 2 ? -1 : 1;
    element.FadeTimeLeft = TimeToFade;
    setTimeout("animateFade(" + new Date().getTime() + ",'" + eid + "')", 33);
  }  
}

function animateFade(lastTick, eid)
{  
  var curTick = new Date().getTime();
  var elapsedTicks = curTick - lastTick;
 
  var element = document.getElementById(eid);
 
  if(element.FadeTimeLeft <= elapsedTicks)
  {
    element.style.opacity = element.FadeState == 1 ? '1' : '0';
    element.style.filter = 'alpha(opacity = '
        + (element.FadeState == 1 ? '100' : '0') + ')';
    element.FadeState = element.FadeState == 1 ? 2 : -2;
    return;
  }
 
  element.FadeTimeLeft -= elapsedTicks;
  var newOpVal = element.FadeTimeLeft/TimeToFade;
  if(element.FadeState == 1)
    newOpVal = 1 - newOpVal;

  element.style.opacity = newOpVal;
  element.style.filter = 'alpha(opacity = ' + (newOpVal*100) + ')';
 
  setTimeout("animateFade(" + curTick + ",'" + eid + "')", 33);
}