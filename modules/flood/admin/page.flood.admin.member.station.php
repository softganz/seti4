<?php
function flood_admin_member_station($self,$uid) {
	$stmt='SELECT * FROM %flood_station% s ORDER BY `basin`, `station`, CONVERT(`title` USING tis620) ASC ';
	$dbs=mydb::select($stmt);

	$stationList=mydb::select('SELECT `station` FROM %flood_user% WHERE `uid`=:uid',':uid',$uid)->lists->text;

	if ($uid && $station=post('station')) {
		$value=post('value');
		$ret.='uid='.$uid.' station='.$station.' value='.post('value');
		if ($value==0) {
			$stmt='DELETE FROM %flood_user% WHERE `uid`=:uid AND `station`=:station LIMIT 1';
			mydb::query($stmt,':uid',$uid, ':station',$station);
		} else {
			$stmt='INSERT %flood_user% (`uid`, `station`) VALUES (:uid, :station)';
			mydb::query($stmt,':uid',$uid, ':station',$station);
		}
		return $ret;
	}

	$ret.='<h2>สถานี</h2>';

	$form = new Form([
		'variable' => 'data',
		'action' => url('flood/admin/member/station/'.$uid),
		'id' => 'project-edit-movemainact',
		'children' => [
			'station' => [
				'type' => 'checkbox',
				'label' => 'เลือกสถานี',
				'value' => explode(',',$stationList),
				'options' => (function() {
					$result = [];
					foreach (mydb::select('SELECT * FROM %flood_station% s ORDER BY `basin`, `station`, CONVERT(`title` USING tis620) ASC ')->items as $item) {
						$result[$item->station] = $item->station.' : '.$item->basin.' : '.$item->title;
					}
					return $result;
				})(),
			],
			'closs' => '<div class="-sg-text-right"><a class="sg-action btn -link" href="'.url('flood/admin/member/station/'.$uid).'" data-rel="box"><i class="icon -material">refresh</i><span>Refresh</span></a> <a class="sg-action btn -primary" data-rel="close" href="javascript:joid(0)"><i class="icon -material">done_all</i><span>เรียบร้อย</span></a></div>',
		],
	]);

	$ret .= $form->build();


	$ret.='<script type="text/javascript">
	$("input[name=\'data[station]\'").change(function() {
		var url=$(this).closest("form").attr("action")
		$.get(url,{station:$(this).val(),value:$(this).is(":checked")?1:0},function(html){
			notify("บันทึกเรียบร้อย "+html,2000)
		})
	});
	</script>';
	//$ret.='<a class="sg-action" href="'.url('project/mainact/'.$tpid.'/move/'.$trid).'" data-rel="box">Refresh</a>';
	//$ret.=print_o(post(),'post()');
	//$ret.=print_o($rs,'$rs');
	//$ret.=print_o($mainact,'$mainact');
	return $ret;
}
?>