<?php
import('model:project.proposal.php');

function project_develop_plan($self,$tpid=NULL,$action=NULL,$trid=NULL) {
	$tagname="develop";
	$devInfo=R::Model('project.develop.get',$tpid);

	if (empty($devInfo)) return 'No project';

	$isEdit=$devInfo->RIGHT & _IS_EDITABLE;
	$isAdmin=$devInfo->RIGHT & _IS_ADMIN;

	if (empty($tpid) || mydb::select('SELECT `tpid` FROM %topic% WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid)->_empty) return 'No project';


	if (empty($action)) $action=post('action');

	if ($action) {
		if (!$isEdit) return '<p class="notify">เกิดข้อผิดพลาด สิทธิ์ในการเข้าถึงถูกปฏิเสธ</p>';
		switch ($action) {
			case 'info':
				if ($isAdmin) {
					$rs=project_model::get_info($tpid)->mainact[post('id')];
					$rs->created=sg_date($rs->created,'Y-m-d H:i:s');
					if ($rs->modified) $rs->modified=sg_date($rs->modified,'Y-m-d H:i:s');
					$iTable = new Table();
					foreach ($rs as $key => $value) $iTable->rows[]=array($key,$value);
					$ret .= $iTable->build();
				}
				return $ret;
				break;

			case 'add' :
				if ($before=post('before')) {
					$sorder=$before;
					// เพิ่มลำดับของกิจกรรมหลัง
					mydb::query('UPDATE %project_tr% SET `sorder`= `sorder`+1 WHERE `tpid`=:tpid AND `formid`="info" AND `part`="mainact" AND `sorder`>=:before ORDER BY `sorder` ASC',':tpid',$tpid,':before',$before);
				} else {
					$sorder=mydb::select('SELECT MAX(`sorder`) `maxOrder` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="mainact" LIMIT 1',':tpid',$tpid)->maxOrder+1;
				}
				$stmt='INSERT INTO %project_tr% (`tpid`, `sorder`, `uid`, `formid`, `part`, `created`) VALUES (:tpid, :sorder, :uid, "info" , "mainact", :created)';
				mydb::query($stmt,':tpid',$tpid, ':sorder',$sorder, ':uid', i()->uid, ':created',date('U'));
				if ($before) {
					// เรียงลำดับกิจกรรมใหม่
					mydb::query('SET @n:=0 ;');
					mydb::query('UPDATE %project_tr% SET `sorder`= @n := @n+1 WHERE `tpid`=:tpid AND `formid`="info" AND `part`="mainact" ORDER BY `sorder` ASC;',':tpid',$tpid);
				}
				break;

			case 'remove' :
				if (post('id')) {
					$delrs=mydb::select('SELECT *, (SELECT COUNT(*) FROM %project_activity% WHERE `mainact`=:trid) totalCalendar FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',post('id'));
					//$ret.=print_o($delrs,'$delrs');
					if ($delrs->totalCalendar) {
						$ret.='<p class="notify">กิจกรรมนี้มีกิจกรรมย่อยจำนวน '.$delrs->totalCalendar.' ครั้ง ไม่สามารลบได้</p>';
					} else {
						mydb::query('DELETE FROM %project_tr% WHERE `trid`=:trid OR `parent`=:trid',':trid',post('id'));
						model::watch_log('project','remove plan','ลบแผนการดำเนินงาน หมายเลข '.post('id').'<br />'.sg_text2html($delrs->detail1).'<br />'.sg_text2html($delrs->text1),NULL,$tpid);
					}
					ProjectProposalModel::calculateExpense($tpid);
				}
				break;

			case 'removeobj' :
				if (post('id')) {
					$currentObjId=mydb::select('SELECT `parent` FROM %project_tr% WHERE `trid`=:actid LIMIT 1',':actid',post('actid'))->parent;
					mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="actobj" AND `gallery`=:actid AND `parent`=:parent LIMIT 1',':tpid',$tpid,':actid',post('actid'),':parent',post('id'));

					// Remove mainact objective (parent) when post('id')=current objective
					//$ret.='currentObjId='.$currentObjId;
					if ($currentObjId==post(id)) {
						$objid=mydb::select('SELECT `parent` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="actobj" AND `gallery`=:actid LIMIT 1',':tpid',$tpid,':actid',post('actid'))->parent;
						mydb::query('UPDATE %project_tr% SET `parent`=:parent WHERE `trid`=:actid LIMIT 1',':tpid',$tpid,':actid',post('actid'),':parent',$objid);
					}
					//$ret.=mydb()->_query;
				}
				return $ret;
				break;

			case 'addtarget' :
				$data=post();
				$stmt='INSERT INTO %project_target%
							(`tpid`, `trid`, `tagname`, `tgtid`, `amount`)
							VALUES
							(:tpid, :trid, "develop:mainact", :target, :amount)
							ON DUPLICATE KEY UPDATE
							`amount`=:amount';
				mydb::query($stmt,':tpid',$tpid, ':trid',$trid, $data);
				//$ret.=mydb()->_query;

				$ret.=R::View('project.develop.plan.target',$devInfo,$trid);
				//$ret.=print_o(post(),'post()');
				return $ret;
				break;

			case 'removetarget' :
				$stmt='DELETE FROM %project_target% WHERE `tpid`=:tpid AND `trid`=:mainactid AND `tagname`="develop:mainact" AND `tgtid`=:tgtid LIMIT 1';
				mydb::query($stmt, ':tpid',$tpid, ':mainactid',$trid, ':tgtid',post('target'));
				//$ret.=mydb()->_query;
				$ret.=R::View('project.develop.plan.target',$devInfo,$trid);
				return $ret;
				break;

			case 'addactivity' :
				$data->tpid=$tpid;
				$data->refid=$trid;
				$data->formid='develop';
				$data->part='activity';
				$data->date1=sg_date(post('datefrom'),'Y-m-d');
				$data->date2=sg_date(post('dateto'),'Y-m-d');
				$data->detail1=post('title');
				$data->num1=sg_strip_money(post('amount'));
				$data->uid=i()->uid;
				$data->created=date('U');
				$stmt='INSERT INTO %project_tr% (`tpid`,`refid`,`formid`,`part`,`date1`,`date2`,`detail1`,`num1`,`uid`,`created`) VALUES (:tpid,:refid,:formid,:part,:date1,:date2,:detail1,:num1,:uid,:created)';
				mydb::query($stmt,$data);
				//$ret.=mydb()->_query;
				$ret.=R::View('project.develop.plan.activity',$devInfo,$trid);
				//$ret.=print_o(post(),'post()');
				return $ret;
				break;

			case 'addexp' :
				if (post('exp')) {
					//$ret.=print_o(post('exp'),'exp');
					$exp=(object)post('exp');
					$exp->amt=sg_strip_money($exp->amt);
					$exp->unitprice=sg_strip_money($exp->unitprice);
					$exp->times=sg_strip_money($exp->times);
					$exp->total=sg_strip_money($exp->total);
					$exp->tpid=$tpid;
					$exp->uid=$exp->modifyby=i()->uid;
					$exp->created=$exp->modified=date('U');
					$stmt='INSERT INTO %project_tr%
									(`trid`, `tpid`, `parent`, `gallery`, `formid`, `part`, `num1`, `num2`, `num3`, `num4`, `detail1`, `text1`, `uid`, `created`)
									VALUES
									(:expid, :tpid, :id, :expcode, "develop","exptr",:amt,:unitprice,:times,:total,:unitname,:detail,:uid,:created)
									ON DUPLICATE KEY
									UPDATE `gallery`=:expcode, `num1`=:amt, `num2`=:unitprice, `num3`=:times, `num4`=:total, `detail1`=:unitname, `text1`=:detail, `modified`=:modified, `modifyby`=:modifyby';
					mydb::query($stmt,$exp);
					$ret .= ProjectProposalModel::calculateExpense($tpid);
				} else {
					$ret.=__project_develop_plan_addexp(post('id'),post('expid'));
					return $ret;
				}
				break;

			case 'removeexp' :
				mydb::query('DELETE FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',post('expid'));
				ProjectProposalModel::calculateExpense($tpid);;
				break;

			case 'calculateexp' :
				ProjectProposalModel::calculateExpense($tpid);;
				break;

			case 'reorder':
				if (SG\confirm()) {
					if (post('id') && post('to')) {
						$to=post('to');
						if ($to=='top') {
							$to=1;
							// เพิ่มลำดับของทุกกิจกรรมขึ้นไปอีก 1
							mydb::query('SET @n:=1 ;');
							$stmt='UPDATE %project_tr% SET `sorder`= @n := @n+1 WHERE `tpid`=:tpid AND `formid`="info" AND `part`="mainact" ORDER BY `sorder` ASC;';
							mydb::query($stmt,':tpid',$tpid);
							// บันทึกลำดับของกิจกรรมที่ต้องการย้าย
							mydb::query('UPDATE %project_tr% SET `sorder`=:to WHERE `trid`=:trid LIMIT 1',':trid',post('id'),':to',$to);
						} else {
							// เพิ่มลำดับของกิจกรรมหลัง
							mydb::query('UPDATE %project_tr% SET `sorder`= `sorder`+1 WHERE `tpid`=:tpid AND `formid`="info" AND `part`="mainact" AND `sorder`>:to ORDER BY `sorder` ASC',':tpid',$tpid,':to',$to);
							// บันทึกลำดับของกิจกรรมที่ต้องการย้าย
							mydb::query('UPDATE %project_tr% SET `sorder`=:to WHERE `trid`=:trid LIMIT 1',':trid',post('id'),':to',$to+1);
							// เรียงลำดับกิจกรรมใหม่
							mydb::query('SET @n:=0 ;');
							mydb::query('UPDATE %project_tr% SET `sorder`= @n := @n+1 WHERE `tpid`=:tpid AND `formid`="info" AND `part`="mainact" ORDER BY `sorder` ASC;',':tpid',$tpid);
						}
					}
				} else {
					$ret.='<h4>เปลี่ยนลำดับกิจกรรม</h4>';
					$info=project_model::get_info($tpid);
					$form->config->variable='data';
					$form->config->method='post';
					$form->config->action=url(q());
					$form->config->class='sg-form';
					$form->config->attr=array(
						'data-rel'=>'project-develop-plan',
						'onsubmit'=>'$.colorbox.close()',
					);

					$form->action=array('type'=>'hidden','name'=>'action','value'=>'reorder');
					$form->id=array('type'=>'hidden','name'=>'id','value'=>post('id'));
					$form->confirm=array('type'=>'hidden','name'=>'confirm','value'=>'yes');

					$form->to->type='radio';
					$form->to->type='radio';
					$form->to->name='to';
					$form->to->label='เลือกลำดับของกิจกรรมที่ต้องการย้ายกิจกรรมนี้ไป';
					$mainact=project_model::get_info($tpid,'info:mainact')->mainact;

					$form->to->options['top']='บนสุด';
					foreach ($mainact as $item) {
						if (post('id')==$item->trid) continue;
						$form->to->options[$item->sorder]='หลัง : '.$item->title;
					}

					$form->submit->type='submit';
					$form->submit->items->save='บันทึก';
					$form->submit->posttext=' <a href="javascript:void(0)" onclick="$.colorbox.close()">ยกเลิก</a>';

					$ret .= theme('form','project-edit-movemainact',$form);
					//$ret.=print_o($mainact,'$mainact');
					return $ret;
				}
				break;

			case 'addobj' :
				if (SG\confirm()) {
					if (post('id') && post('to')) {
						if (
							!mydb::select('SELECT `parent` FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',post('id'))->parent
							|| mydb::select('SELECT COUNT(*) `total` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="actobj" AND `gallery`=:actid LIMIT 1',':tpid',$tpid,':actid',post('id'))->total==0
							) {
							mydb::query('UPDATE %project_tr% SET `parent`=:to WHERE `trid`=:from LIMIT 1',':from',post('id'), ':to',post('to'));
						}
						$isDup=mydb::select('SELECT `parent` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="actobj" AND `gallery`=:actid AND `parent`=:to LIMIT 1',':tpid',$tpid,':actid',post('id'),':to',post('to'))->parent;
						if (!$isDup) {
							mydb::query('INSERT INTO %project_tr% (`tpid`,`parent`,`gallery`,`formid`,`part`,`uid`,`created`) VALUES (:tpid,:parent,:gallery,"info","actobj",:uid,:created)',':tpid',$tpid,':parent',post('to'),':gallery',post('id'),':uid',i()->uid,':created',date('U'));
						}
					}
					return $ret;
				} else {
					$ret.='<h3>กิจกรรมตอบวัตถุประสงค์</h3>';
					$form->config->variable='data';
					$form->config->method='post';
					$form->config->action=url(q());
					$form->config->class='sg-form';
					$form->config->attr=array(
						'data-rel'=>'#project-develop-plan',
						'onsubmit'=>'$.colorbox.close();',
					);

					$form->action=array('type'=>'hidden','name'=>'action','value'=>'obj');
					$form->id=array('type'=>'hidden','name'=>'id','value'=>post('id'));
					$form->confirm=array('type'=>'hidden','name'=>'confirm','value'=>'yes');

					$form->to->type='radio';
					$form->to->name='to';
					$form->to->label='เลือกวัตถุประสงค์ที่ต้องการ';
					$objective=project_model::get_tr($tpid,'info:objective');
					foreach ($objective->items['objective'] as $item) {
						$form->to->options[$item->trid]=$item->text1;
					}
					$form->to->value=SG\getFirst($rs->privacy,'public');

					$form->submit->type='submit';
					$form->submit->items->save='บันทึก';
					$form->submit->posttext=' <a href="javascript:void(0)" onclick="$.colorbox.close()">ยกเลิก</a>';

					$ret .= theme('form','project-edit-movemainact',$form);

					return $ret;
				}
				break;
		}
	}


	$ret.=R::Page('project.develop.plan.view',$self,$tpid);

	return $ret;
}

function __project_develop_plan_addexp($actid,$expid) {
	$ret.='<h4>'.($expid?'แก้ไข':'เพิ่ม').'ค่าใช้จ่าย</h4>';
	if ($expid) {
		$post=mydb::select('SELECT `tpid`,`parent`,`gallery` `expcode`,`num1` `amt`, `num2` `unitprice`, `num3` `times`, `num4` `total`,`detail1` `unitname`, `text1` `detail` FROM %project_tr% WHERE `trid`=:expid LIMIT 1',':expid',$expid);
		//$ret.=print_o($post,'$post');
	}
	$post->unitprice=SG\getFirst($post->unitprice,0);
	$post->times=SG\getFirst($post->times,1);
	$post->amt=SG\getFirst($post->amt,1);
	$post->total=SG\getFirst($post->total,0);

	$expCodeList=model::get_category('project:expcode','catid');
	foreach ($expCodeList as $key => $value) if (empty($value)) unset($expCodeList[$key]);

	$form->config->variable='exp';
	$form->config->method='post';
	$form->config->action=url(q());
	$form->config->class='sg-form';
	$form->config->attr=array(
		'data-rel'=>'#project-develop-plan',
		'onsubmit'=>'$.colorbox.close()',
	);

	$form->action=array('type'=>'hidden','name'=>'action','value'=>'addexp');
	$form->id=array('type'=>'hidden','value'=>$actid);
	$form->expid=array('type'=>'hidden','value'=>$expid);
	$form->expcode=array('type'=>'select','label'=>'ประเภทรายจ่าย','options'=>$expCodeList,'class'=>'w-9','value'=>$post->expcode);
	$form->amt=array('type'=>'text','label'=>'จำนวนหน่วย','class'=>'w-9','placeholder'=>0,'value'=>$post->amt);
	$form->unitname=array('type'=>'select','label'=>'หน่วยนับ','options'=>array('คน'=>'คน','ครั้ง'=>'ครั้ง','เที่ยว'=>'เที่ยว','ชิ้น'=>'ชิ้น','ชุด'=>'ชุด'),'class'=>'w-9','value'=>$post->unitname);
	$form->unitprice=array('type'=>'text','label'=>'ค่าใช้จ่ายต่อหน่วย (บาท)','class'=>'w-9','placeholder'=>0,'value'=>$post->unitprice);
	$form->times=array('type'=>'text','label'=>'จำนวนครั้งกิจกรรม','class'=>'w-9','value'=>1,'value'=>$post->times);
	$form->total=array('type'=>'text','label'=>'รวมเงิน','class'=>'w-9','placeholder'=>0,'value'=>$post->total,'readonly'=>true);
	$form->detail=array('type'=>'textarea','label'=>'รายละเอียดค่าใช้จ่าย','class'=>'w-9','rows'=>3,'value'=>$post->detail);

	$form->submit->type='submit';
	$form->submit->items->save='บันทึก';
	$form->submit->posttext=' <a href="javascript:void(0)" onclick="$.colorbox.close()">ยกเลิก</a>';

	$ret .= theme('form','project-edit-exp',$form);
	$ret.='<script>
	$("#project-edit-exp input").keyup(function(){
		var total=0
		var amt=parseFloat($("#edit-exp-amt").val().replace(/,/g, ""))
		var unitprice=parseFloat($("#edit-exp-unitprice").val().replace(/,/g, ""))
		var times=parseFloat($("#edit-exp-times").val().replace(/,/g, ""))
		total=amt*unitprice*times
		$("#edit-exp-total").val(total)
	});
	</script>';
	return $ret;
}


?>