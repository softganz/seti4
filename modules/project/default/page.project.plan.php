<?php
/**
* Project calendar information
*
* @param Object $self
* @param Integer $tpid
* @param String $action
* @param Integer $actid
* @param $topic Object
* @param $info Array
* @param $options JSON
* @param
* @return String
*/
function project_plan($self,$tpid,$action=NULL,$trid=NULL) {
	if (!is_object($tpid)) {
		$projectInfo=R::Model('project.get',$tpid);
	} else {
		$projectInfo=$tpid;
		$tpid=$projectInfo->tpid;
	}

	//$ret.='Topic='.$tpid;
	if ($projectInfo->info->type!='project') return $ret.message('error','This is not a project');

	$tagname='info';
	$action=SG\getFirst($action,post('act'));
	$isEdit=$projectInfo->RIGHT & _IS_EDITABLE;
	$isEditDetail=$projectInfo->RIGHT & _IS_EDITDETAIL;

	switch ($action) {
		case 'add' :
			if ($isEdit) {
				//$ret.=print_o(post(),'post()');
				//$ret.=__project_plan_add($tpid,NULL,NULL,$info);
				$title=post('title');
				if ($title) {
					if ($before=post('before')) {
						$sorder=$before;
						// เพิ่มลำดับของกิจกรรมหลัง
						mydb::query('UPDATE %project_tr% SET `sorder`= `sorder`+1 WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" AND `sorder`>=:before ORDER BY `sorder` ASC',':tpid',$tpid, ':tagname',$tagname ,':before',$before);
					} else {
						$sorder=mydb::select('SELECT MAX(`sorder`) `maxOrder` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" LIMIT 1',':tpid',$tpid, ':tagname',$tagname)->maxOrder+1;
					}

					$data->tpid=$tpid;
					$data->parent=$trid;
					$data->tagname=$tagname;
					$data->sorder=$sorder;
					$data->uid=i()->uid;
					$data->title=$title;
					$data->created=date('U');
					$data->fromdate=post('fromdate')?sg_date(post('fromdate'),'Y-m-d'):'';
					$data->todate=post('todate')?sg_date(post('todate'),'Y-m-d'):'';
					$data->detail=post('detail');
					$data->orgsupport=post('orgsupport');
					$stmt='INSERT INTO %project_tr%
						(`tpid`, `parent`, `sorder`, `uid`, `formid`, `part`, `date1`, `date2`, `detail1`, `text1`,`text3`, `created`)
						VALUES
						(:tpid, :parent, :sorder, :uid, :tagname , "activity", :fromdate, :todate, :title, :detail, :orgsupport, :created)';
					mydb::query($stmt,$data);
					$addTrid=mydb()->insert_id;
					//$ret.=mydb()->_query.'<br />';
					//$ret.=print_o($data,'$data');

					// เรียงลำดับกิจกรรมใหม่
					if ($before) {
						mydb::query('SET @n:=0 ;');
						mydb::query('UPDATE %project_tr% SET `sorder`= @n := @n+1 WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" ORDER BY `sorder` ASC;',':tpid',$tpid, ':tagname',$tagname);
					}

					$projectInfo=R::Model('project.get',$tpid);
					$ret.=R::Page('project.plan.tree',NULL,$tpid,'view',$trid);

				} else {
					$ret.=R::View('project.plan.form.add',$tpid,$trid);
				}
			}
			return $ret;
			break;

		case 'edit' :
			if ($isEdit) {
				$ret.=__project_plan_add($projectInfo,$actid,$info->mainact[$actid],$info);
			}
			return $ret;
			break;

		case 'remove' :
			if ($isEdit && SG\confirm()) {
				if (empty($projectInfo->activity[$trid]->childsCount)) {
					// Delete Objective
					$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="actobj" AND `refid`=:trid';
					mydb::query($stmt,':tpid',$tpid, ':tagname',$tagname, ':trid',$trid);
					$ret.=mydb()->_query.'<br />';

					// Delete Expense
					$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="exptr" AND `parent`=:trid';
					mydb::query($stmt,':tpid',$tpid, ':tagname',$tagname, ':trid',$trid);
					$ret.=mydb()->_query.'<br />';

					// Delete Target


					$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `trid`=:trid AND `formid`=:tagname AND `part`="activity" LIMIT 1';
					mydb::query($stmt, ':tpid',$tpid, ':tagname',$tagname, ':trid',$trid);
					$ret.=mydb()->_query.'<br />';

					$parent=$projectInfo->activity[$trid]->parent;
					$projectInfo=R::Model('project.get',$tpid);
				} else {
					$ret.=message('error','กิจกรรมหลักนี้มีการกิจกรรมย่อยแล้ว ไม่สามารถลบทิ้งได้');
				}
			}
			return $ret;
			break;

		case 'addexp' :
			if ($isEdit) {
				if (post('exp')) {
					//$ret.=print_o(post('exp'),'exp');
					$exp=(object)post('exp');
					$trid=$exp->id;
					if (empty($exp->expid)) $exp->expid=NULL;
					$exp->amt=sg_strip_money($exp->amt);
					$exp->unitprice=sg_strip_money($exp->unitprice);
					$exp->times=sg_strip_money($exp->times);
					$exp->total=sg_strip_money($exp->total);
					$exp->tpid=$tpid;
					$exp->formid=$tagname;
					$exp->uid=$exp->modifyby=i()->uid;
					$exp->created=$exp->modified=date('U');
					$stmt='INSERT INTO %project_tr%
									(`trid`, `tpid`, `parent`, `gallery`, `formid`, `part`, `num1`, `num2`, `num3`, `num4`, `detail1`, `text1`, `uid`, `created`)
									VALUES
									(:expid, :tpid, :id, :expcode, :formid,"exptr",:amt,:unitprice,:times,:total,:unitname,:detail,:uid,:created)
									ON DUPLICATE KEY
									UPDATE `gallery`=:expcode, `num1`=:amt, `num2`=:unitprice, `num3`=:times, `num4`=:total, `detail1`=:unitname, `text1`=:detail, `modified`=:modified, `modifyby`=:modifyby';
					mydb::query($stmt,$exp);
					//$ret.='trid='.$trid.'<br />';
					//$ret.=mydb()->_query;
					R::On('project.plan.expense.change',$tpid);
					R::Model('project.plan.expense.calculate',$tpid);
					$ret.=R::Page('project.plan.detail',NULL,$tpid,'view',$trid);
					return $ret;
				} else {
					$ret.=R::View('project.plan.expense.form',$trid,post('expid'));
					return $ret;
				}
			}
			break;

		case 'removeexp' :
			if ($isEdit && SG\confirm()) {
				$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="exptr" AND `trid`=:trid LIMIT 1';
				mydb::query($stmt,':tpid',$tpid, ':tagname',$tagname, ':trid',post('expid'));
				R::On('project.plan.change',$tpid);
				R::Model('project.plan.expense.calculate',$tpid);
				//$ret.='trid='.$trid;
				$ret.=R::Page('project.plan.detail',NULL,$tpid,'view',$trid);
			}
			return $ret;
			break;

		case 'reorder':
			$activity=$projectInfo->activity[$trid];
			if (SG\confirm()) {
				if ($trid && ($to=post('to'))) {
					if ($to=='top') {
						$to=post('min')-1;
						// เพิ่มลำดับของทุกกิจกรรมขึ้นไปอีก 1
						mydb::query('SET @n:=1 ;');
						$stmt='UPDATE %project_tr% SET `sorder`= @n := @n+1 WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" ORDER BY `sorder` ASC;';
						mydb::query($stmt,':tpid',$tpid, ':tagname',$tagname);
						// บันทึกลำดับของกิจกรรมที่ต้องการย้าย
						mydb::query('UPDATE %project_tr% SET `sorder`=:to WHERE `trid`=:trid LIMIT 1',':trid',$trid,':to',$to);
						//$ret.=mydb()->_query.'<br />';
					} else {
						// เพิ่มลำดับของกิจกรรมหลัง
						mydb::query('UPDATE %project_tr% SET `sorder`= `sorder`+1 WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" AND `sorder`>:to ORDER BY `sorder` ASC',':tpid',$tpid, ':tagname',$tagname, ':to',$to);
						//$ret.=mydb()->_query.'<br />';
						// บันทึกลำดับของกิจกรรมที่ต้องการย้าย
						mydb::query('UPDATE %project_tr% SET `sorder`=:to WHERE `trid`=:trid LIMIT 1',':trid',$trid,':to',$to+1);
						//$ret.=mydb()->_query.'<br />';
						// เรียงลำดับกิจกรรมใหม่
						mydb::query('SET @n:=0 ;');
						mydb::query('UPDATE %project_tr% SET `sorder`= @n := @n+1 WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" ORDER BY `sorder` ASC;',':tpid',$tpid, ':tagname',$tagname);
						//$ret.=mydb()->_query.'<br />';
					}
					//R::On('project.proposal.change',$tpid);
					//$projectInfo=R::Model('project.get',$tpid);
					//$ret.=print_o($activity,'$activity');
					$ret.=R::Page('project.plan.tree',NULL,$tpid);
					return $ret;
				}
			} else {
				$ret.='<h4>เปลี่ยนลำดับกิจกรรม</h4>';
				$ret.='<h5>กิจกรรม : '.$activity->title.'</h5>';

				$form=new Form('data',url('project/plan/'.$tpid.'/reorder/'.$trid),NULL,'sg-form');
				if (_AJAX) $form->addData('rel','replace:#project-plan-item-master');
				$form->addAttr('onsubmit','$.colorbox.close()');

				$form->addField('confirm',array('type'=>'hidden','name'=>'confirm','value'=>'yes'));

				$options=array();
				$options['top']='บนสุด';
				$min=999999999;
				foreach ($projectInfo->activity as $item) {
					//$ret.=$trid.' : '.$item->trid.' : '.$item->sorder.' : '.$item->title.' expense='.count($item->expense).'<br />';
					if ($trid==$item->trid || $item->parent!=$activity->parent) continue;
					$options[$item->sorder]='หลัง : '.$item->title;
					$min=$item->sorder<$min?$item->sorder:$min;
				}
				$form->addField('min',array('type'=>'hidden','name'=>'min','value'=>$min));
				$form->addField('to',array('type'=>'radio','name'=>'to','label'=>'เลือกลำดับของกิจกรรมที่ต้องการย้ายกิจกรรมนี้ไป','options'=>$options));

				$form->addField(
									'save',
									array(
										'type'=>'button',
										'name'=>'save',
										'value'=>'บันทึก',
										'posttext'=>' หรือ <a href="javascript:void(0)" onclick="$.colorbox.close()">ยกเลิก</a>'
										)
									);

				$ret .= $form->build();
				//$ret.=print_o($projectInfo->activity,'$projectInfo->activity');
				return $ret;
			}
			break;

		case 'changeparent':
			$activity=$devInfo->activity[$trid];
			if (SG\confirm()) {
				if ($trid && $to=post('to')) {
					if ($to=='master') $to=NULL;
					// บันทึก parent ของกิจกรรมที่ต้องการย้าย
					mydb::query('UPDATE %project_tr% SET `parent`=:to WHERE `trid`=:trid LIMIT 1',':trid',$trid,':to',$to);
					//$ret.=mydb()->_query.'<br />';

					//R::On('project.change',$tpid);
					$ret.=R::Page('project.plan.tree',NULL,$tpid);
					return $ret;
				}
			} else {
				$ret.='<h4>ย้ายกิจกรรมไปอยู่ภายใต้กิจกรรมหลักอื่น</h4>';
				$ret.='<h5>กิจกรรม : '.$activity->title.'</h5>';

				$form=new Form('data',url('project/plan/'.$tpid.'/changeparent/'.$trid),'project-edit-movemainact','sg-form');
				if (_AJAX) $form->addData('rel','replace:#project-plan-item-master');
				$form->addAttr('onsubmit','$.colorbox.close()');

				$form->addField('confirm',array('type'=>'hidden','name'=>'confirm','value'=>'yes'));

				$options=array();
				$options['master']='กิจกรรมหลัก/แผนดำเนินงาน';
				foreach ($projectInfo->activity as $item) {
					//$ret.=$trid.' : '.$item->trid.' : '.$item->sorder.' : '.$item->title.' expense='.count($item->expense).'<br />';
					if ($trid==$item->trid || $item->expense) continue;
					if ($item->parent) {
						$options['ภายใต้กิจกรรมย่อยอื่น:'][$item->trid]=$item->title;
					} else {
						$options['ภายใต้กิจกรรมหลักอื่น:'][$item->trid]=$item->title;
					}
				}
				$form->addField('to',array('type'=>'radio','name'=>'to','label'=>'เลือกกิจกรรมหลักที่ต้องการย้ายกิจกรรมนี้ไป:','options'=>$options));

				$form->addField(
									'save',
									array(
										'type'=>'button',
										'name'=>'save',
										'value'=>'<i class="icon -save -white"></i><span>ย้ายกิจกรรม</span>',
										'posttext'=>' หรือ <a href="javascript:void(0)" onclick="$.colorbox.close()">ยกเลิก</a>'
										)
									);

				$ret .= $form->build();
				//$ret.=print_o($projectInfo->activity,'$activity');
				return $ret;
			}
			break;

		case 'deletetarget':
			if ($isEdit && $trid && post('tgtid') && SG\confirm()) {
				$stmt='DELETE FROM %project_target% WHERE `tpid`=:tpid AND `trid`=:trid AND `tagname`="project:mainact" AND `tgtid`=:tgtid LIMIT 1';
				mydb::query($stmt, ':tpid',$tpid, ':tagname',$tagname, ':trid',$trid,':tgtid',post('tgtid'));
			}
			break;

		case 'info' :
			$mainact=$info->mainact[$actid];
			$ret.='<h3>กิจกรรมหลัก : '.$mainact->title.'</h3>';
			$ret.='<p><b>งบประมาณ '.number_format($mainact->budget,2).' บาท</b></p>';
			$ret.='<h3>วัตถุประสงค์</h3>';
			$ret.='<p>'.$mainact->objectiveTitle.'</p>';
			$ret.='<h3>รายละเอียดกิจกรรม : </h3>'.sg_text2html($mainact->desc);
			$ret.='<h3>ผลผลิต : </h3>'.sg_text2html($mainact->output);
			$ret.='<h3>ผลลัพธ์ : </h3>'.sg_text2html($mainact->outcome);
			$ret.='<h3>ภาคีร่วมสนับสนุน : </h3>'.sg_text2html($mainact->copartner);

			//$ret.=print_o($mainact,'$mainact');
			//$ret.=print_o($info->mainact,'$info');

			return $ret;
			break;
	}



	return $ret;
}


