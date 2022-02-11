<?php
function project_admin_planning_issue_view($self,$catid=NULL,$action=NULL,$trid=NULL) {
	$self->theme->sidebar=R::View('project.admin.menu');

	$stmt='SELECT * FROM %tag% WHERE `taggroup`="project:planning" AND `catid`=:catid LIMIT 1';
	$rs=mydb::select($stmt,':catid',$catid);

	R::View('project.toolbar',$self,$rs->name,'admin');


	$stmt='SELECT * FROM %tag% WHERE `taggroup`=:taggroup ORDER BY `weight` ASC, `catid` ASC';
	$dbs=mydb::select($stmt,':taggroup','project:problem:'.$catid);

	$ret.='<h3>สภาพปัญหา/วัตถุประสงค์</h3>';

	$ret.='<form class="sg-form" method="post" action="'.url('project/admin/planning/issue/'.$catid.'/addproblem').'" data-rel="#main">';
	
	$tables = new Table();
	$tables->addClass('-problem');
	$tables->thead = array(
		'center -catid'=>'ID',
		'สภาพปัญหา',
		'unit -amt' => 'หน่วย',
		'min -amt' => 'Min',
		'max -amt' => 'Max',
		'วัตถุประสงค์',
		'ตัวชี้วัด',
		'weight -amt'=>'ลำดับ',
		'icons -c2 -center -nowrap'=>'',
	);

	foreach ($dbs->items as $rs) {
		$detail = json_decode($rs->description);
		if ($action=='editproblem' && $trid==$rs->catid) {
			$detail->indicator = str_replace('<br />',"\n",$detail->indicator);
			$detail->datasource = str_replace('<br />',"\n",$detail->datasource);

			$tables->rows[]=array(
				'<input class="form-text -fill -numeric" type="text" name="data[catid]" value="'.$rs->catid.'" readonly="readonly" />',
				'<input class="form-text -fill" type="text" name="data[problem]" placeholder="สภาพปัญหา" value="'.htmlspecialchars($rs->name).'" />'
				. '<textarea class="form-textarea -fill" name="data[datasource]" rows="5" placeholder="ที่มาของข้อมูล" style="margin-top:8px;">'.htmlspecialchars(($detail->datasource)).'</textarea>',
				'<select class="form-select -fill" name="data[unitsize]"><option value="ร้อยละ"'.($detail->unitsize == 'ร้อยละ' ? ' selected="selected"' : '').'>ร้อยละ</option><option value="จำนวน"'.($detail->unitsize == 'จำนวน' ? ' selected="selected"' : '').'>จำนวน</option></select>',
				'<input class="form-text -numeric" type="text" name="data[minValue]" size="3" value="'.$detail->minValue.'" placeholder="Min value" />',
				'<input class="form-text -numeric" type="text" name="data[maxValue]" size="3" value="'.$detail->maxValue.'" placeholder="Max value" />',
				'<input class="form-text -fill" type="text" name="data[objective]" placeholder="วัตถุประสงค์" value="'.htmlspecialchars($detail->objective).'" />',
				'<textarea class="form-textarea -fill" name="data[indicator]" placeholder="ตัวชี้วัด" rows="7">'.htmlspecialchars(($detail->indicator)).'</textarea>',
				'<input class="form-text -numeric" type="text" name="data[weight]" size="1" value="'.htmlspecialchars($rs->weight).'" />',
				'<button class="btn -primary" name="act" value="addproblem"><i class="icon -save -white"></i></button>',
			);
		} else {
			$tables->rows[]=array(
				$rs->catid,
				$rs->name,
				$detail->unitsize,
				$detail->minValue,
				$detail->maxValue,
				$detail->objective,
				$detail->indicator,
				$rs->weight,
				'<a class="sg-action" href="'.url('project/admin/planning/issue/'.$catid.'/visible/'.$rs->catid).'" data-rel="none" data-callback="projectAdminPlanningIssueViewVisible"><i class="icon -'.($rs->process>0?'visible':'invisible -gray').'"></i></a>'
				.'<a class="sg-action" href="'.url('project/admin/planning/issue/'.$catid.'/editproblem/'.$rs->catid).'" data-rel="#main"><i class="icon -edit"></i></a>',
			);
		}
	}
	if (empty($action)) {
		$tables->rows[]=array(
			'<input class="form-text -fill -numeric" type="text" name="data[catid]" placeholder="auto" />',
			'<input class="form-text -fill" type="text" name="data[problem]" placeholder="เพิ่มสภาพปัญหา" />'
			. '<textarea class="form-textarea -fill" name="data[datasource]" rows="5" placeholder="ที่มาของข้อมูล" style="margin-top:8px;"></textarea>',
			'<select class="form-select -fill" name="data[unitsize]"><option value="ร้อยละ">ร้อยละ</option><option value="จำนวน">จำนวน</option></select>',
			'<input class="form-text -numeric" type="text" name="data[minValue]" size="3" placeholder="Min value" />',
			'<input class="form-text -numeric" type="text" name="data[maxValue]" size="3" placeholder="Max value" />',
			'<input class="form-text -fill" type="text" name="data[objective]" placeholder="วัตถุประสงค์" />',
			'<textarea class="form-textarea -fill" name="data[indicator]" placeholder="ตัวชี้วัด" rows="7"></textarea>',
			'<input class="form-text -numeric" type="text" name="data[weight]" size="1" value="" placeholder="auto" />',
			'<button class="btn -primary" name="act" value="addproblem"><i class="icon -save -white"></i></button>',
		);
	}

	$ret .= $tables->build();

	$ret .= '</form>';


	$stmt='SELECT * FROM %tag% WHERE `taggroup`=:taggroup ORDER BY `catid` ASC';
	$dbs=mydb::select($stmt,':taggroup','project:guideline:'.$catid);





	$ret.='<h3>แนวทางดำเนินงาน/วิธีการสำคัญ</h3>';
	$ret.='<form method="post" action="'.url('project/admin/planning/issue/'.$catid).'">';
	$tables = new Table();
	$tables->thead=array('center -catid'=>'ID','แนวทางดำเนินงาน','วิธีการสำคัญ	','icons -c1 -center'=>'');
	foreach ($dbs->items as $rs) {
		//$ret.=print_o($rs,'$rs');
		$detail=json_decode($rs->description);
		//$ret.=print_o($detail,'$detail');
		if ($action=='editguideline' && $trid==$rs->catid) {
			$detail->process=str_replace('<br />',"\n",$detail->process);

			$tables->rows[]=array(
				'<input class="form-text -fill -numeric" type="text" name="data[catid]" value="'.$rs->catid.'" readonly="readonly" />',
				'<textarea class="form-textarea -fill" name="data[guideline]" placeholder="แนวทางดำเนินงาน" rows="7">'.htmlspecialchars($rs->name).'</textarea>',
				'<textarea class="form-textarea -fill" name="data[process]" placeholder="วิธีการสำคัญ" rows="7">'.htmlspecialchars(($detail->process)).'</textarea>',
				'<button class="btn -primary" name="act" value="addguideline"><i class="icon -save -white"></i></button>',
			);
		} else {
			$tables->rows[]=array(
				$rs->catid,
				$rs->name,
				$detail->process,
				'<a class="" href="'.url('project/admin/planning/issue/'.$catid.'/editguideline/'.$rs->catid).'"><i class="icon -edit"></i></a>',
			);
		}
	}
	if (empty($action)) {
		$tables->rows[]=array(
			'<input class="form-text -fill -numeric" type="text" name="data[catid]" placeholder="auto" />',
			'<textarea class="form-textarea -fill" name="data[guideline]" placeholder="เพิ่มแนวทางดำเนินงาน" rows="7"></textarea>',
			'<textarea class="form-textarea -fill" name="data[process]" placeholder="วิธีการสำคัญ" rows="7"></textarea>',
			'<button class="btn -primary" name="act" value="addguideline"><i class="icon -save -white"></i></button>',
		);
	}
	$ret.=$tables->build();

	$ret.='</form>';

	//$ret.=print_o($dbs);

	//$ret.=print_o($rs);


	$ret.='<style type="text/css">
	.item.-problem td:nth-child(2),.item.-problem td:nth-child(6),.item.-problem td:nth-child(7) {width:25%;}
	</style>';

	$ret.='<script type="text/javascript">
	function projectAdminPlanningIssueViewVisible($this) {
		var $icon=$this.find(".icon");
		if ($icon.hasClass("-visible")) {
			$icon.removeClass("-visible").addClass("-invisible -gray")
		} else {
			$icon.removeClass("-invisible").removeClass("-gray").addClass("-visible")
		}
	}
	</script>';
	return $ret;
}
?>