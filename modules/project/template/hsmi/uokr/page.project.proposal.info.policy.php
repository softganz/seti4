<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_proposal_info_policy($self, $proposalInfo = NULL, $policyId = NULL, $action = NULL) {
	$tpid = $proposalInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$isAdmin = $proposalInfo->RIGHT & _IS_ADMIN;
	$isEditable = $isAdmin || $proposalInfo->RIGHT & _IS_EDITABLE;
	$isInEditMode = $isEditable && $action == 'edit';

	$policyInfo = mydb::select('SELECT * FROM %tag% WHERE `taggroup` = "project:uokr:policy" AND `catid` = :catid LIMIT 1', ':catid', $policyId);

	$ret = '<header class="header"><h5>'.$policyInfo->name.'</h5></header>'._NL;


	$ret .= '<div class="-grant">'._NL;

	$ret .= '<header class="header"><h5>หน่วยให้ทุน</h5></header>'._NL;

	$stmt = 'SELECT * FROM %tag% WHERE `taggroup` = "project:uokr:grant" AND `catparent` = :parent';

	$dbs = mydb::select($stmt, ':parent', $policyId);

	foreach ($dbs->items as $rs) {
		$ret .='<abbr class="checkbox -block -grant'.($rs->catid == $proposalInfo->data['grant'] ? ' -active' : '').'"><label>'
			.view::inlineedit(
				array(
					'group'=>'bigdata:project.develop:grant',
					'fld' => 'grant',
					'name' => 'grant',
					'tr' => $rs->bigid,
					'value' => $proposalInfo->data['grant'],
					'callback' => 'grantChange',
				),
				$rs->catid.':'.$rs->name,
				$isInEditMode,
				'radio')
			. ' </label>'
			. '<div class="detail">ตัวเลือกของแหล่งทุน</div>'
			. '</abbr>'._NL;
	}


	$ret .= nl2br($policyInfo->description)._NL;


	$ret .= '</div><!-- box -grant -->'._NL._NL._NL;




	// Plateform ของ Policy ที่ระบุมาใน $policyId
	$stmt = 'SELECT
		  tg.`catid` `plateformId`, tg.`catparent` `policyId`, tg.`name`
		, tr.`bigid`, tr.`flddata`, tg.`process`, tg.`weight`
		FROM %tag% tg
			LEFT JOIN %bigdata% tr ON tr.`keyname` = "project.develop" AND tr.`keyid` = :tpid AND tr.`fldname` = "plateform" AND tr.`fldref` = tg.`catid`
		WHERE tg.`taggroup` = "project:uokr:plateform" AND tg.`catparent` = :parent AND tg.`process` = 1
		ORDER BY tg.`weight` ASC, tg.`catid` ASC';

	$plateformDbs = mydb::select($stmt,':tpid',$tpid, ':parent', $policyId);

	// Key Result ของแต่ละเป้าหมายของแพลตฟอร์ม
	$stmt = 'SELECT
		  tg.`catid` `objectiveId`, tg.`catparent` `plateformId`, tg.`name`
		, tr.`bigid`, tr.`flddata`, tg.`process`, tg.`weight`
		FROM %tag% tg
			LEFT JOIN %bigdata% tr ON tr.`keyname` = "project.develop" AND tr.`keyid` = :tpid AND tr.`fldname` = "objective" AND tr.`fldref` = tg.`catid`
		WHERE tg.`taggroup` = "project:uokr:objective" AND tg.`process` = 1
		ORDER BY tg.`weight` ASC, tg.`catid` ASC;
		-- {group: "plateformId", key: "objectiveId"}';

	$objectiveDbs = mydb::select($stmt,':tpid',$tpid)->items;

	// Key Result ของแต่ละเป้าหมายของแพลตฟอร์ม
	$stmt = 'SELECT
		  tg.`catid` `keyresultId`, tg.`catparent` `plateformId`, tg.`name`
		, tr.`bigid`, tr.`flddata`, tg.`process`, tg.`weight`
		FROM %tag% tg
			LEFT JOIN %bigdata% tr ON tr.`keyname` = "project.develop" AND tr.`keyid` = :tpid AND tr.`fldname` = "keyresult" AND tr.`fldref` = tg.`catid`
		WHERE tg.`taggroup` = "project:uokr:keyresult" AND tg.`process` = 1
		ORDER BY tg.`weight` ASC, tg.`catid` ASC;
		-- {group: "plateformId", key: "keyresultId"}';

	$keyresultDbs = mydb::select($stmt,':tpid',$tpid)->items;

	// Process ของแต่ละเป้าหมายของแพลตฟอร์ม
	$stmt = 'SELECT
		  tg.`catid` `processId`, tg.`catparent` `objectiveId`, tg.`name`
		, tr.`bigid`, tr.`flddata`, tg.`process`, tg.`weight`
		FROM %tag% tg
			LEFT JOIN %bigdata% tr ON tr.`keyname` = "project.develop" AND tr.`keyid` = :tpid AND tr.`fldname` = "process" AND tr.`fldref` = tg.`catid`
		WHERE tg.`taggroup` = "project:uokr:process" AND tg.`process` = 1
		ORDER BY tg.`weight` ASC, tg.`catid` ASC;
		-- {group: "objectiveId", key: "processId"}';

	$processDbs = mydb::select($stmt,':tpid',$tpid)->items;


	// objectiveOfProcess ของแต่ละ Process ของเป้าหมาย ของแพลตฟอร์ม
	$stmt = 'SELECT
		  tg.`catid` `objectiveId`, tg.`catparent` `processId`, tg.`name`
		, tr.`bigid`, tr.`flddata`, tg.`process`, tg.`weight`
		FROM %tag% tg
			LEFT JOIN %bigdata% tr ON tr.`keyname` = "project.develop" AND tr.`keyid` = :tpid AND tr.`fldname` = "process-objective" AND tr.`fldref` = tg.`catid`
		WHERE tg.`taggroup` = "project:uokr:process:objective" AND tg.`process` = 1
		ORDER BY tg.`weight` ASC, tg.`catid` ASC;
		-- {group: "processId", key: "objectiveId"}';

	$objectiveOfProcessDbs = mydb::select($stmt,':tpid',$tpid)->items;

	// keyresultOfProcess ของแต่ละ Process ของเป้าหมาย ของแพลตฟอร์ม
	$stmt = 'SELECT
		  tg.`catid` `keyresultId`, tg.`catparent` `objectiveId`, tg.`name`
		, tr.`bigid`, tr.`flddata`, tg.`process`, tg.`weight`
		FROM %tag% tg
			LEFT JOIN %bigdata% tr ON tr.`keyname` = "project.develop" AND tr.`keyid` = :tpid AND tr.`fldname` = "process-objective-keyresult" AND tr.`fldref` = tg.`catid`
		WHERE tg.`taggroup` = "project:uokr:process:keyresult" AND tg.`process` = 1
		ORDER BY tg.`weight` ASC, tg.`catid` ASC;
		-- {group: "objectiveId", key: "keyresultId"}';

	$keyresultOfObjectiveDbs = mydb::select($stmt,':tpid',$tpid)->items;



	$ret .= '<section id="project-info-plateform" class="project-info-plateform">'._NL;
	$ret .= '<header class="header"><h5>แพลตฟอร์ม (Plateform)</h5></header>'._NL;


	foreach ($plateformDbs->items as $plateformRs) {
		$plateformStr = '<header class="header"><h6>เป้าหมายของแพลตฟอร์ม (Objective)</h6></header>'._NL;

		foreach ($objectiveDbs[$plateformRs->plateformId] as $key => $objectiveItem) {
			$plateformStr .= '<div class="uokr-objective">'._NL;
			$objectiveId = $objectiveItem->objectiveId;
			$plateformStr .='<abbr class="checkbox -block"><label>'
				.view::inlineedit(
					array(
						'group'=>'bigdata:project.develop:objective-'.$objectiveId,
						'fld' => 'objective',
						'fldref' => $objectiveId,
						'tr' => $objectiveItem->bigid,
						'value'=>$objectiveItem->flddata,
						'removeempty'=>'yes',
					),
					$objectiveId.':'.$objectiveItem->name.' ('.$objectiveId.')',
					$isInEditMode,
					'checkbox')
				. ' </label>'
				//. '<div class="detail">'.$plateformStr.'</div>'
				. ' </abbr>'._NL;

			if ($keyresultDbs[$plateformRs->plateformId]) $plateformStr .= '<header class="header"><h6>ผลสัมฤทธิ์ที่สำคัญของเป้าหมาย (Key Result)</h6></header>'._NL;
			foreach ($keyresultDbs[$plateformRs->plateformId] as $key => $keyresultItem) {
				$keyId = $keyresultItem->keyresultId;
				$plateformStr .='<abbr class="checkbox -block"><label>'
					.view::inlineedit(
						array(
							'group'=>'bigdata:project.develop:'.$keyId,
							'fld' => 'keyresult',
							'fldref' => $keyId,
							'tr' => $keyresultItem->bigid,
							'value'=>$keyresultItem->flddata,
							'removeempty'=>'yes',
						),
						$keyId.':'.$keyresultItem->name.' ('.$keyId.')',
						$isInEditMode,
						'checkbox')
					. ' </label>'
					//. '<div class="detail">'.$plateformStr.'</div>'
					. ' </abbr>'._NL;
			}

			if ($processDbs[$objectiveId]) $plateformStr .= '<header class="header"><h6>Program</h6></header>'._NL;
			$plateformStr .= '<div class="uokr-process-wrapper -sg-flex -flex-nowrap">'._NL;
			foreach ($processDbs[$objectiveId] as $key => $processItem) {
				$processId = $processItem->processId;

				$objectiveOfProcessStr = '';
				if ($objectiveOfProcessDbs[$processId]) $objectiveOfProcessStr .= '<header class="header"><h6>เป้าหมายของ Program (Program Objective)</h6></header>'._NL;
				foreach ($objectiveOfProcessDbs[$processId] as $objectiveOfProcessItem) {
					$objectiveOfProcessId = $objectiveOfProcessItem->objectiveId;

					$keyresultOfObjectiveStr = '<header class="header"><h6>ผลสัมฤทธิ์ของ Program (Program Key Result)</h6></header>'._NL;
					//$keyresultOfObjectiveStr .= print_o($keyresultOfObjectiveDbs[$objectiveOfProcessId],'$keyresultOfProcessDbs');
					foreach ($keyresultOfObjectiveDbs[$objectiveOfProcessId] as $keyresultOfObjectiveItem) {
						$keyresultOfObjectiveId = $keyresultOfObjectiveItem->objectiveId;
						$keyresultOfObjectiveStr .='<abbr class="checkbox -block"><label>'
							.view::inlineedit(
								array(
									'group'=>'bigdata:project.develop:process-objective-keyresult-'.$keyresultOfObjectiveId,
									'fld' => 'process-objective-keyresult',
									'fldref' => $keyresultOfObjectiveId,
									'tr' => $keyresultOfObjectiveItem->bigid,
									'value'=>$keyresultOfObjectiveItem->flddata,
									'removeempty'=>'yes',
								),
								$keyresultOfObjectiveId.':'.$keyresultOfObjectiveItem->name.' ('.$keyresultOfObjectiveId.')',
								$isInEditMode,
								'checkbox')
							. ' </label>'
							. ' </abbr>'._NL;
					}



					$objectiveOfProcessStr .='<abbr class="checkbox -block"><label>'
						.view::inlineedit(
							array(
								'group'=>'bigdata:project.develop:process-objective-'.$objectiveOfProcessId,
								'fld' => 'process-objective',
								'fldref' => $objectiveOfProcessId,
								'tr' => $objectiveOfProcessItem->bigid,
								'value'=>$objectiveOfProcessItem->flddata,
								'removeempty'=>'yes',
							),
							$objectiveOfProcessId.':'.$objectiveOfProcessItem->name.' ('.$objectiveOfProcessId.')',
							$isInEditMode,
							'checkbox')
						. ' </label>'
						. '<div class="detail-process-objective-keyresult">'.$keyresultOfObjectiveStr.'</div>'
						. ' </abbr>'._NL;
				}

				//$objectiveOfProcessStr .= print_o($objectiveOfProcessDbs[$processId],'$objectiveOfProcessDbs');



				$plateformStr .='<abbr class="checkbox -block"><label>'
					.view::inlineedit(
						array(
							'group'=>'bigdata:project.develop:process-'.$processId,
							'fld' => 'process',
							'fldref' => $processId,
							'tr' => $processItem->bigid,
							'value'=>$processItem->flddata,
							'removeempty'=>'yes',
						),
						$processId.':'.$processItem->name.' ('.$processId.')',
						$isInEditMode,
						'checkbox')
					. ' </label>'
					. '<div class="detail-process-objective">'.$objectiveOfProcessStr.'</div>'
					. '<nav class="nav"><a class="sg-action btn" href="'.url('project/'.$proposalInfo->parent.'/child.proposal', array('process'=>$processId)).'" data-rel="box" data-width="640"><i class="icon -material">list</i><span>รายชื่อโครงการ</span></a></nav>'
					. ' </abbr>'._NL;

			}

			$plateformStr .= '</div><!-- uokr-process-wrapper -->'._NL;

			$plateformStr .= '</div><!-- uokr-objective -->'._NL._NL;
		}


		$ret .= '<div class="uokr-platform">'._NL;

		$ret .='<abbr class="checkbox -plateform -block'.($plateformRs->plateformId == $plateformRs->flddata ? ' -active' : '').'"><label>'
			.view::inlineedit(
				array(
					'group'=>'bigdata:project.develop:platform-'.$plateformRs->plateformId,
					'fld' => 'plateform',
					'fldref' => $plateformRs->plateformId,
					'tr' => $plateformRs->bigid,
					'value'=>$plateformRs->flddata,
					'removeempty'=>'yes',
					'callback' => 'plateformChange',
				),
				$plateformRs->plateformId.':'.$plateformRs->name.' ('.$plateformRs->plateformId.')',
				$isInEditMode,
				'checkbox')
			. ' </label>'
			. '<div class="detail">'.$plateformStr.'</div>'
			. ' </abbr>'._NL;

		$ret .= '</div><!-- uokr-platform -->'._NL._NL;

		//$ret .= print_o($plateformRs,'$plateformRs');
	}


	//$ret .= print_o($plateformDbs, '$plateformDbs');
	//$ret .= print_o($objectiveDbs, '$objectiveDbs');
	//$ret .= print_o($keyresultDbs, '$keyresultDbs');
	//$ret .= print_o($processDbs, '$processDbs');


	$ret .= '</section><!-- box -plateform -->'._NL;

	//$ret .= print_o($policyInfo,'$policyInfo');
	//$ret .= print_o($proposalInfo,'$proposalInfo');

	$ret .= '<style type="text/css">
	.checkbox.-active {border: 1px #ffb482 solid; border-radius: 8px; background-color: #ffe8d8; margin-bottom: 8px;}
	.checkbox.-active:hover {border: 1px #ffc49c solid; background-color: #fff2e9;}
	.checkbox.-active>label {font-weight: bold;}
	.checkbox>.detail {display: none; font-size: 0.9em; color: #666; padding: 4px 16px 0 16px;}
	.checkbox.-active>.detail {display: block;}
	.uokr-platform>.checkbox.-active>label {background-color: #8dffa0; padding: 8px; border-radius: 8px 8px 0 0;}
	.uokr-platform>.checkbox.-active {padding: 0;}
	.uokr-process-wrapper>.checkbox {flex: 1 1 0px;}
	.detail-process-objective {background-color: #ffb98a; padding: 0;}
	.detail-process-objective>.checkbox {padding: 0;}
	.detail-process-objective>.checkbox>label {padding: 8px;}
	.detail-process-objective-keyresult {background-color: #ffd7c0;}
	.uokr-objective>.checkbox>label {background-color: #ddd; padding: 8px;}
	</style>';

	$ret .= '<script type="text/javascript">
	function grantChange($this,data,$parent) {
		console.log("CHANGE", $this)
		$(".checkbox.-grant").removeClass("-active")
		$this.closest(".checkbox").addClass("-active")
	}

	function plateformChange($this,data,$parent) {
		console.log("CHANGE", data)
		if (data.value) {
			$this.closest(".checkbox").addClass("-active")
		} else {
			$this.closest(".checkbox").removeClass("-active")
		}
	}
	</script>';

	return $ret;
}
?>