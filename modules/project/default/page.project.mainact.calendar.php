<?php
/**
* Project main activity information
*
* @param Object $self
* @param Object $actid
* @return String
*/
function project_mainact_calendar($self,$actid) {
	$act=post('act');
	$ret.='<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back">'._CHAR_BACKARROW.'</a></nav><h3 class="title">ปฏิทินกิจกรรม</h3></header>';

	$tpid=mydb::select('SELECT `tpid` FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$actid)->tpid;
	$project=project_model::get_project($tpid);

	$isEdit=$project->isEdit;
	$isAdmin=$project->isAdmin;

	$ret.='<div class="box--mainbar">';
	switch ($act) {
		case 'add' :
			if ($isEdit) $ret.=__project_info_calendar_add($tpid,$actid,NULL,$project);
			break;

		case 'edit':
			if ($isEdit && $calid=post('calid')) {
				$stmt='SELECT * FROM %calendar% c LEFT JOIN %project_activity% a ON a.`calid`=c.`id` WHERE `id`=:calid LIMIT 1';
				$data=mydb::select($stmt,':calid',$calid);
				$data->from_date=sg_date($data->from_date,'d/m/Y');
				$data->to_date=sg_date($data->to_date,'d/m/Y');
				$data->from_time=substr($data->from_time,0,5);
				$data->to_time=substr($data->to_time,0,5);
				$data->color=property('calendar:color:'.$data->id);
				$ret.=__project_info_calendar_add($tpid,$actid,$data,$project);
			}
			break;

		case 'remove' :
			if ($isEdit && $calid=post('calid')) {
				mydb::query('DELETE FROM %calendar% WHERE `id`=:calid LIMIT 1',':calid',$calid);
				mydb::query('DELETE FROM %project_activity% WHERE `calid`=:calid LIMIT 1',':calid',$calid);
			}
			break;

		case 'detail' :
			$calid=post('calid');
			$calendar=project_model::get_calendar($tpid,NULL,NULL,$calid);
			$ret.='<h4>กิจกรรม : '.$calendar->title.'</h4>';
			$ret.='<p><strong>วันที '.sg_date($calendar->from_date,'ว ดด ปปปป').($calendar->to_date==$calendar->from_date?'':' - '.sg_date($calendar->to_date,'ว ดด ปปปป')).' เวลา '.$calendar->from_time.' - '.$calendar->to_time.' น.</strong></p>';
			$ret.='<h5>สถานที่</h5>'.$calendar->location;
			$ret.='<h5>รายละเอียดกิจกรรมตามแผน</h5>'.sg_text2html($calendar->detail);
			$ret.='<p><strong>งบประมาณ '.number_format($calendar->budget,2).' บาท</strong></p>';
			break;
	}

	$stmt='SELECT a.*, c.*
					FROM %project_activity% a
						LEFT JOIN %calendar% c ON c.`id`=a.`calid`
					WHERE `mainact`=:mainact
					ORDER BY c.`from_date` ASC';
	$dbs=mydb::select($stmt,':mainact',$actid);

	if ($isEdit) {
		$inlineAttr['class']='inline-edit';
		$inlineAttr['data-update-url']=url('project/edit/tr');
		$inlineAttr['data-current-url']=url('project/mainact/calendar/'.$mainact);
		$inlineAttr['data-refresh-url']=url('paper/'.$tpid);
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div '.sg_implode_attr($inlineAttr).'>'._NL;

	$tables = new Table();
	$tables->thead=array('date'=>'วันที่','กิจกรรม','amt'=>'กลุ่มเป้าหมาย(คน)','money'=>'งบประมาณ(บาท)','');
	$no=0;
	$target=0;
	$budget=0;
	foreach ($dbs->items as $rs) {
		$lockReport=$rs->from_date<=$project->lockReportDate;
		$is_item_edit=$isEdit && !$lockReport;

		$ui=new ui();
		$ui->add('<a class="sg-action" href="'.url('project/mainact/calendar/'.$actid,array('act'=>'detail','calid'=>$rs->calid)).'" data-rel="box">รายละเอียด</a>');
		if ($is_item_edit) {
			$ui->add('<a class="sg-action" href="'.url('project/mainact/calendar/'.$actid,array('act'=>'edit','calid'=>$rs->calid)).'" data-rel="box">แก้ไข</a>');
			$ui->add('<a class="sg-action" href="'.url('project/mainact/calendar/'.$actid,array('act'=>'remove','calid'=>$rs->calid)).'" data-removeparent="tr" data-confirm="ต้องการลบปฏิทินกิจกรรมนี้ กรุณายืนยัน?">ลบ</a>');
		}
		$submenu=sg_dropbox($ui->build('ul'));

		$tables->rows[] = [
			sg_date($rs->from_date,'ว ดด ปปปป'),
			view::inlineedit(array('group'=>'calendar','fld'=>'title','tr'=>$rs->calid,'class'=>'w-9'),$rs->title,$is_item_edit),
			view::inlineedit(array('group'=>'activity','fld'=>'targetpreset','tr'=>$rs->calid,'ret'=>'numeric'),number_format($rs->targetpreset),$is_item_edit,'text'),
			view::inlineedit(array('group'=>'activity','fld'=>'budget','tr'=>$rs->calid,'ret'=>'numeric','callback'=>'refreshContent'),number_format($rs->budget,2),$is_item_edit,'text'),
			//$isEdit?'act id = '.$rs->calid.' : calendar id='.$rs->id:'No',
			$submenu,
		];

		$budget+=$rs->budget;
		$target+=$rs->targetpreset;
	}
	$tables->tfoot[] = [
		'<td colspan="2" align="center">รวม '.$dbs->_num_rows.' กิจกรรม</td>',
		'<td align="center"><strong>'.number_format($target).'</strong></td>',
		'<td align="right"><strong>'.number_format($budget,2).'</strong></td>',
		''
	];
	$ret .= $tables->build();
	$ret.='</div><br /><br />';

	if ($isEdit) $ret.='<a class="sg-action floating circle add--main" href="'.url('project/mainact/calendar/'.$actid,array('act'=>'add')).'" data-rel="box" title="เพิ่มกิจกรรมย่อย">+</a>';


	//$ret.=print_o($project,'$project');
	$ret.='</div>';

	$ret.='<div class="box--sidebar">'.R::Page('project.mainact',NULL,$tpid,'info',$actid).'</div>';
	//$ret.='<div class="widget request" widget-request="project/mainact/mainact/'.$actid.'" ></div>';

	return $ret;
}

function __project_info_calendar_add($tpid,$actid,$data,$project) {
	$ret.='<h3>'.($data?'แก้ไข':'เพิ่ม').'กิจกรรมย่อย</h3>';
	if ($data) $post=$data;
	else $post=(object)post('calendar');

	if (post('calendar') && $post->title && sg_date($post->from_date,'Y-m-d')<=$project->lockReportDate) {
		$ret.='<p class="notify">วันที่ทำกิจกรรมจะต้องหลังวันส่งรายงานครั้งสุดท้าย คือ '.sg_date($project->lockReportDate,'d/m/ปปปป');
	} else if (post('calendar') && $post->title) {
		$post->calid=empty($post->id) ? NULL : $post->id;
		$post->owner=SG\getFirst(i()->uid,NULL);

		// Change BC to DC on year > 2500
		$post->DCfrom_date=sg_date($post->from_date,'Y-m-d');
		$post->DCto_date=sg_date($post->to_date,'Y-m-d');

		$post->ip=ip2long(GetEnv('REMOTE_ADDR'));
		$post->created_date='func.NOW()';
		$post->category=SG\getFirst($post->category,'func.NULL');
		$post->reminder=SG\getFirst($post->reminder,'no');
		$post->repeat=SG\getFirst($post->repeat,'no');

		$address=SG\explode_address($post->location);
		$post->changwat=substr($post->areacode,0,2);
		$post->ampur=substr($post->areacode,2,2);
		$post->tambon=substr($post->areacode,4,2);
		$post->village=$address['village']?sprintf('%02d',$address['village']):'';

		$stmt='INSERT INTO %calendar%
						(`id`, `tpid`, `owner`, `privacy`, `category`, `title`, `location`, `latlng`, `village`, `tambon`, `ampur`, `changwat`, `from_date`, `from_time`, `to_date`, `to_time`, `detail`, `reminder`, `repeat`, `ip`, `created_date`)
					VALUES
						(:calid, :tpid, :owner, :privacy, :category, :title, :location, :latlng, :village, :tambon, :ampur, :changwat, :DCfrom_date, :from_time, :DCto_date, :to_time, :detail, :reminder, :repeat, :ip, :created_date)
					ON DUPLICATE KEY UPDATE
						`title`=:title, `location`=:location, `latlng`=:latlng, `village`=:village, `tambon`=:tambon, `ampur`=:ampur, `changwat`=:changwat, `from_date`=:DCfrom_date, `from_time`=:from_time, `to_date`=:DCto_date, `to_time`=:to_time, `detail`=:detail';
		mydb::query($stmt,$post);
		//$ret.=mydb()->_query.'<br />';

		if (empty($post->calid)) $post->calid=mydb()->insert_id;
		$post->targetpreset=intval(abs(sg_strip_money($post->targetpreset)));
		$post->budget=abs(sg_strip_money($post->budget));
		if ($post->mainact<=0) $post->mainact=NULL;
		$stmt='INSERT INTO %project_activity%
					(`calid`, `calowner`, `mainact`, `targetpreset`, `target`, `budget`)
					VALUES
					(:calid, :calowner, :mainact, :targetpreset, :target, :budget)
					ON DUPLICATE KEY UPDATE
					`calowner`=:calowner, `mainact`=:mainact, `targetpreset`=:targetpreset, `target`=:target, `budget`=:budget';
		mydb::query($stmt, $post);
		//$ret.=mydb()->_query.'<br />';

		if ($post->color) property('calendar:color:'.$post->calid,$post->color);

		return $ret;
	}

		if (is_array($post)) $post=(object)$post;
		if (empty($post->tpid)) $post->tpid=$tpid;
		if (empty($post->mainact)) $post->mainact=$actid;
		if (empty($post->from_date)) $post->from_date=date('j/n/').(date('Y')+543);
		if (empty($post->to_date)) $post->to_date=$post->from_date;
		if (empty($post->from_time)) $post->from_time='09:00';
		if (empty($post->to_time)) {
			list($hr,$min)=explode(':',$post->from_time);
			$post->to_time=sprintf('%02d',$hr+1).':'.$min;
		}
		if (empty($post->privacy)) $post->privacy='public';

		list(,$month,$year)=explode('/',$post->from_date);

		$form = new Form([
			'variable' => 'calendar',
			'action' => url('project/mainact/calendar/'.$actid),
			'id' => 'edit-calendar',
			'class' => 'sg-form',
			'rel' => 'box',
			'children' => [
				'act' => ['type' => 'hidden', 'name' => 'act', 'value' => 'add'],
				'id' => $post->id ? ['type' => 'hidden','value' => $post->id] : NULL,
				'tpid' => $post->tpid ? ['type' => 'hidden', 'value' => $post->tpid] : NULL,
				'mainact' => ['type' => 'hidden', 'value' => $actid],
				'privacy' => ['type' => 'hidden', 'value' => 'public'],
				'tbutton' => [
					'type' => 'button',
					'save' => tr('Save'),
					'posttext' => '<a class="sg-action" href="'.url('project/mainact/calendar/'.$actid).'" data-rel="box">ยกเลิก</a>',
				],
				'calowner' => [
					'type' => 'radio',
					'options' => [1 => 'กิจกรรมของโครงการ', 2 => 'กิจกรรมของพี่เลี้ยง'],
					'value' => SG\getFirst($post->calowner,1),
				],
				'title' => [
					'type' => 'text',
					'label' => 'ทำอะไร',
					'maxlength' => 255,
					'class' => '-fill',
					'require' => true,
					'placeholder' => 'ระบุชื่อกิจกรรม',
					'value' => htmlspecialchars($post->title),
				],
				'date' => [
					'type' => 'textfield',
					'label' => 'เมื่อไหร่',
					'require' => true,
					'value' => (function(){
						for ($hr=7;$hr<24;$hr++) {
							for ($min=0;$min<60;$min+=30) {
								$times[]=sprintf('%02d',$hr).':'.sprintf('%02d',$min);
							}
						}
						$result = '<input type="text" name="calendar[from_date]" id="edit-calendar-from_date" maxlength="10" class="sg-datepicker form-text require" style="width:80px;" value="'.htmlspecialchars($post->from_date).'"> '
							. '<select class="form-select" name="calendar[from_time]" id="edit-calendar-from_time">';
						foreach ($times as $time) {
							$result .= '<option value="'.$time.'"'.($time==$post->from_time?' selected="selected"':'').'>'.$time.'</option>';
						}
						$result .= '</select>
						ถึง <select class="form-select" name="calendar[to_time]" id="edit-calendar-to_time">';
						foreach ($times as $time) {
							$result .= '<option value="'.$time.'"'.($time==$post->to_time?' selected="selected"':'').'>'.$time.'</option>';
						}
						$result .= '</select>
						<input type="text" name="calendar[to_date]" id="edit-calendar-to_date" maxlength="10" class="sg-datepicker form-text require" style="width:80px;" value="'.htmlspecialchars($post->to_date).'">';
						return $result;
					})(),
				],
				'areacode' => ['type' => 'hidden', 'value' => $post->areacode],
				'latlng' => ['type' => 'hidden', 'value' => $post->latlng],
				'location' => [
					'type' => 'text',
					'label' => 'ที่ไหน',
					'maxlength' => 255,
					'class' => 'sg-address -fill',
					'placeholder' => 'ระบุสถานที่ หมู่ที่ ตำบล',
					'value' => htmlspecialchars($post->location),
					'attr' => 'data-altfld="edit-calendar-areacode"',
					'posttext' => ' <a href="javascript:void(0)" id="calendar-addmap">แผนที่</a><div id="calendar-mapcanvas" class="-hidden"></div>',
				],
				'detail' => [
					'type' => 'textarea',
					'label' => 'รายละเอียดกิจกรรมตามแผน',
					'class' => '-fill',
					'rows' => 3,
					'placeholder' => 'ระบุรายละเอียดของกิจกรรมที่วางแผนว่าจะทำ',
					'value' => $post->detail,
				],
				'budget' => [
					'type' => 'text',
					'label' => 'งบประมาณที่ตั้งไว้ (บาท)',
					'maxlength' => 12,
					'placeholder' => '0.00',
					'value' => $post->budget,
				],
				'targetpreset' => [
					'type' => 'text',
					'label' => 'จำนวนกลุ่มเป้าหมาย (คน)',
					'maxlength' => 7,
					'placeholder' => 0,
					'value' => $post->targetpreset,
				],
				'target' => [
					'type' => 'textarea',
					'label' => 'รายละเอียดกลุ่มเป้าหมาย',
					'class' => '-fill',
					'rows' => 5,
					'placeholder' => 'ระบุรายละเอียดของกลุ่มเป้าหมายที่จะเข้าร่วม',
					'value' => $post->target,
				],
				'color' => [
					'type' => 'colorpicker',
					'label' => 'สีของกิจกรรม',
					'color' => 'Red, Green, Blue, Black, Purple, Aquamarine, Aqua, Chartreuse,Coral, DarkGoldenRod, Olive, Teal, HotPink, Brown',
					'value' => $post->color,
				],
				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
					'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('project/mainact/calendar/'.$actid).'" data-rel="box"><i class="icon -material">cancel</i>ยกเลิก</a>',
					'container' => '{class: "-sg-text-right"}',
				],
			],
		]);

		if ($para->module) $form=do_class_method($para->module.'.extension','calendar_form', $form, $post, $para);

		$ret .= $form->build();

		$gis['zoom']=7;

		if ($post->latlng) {
			list($lat,$lng)=explode(',', $post->latlng);
			$gis['center']=$post->latlng;
			$gis['zoom']=10;
			$gis['current'] = [
				'latitude'=>$lat,
				'longitude'=>$lng,
				'title'=>$post->location,
				'content'=>'<h4>'.$post->title.'</h4>'.($post->topic_title?'<p><strong>'.$post->topic_title.'</strong></p>':'').($post->location?'<p>สถานที่ : '.$post->location.'</p>':''),
			];
		} else {
			$gis['center']=property('project:map.center:NULL');
		}
		//$ret.=print_o($post,'$post').print_o($gis,'$gis');

		$ret.='<script type="text/javascript">
		var from=$("#edit-calendar-from_date").val().split("/");
		var to=$("#edit-calendar-to_date").val().split("/");
		var fromDate=new Date(from[2],from[1]-1,from[0]);
		var toDate=new Date(to[2],to[1]-1,to[0]);

		var minutes = 1000*60;
		var hours = minutes*60;
		var days = hours*24;

		var diff_date = Math.round((toDate - fromDate)/days);

		$("#edit-calendar-from_date").change(function() {
			var from=$(this).val().split("/");
			toDate=new Date(from[2],from[1]-1,from[0]);
			toDate.setDate(toDate.getDate()+diff_date);
			$("#edit-calendar-to_date").val($.datepicker.formatDate("dd/mm/yy",toDate));
		});
		$("#edit-calendar-to_date").change(function() {
			from=$("#edit-calendar-from_date").val().split("/");
			to=$("#edit-calendar-to_date").val().split("/");
			fromDate=new Date(from[2],from[1]-1,from[0]);
			toDate=new Date(to[2],to[1]-1,to[0]);
			diff_date = Math.round((toDate - fromDate)/days);
		});

	  setTimeout(function() { $("#edit-calendar-title").focus() }, 500);
		var gis='.json_encode($gis).'
		</script>';
	return $ret;
}
?>