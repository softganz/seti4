<?php
/**
* Project detail
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_form_comment($self,$topic,$para,$body) {
	$tpid=$topic->tpid;

	// Section :: User comments : ความคิดเห็น
	$ret.='<div id="project-comment">';
	$ret.=$body->comment._NL;
	$ret.='</div>';

	$ret.='<style tyle="text/css">
	#edit-comment .button-preview {display:none;}
	</style>';

	$ret.='<script type="text/javascript">
	$("#edit-comment").submit(function() {
		var $this=$(this);
		var url="'.url('paper/'.$tpid.'/info/comment').'";
		//var url=$this.attr("action");
		notify("กำลังบันทึกความคิดเห็น");
		$.post(url,$this.serialize()+"&"+$.param({save:"Yes"}),function(html){
			console.log(html);
			$.get(url,function(html){
				$(".project.-detail").html(html);
				notify();
			});
		});
		console.log("Submit comment to "+url);
		return false;
	});
	</script>';
	return $ret;
}
?>