/**
* Add/edit plan/mainact of project
*
* @param Integer $tpid
* @param Integer $objectId
* @return String
*/
function __project_plan_add($projectInfo,$planId,$data) {
	$post=(object)post('data');
	$tpid = $projectInfo->projectId;

	//if (!property_exists($post, 'objective') && $objectiveId) $post->objective[$objectiveId]=$objectiveId;
	$objective=project_model::get_tr($tpid,'info:objective');

	if ($post->title) {
		$post->trid=$planId;
		$post->tpid=$tpid;
		$post->uid=i()->uid;
		$post->fromdate=sg_date($post->fromdate,'Y-m-d');
		$post->todate=sg_date($post->todate,'Y-m-d');
		$post->parentObjectiveId=reset($post->objective);
		$post->sorder=mydb::select('SELECT MAX(`sorder`) maxOrder FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="mainact" LIMIT 1',':tpid',$tpid)->maxOrder+1;
		$post->formid='info';
		$post->part='mainact';
		$post->created=date('U');

		if (cfg('project.options.multipletarget')) {
			$post->target=0;
			foreach (cfg('project.target') as $key => $value) {
				$post->{$key}=sg_strip_money($post->{$key});
				$post->target+=$post->{$key};
			}
		} else {

		}

		$stmt='INSERT INTO %project_tr%
						(`trid`, `tpid`, `parent`, `uid`, `sorder`, `formid`, `part`
						, `num1`, `num2`
						, `detail1`, `text1`, `date1`, `date2`
						, `text3`, `text4`, `text6`
						, `created`)
						VALUES
						(:trid, :tpid, :parentObjectiveId, :uid, :sorder, :formid, :part
						, :budget, :target
						, :title, :desc, :fromdate, :todate
						, :output, :copartner, :outcome
						, :created)
						ON DUPLICATE KEY
						UPDATE `num1`=:budget
						, `num2`=:target
						, `detail1`=:title
						, `text1`=:desc
						, `date1`=:fromdate
						, `date2`=:todate
						, `text3`=:output
						, `text4`=:copartner
						, `text6`=:outcome';
		mydb::query($stmt,$post);
		$post->mainActId=$planId?$planId:mydb()->insert_id;
		$ret.=mydb()->_query.'<br />';
		if (!mydb()->_error) {
			$remainObjectId=array();
			foreach ($post->objective as $item) if ($item) $remainObjectId[]=$item;
			//$ret.=print_o($remainObjectId,'$remainObjectId');
			$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="actobj" AND `gallery`=:mainActId '.($remainObjectId?' AND `parent` NOT IN (:objidset)':'');
			mydb::query($stmt,':tpid',$tpid, ':calid',$post->calid, ':mainActId',$post->mainActId, ':objidset','SET:'.implode(',',$remainObjectId));
			//$ret.=mydb()->_query.'<br />';

			/*
			foreach ($post->objid as $key => $value) {
				$stmt='INSERT INTO %project_actguide% (`tpid`,`calid`,`objid`) VALUES (:tpid,:calid,:objid)
							ON DUPLICATE KEY UPDATE `objid`=:objid';
				mydb::query($stmt,':tpid',$tpid, ':calid',$post->calid, ':objid',$value);
				//$ret.=mydb()->_query.'<br />';
			}
			*/

			foreach ($post->objective as $parentObjectiveId=>$objIdKey) {
				$parentObjective->trid=$objIdKey;
				$parentObjective->tpid=$tpid;
				$parentObjective->parentObjectiveId=$parentObjectiveId;
				$parentObjective->mainActId=$post->mainActId;
				$parentObjective->uid=i()->uid;
				$parentObjective->formid='info';
				$parentObjective->part='actobj';
				$parentObjective->created=date('U');
				$stmt='INSERT INTO %project_tr%
								(`trid`, `tpid`, `parent`, `gallery`, `uid`, `formid`, `part`, `created`)
							VALUES
								(:trid, :tpid, :parentObjectiveId, :mainActId, :uid, :formid, :part, :created)
							ON DUPLICATE KEY UPDATE `parent`=:parentObjectiveId';
				mydb::query($stmt,$parentObjective);
				//$ret.=mydb()->_query.'<br />';
			}
			$stmt='UPDATE %project_tr% tr
							LEFT JOIN %project_tr% o ON o.`tpid`=:tpid AND o.`formid`="info" AND o.`part`="actobj" AND o.`gallery`=tr.`trid`
						SET tr.`parent`=o.`parent`
						WHERE tr.`trid`=:trid';
			mydb::query($stmt, ':tpid',$tpid, ':trid',$post->mainActId);
			//$ret.=mydb()->_query;
		}
		$ret.=print_o($post,'$post');
		//$ret.=__project_mainact_detail($tpid,$mainActId);
		return $ret;
	}

	if ($data) $post=$data;
	foreach (explode('|',$post->parentObjectiveList) as $item) {
		list($id,$parent)=explode('=',$item);
		$post->objective[$parent]=$id;
	}

	$stmt='SELECT `num3` `studentjoin`, `num4` `teacherjoin`, `num5` `parentjoin`, `num6` `clubjoin`, `num7` `localorgjoin`, `num8` `govjoin`, `num9` `otherjoin` FROM %project_tr% WHERE `trid`=:trid LIMIT 1';
	$joinRs=mydb::select($stmt,':trid',$post->trid);
	$post->studentjoin=number_format($joinRs->studentjoin);
	$post->teacherjoin=number_format($joinRs->teacherjoin);
	$post->parentjoin=number_format($joinRs->parentjoin);
	$post->clubjoin=number_format($joinRs->clubjoin);
	$post->localorgjoin=number_format($joinRs->localorgjoin);
	$post->govjoin=number_format($joinRs->govjoin);
	$post->otherjoin=number_format($joinRs->otherjoin);

	$ret.='<h4>เพิ่มกิจกรรมหลัก</h4>';


	$form = new Form([
		'variable' => 'data',
		'checkValid' => true,
		'action' => url('project/plan/'.$tpid.($post->trid?'/edit/'.$planId:'/add')),
		'id' => 'project-edit-movemainact',
		'class' => 'sg-form',
		'attr' => _AJAX ? 'data-rel="box" data-callback="refreshContent" data-refresh-url="'.url('paper/'.$tpid).'" data-done="close"' : NULL,
		'children' => [
			'title' => [
				'type' => 'text',
				'label' => 'ชื่อกิจกรรมหลัก',
				'class' => '-fill',
				'require' => true,
				'value' => htmlspecialchars($post->title),
			],

			'objective' => [
				'label' => 'เลือกวัตถุประสงค์ :',
				'type' => 'textfield',
				'value' => (function($objective, $post){
					$result = '';
					foreach ($objective->items['objective'] as $item) {
						$result .= '<div class="form-item"><label class="option" style="display:block;"><input type="checkbox" name="data[objective]['.$item->trid.']" value="'.$post->objective[$item->trid].'" '.($post->objective[$item->trid]?'checked="checked"':'').' /> '.$item->text1.'</label></div>'._NL;
					}
					return $result;
				})($objective, $post),
			],
			'budget' => [
				'type' => 'text',
				'label' => 'งบประมาณ (บาท)',
				'class' => '-money',
				'value' => htmlspecialchars($post->budget),
			],
			'target' => cfg('project.options.multipletarget') ? NULL : [
				'type' => 'text',
				'label' => 'กลุ่มเป้าหมาย (คน)',
				'class' => '-numeric',
				'value' => number_format($post->target,0,'',''),
			],
			'fromdate' => [
				'type' => 'text',
				'label' => 'ระยะเวลาดำเนินงาน จาก',
				'class' => 'sg-datepicker -date',
				'require' => true,
				'value' => sg_date(SG\getFirst($post->fromdate,date('Y-m-d')),'d/m/Y'),
				'attr' => [
					'data-min-date'=>sg_date($info->project->date_from,'d/m/Y'),
					'data-max-date'=>sg_date($info->project->date_end,'d/m/Y'),
					'data-change-month'=>true,
					'data-change-year'=>true,
				],
			],
			'todate' => [
				'type' => 'text',
				'label' => 'ถึง',
				'class' => 'sg-datepicker -date',
				'require' => true,
				'value' => sg_date(SG\getFirst($post->todate,date('Y-m-d')),'d/m/Y'),
				'attr' => [
					'data-min-date'=>sg_date($info->project->date_from,'d/m/Y'),
					'data-max-date'=>sg_date($info->project->date_end,'d/m/Y'),
					'data-change-month'=>true,
					'data-change-year'=>true,
				],
			],
			'desc' => [
				'type' => 'textarea',
				'label' => 'รายละเอียดกิจกรรม',
				'class' => '-fill',
				'rows' => 3,
				'value' => htmlspecialchars($post->desc),
			],
			'output' => [
				'type' => 'textarea',
				'label' => 'ผลผลิต (Output)',
				'class' => '-fill',
				'rows' => 3,
				'value' => htmlspecialchars($post->output),
			],
			'outcome' => [
				'type' => 'textarea',
				'label' => 'ผลลัพธ์ (Outcome)',
				'class' => '-fill',
				'rows' => 3,
				'value' => htmlspecialchars($post->outcome),
			],
			'copartner' => [
				'type' => 'textarea',
				'label' => 'ภาคีร่วมสนับสนุน',
				'class' => '-fill',
				'rows' => 3,
				'description' => 'ระบุชื่อภาคีและวิธีการสนับสนุน เช่น งบประมาณ สิ่งของ การเข้าร่วมอื่นๆ',
				'value' => htmlspecialchars($post->copartner),
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-re="close"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			],
		],
	]);

	$ret .= $form->build();

	if (user_access(false)) $ret.='<hr /><a class="sg-action" href="'.url('project/plan/'.$tpid.'/add/'.$objectiveId).'" data-rel="box">Refresh</a>';

	//$ret.=print_o($post,'$post');
	//$ret.=print_o($info,'$info');
	return $ret;
}
?>