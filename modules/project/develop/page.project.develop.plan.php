<?php
import('model:project.proposal.php');

function project_develop_plan($self, $tpid = NULL, $action = NULL, $tranId = NULL) {
	if (empty($action)) $action=post('action');

	$tagname="develop";
	$devInfo=R::Model('project.develop.get',$tpid);

	if (empty($devInfo)) return message('error','No project');

	if (empty($tpid) || mydb::select('SELECT `tpid` FROM %topic% WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid)->_empty) return 'No project';


	$isEdit=$devInfo->RIGHT & _IS_EDITABLE;
	$isAdmin=$devInfo->RIGHT & _IS_ADMIN;


	switch ($action) {

		case 'info':
			if ($isAdmin) {
				$rs=$devInfo->activity[$tranId];
				$rs->created=sg_date($rs->created,'Y-m-d H:i:s');
				if ($rs->modified) $rs->modified=sg_date($rs->modified,'Y-m-d H:i:s');
				$iTable = new Table();
				foreach ($rs as $key => $value) $iTable->rows[]=array($key,$value);
				$ret .= $iTable->build();
			}
			return $ret;
			break;

		case 'add' :
			if ($isEdit) {
				//$ret.=print_o(post(),'post()');
				//$ret.=__project_plan_add($tpid,NULL,NULL,$info);
				$title = post('title');
				if ($title) {
					if ($before = post('before')) {
						$sorder = $before;
						// เพิ่มลำดับของกิจกรรมหลัง
						mydb::query('UPDATE %project_tr% SET `sorder`= `sorder`+1 WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" AND `sorder`>=:before ORDER BY `sorder` ASC',':tpid',$tpid, ':tagname',$tagname ,':before',$before);
					} else {
						$sorder = mydb::select('SELECT MAX(`sorder`) `maxOrder` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" LIMIT 1',':tpid',$tpid, ':tagname',$tagname)->maxOrder+1;
					}

					$data->tpid = $tpid;
					$data->parent = $tranId;
					$data->tagname = $tagname;
					$data->sorder = $sorder;
					$data->uid = i()->uid;
					$data->title = $title;
					$data->created = date('U');
					$data->fromdate = post('fromdate')?sg_date(post('fromdate'),'Y-m-d'):NULL;
					$data->todate = post('todate')?sg_date(post('todate'),'Y-m-d'):NULL;
					$data->detail = post('detail');
					$data->outputoutcome = post('outputoutcome');
					$data->orgsupport = post('orgsupport');
					$data->budget = post('budget');
					$stmt='INSERT INTO %project_tr%
						(`tpid`, `parent`, `sorder`, `uid`, `formid`, `part`, `date1`, `date2`, `detail1`, `text1`,`text3`, `text4`, `num1`, `created`)
						VALUES
						(:tpid, :parent, :sorder, :uid, :tagname , "activity", :fromdate, :todate, :title, :detail, :outputoutcome, :orgsupport, :budget, :created)';
					mydb::query($stmt, $data);
					$addTrid = mydb()->insert_id;
					//$ret.=mydb()->_query.'<br />';
					//$ret.=print_o($data,'$data');

					// เรียงลำดับกิจกรรมใหม่
					if ($before) {
						mydb::query('SET @n:=0 ;');
						mydb::query('UPDATE %project_tr% SET `sorder`= @n := @n+1 WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" ORDER BY `sorder` ASC;',':tpid',$tpid, ':tagname',$tagname);
					}

					// Calculate total budget
					ProjectProposalModel::calculateBudget($tpid);


					// Return case
					if (post('ret')) {
						$ret .= R::Page('project.develop.plan.'.post('ret'), NULL, $tpid, 'edit');
					} else $ret .= R::Page('project.develop.plan.tree', NULL, $tpid, 'view', $tranId);
				} else {
					$options = (object)array('ret' => post('ret'), 'rel' => post('rel'));
					$ret .= R::View('project.develop.plan.form', $tpid, $tranId, $options);
				}
			}
			return $ret;
			break;

		case 'remove' :
			if ($isEdit && $tranId && SG\confirm()) {
				// Delete Objective
				$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="actobj" AND `refid`=:trid';
				mydb::query($stmt,':tpid',$tpid, ':tagname',$tagname, ':trid',$tranId);
				//$ret.=mydb()->_query.'<br />';

				// Delete Expense
				$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="exptr" AND `parent`=:trid';
				mydb::query($stmt,':tpid',$tpid, ':tagname',$tagname, ':trid',$tranId);
				//$ret.=mydb()->_query.'<br />';

				// Delete Target


				$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `trid`=:trid AND `formid`=:tagname AND `part`="activity" LIMIT 1';
				mydb::query($stmt, ':tpid',$tpid, ':tagname',$tagname, ':trid',$tranId);
				//$ret.=mydb()->_query.'<br />';

				ProjectProposalModel::calculateBudget($tpid);
			}
			return $ret;
			break;

		case 'addtarget' :
			$data=post();
			$stmt='INSERT INTO %project_target%
						(`tpid`, `trid`, `tagname`, `tgtid`, `amount`)
						VALUES
						(:tpid, :trid, :tagname, :target, :amount)
						ON DUPLICATE KEY UPDATE
						`amount`=:amount';
			mydb::query($stmt,':tpid',$tpid, ':tagname',$tagname.':mainact', ':trid',$tranId, $data);
			//$ret.=mydb()->_query;

			R::On('project.proposal.change',$tpid);
			$ret.=R::View('project.develop.plan.target',$devInfo,$tranId);
			//$ret.=print_o(post(),'post()');
			return $ret;
			break;

		case 'removetarget' :
			$stmt='DELETE FROM %project_target% WHERE `tpid`=:tpid AND `trid`=:mainactid AND `tagname`=:tagname AND `tgtid`=:tgtid LIMIT 1';
			mydb::query($stmt, ':tpid',$tpid, ':tagname',$tagname.':mainact', ':mainactid',$tranId, ':tgtid',post('target'));
			//$ret.=mydb()->_query;
			R::On('project.proposal.change',$tpid);
			$ret.=R::View('project.develop.plan.target',$devInfo,$tranId);
			return $ret;
			break;

		case 'addexp' :
			//$ret.='trid='.$tranId.'<br />';
			if ($tpid && $tranId && post('exp')) {
				//$ret.=print_o(post('exp'),'exp');
				$exp=(object)post('exp');
				$exp->parent=$tranId;
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
								(:expid, :tpid, :parent, :expcode, :formid,"exptr",:amt,:unitprice,:times,:total,:unitname,:detail,:uid,:created)
								ON DUPLICATE KEY
								UPDATE `gallery`=:expcode, `num1`=:amt, `num2`=:unitprice, `num3`=:times, `num4`=:total, `detail1`=:unitname, `text1`=:detail, `modified`=:modified, `modifyby`=:modifyby';
				mydb::query($stmt,$exp);
				//debugMsg(mydb()->_query);
				R::On('project.proposal.change',$tpid,'addexp',array('value'=>'บันทึกค่าใช้จ่ายพัฒนาโครงการ  '.$exp->expcode.'='.$exp->total));

				$ret .= ProjectProposalModel::calculateExpense($tpid);
				$ret.=R::Page('project.develop.plan.single',NULL,$tpid,'edit');
				//$devInfo=R::Model('project.develop.get',$tpid);
				//$ret.=R::View('project.develop.plan.render', $devInfo, $devInfo->activity[$tranId], true);
				//$ret.=R::View('project.develop.plan.activity',$devInfo,$tranId);
				return $ret;
			} else {
				$ret.=R::View('project.develop.plan.exp.form', $tranId, post('expid'));
				return $ret;
			}
			break;

		case 'removeexp' :
			if (SG\confirm()) {
				mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="exptr" AND `trid`=:trid LIMIT 1',':tpid',$tpid, ':tagname',$tagname, ':trid',post('expid'));
				ProjectProposalModel::calculateExpense($tpid);
				//$ret.='trid='.$tranId;
				R::On('project.proposal.change',$tpid);

				$ret.=R::Page('project.develop.plan.single',NULL,$tpid,'edit');

				//$devInfo=R::Model('project.develop.get',$tpid);
				//$ret.=R::View('project.develop.plan.detail',$devInfo,$tranId);
				//$ret.=R::View('project.develop.plan.activity',$devInfo,$tranId);
			}
			return $ret;
			break;

		case 'calculatebudget':
			// Calculate total budget
			return ProjectProposalModel::calculateBudget($tpid);
			break;

		case 'calculateexp' :
			$ret = ProjectProposalModel::calculateExpense($tpid);
			return $ret;
			break;

		case 'reorder':
			$activity=$devInfo->activity[$tranId];
			if (SG\confirm()) {
				if ($tranId && $to=post('to')) {
					if ($to=='top') {
						$to=post('min')-1;
						// เพิ่มลำดับของทุกกิจกรรมขึ้นไปอีก 1
						mydb::query('SET @n:=1 ;');
						$stmt='UPDATE %project_tr% SET `sorder`= @n := @n+1 WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" ORDER BY `sorder` ASC;';
						mydb::query($stmt,':tpid',$tpid, ':tagname',$tagname);
						// บันทึกลำดับของกิจกรรมที่ต้องการย้าย
						mydb::query('UPDATE %project_tr% SET `sorder`=:to WHERE `trid`=:trid LIMIT 1',':trid',$tranId,':to',$to);
						//$ret.=mydb()->_query.'<br />';
					} else {
						// เพิ่มลำดับของกิจกรรมหลัง
						mydb::query('UPDATE %project_tr% SET `sorder`= `sorder`+1 WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" AND `sorder`>:to ORDER BY `sorder` ASC',':tpid',$tpid, ':tagname',$tagname, ':to',$to);
						//$ret.=mydb()->_query.'<br />';
						// บันทึกลำดับของกิจกรรมที่ต้องการย้าย
						mydb::query('UPDATE %project_tr% SET `sorder`=:to WHERE `trid`=:trid LIMIT 1',':trid',$tranId,':to',$to+1);
						//$ret.=mydb()->_query.'<br />';
						// เรียงลำดับกิจกรรมใหม่
						mydb::query('SET @n:=0 ;');
						mydb::query('UPDATE %project_tr% SET `sorder`= @n := @n+1 WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" ORDER BY `sorder` ASC;',':tpid',$tpid, ':tagname',$tagname);
						//$ret.=mydb()->_query.'<br />';
					}
					R::On('project.proposal.change',$tpid);
					$devInfo=R::Model('project.develop.get',$tpid);
					//$ret.=print_o($activity,'$activity');
					$ret.=R::View('project.develop.plan.detail',$devInfo,$devInfo->activity[$tranId]->parent);
					$ret.=R::View('project.develop.plan.activity',$devInfo,$devInfo->activity[$tranId]->parent);
					return $ret;
				}
			} else {
				$ret.='<h4>เปลี่ยนลำดับกิจกรรม</h4>';
				$ret.='<h5>กิจกรรม : '.$activity->title.'</h5>';

				$form=new Form('data',url('project/develop/plan/'.$tpid.'/reorder/'.$tranId),'project-edit-movemainact','sg-form');
				if (_AJAX) $form->addData('rel','#project-develop-plan-item-'.($activity->parent?$activity->parent:'master'));
				$form->addAttr('onsubmit','$.colorbox.close()');

				$form->addField('confirm',array('type'=>'hidden','name'=>'confirm','value'=>'yes'));

				$options=array();
				$options['top']='บนสุด';
				$min=999999999;
				foreach ($devInfo->activity as $item) {
					//$ret.=$tranId.' : '.$item->trid.' : '.$item->sorder.' : '.$item->title.' expense='.count($item->expense).'<br />';
					if ($tranId==$item->trid || $item->parent!=$activity->parent) continue;
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
				//$ret.=print_o($devInfo->activity,'$devInfo->activity');
				return $ret;
			}
			break;

		case 'changeparent':
			$activity=$devInfo->activity[$tranId];
			if (SG\confirm()) {
				if ($tranId && $to=post('to')) {
					if ($to=='master') $to=NULL;
					// บันทึก parent ของกิจกรรมที่ต้องการย้าย
					mydb::query('UPDATE %project_tr% SET `parent`=:to WHERE `trid`=:trid LIMIT 1',':trid',$tranId,':to',$to);
					//$ret.=mydb()->_query.'<br />';

					R::On('project.proposal.change',$tpid);
					$parent=$devInfo->activity[$tranId]->parent;
					$devInfo=R::Model('project.develop.get',$tpid);
					//$ret.=print_o($activity,'$activity');
					$ret.=R::View('project.develop.plan.detail',$devInfo,$parent);
					$ret.=R::View('project.develop.plan.activity',$devInfo,$parent);
					return $ret;
				}
			} else {
				$ret.='<h4>ย้ายกิจกรรมไปอยู่ภายใต้กิจกรรมหลักอื่น</h4>';
				$ret.='<h5>กิจกรรม : '.$activity->title.'</h5>';

				$form=new Form('data',url('project/develop/plan/'.$tpid.'/changeparent/'.$tranId),'project-edit-movemainact','sg-form');
				if (_AJAX) $form->addData('rel','#project-develop-plan-item-'.($activity->parent?$activity->parent:'master'));
				$form->addAttr('onsubmit','$.colorbox.close()');

				$form->addField('confirm',array('type'=>'hidden','name'=>'confirm','value'=>'yes'));

				$options=array();
				$options['master']='กิจกรรมหลัก/แผนดำเนินงาน';
				foreach ($devInfo->activity as $item) {
					//$ret.=$tranId.' : '.$item->trid.' : '.$item->sorder.' : '.$item->title.' expense='.count($item->expense).'<br />';
					if ($tranId==$item->trid || $item->expense) continue;
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
				//$ret.=print_o($devInfo->activity,'$activity');
				return $ret;
			}
			break;

		case 'addobj' :
			if (SG\confirm()) {
				if (post('id') && post('to')) {
					if (
						!mydb::select('SELECT `parent` FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',post('id'))->parent
						|| mydb::select('SELECT COUNT(*) `total` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="actobj" AND `gallery`=:actid LIMIT 1',':tpid',$tpid, ':tagname',$tagname,':actid',post('id'))->total==0
						) {
						//mydb::query('UPDATE %project_tr% SET `parent`=:to WHERE `trid`=:from LIMIT 1',':from',post('id'), ':to',post('to'));
					}
					$isDup=mydb::select('SELECT `parent` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="actobj" AND `gallery`=:actid AND `parent`=:to LIMIT 1',':tpid',$tpid, ':tagname',$tagname, ':actid',post('id'), ':to',post('to'))->parent;
					if (!$isDup) {
						$stmt = 'INSERT INTO %project_tr%
										(`tpid`, `parent`, `refid`, `gallery`, `formid`, `part`, `uid`, `created`)
										VALUES
										(:tpid, :parent, :refid, :refid, :tagname, "actobj", :uid, :created)';
						mydb::query($stmt,':tpid', $tpid, ':tagname', $tagname, ':parent', post('to'), ':refid', post('id'), ':uid', i()->uid, ':created', date('U'));
					}
					R::On('project.proposal.change',$tpid);
				}
			}
			return $ret;
			break;

		case 'removeobj' :
			if (post('id')) {
				$currentObjId=mydb::select('SELECT `parent` FROM %project_tr% WHERE `trid`=:actid LIMIT 1',':actid',post('actid'))->parent;
				mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="actobj" AND `gallery`=:actid AND `parent`=:parent LIMIT 1',':tpid',$tpid, ':tagname',$tagname ,':actid',post('actid'), ':parent',post('id'));

				// Remove mainact objective (parent) when post('id')=current objective
				//$ret.='currentObjId='.$currentObjId;
				if ($currentObjId==post(id)) {
					$objid=mydb::select('SELECT `parent` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="actobj" AND `gallery`=:actid LIMIT 1',':tpid',$tpid,':actid',post('actid'))->parent;
					//mydb::query('UPDATE %project_tr% SET `parent`=:parent WHERE `trid`=:actid LIMIT 1',':tpid',$tpid, ':tagname',$tagname, ':actid',post('actid'), ':parent',$objid);
				}
				//$ret.=mydb()->_query;
				R::On('project.proposal.change',$tpid);
			}
			return $ret;
			break;

		case 'refresh' :
			$ret.=R::View('project.develop.plan.detail',$devInfo,$tranId);
			$ret.=R::View('project.develop.plan.activity',$devInfo,$tranId);
			return $ret;
			break;

		case 'detail' :
			$ret.=R::View('project.develop.plan.detail',$devInfo,$tranId);
			return $ret;
			break;

	}

	$planFormatType=SG\getFirst($devInfo->info->template,'tree');
	//$ret.='$planFormatType='.$planFormatType;
	$ret.=R::Page('project.develop.plan.'.$planFormatType,NULL,$devInfo);
	//$ret.=print_o($devInfo);

	return $ret;
}
?>