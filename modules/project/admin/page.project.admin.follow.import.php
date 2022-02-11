<?php
/**
* Module :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{id}/method
*/

$debug = true;

function project_admin_follow_import($self) {
	$toolbar = new Toolbar($self, 'Project Administrator');
	$self->theme->sidebar = R::View('project.admin.menu','follow');

	$ret = '';

	$isSimulate = post('simulate') == 'Yes';
	$importAreacode = post('areacode');
	$importSeries = post('importseries');
	$importUid = post('uid');
	$importName = post('name');
	$personType = post('persontype');
	$sourceData = post('data');
	$updateData = post('update');

	$deleteOldSeries = post('deleteseries') == 'Yes';
	$showQt = post('showqt') == 'Yes';
	$isDebug = post('debug');
	$dateCreatedU = date('U');

	$splitAddress = false;


	$ret .= '<div style="width: 100%;">';
	$ret .= '<header class="header"><h3>นำเข้าข้อมูลโครงการ</h3></header>';

	$tableError = new Table();
	$tableError->caption='รายการนำเข้าผิดพลาด';

	$tableComplete = new Table();
	$tableComplete->caption='รายการนำเข้าสมบูรณ์';

	$tableQt = new Table();
	$tableQt->caption='รายการนำเข้าแบบสอบถาม';


	$dataLineFormat = 'รหัสองค์กร	ประเภทโครงการ	รหัสพื้นที่	uid	รหัสชุดโครงการ	ชื่อโครงการ	ปี	เริ่ม	สิ้นสุด	อนุมัติ	งบ';

	if ($sourceData) {

		$lines = explode("\n", $sourceData);
		$sep = "\t";

		foreach ($lines as $key=>$line) {
			unset($error);
			$line = trim($line);
			if (empty($line)) continue;

			$row = str_getcsv($line,$sep);
			foreach ($row as $k=>$v) $row[$k] = trim($v);

			//$ret .= print_o($row,'$row');

			if ($row[0] == 'title') {
				unset($lines[$key]);
				continue;
			}

			// If no title
			if (empty($row[5])) continue;

			$project = new stdClass();
			$project->orgid = $row[0];
			$project->prtype = $row[1];
			$areacode = SG\getFirst($row[2], $importAreacode);
			if ($areacode) {
				$project->areacode = $areacode;
				$project->changwat = substr($areacode, 0, 2);
				$project->ampur = substr($areacode, 2, 2);
				$project->tambon = substr($areacode, 4, 2);
			}
			$project->uid = SG\getFirst($row[3], $importUid);
			$project->projectset = $row[4];
			$project->title = $row[5];
			$project->pryear = $row[6];
			$project->date_from = $row[7];
			$project->date_end = $row[8];
			$project->date_approve = $row[9];
			$project->budget = $row[10];

			if (in_array($project->prtype, array('แผนงาน','ชุดโครงการ'))) {
				$project->ischild = 1;
			}

			if ($isDebug) $ret .= print_o($project,'$project');

			if (!$isSimulate) {
				$result = R::Model('project.create', $project, '{debug: true}');
				if ($isDebug) $ret .= print_o($result, '$result');
			}

			if ($error) {
				$tableError->rows[] = $project;
				$tableError->rows[] = '<tr><td colspan="15"><span style="color:red;">'.$error.'</span></td></tr>';
			} else {
				$tableComplete->rows[] = (Array) $project;
				$complete[] = $line;
				unset($lines[$key]);
			}
		}
		$post->data = implode("\n", $lines);
	}



	$form = new Form(NULL, url(q()), 'edit-info', 'sg-form');
	$form->addData('checkValid', true);

	$form->addField('uid', array('type' => 'hidden', 'name' => 'uid', 'id' => 'uid', 'value' => $importUid));
	$form->addField('areacode', array('type' => 'hidden', 'name' => 'areacode', 'value' => htmlspecialchars($importAreacode)));

	$form->addField(
		'importseries',
		array(
			'type' => 'text',
			'label' => 'Import Series',
			'require' => false,
			'value' => htmlspecialchars($importSeries),
			'posttext' => ' <span><label><input type="checkbox" name="simulate" value="Yes" '.($isSimulate?'checked="checked"':'').' /> Simulate </label></span>'
				//. '<span><label><input type="checkbox" name="deleteseries" value="Yes" '.($deleteOldSeries?'checked="checked"':'').' /> Delete old import series </label></span>'
				. '<span><label><input type="checkbox" name="debug" value="Yes" '.($isDebug?'checked="checked"':'').' /> Debug </label><span>',
		)
	);

	$form->addField(
		'name',
		array(
			'type' => 'text',
			'label' => 'Import with userId',
			'class' => 'sg-autocomplete -fill',
			'value' => $importName,
			'placeholder' => 'ระบุ ชื่อจริง หรือ อีเมล์ ของสมาชิกที่เป็นเจ้าของโครงการ',
			'attr' => array(
				'data-query' => url('api/user'),
				'data-altfld' => 'uid',
			),
		)
	);

	$form->addField(
		'areaname',
		array(
			'type' => 'text',
			'label' => 'ตำบล-อำเภอ-จังหวัด',
			'class' => 'sg-address -fill',
			'value' => htmlspecialchars($_POST['areaname']),
			'placeholder' => 'ระบุตำบล แล้วเลือกจากรายการที่แสดง',
			'attr'=>array('data-altfld'=>'edit-areacode'),
		)
	);


	$form->addField(
		'data',
		array(
			'type' => 'textarea',
			'label' => 'ข้อมูลสำหรับนำเข้า (CVS - แยกฟิลด์ด้วยเครื่องหมาย Tab )',
			'class' => '-fill',
			'rows' => 18,
			'value' => $post->data,
			'placeholder' => $dataLineFormat,
		)
	);

	$form->addField(
		'submit',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">cloud_upload</i><span>{tr:IMPORT}</span>',
			'pretext' => '<a class="btn -link cancel" href="'.url('imed/admin/import/disableds').'" data-rel="#main"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	$ret .= '<div style="width: 100%; overflow: scroll;">';

	$tableError->thead = array_keys($project);
	$ret .= $tableError->build();

	$tableComplete->thead = array_keys(reset($tableComplete->rows));
	$ret .= $tableComplete->build();

	$ret .= '</div>';

	//$ret .= print_o(post(),'post()');

	$ret .= '</div>';

	return $ret;
}
?>