<?php
function project_admin_upgrade_proposal_objective($self) {
	R::View('project.toolbar', $self, 'Project Development Upgrade : Project Objective', 'develop');

	if (!user_access('administer projects')) return message('error','access denied');
	$post=(object)post('upgrade');
	$tpid=post('tpid');
	$all=post('all');
	$remove=post('remove');
	$isSimulate=post('simulate');

	$form = new Form([
		'variable' => 'upgrade',
		'method' => 'get',
		'action' => url('project/develop/upgrade/objective'),
		'id' => 'project-develop-upgrade',
		'children' => [
			'tpid' => ['name'=>'tpid','type'=>'text','label'=>'หมายเลขโครงการ', 'value'=>$tpid],
			'all' => [
				'type' => 'checkbox',
				'name' => 'all',
				'label' => 'ทุกโครงการ',
				'options' => [1 => 'ทุกโครงการ'],
			],
			'remove' => [
				'type' => 'checkbox',
				'name' => 'remove',
				'label' => 'ลบวัตถุประสงค์เดิม',
				'options' => [1 => 'ลบวัตถุประสงค์เดิม'],
			],
			'simulate' => [
				'type' => 'checkbox',
				'name' => 'simulate',
				'label' => 'Simulate',
				'options' => [1 => 'Simulate Only'],
				'value' => $isSimulate,
			],
			'go' => [
				'type' => 'button',
				'value' => 'START UPGRADE',
				'pretext' => '<a class="btn -link -cancel" href="'.url('project/develop/upgrade/objective').'"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a> ',
				'container' => '{class: "-sg-text-right"}',
			],
		],
	]);

	$ret .= $form->build();

	if (!SG\confirm()) return $ret;
	if (!($tpid || $all)) return $ret;

	$ret.='<h2>Start upgrade</h2>';

	if ($remove) {
		if ($tpid) {
			mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part` IN ("objective")',':tpid',$tpid);
		} else {
			mydb::query('DELETE FROM %project_tr% WHERE `tpid` IN (SELECT `tpid` FROM %project_dev% WHERE `pryear`="2015") AND `formid`="info" AND `part` IN ("objective")');
		}
		$ret.=mydb()->_query.'<br />';
	}

	$where=array();
	$where=sg::add_condition($where,'`keyname`="project.develop" AND `fldname` LIKE "objective-%"');
	if ($tpid) $where=sg::add_condition($where,'`keyid`=:tpid','tpid',$tpid);

	$stmt='SELECT *
				FROM %bigdata%
				'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
				ORDER BY `keyid`,`fldname`';
	$dbs=mydb::select($stmt,$where['value']);

	$curTpid=0;
	foreach ($dbs->items as $k=>$rs) {
		if (empty($rs->flddata)) continue;
		list($obj,$id,$name)=explode('-',$rs->fldname);
		$id=intval($id);
		$rs->name=$name;
		$rs->id=$id;
		$items[$rs->keyid][$id][$name]=$rs;
	}

	foreach ($items as $tpid=>$rows) {
		foreach ($rows as $objid=>$obj) {
			$title=$obj['title']->flddata;
			$indicators=trim($obj['indicators']->flddata);
			foreach (explode("\n",$title) as $eachTitle) {
				$eachTitle=trim($eachTitle);
				if (empty($eachTitle) || strlen($eachTitle)<5) continue;
				$ret.='Objective='.$eachTitle.'<br />Indicators='.$indicators.'<br />';

				unset($objrs);
				$objrs['tpid']=$tpid;
				$objrs['parent']=$obj['title']->id;
				$objrs['uid']=SG\getFirst($obj['title']->ucreated,NULL);
				$objrs['objective']=$eachTitle;
				$objrs['indicator']=$indicators;
				$objrs['created']=$obj['title']->created;
				$objrs['modified']=SG\getFirst($obj['title']->modified,NULL);
				$objrs['modifyby']=SG\getFirst($obj['title']->umodified,NULL);
				$stmt='INSERT INTO %project_tr%
								(`tpid`, `parent`, `formid`,`part`, `flag`, `uid`,`text1`,`text2`,`created`,`modified`,`modifyby`)
							VALUES
								(:tpid, :parent, "info","objective", 1, :uid,:objective,:indicator,:created,:modified,:modifyby)';
				if (!$isSimulate) mydb::query($stmt,$objrs);
				$obj_insertid=mydb()->insert_id;
				$ret.='<p>'.($isSimulate?$stmt:mydb()->_query).(mydb()->_error?'<br /><font color="red">'.mydb()->_error.'</font>':'').'</p>';
				//$ret.=print_o($objrs,'$objrs');
			}
			//$ret.=print_o($obj,'$obj');
		}
	}
	//$ret.=print_o($objs,'$objs');
	//$ret.=print_o($items,'$items');

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>