<?php
function project_info_target($self, $tpid = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{initTemplate: true}');
	$tpid = $projectInfo->tpid;

	$tagname = 'info';
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;


	$ret .= '<div id="project-info-target-'.$tpid.'" class="project-info-target">'._NL;

	$targetTables = new Table();
	$targetTables->addClass('-target');
	$targetTables->thead = array('กลุ่มเป้าหมาย','amt -target'=>'จำนวนที่วางไว้(คน)','amt -output'=>'จำนวนที่เข้าร่วม(คน)','icons -c1 -hover-parent'=>'');

	$targetTables->rows['totaltarget'] = array(
						'<b>จำนวนกลุ่มเป้าหมายทั้งหมด</b>',
						'',
						$projectInfo->info->jointarget
					);

	$targetTables->rows[] = '<header>';

	foreach ($projectInfo->target as $targetGroup) {
		$h = reset($targetGroup);

		$targetTables->rows[] = array('<b>'.$h->parentName.'</b>','','');
		foreach ($targetGroup as $key => $targetItem) {
			$ui = new Ui();
			if ($isEdit) {
				if (!is_numeric($targetItem->catid)) {
					$ui->add('<a class="sg-action btn -link" href="'.url('project/'.$tpid.'/info/target.delete',array('tid'=>$targetItem->catid)).'" data-rel="none" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-removeparent="tr"><i class="icon -cancel -gray"></i></a>');
				}
			}
			$menu = $ui->count() ? '<nav class="nav -icons -hover">'.$ui->build().'</nav>' : '';
			$targetAmount += $targetItem->amount;
			$targetTables->rows[] = array(
					$targetItem->targetName,
					View::inlineedit(
						array(
							'group'=>'target:'.$targetItem->catid,
							'fld'=>'amount',
							'tagname'=>'info',
							'tr'=>$targetItem->catid,
							'class'=>'-fill',
							'ret'=>'numeric',
						),
						$targetItem->amount?number_format($targetItem->amount):'',
						$isEdit
					),
					is_null($targetItem->joinamt) ? '-' : number_format($targetItem->joinamt),
					$menu,
					'config'=>array('class'=>'project-target-item','data-tgtid'=>$targetItem->catid));
		}
	}
	$targetTables->rows['totaltarget'][1] = $targetAmount;

	if ($isEdit) {
		$ret .= '<form class="sg-form" method="post" action="'.url('project/'.$tpid.'/info/target.add').'" data-checkvalid="yes" data-rel="replace:#project-info-target-'.$tpid.'" data-ret="'.url('project/'.$tpid.'/info.target').'">';
		$targetTables->rows[] = array(
												'<input class="form-text -fill -require" type="text" name="targetname" placeholder="ระบุกลุ่มเป้าหมายเพิ่มเติม" />',
												'<input class="form-text -numeric -require" type="text" name="amount" size="5" placeholder="0" />',
												'',
												'<button class="btn -link"><i class="icon -add"></i></button>',
											'config' => array('class' => '-no-print'),
											);
	}
	$ret .= $targetTables->build();
	if ($isEdit) $ret .= '</form>';

	$ret .= '</div><!-- project-info-target-'.$tpid.' -->'._NL;

	return $ret;

	/*

		$targetList = R::Model('category.get','project:target', 'catid', '{condition: "`process` IS NOT NULL"}');

		$stmt = 'SELECT
						  p.`catid` `parentId`, p.`name` `parentName`
						, c.`catid`, c.`name` `targetName`
						, tr.`trid`, tr.`refid`, tr.`num1` `amount`
						FROM %tag% p
							LEFT JOIN %tag% c ON c.`taggroup`="project:target" AND c.`catparent`=p.`catid`
							LEFT JOIN %project_tr% tr ON tr.`tpid`=:tpid AND tr.`formid`="develop" AND tr.`part`="target" AND tr.`refid`=c.`catid`
						WHERE p.`taggroup`="project:target" AND p.`process` IS NOT NULL AND p.`catparent` IS NULL
						UNION
						SELECT
						  3, NULL
						, t2.`trid`, t2.`detail1`
						, t2.`trid`, NULL, t2.`num1`
						FROM %project_tr% t2
						WHERE t2.`tpid` = :tpid AND t2.`formid` = "info" AND t2.`part` = "target" AND t2.`refid` IS NULL
						ORDER BY `parentId`, `catid`
						;
						-- {group:"parentId", key:"catid"}';

		$targetList = mydb::select($stmt, ':tpid', $tpid)->items;
		$ret .= mydb()->_query;
		$ret .= print_o($targetList);

		$targetTables = new Table();
		$targetTables->addClass('-target');
		$targetTables->thead = array('กลุ่มเป้าหมาย','amt -hover-parent'=>'จำนวน(คน)');
		foreach ($targetList as $targetGroup) {
			$h = reset($targetGroup);

			if($h->parentName)
				$targetTables->rows[] = array('<b>'.$h->parentName.'</b>','');
			foreach ($targetGroup as $key => $targetItem) {
				$menu = '';
				if ($isEdit && $targetItem->trid) {
					$menu .= '<nav class="nav iconset -hover">';
					$menu .= '<a class="sg-action" href="'.url('project/develop/target/'.$tpid.'/delete/'.$targetItem->trid).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="#project-develop-target"><i class="icon -cancel -gray"></i></a>';
					$menu .= '</nav>';
				}
				$targetTables->rows[] = array(
						$targetItem->parentId == 3 ?
							view::inlineedit(
								array(
									'group' => 'info:target:'.$targetItem->trid,
									'fld' => 'detail1',
									'class' => '-fill',
									'tr' => $targetItem->trid,
									'placeholder' => '?',
								),
								$targetItem->targetName,
								$isEdit
							)
							:
							$targetItem->targetName,
						view::inlineedit(
							array(
								'group' => 'info:target:'.$targetItem->catid,
								'fld' => 'num1',
								'class' => '-fill',
								'tr' => $targetItem->trid,
								'refid' => $targetItem->catid,
								'ret' => 'numeric',
								'placeholder' => '?',
							),
							$targetItem->amount ? number_format($targetItem->amount) : '',
							$isEdit
						)
						. $menu,
						'config' => array(
													'class' => 'project-target-item',
													'data-tgtid' => $targetItem->catid,
												)
					);
			}
		}

		if ($isEdit) {
			$ret .= '<form class="sg-form" method="post" action="'.url('project/develop/target/'.$tpid.'/add').'" data-checkvalid="yes" data-rel="#project-develop-target">';
			$targetTables->rows[] = array(
													'<input class="form-text -fill -require" type="text" name="targetname" placeholder="ระบุกลุ่มเป้าหมายเพิ่มเติม" />',
													'<input class="form-text -numeric -require" type="text" name="targetsize" size="3" placeholder="0" /> '
													. '<button class="btn -primary -circle32"><i class="icon -save -white"></i></button>',
												'config' => array('class' => '-no-print'),
												);
		}
		$ret .= $targetTables->build();
		if ($isEdit)
			$ret .= '</form>';

		return $ret;
	*/
}
?>