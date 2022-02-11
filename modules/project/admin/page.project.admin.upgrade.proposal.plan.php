<?php
function project_admin_upgrade_proposal_plan($self) {
	R::View('project.toolbar', $self, 'Project Development Upgrade : Project Plan', 'develop');

	if (!user_access('administer projects')) return message('error','access denied');
	$isSimulate=false;

	$post = (Object) post('upgrade');
	$tpid = $post->tpid;
	$all = $post->all;
	$remove = $post->remove;



	$form = new Form([
		'variable' => 'upgrade',
		//'method' => 'get',
		'action' => url('project/admin/upgrade/proposal/plan'),
		'children' => [
			'tpid' => [
				'type' => 'text',
				'label' => 'หมายเลขโครงการ',
				'value' => $tpid
			],
			'all' => [
				'type' => 'checkbox',
				'label' => 'ทุกโครงการ',
				'options' => [1 => 'ทุกโครงการ'],
				'value' => $all,
			],
			'remove' => [
				'type' => 'checkbox',
				'label' => 'ลบแผนงานเดิม',
				'options' => [1 => 'ลบแผนงานเดิม'],
				'value' => $remove,
			],
			'confirm' => [
				'type' => 'button',
				'value' => 'START UPGRADE',
				'container' => '{class: "-sg-text-right"}',
			],
		],
	]);

	$ret .= $form->build();



	if (!($tpid || $all)) return $ret;

	$ret.='<h2>Start upgrade</h2>';

	if ($tpid && $remove) {
		mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part` IN ("objective","mainact") AND `flag`=1',':tpid',$tpid);
		$ret.=mydb()->_query.'<br />';
	}

	mydb::where('`keyname` = "project.develop" AND `fldname` LIKE "plan-%"');
	if ($tpid) mydb::where('`keyid` = :tpid',':tpid',$tpid);

	$stmt='SELECT *
				FROM %bigdata%
				%WHERE%
				ORDER BY `keyid`,`fldname`';
	$dbs=mydb::select($stmt);

	$curTpid=0;
	foreach ($dbs->items as $k=>$rs) {
		if (empty($rs->flddata)) continue;
		list($plan,$id,$name)=explode('-',$rs->fldname);
		$id=intval($id);
		$rs->name=$name;
		$rs->id=$id;
		$items[$rs->keyid][$id][$name]=$rs;
	}
	foreach ($items as $tpid=>$rows) {
		ksort($items[$tpid]);
		ksort($rows);
		$objid=0;
		foreach ($rows as $rowid=>$row) {
			foreach ($row as $key => $rs) {
				if ($key=='objective') {
					$objid++;
					$objs[$tpid][$objid]['objective']=$rs->flddata;
					if (empty($objs[$tpid][$objid]['indicator'])) $objs[$tpid][$objid]['indicator']='';
					$objs[$tpid][$objid]['created']=$rs->created;
					$objs[$tpid][$objid]['uid']=$rs->ucreated;
					$objs[$tpid][$objid]['modified']=$rs->modified;
					$objs[$tpid][$objid]['modifyby']=$rs->umodified;
				}
				//$ret.='objid='.$objid.'<br />'.print_o($rs,'$rs');
			}
			foreach ($row as $key => $rs) {
				if ($key=='objective') {
					unset($items[$tpid][$rowid][$key]);
					continue;
				}
				if (!$objs[$tpid][$objid]['item'][$rs->id]) {
					$objs[$tpid][$objid]['item'][$rs->id]=array('indicator'=>'','activity'=>'','period'=>'','output'=>'','parties'=>'');
				}
				$items[$tpid][$rowid][$key]->objid=$objid;
				if ($key=='indicator') $objs[$tpid][$objid]['indicator'].='- '.trim($rs->flddata)._NL;
				$objs[$tpid][$objid]['item'][$rs->id][$key]=$rs->flddata;

				if ($rs->created>$objs[$tpid][$objid]['item'][$rs->id]['created']) $objs[$tpid][$objid]['item'][$rs->id]['created']=$rs->created;
				if ($rs->ucreated>0 && $rs->ucreated!=$objs[$tpid][$objid]['item'][$rs->id]['uid']) $objs[$tpid][$objid]['item'][$rs->id]['uid']=$rs->ucreated;
				if ($rs->modified>$objs[$tpid][$objid]['item'][$rs->id]['modified']) $objs[$tpid][$objid]['item'][$rs->id]['modified']=$rs->modified;
				if ($rs->umodified>0 && $rs->umodified!=$objs[$tpid][$objid]['item'][$rs->id]['modifyby']) $objs[$tpid][$objid]['item'][$rs->id]['modifyby']=$rs->umodified;
			}
		}
		foreach ($objs[$tpid] as $obj) {
			$objrs['tpid']=$tpid;
			$objrs['uid']=$obj['uid'];
			$objrs['objective']=trim($obj['objective']);
			$objrs['indicator']=trim($obj['indicator']);
			$objrs['created']=$obj['created'];
			$objrs['modified']=SG\getFirst($obj['modified'],NULL);
			$objrs['modifyby']=SG\getFirst($obj['modifyby'],NULL);
			$stmt='INSERT INTO %project_tr%
							(`tpid`,`formid`,`part`, `flag`, `uid`,`text1`,`text2`,`created`,`modified`,`modifyby`)
						VALUES
							(:tpid,"info","objective", 1, :uid,:objective,:indicator,:created,:modified,:modifyby)';
			if (!$isSimulate) mydb::query($stmt,$objrs);
			$obj_insertid=mydb()->insert_id;
			$ret.='<p>'.($isSimulate?$stmt:mydb()->_query).(mydb()->_error?'<br /><font color="red">'.mydb()->_error.'</font>':'').'</p>';
			foreach ($obj['item'] as $rs) {
				$rs['tpid']=$tpid;
				$rs['parent']=$obj_insertid;
				$rs['desc']=$rs['activity'];
				$rs['modified']=SG\getFirst($rs['modified'],NULL);
				$rs['modifyby']=SG\getFirst($rs['modifyby'],NULL);
				/*
				  `tpid`, `trid`, `uid`
				, `parent` `objectiveId`
				, `num1` `budget`
				, `num2` `target`
				, `num3` `targetChild`
				, `num4` `targetTeen`
				, `num5` `targetWorker`
				, `num6` `targetElder`
				, `num7` `targetDisabled`
				, `num8` `targetWoman`
				, `num9` `targetMuslim`
				, `num10` `targetWorkman`
				, `detail1` `title`
				, `text1` `desc`
				, `text2` `indicator`
				, `detail2` `timeprocess`
				, `text3` `output`
				, `text4` `copartner`
				, `text5` `budgetdetail`
				, `created`, `modified`, `modifyby`
				*/
				$stmt='INSERT INTO %project_tr% 
								(`tpid`, `parent`, `formid`, `part`, `flag`, `uid`, `detail1`, `text1`, `text2`, `text3`, `detail2`, `text4`, `created`, `modified`, `modifyby`)
							VALUES
								(:tpid, :parent, "info", "mainact", 1, :uid, :activity, :desc, :indicator, :output, :period, :parties, :created, :modified, :modifyby)';
				if (!$isSimulate) mydb::query($stmt,$rs);
				$ret.='<p>'.($isSimulate?$stmt:mydb()->_query).(mydb()->_error?'<br /><font color="red">'.mydb()->_error.'</font>':'').'</p>';
			}
		}
	}
	//$ret.=print_o($objs,'$objs');
	//$ret.=print_o($items,'$items');

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>