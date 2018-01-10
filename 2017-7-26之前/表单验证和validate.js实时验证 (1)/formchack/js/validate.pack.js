//代码整理：www.97zzw.com - 97站长网
$(function(){
	var xOffset = -20; // x distance from mouse
    var yOffset = 20; // y distance from mouse  
	
	//input tips
	$("input[reg],textarea[reg]").hover(	
		function(e) {
			var tip = $(this).attr("tip");
			var top = (e.clientY + yOffset);
			var left = (e.clientX + xOffset);
			$('body').append( '<p id="vtip"><img id="vtipArrow" src="images/vtip_arrow.png"/>' + tip + '</p>' );
			$('p#vtip').css("top", top+"px").css("left", left+"px");
		},
		function() {
			$("p#vtip").remove();
		}
	).mousemove(
		function(e) {
			var top = (e.clientY + yOffset);
			var left = (e.clientX + xOffset);
			$("p#vtip").css("top", top+"px").css("left", left+"px");
		}
	); 
	
	$("input[reg],textarea[reg]").blur(function(){
		validate($(this),"input");
	});
	$("select[reg]").blur(function(){
		validate($(this),"select");
	});
	
	
	$("form").submit(function(){
		var isSubmit = true;
		$("input[reg],textarea[reg]").each(function(){
			if(!validate($(this),"input")){
				isSubmit = false;
			}
		});
		$("select[reg]").each(function(){
			if(!validate($(this),"select")){
				isSubmit = false;
			}
		});
		return isSubmit;
	});
	
});

function validate(obj,tagType){
	var reg = new RegExp(obj.attr("reg"));
	var objValue = obj.attr("value");
	if(!reg.test(objValue)){
		if(tagType == "input"){
			obj.addClass("input_validation-failed");
		}else{
			obj.addClass("select_validation-failed");
		}
		
		return false;
	}else{
		if(tagType == "input"){
			obj.removeClass("input_validation-failed");
		}else{
			obj.removeClass("select_validation-failed");
		}
		return true;
	}
}