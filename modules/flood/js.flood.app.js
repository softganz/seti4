$(document).on("click","#flood-camera-photos>li>a", function() {
	var $this=$(this);
	$(".photo-last").css("background","#ffffff");
	$("#photo-last").css("background","#ffffff").fadeOut().attr("src",$this.data("image")).fadeIn();
	return false;
});