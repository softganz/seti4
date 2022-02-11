<?php
/**
* Project Development Target
*
* @param Object $self
* @param Int $tpid
* @param String $action
* @return String
*/
function project_develop_target($self, $tpid, $action = NULL, $tranId = NULL) {
	$devInfo = is_object($tpid) ? $tpid : R::Model('project.develop.get',$tpid, '{initTemplate: true}');
	$tpid = $devInfo->tpid;

	$isEdit = $action == 'edit';

	$options = new StdClass;
	$options->usePresetTarget = true;

	//$targetList = model::get_category('project:target', 'catid');

	$stmt = 'SELECT
					  p.`catid` `parentId`, p.`name` `parentName`
					, c.`catid`, c.`name` `targetName`
					, tr.`trid`, tr.`refid`, tr.`num1` `amount`
					FROM %tag% p
						LEFT JOIN %tag% c ON c.`taggroup` = "project:target" AND c.`catparent` = p.`catid` AND c.`process` IS NOT NULL
						LEFT JOIN %project_tr% tr ON tr.`tpid` = :tpid AND tr.`formid` = "develop" AND tr.`part` = "target" AND tr.`refid` = c.`catid`
					WHERE p.`taggroup` = "project:target" AND p.`catparent` IS NULL AND p.`process` IS NOT NULL
					UNION
					SELECT
					  3, NULL
					, t2.`trid`, t2.`detail1`
					, t2.`trid`, NULL, t2.`num1`
					FROM %project_tr% t2
					WHERE t2.`tpid` = :tpid AND t2.`formid` = "develop" AND t2.`part` = "target" AND t2.`refid` IS NULL
					ORDER BY `parentId`, `catid`
					;
					-- {group:"parentId", key:"catid"}';

	$stmt = 'SELECT
					  p.`catid` `parentId`, p.`name` `parentName`
					, c.`catid`, c.`name` `targetName`
					, tr.`trid`, tr.`tgtid`, tr.`amount`
					FROM %tag% p
						LEFT JOIN %tag% c ON c.`taggroup` = "project:target" AND c.`catparent` = p.`catid` AND c.`process` IS NOT NULL
						LEFT JOIN %project_target% tr ON tr.`tpid` = :tpid AND tr.`tagname` = "develop" AND tr.`tgtid` = c.`catid`
					WHERE p.`taggroup` = "project:target" AND p.`catparent` IS NULL AND p.`process` IS NOT NULL
					UNION
					SELECT
					  3, "กลุ่มเป้าหมายจำแนกเพิ่มเติม"
					, t2.`tgtid`, t2.`tgtid`
					, t2.`trid`, t2.`tgtid`, t2.`amount`
					FROM %project_target% t2
					WHERE t2.`tpid` = :tpid AND t2.`tagname` = "develop"
						AND t2.`tgtid` REGEXP "[[:alpha:]]"
					ORDER BY `parentId`, `catid`
					;
					-- {group:"parentId", key:"catid"}';

	$targetList = mydb::select($stmt, ':tpid', $tpid)->items;
	//$ret .= mydb()->_query;
	//$ret .= print_o($targetList,'$targetList');

	$targetTables = new Table();
	$targetTables->addClass('-target');
	$targetTables->thead = array('กลุ่มเป้าหมาย','amt'=>'จำนวน(คน)');
	if ($isEdit) $targetTables->thead['icons -c1 -hover-parent'] = '';
	foreach ($targetList as $targetGroup) {
		$h = reset($targetGroup);

		if($h->parentName)
			$targetTables->rows[] = array('<b>'.$h->parentName.'</b>','');
		foreach ($targetGroup as $key => $targetItem) {
			$menu = '';
			if ($isEdit && $targetItem->tgtid) {
				$menu .= '<nav class="nav iconset -hover">';
				$menu .= '<a class="sg-action" href="'.url('project/develop/'.$tpid.'/info/target.delete',array('id'=>$targetItem->tgtid)).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="#project-develop-target" data-ret="'.url('project/develop/'.$tpid.'/target/edit').'"><i class="icon -cancel -gray"></i></a>';
				$menu .= '</nav>';
			}
			unset($row);
			$row = array(
					$targetItem->parentId == 3 ?
						view::inlineedit(
							array(
								'group' => 'target:develop:target:'.$targetItem->trid,
								'fld' => 'tgtid',
								'tgtid' => $targetItem->catid,
								'class' => '-fill',
								'placeholder' => '?',
								'options' => array(
															'rel' => "project-develop-target",
															'ret'=>url('project/develop/'.$tpid.'/target/edit')
														)
							),
							$targetItem->targetName,
							$isEdit
						)
						:
						$targetItem->targetName,
					view::inlineedit(
						array(
							'group' => 'target:develop:target:'.$targetItem->catid,
							'fld' => 'amount',
							'class' => '-numeric -fill',
							'tgtid' => $targetItem->catid,
							'ret' => 'numeric',
							'placeholder' => '?',
						),
						$targetItem->amount ? number_format($targetItem->amount) : '',
						$isEdit
					),
					'config' => array(
												'class' => 'project-target-item',
												'data-tgtid' => $targetItem->catid,
											)
				);
			if ($isEdit) $row[] = $menu;

			$targetTables->rows[] = $row;
		}
	}

	if ($isEdit) {
		$ret .= '<form class="sg-form" method="post" action="'.url('project/develop/info/'.$tpid.'/target.add').'" data-checkvalid="yes" data-rel="#project-develop-target" data-ret="'.url('project/develop/'.$tpid.'/target/edit').'">';
		$targetTables->rows[] = array(
												'<input class="form-text -fill -require" type="text" name="targetname" maxlength="50" placeholder="ระบุกลุ่มเป้าหมายเพิ่มเติม" />',
												'<input class="form-text -fill -numeric -require" type="text" name="targetsize" size="3" placeholder="0" /> '
												,'<button class="btn -link" type="submit"><i class="icon -add"></i></button>',
											'config' => array('class' => '-no-print'),
											);
	}
	$ret .= $targetTables->build();
	if ($isEdit)
		$ret .= '</form>';

	//$ret .= print_o($targetList,'$targetList');
	return $ret;
}
?>