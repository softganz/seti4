<?php
/**
* Project Admin Comments
* Created : 2015-05-01
* Modify : 2015-05-01
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/
function project_info_adminreport($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo,'{showPrint: true}');

	$isEdit = user_access('administer projects');
	$isAdd = user_access('administer projects');
	$post = (object) post('data');

	if (post('act')=='delete' && post('trid')) {
		$stmt='DELETE FROM %project_tr% WHERE `trid`=:trid LIMIT 1';
		mydb::query($stmt,':trid',post('trid'));
		return 'ลบทิ้งเรียบร้อย';
	} else if ($post->followtype || $post->msg) {
		$post->tpid=$tpid;
		$post->formid='admin';
		$post->part='comment';
		$post->uid=i()->uid;
		$post->rate=SG\getFirst($post->rate,NULL);
		$post->when=sg_date($post->when,'Y-m-d');
		$post->created=date('U');
		$stmt='INSERT INTO %project_tr%
							(`tpid`, `formid`, `part`, `uid`, `date1`, `detail1`, `detail2`, `rate1`, `text1`, `created`)
						VALUES
							(:tpid, :formid, :part, :uid, :when, :followtype, :attention, :rate, :msg, :created)';
		mydb::query($stmt,$post);
		//$ret.=mydb()->_query.print_o($post,'$post');
		if ($isAdd) $ret.=__project_adminreport_form($tpid);
		$ret.=__project_adminreport_draw($tpid,$isEdit);
		return $ret;
	} else {
		$ret.='<h3>บันทึกเจ้าหน้าที่</h3>'._NL;
	}

	$ret.='<div id="project-adminreport-info">'._NL;
	if ($isAdd) $ret.=__project_adminreport_form($tpid);
	$ret.=__project_adminreport_draw($tpid,$isEdit);
	$ret.='</div>'._NL;

	//$ret.=print_o($post,'$post').print_o($_FILES,'$_FILES');
	//$ret.=print_o($topic,'$topic');
	return $ret;
}

function __project_adminreport_draw($tpid,$isEdit=false) {
	$stmt='SELECT
						tr.`trid`, tr.`uid`, u.`username`, u.`name` posterName,
						`date1` `reportDate`,
						`rate1` `rate`,
						`detail1` `followfrom`,
						`detail2` `attention`,
						`detail3` `followtype`,
						`text1` `msg`,
						fc.`name` `catName`,
						fp.`name` `catParentName`,
						fs.`name` `rateName`
					FROM %project_tr% tr
						LEFT JOIN %users% u USING(`uid`)
						LEFT JOIN %tag% fc ON tr.`detail1`=fc.`catid` AND fc.`taggroup`="project:followtype"
						LEFT JOIN %tag% fp ON fp.`catid`=fc.`catparent` AND fp.`taggroup`="project:followtype"
						LEFT JOIN %tag% fs ON fs.`catid`=tr.`rate1` AND fs.`taggroup`="project:followstatus"
					WHERE `tpid`=:tpid AND `formid`="admin" AND `part`="comment"
					ORDER BY `trid` DESC';

	$dbs=mydb::select($stmt,':tpid',$tpid);

	//$ret.=print_o($dbs,'$dbs');


	$ret.='<ul class="project-report-items">'._NL;
	foreach ($dbs->items as $item) {
		$ret.='<li class="project-report-item">'._NL;
		if ($isEdit) $ret.='<span class="iconset"><a href="'.url('project/'.$tpid.'/info.adminreport',array('act'=>'delete','trid'=>$item->trid)).'" class="sg-action" data-confirm="ต้องการลบข้อความนี้ กรุณายืนยัน" data-removeparent="li" data-rel="this"><i class="icon -delete"></i></a></span>'._NL;
		$ret.='<div class="poster">By '.$item->posterName.' @'.sg_date($item->reportDate,'ว ดด ปป').'</div>'._NL;
		$ret.='<div>ติดตามจาก : '.$item->catParentName.'<br />ชื่อผู้ติดต่อ : '.$item->attention.'<br />ประเภทการติดตาม : '.$item->catName.'<br />ผลการติดตาม : '.$item->rateName.'</div>';
		$ret.='<div class="summary">'.sg_text2html($item->msg).'</div><br clear="all" />'._NL;

		$ret.='</li>'._NL;
	}
	$ret.='</ul>'._NL;
	return $ret;
}

function __project_adminreport_form($tpid) {

	$form = new Form('data', url('project/'.$tpid.'/info.adminreport'), 'project-adminreport-post', 'sg-form -no-print');
	$form->config->attr='data-rel="#project-adminreport-info"';

	$followCate=model::get_category_by_group('project:followtype','catid');

	$form->followtype->type='select';
	$form->followtype->label='ประเภทการติดตาม :';
	$form->followtype->class = '-fill';
	$form->followtype->require=true;
	$form->followtype->options=array_merge(array(''=>'===เลือกประเภทการติดตาม==='),$followCate);

	$form->attention->type='text';
	$form->attention->label='ชื่อผู้ที่ติดต่อด้วย';
	$form->attention->class='-fill';
	$form->attention->placeholder='ระบุชื่อผู้ที่ติดต่อด้วย';

	$form->followfrom->type='textfield';
	$form->followfrom->label='ผลการติดตาม :';
	$form->followfrom->require=true;
	$form->followfrom->pretext='<span>เอกสารสัญญาโครงการ : </span><br />
	<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="21"> ได้รับเอกสารเรียบร้อย
	<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="22"> เอกสารไม่เรียบร้อย<br />
	<span>การส่งรายงานและกระบวนการเบิกจ่าย :</span><br />
	<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="31"> ได้รับรายงานแล้ว
	<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="32"> อยู่ระหว่างการตรวจรายงาน
	<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="33"> อยู่ระหว่างการตั้งเบิกจ่าย
	<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="34"> โอนเงินงวดแล้ว<br />
	<span>อื่น ๆ :</span><br />
	<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="11"> เรียบร้อย
	<input type="radio" id="project-adminreport-rate" name="data[rate]" class="form-radio" value="12"> ต้องแก้ไข'._NL;
	//$form->followfrom->options=

	$form->msg->type='textarea';
	$form->msg->label='รายละเอียดกิจกรรม';
	$form->msg->class='-fill';
	$form->msg->rows=3;
	$form->msg->placeholder='รายละเอียดเหตุการและผลสรุปที่สำคัญของการติดตาม';

	$form->when->type='text';
	$form->when->label='เมื่อไหร่?';
	$form->when->class='sg-datepicker';
	$form->when->value=date('Y-m-d');


	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>โพสท์</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	return $ret;
}
?>