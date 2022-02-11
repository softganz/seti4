<?php
function project_admin_monitor_get($self) {
	$url = 'https://sg-project-man.firebaseio.com/update.json';
	$data_string = json_encode($arr);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$json = curl_exec($ch);

	$input=json_decode($json);
	$data=array_reverse((array)$input,true);
	//$ret.=print_o($data,'$data');

	$ret.='<div class="chats">
<div id="chats">';
	foreach ($data as $key => $value) {
		$ret.='<div class="additem"><span class="time">Topic '.$value->tpid.' Group='.$value->group.' Field='.$value->field.' Time='.$value->time.'</span><div class="post">'.$value->value.'</div>';
		if ($value->url) {
			$ret.='<div class="url"><a href="'.$value->url.'" target="_blank">'.$value->url.'</a></div>';
		}
		$ret.='</div>';
	}
	$ret.='</div>
</div>';

	$ret.='<style type="text/css">
	.time {color:gray;font-size:0.8em;}
	.additem {margin:16px 0; padding:16px; border:none; box-shadow: 0px 0px 0px 1px #ddd inset; border-radius:2px;}
	.post {margin: 16px 0;}
	.url {}
	.url a {color:gray;}
	</style>';
	return $ret;
}
?>