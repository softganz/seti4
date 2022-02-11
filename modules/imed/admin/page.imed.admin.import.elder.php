<?php
/**
 * Import data รายชื่อคนพิการเพิ่มใหม่
 *
 * @return String
 */
function imed_admin_import_elder() {
	$isSimulate=post('simulate')=='Yes';
	$areacode=post('areacode');
	$importseries=post('importseries');
	$deleteOldSeries=post('deleteseries')=='Yes';
	$showQt=post('showqt')=='Yes';

	$now=date('U');
	$uid=i()->ok?i()->uid:'func.NULL';

	if (!user_access('create set content')) return R::View('signform');

	$ret = '<section>';
	$ret .= '<header class="header"><h3>นำเข้าข้อมูลผู้สูงอายุของตำบล</h3></header>';

	/*
	empty(value) set to ''
	วันเกิด db_persion.birth = date('Y')-age -01-01
	ศาสนา db_persion.religion 1=>1 , 2=>3, 3=>2, 4=>5
	สถานภาพสมรส db_persion.mstatus 1=>19 2=>20 3=>24 4=>23
	การศึกษา db_persion.educate 1=>a 2=>2 3=>4 4=>6
	ที่อยู่เดิม ตำบลเดิม+อำเภอเดิม+จังหวัดเดิม
	สิทธิ์การรักษา PSNL.1.10.1 1=>สิทธิหลักประกันสุขภาพถ้วนหน้า ระบุ ท. 2=>สิทธิประกันสังคม 3=>สิทธิข้าราชการ


	field ที่จะไม่ถูกบันทึกใน qt
	psnid,prename,name,lname,house,road,village,ตำบล,อำเภอ,จังหวัด,phone,hid,cid,สิทธิ์การรักษา,อายุ,sex}religion,mstatus,educate,ตำบลเดิม,อำเภอเดิม,จังหวัดเดิม,

	empty(value) ไม่ต้องบันทึก ยกเว้น ADL	dep1_new	dep2_new	ADLG

	*/

	$religionConv=array(1=>1 , 2=>3, 3=>2, 4=>5);
	$mStatusConv=array(1=>19 , 2=>20 , 3=>24 , 4=>23);
	$educateConv=array(1=>'a' , 2=>2 , 3=>4 , 4=>6);
	$religionString=array(1=>'พุทธ',2=>'คริสต์',3=>'อิสลาม','อื่น ๆ');
	$sexConv=array(1=>'ชาย',2=>'หญิง');
	$emptyExcept=array('ADL','dep1-new','dep2-new','ADLG');
	$rightHealthConv=array(1=>'สิทธิหลักประกันสุขภาพถ้วนหน้า ระบุ ท.', 2=>'สิทธิประกันสังคม', 3=>'สิทธิข้าราชการ');


	$qtExclodeFields=array('psnid', 'prename', 'name', 'lname', 'house', 'road', 'village','areacode','ตำบล', 'อำเภอ', 'จังหวัด', 'commune', 'phone', 'hid', 'cid', 'สิทธิ์การรักษา', 'อายุ', 'sex', 'religion', 'mstatus', 'educate', 'ตำบลเดิม', 'อำเภอเดิม', 'จังหวัดเดิม', 'ช่วงอายุ');

	// Remove old import series
	if ($importseries && $deleteOldSeries) {
		mydb::query('DELETE FROM %imed_qt% WHERE `pid` IN (SELECT `psnid` FROM %db_person% WHERE `importseries`=:importseries)', ':importseries',$importseries);
		mydb::query('DELETE FROM %imed_care% WHERE `careid`='._IMED_CARE_ELDER.' AND `pid` IN (SELECT `psnid` FROM %db_person% WHERE `importseries`=:importseries)', ':importseries',$importseries);

		mydb::query('DELETE FROM %db_person% WHERE `importseries`=:importseries AND `importtype` IS NULL ',':importseries',$importseries);

		$maxid = mydb::select('SELECT MAX(psnid) maxid FROM %db_person% LIMIT 1')->maxid;
		mydb::query('ALTER TABLE %db_person% AUTO_INCREMENT='.$maxid);
		$maxid = mydb::select('SELECT MAX(qid) as maxid FROM %imed_qt% LIMIT 1')->maxid;
		mydb::query('ALTER TABLE %imed_qt% AUTO_INCREMENT='.$maxid);
	}

	if ($_POST['data'] && $areacode && $importseries) {
		$lines=explode("\n",$_POST['data']);
		$sep="\t";
		foreach ($lines as $key=>$line) {
			$line=trim($line);
			if (empty($line)) continue;
			// $line=preg_replace('/  /',' ',$line);
			// $ret.=$line.'<br />';
			// $row=explode($sep,$line);
			$row=str_getcsv($line,$sep);
			// $ret.=print_o($row,'$row');
			foreach ($row as $k=>$v) $row[$k]=trim($v);
			if ($row[0]=='pid') {
				$row[]='ภูมิลำเนา-เดิม';
				$head=$row;
				continue;
			}
			if (!$head) {
				$ret.=message('error','ไม่มี Table field structure');
				break;
			}
			foreach ($row as $k=>$v) {
				$row[$head[$k]]=trim($v);
				unset($row[$k]);
			}
			$row['pid']=intval($row['pid']);
			$row['cid']=preg_replace('/[^\d]+/', '', $row['cid']); // remove not numeric
			$row['ภูมิลำเนา-เดิม']=trim(($row['ตำบลเดิม']?'ต.'.$row['ตำบลเดิม']:'').($row['อำเภอเดิม']?' อ.'.$row['อำเภอเดิม']:'').($row['จังหวัดเดิม']?' จ.'.$row['จังหวัดเดิม']:''));
			$row['PSNL.1.5.3']=$religionString[$row['religion']];
			$row['PSNL.1.10.1']=$rightHealthConv[$row['สิทธิ์การรักษา']];

			// if ($row['pid'] && mydb::select('SELECT `cid` FROM %db_person% WHERE `cid`=:cid LIMIT 1',':cid',$row['cid'])->_num_rows) continue;


			unset($error);
			unset($person);

			$person['uid']=$uid;
			$person['cid']=$row['cid']?$row['cid']:'func.NULL';
			$person['prename']=$row['prename'];
			$person['name']=$row['name'];
			$person['lname']=$row['lname'];
			$person['house']=$person['rhouse']=$row['house'].($row['road'] ? ' ถ.'.$row['road'] : '');
			$person['village']=$person['rvillage']=$row['village']?$row['village']:'';
			$curAreaCode=$row['areacode']?$row['areacode']:$areacode;
			$person['tambon']=$person['rtambon']=substr($curAreaCode,4,2);
			$person['ampur']=$person['rampur']=substr($curAreaCode,2,2);
			$person['changwat']=$person['rchangwat']=substr($curAreaCode,0,2);
			$person['commune']=$row['commune'];
			$person['phone']=$row['phone'];
			$person['hid']=$row['hid'];
			$person['birth']=(date('Y')-$row['อายุ']).'-01-01';

			$person['sex']=$sexConv[$row['sex']];
			$person['religion']=$religionConv[$row['religion']];
			$person['mstatus']=$mStatusConv[$row['mstatus']];
			$person['educate']=$educateConv[$row['educate']];

			$person['created']=$now;
			$person['importseries']=$importseries;

			if (empty($row['pid'])) {
				$stmt='INSERT INTO %db_person% (
									`'.implode('`, `',array_keys($person)).'`
								) VALUES (
									:'.implode(', :', array_keys($person)).'
								)';
			} else {
				$person['importtype']='update';
				$stmt='UPDATE %db_person% SET ';
				foreach (array_keys($person) as $fld) $stmt.='`'.$fld.'`=:'.$fld.', ';
				$stmt=trim($stmt,', ').' WHERE `psnid`=:psnid LIMIT 1';
				$person['psnid']=$row['pid'];
			}

			if (!$isSimulate) {
				mydb::query($stmt,$person);
				//$ret.=mydb()->_query.'<br />';
				if (mydb()->_error) $error.=mydb()->_query;
				$pid=empty($row['pid']) ? mydb()->insert_id : $row['pid'];
			}
			if ($error) {
				$tableError->rows[]=$person;
				$tableError->rows[]=array('','<td colspan="22">'.($isSimulate?$stmt:mydb()->_query).'</td>');
				continue;
			} else {
				$tableComplete->rows[]=$person;
				$tableComplete->rows[]=array('','<td colspan="22">'.($isSimulate?$stmt:mydb()->_query).'</td>');
			}

			$stmt='INSERT IGNORE INTO %imed_care%
								(`pid`, `careid`, `status`, `uid`, `created`)
							VALUES
								(:pid, '._IMED_CARE_ELDER.', 1, :uid, :created) ';
			mydb::query($stmt,':pid',$pid, ':uid',$uid, ':created', $now);

			foreach ($row as $qtKey=>$qtItem) {
				if (in_array($qtKey, $qtExclodeFields)) continue;
				if (empty($qtItem) && !in_array($qtKey, $emptyExcept)) continue;
				$qtData=array('pid'=>$pid,'part'=>$qtKey,'value'=>$qtItem,'ucreated'=>$uid,
					'dcreated'=>$now);
				$tableQt->rows[]=$qtData;

				$stmt='INSERT INTO %imed_qt%
									(`pid`, `part`, `value`, `ucreated`, `dcreated`)
									VALUES
									(:pid, :part, :value, :ucreated, :dcreated)';
				if (!$isSimulate) mydb::query($stmt,$qtData);
				$tableQt->rows[]=array('','<td colspan="4">'.($isSimulate?$stmt:mydb()->_query).'</td>');
			}

			if ($error) {
			} else {
				$complete[]=$line;
				unset($lines[$key]);
			}
		}
		$post->data=implode("\n",$lines);
	}

	$form = new Form([
		'variable' => 'import',
		'action' => url(q()),
		'id' => 'edit-info',
		'method' => 'post',
		'enctype' => 'multipart/form-data',
		'children' => [
			'areacode' => [
				'type' => 'hidden',
				'name' => 'areacode',
				'value' => $areacode,
			],
			'importseries' => [
				'type' => 'text',
				'name' => 'importseries',
				'label' => 'Import Series',
				'value' => $importseries,
				'posttext' => ' <input type="checkbox" name="simulate" value="Yes" '.($isSimulate?'checked="checked"':'').' /> Simulate <input type="checkbox" name="deleteseries" value="Yes" '.($deleteOldSeries?'checked="checked"':'').' /> Delete old import series <input type="checkbox" name="showqt" value="Yes" '.($showQt?'checked="checked"':'').' /> แสดงผลแบบสอบถาม',
			],
			'areaname' => [
				'type' => 'text',
				'name' => 'areaname',
				'label' => 'ตำบล-อำเภอ-จังหวัด',
				'class' => '-fill',
				'value' => post('areaname'),
				'placeholder' => 'ระบุตำบล แล้วเลือกจากรายการที่แสดง',
			],
			'data' => [
				'type' => 'textarea',
				'name' => 'data',
				'label' => 'ข้อมูลสำหรับนำเข้า (CVS - แยกฟิลด์ด้วยเครื่องหมาย Tab )',
				'class' => '-fill',
				'rows' => 30,
				'value' => $post->data,
			],
			'go' => [
				'type' => 'button',
				'value' => tr('Save'),
				'posttext' => 'หรือ <a class="sg-action" href="'.url('db/disabe').'" data-rel="#app-output">ยกเลิก</a>',
			],
		], // children
	]);

	$ret .= $form->build();


	$tableError = new Table();
	$tableError->caption='รายการนำเข้าผิดพลาด';
	$tableError->thead=array_keys($person);
	$ret .= $tableError->build();

	$tableComplete = new Table();
	$tableComplete->caption='รายการนำเข้าสมบูรณ์';
	$tableComplete->thead=array_keys($person);
	$ret .= $tableComplete->build();

	if ($showQt) {
		$tableQt = new Table();
		$tableQt->caption='รายการนำเข้าแบบสอบถาม';
		$tableQt->thead=array('pid','key','value','uid','dcreated');
		$ret .= $tableQt->build();
	}

	$ret .= '</section>';

	$ret.='<script type="text/javascript">
$(document).ready(function() {
$("#edit-areaname")
.autocomplete({
	source: function(request, response) {
		$.get(url+"api/tambon?q="+encodeURIComponent(request.term), function(data){
			response($.map(data, function(item){
			return {
				label: item.label,
				value: item.value
			}
			}))
		}, "json");
	},
	minLength: 2,
	dataType: "json",
	cache: false,
	select: function(event, ui) {
		this.value = ui.item.value+" "+ui.item.label;
		// Do something with id
		$("#edit-areacode").val(ui.item.value);
		return false;
	}
})
});
</script>';
	return $ret;
}
?>