<?php
function saveup_member_line($self) {
	$para=para(func_get_args());

	/*
	$sql_cmd='SELECT m.mid,l.lid,CONCAT(m.firstname," ",m.lastname) AS name, COUNT(*)-1 as childs ,phone
					FROM %saveup_member% m
						LEFT JOIN %saveup_line% l ON l.lid=m.mid
					WHERE status="active"
					GROUP BY lid,mid
					HAVING childs>1
					UNION
						SELECT m.mid,IF(ISNULL(l.lid),m.mid,l.lid) AS lid,CONCAT(m.firstname," ",m.lastname) AS name,NULL,phone
						FROM %saveup_member% m
							LEFT JOIN %saveup_line% l ON l.mid=m.mid
						WHERE status="active" AND l.lid IS NULL
					ORDER BY CONVERT(`name` USING tis620) ASC';
	*/

	$stmt = 'SELECT
					  l.`mid`, l.`lid`
					, CONCAT(m.`firstname`," ",m.`lastname`) AS `name`
					, `phone`, `cprovince` as `province`
					, count(`lid`) as `childs`
						FROM %saveup_line% l
							LEFT JOIN %saveup_member% m ON m.`mid` = l.`lid`
						WHERE m.`status` = "active"
						GROUP BY l.`lid`
						ORDER BY CONVERT(`name` USING tis620) ASC';

	$parents = mydb::select($stmt);

	R::View('saveup.toolbar',$self,'กลุ่มสายสัมพันธ์','member');


	if ($users->_empty) return $ret.message('error','ไม่มีสมาชิกตามเงื่อนไขที่กำหนด');

	$ret.='<p>จำนวน <strong>'.$parents->_num_rows.'</strong> สาย</p>';
	$ret .= $pagenv->show._NL;
	$total=0;


	$tables = new Table();
	$tables->addClass('saveup-line-list');
	$tables->thead = array('ID', 'ชื่อ - สกุล', 'childs -amt' => 'Childs', 'โทรศัพท์','province -hover-parent' => 'จังหวัด');
	foreach ($parents->items as $item) {
		if ($item->uid==1) continue;
		$tables->rows[] = array(
												$item->lid,
												($item->childs ? '<a class="line -parent" href="javascript:void(0)" data-lid="'.$item->lid.'" data-url="'.url('saveup/member/linechild/'.$item->lid).'" title="ดูรายชื่อทั้งหมด">+' : '&nbsp;&nbsp;')
												. $item->name
												. ($item->childs ? '</a>' : ''),
												$item->childs,
												$item->phone,
												$item->province
												.'<nav class="nav iconset -hover"><a href="'.url('saveup/member/view/'.$item->lid).'" title="ดูรายละเอียด"><i class="icon -view"></i></a></nav>',
											);
		$tables->rows[] = array(
												'',
												'<td colspan="4" cellspacing="0" cellpadding="0" style="padding:0;"><div id="line-'.$item->lid.'"></div>',
												'config' => array('id'=>'line-container-'.$item->lid,'class'=>'-hidden'),
											);
		$total+=$item->childs;
	}
	$ret .= $tables->build();

	$ret .= $pagenv->show._NL;

	$ret.='<script type="text/javascript">
	$(document).on("click",".line.-parent",function() {
		var $this=$(this)
		var targetId="#line-container-"+$this.data("lid")
		//console.log("Click "+targetId)
		if ($(targetId).is(":hidden")) {
			var targetContainer="#line-"+$this.data("lid")
			console.log("Show content on "+targetContainer)
			$.get($this.data("url"),function(html){
				$(targetContainer).html(html)
				console.log(html)
				$(targetId).show()
			})
		} else {
			$(targetId).hide()
			console.log("Hide element")
		}
		return false
	});
	</script>';

	$ret.='<h3>รายชื่อสมาชิกที่ไม่มีสายสัมพันธ์</h3>';
	$stmt='SELECT m.`mid`,l.`parent`,CONCAT(m.`firstname`," ",m.`lastname`) AS name,phone,cprovince as province
						FROM %saveup_member% m
							LEFT JOIN %saveup_line% l USING(`mid`)
						WHERE `status`="active" AND firstname IS NOT NULL AND parent IS NULL
						ORDER BY CONVERT(`name` USING tis620) ASC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','ID','ชื่อ - สกุล','โทรศัพท์','จังหวัด');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(++$no,$rs->mid,'<a href="'.url('saveup/member/view/'.$rs->mid).'">'.$rs->name.'</a>',$rs->phone,$rs->province);
	}
	$ret .= $tables->build();
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>