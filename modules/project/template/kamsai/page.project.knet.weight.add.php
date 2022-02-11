<?php
/**
 * แบบฟอร์มรายงานภาวะโภชนาการนักเรียน
 *
 * @param Object $topic
 * @param Object $para
 */
define(_KAMSAIINDICATOR,'weight');
define(_INDICATORHEIGHT,'height');

function project_knet_weight_add($self, $orgId, $weightInfo = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('school.get',$orgId);
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error', 'ไม่มีข้อมูลองค์กรที่ระบุ');

	R::View('project.toolbar', $self, $orgInfo->name, 'knet', $orgInfo);

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isEdit = ($isAdmin || in_array($orgInfo->officers[i()->uid],array('ADMIN','OFFICER'))) && post('mode') != 'view';

	$percentDigit = 2;
	$classLevelList = explode(',',$orgInfo->info->classlevel);

	if (empty($weightInfo)) $weightInfo = R::Model('project.knet.weight.get', NULL);

	$ret .= '<header class="header -box"><h3>สถานการณ์ภาวะโภชนาการนักเรียน - ดัชนีมวลกาย</h3></header>';

	$form = new Form([
		'variable' => 'title',
		'action' => url('project/knet/'.$orgId.'/weight.save'.($weightInfo ? '/'.$weightInfo->trid : '')),
		'id' => 'weight-add',
		'class' => 'sg-form container',
		'checkValid' => true,
		'rel' => 'notify',
		'done' => 'load | close',
	]);

	for ($i = 2015; $i <= date('Y'); $i++) $yearOptions[$i] = $i + 543;
	if (date('m') >= 10) $yearOptions[date('Y')] = date('Y') + 543;

	$form->addText('<div class="row -flex">');

	$form->addField(
		'year',
		[
			'type' => 'radio',
			'label' => 'ปีการศึกษา :',
			'require' => true,
			'options' => $yearOptions,
			'value' => $weightInfo->year,
			'container' => ['class' => 'col -md-4'],
		]
	);

	$form->addField(
		'termperiod',
		[
			'type' => 'radio',
			'label' => 'ภาคการศึกษา :',
			'require' => true,
			'options' => array(
				'1:1'=>'ภาคการศึกษา 1 ต้นเทอม',
				'1:2'=>'ภาคการศึกษา 1 ปลายเทอม',
				'2:1'=>'ภาคการศึกษา 2 ต้นเทอม',
				'2:2'=>'ภาคการศึกษา 2 ปลายเทอม'),
			'value' => $weightInfo->termperiod,
			'container' => ['class' => 'col -md-4'],
		]
	);

	$form->addText('<div class="form-item col -md-4">');

	$form->addField(
		'postby',
		[
			'type' => 'text',
			'label' => 'ผู้ประเมิน',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($weightInfo->postby),
		]
	);

	$form->addField(
		'dateinput',
		[
			'type' => 'text',
			'label' => 'วันที่ชั่ง/วัด',
			'class' => 'sg-datepicker -fill',
			'require' => true,
			'value' => htmlspecialchars($weightInfo->dateinput?sg_date($weightInfo->dateinput,'d/m/Y'):''),
		]
	);

	$form->addText('</div>');

	$form->addText('</div><!-- row -flex -->');


	if ($weightInfo->trid) {
		$form->addField(
			'submit1',
			[
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i>บันทึกข้อมูลโภชนาการ',
				'pretext' => '<a class="sg-action btn -link -cancel" href="" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
				'container' => array('class'=>'-sg-text-right'),
			]
		);
	}


	$tables = new Table([
		'class' => '-input -weight',
		'caption' => 'น้ำหนักตามเกณฑ์ส่วนสูง',
		'thead' => [
			'ชั้น',
			'amt total'=>'จำนวนนักเรียน<br />(คน)',
			'amt getweight'=>'จำนวนนักเรียนที่ชั่งน้ำหนัก/วัดส่วนสูง<br />(คน)',
			'ผอม<br />(คน)',
			'ค่อนข้างผอม<br />(คน)',
			'สมส่วน<br />(คน)',
			'ท้วม<br />(คน)',
			'เริ่มอ้วน<br />(คน)',
			'อ้วน<br />(คน)',
		],
		'showHeader' => false,
	]);


	$i = 0;
	foreach ($weightInfo->weight as $rs) {
		if (substr($rs->qtno,0,1) == '1' && !in_array('อนุบาล', $classLevelList)) continue;
		else if (substr($rs->qtno,0,1) == '2' && !in_array('ประถม', $classLevelList)) continue;
		else if (substr($rs->qtno,0,1) == '3' && !in_array('มัธยม', $classLevelList)) continue;

		$i++;

		if (in_array($rs->qtno,array(11,21,31))) $tables->rows[]='<header>';
		if (in_array($rs->qtno,array(11,21,31))) {
			$tables->rows[]='<tr class="subheader"><th colspan="9"><h3>'.rtrim(substr($rs->question,0,strpos($rs->question,' ')),'ปีที่').'</h3></th></tr>';
		}

		$tables->rows[] = [
			$rs->question
			//.'<br />'.$stdKey.print_o($rs,'$rs')
			,
			'<input class="form-text -numeric -total" type="text" size="3" name="weight['.$rs->qtno.'][total]" value="'.number_format($rs->total).'" autocomplete="off" />',
			'<span id="schoolclass'.$rs->qtno.'">'.number_format($rs->getweight).'</span>',
			'<input class="form-text -numeric -item" type="text" size="3" name="weight['.$rs->qtno.'][thin]" value="'.number_format($rs->thin).'" autocomplete="off" />',
			'<input class="form-text -numeric -item" type="text" size="3" name="weight['.$rs->qtno.'][ratherthin]" value="'.number_format($rs->ratherthin).'" autocomplete="off" />',
			'<input class="form-text -numeric -item" type="text" size="3" name="weight['.$rs->qtno.'][willowy]" value="'.number_format($rs->willowy).'" autocomplete="off" />',
			'<input class="form-text -numeric -item" type="text" size="3" name="weight['.$rs->qtno.'][plump]" value="'.number_format($rs->plump).'" autocomplete="off" />',
			'<input class="form-text -numeric -item" type="text" size="3" name="weight['.$rs->qtno.'][gettingfat]" value="'.number_format($rs->gettingfat).'" autocomplete="off" />',
			'<input class="form-text -numeric -item" type="text" size="3" name="weight['.$rs->qtno.'][fat]" value="'.number_format($rs->fat).'" autocomplete="off" />',
		];
		$subtotal+=$rs->answer;
	}

	$form->addText($tables->build());


	if ($weightInfo->trid) {
		$form->addField(
			'submit2',
			[
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i>บันทึกข้อมูลโภชนาการ',
				'pretext' => '<a class="sg-action btn -link -cancel" href="" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
				'container' => array('class'=>'-sg-text-right'),
			]
		);
	}


	$tables = new Table([
		'class' => '-input -height',
		'caption' => 'ส่วนสูงตามเกณฑ์อายุ',
		'thead' => [
			'ชั้น',
			'amt total'=>'จำนวนนักเรียนทั้งหมด<br />(คน)',
			'amt getweight'=>'จำนวนนักเรียนที่วัดส่วนสูง<br />(คน)',
			'เตี้ย<br />(คน)',
			'ค่อนข้างเตี้ย<br />(คน)',
			'สูงตามเกณฑ์<br />(คน)',
			'ค่อนข้างสูง<br />(คน)',
			'สูง<br />(คน)',
		],
		'showHeader' => false,
	]);


	$i=0;
	foreach ($weightInfo->height as $rs) {
		if (substr($rs->qtno,0,1) == '1' && !in_array('อนุบาล', $classLevelList)) continue;
		else if (substr($rs->qtno,0,1) == '2' && !in_array('ประถม', $classLevelList)) continue;
		else if (substr($rs->qtno,0,1) == '3' && !in_array('มัธยม', $classLevelList)) continue;

		$i++;
		if (in_array($rs->qtno,array(11,21,31))) $tables->rows[]='<header>';
		if (in_array($rs->qtno,array(11,21,31))) $tables->rows[]='<tr class="subheader"><th colspan="8"><h3>'.rtrim(substr($rs->question,0,strpos($rs->question,' ')),'ปีที่').'</h3></th></tr>';
		$tables->rows[] = [
			$rs->question
			//.'<br />'.$stdKey.print_o($rs,'$rs')
			,
			'<input class="form-text -numeric -total" type="text" size="3" name="height['.$rs->qtno.'][total]" value="'.number_format($rs->total).'" autocomplete="off" />',
			'<span id="schoolclass'.$rs->qtno.'">'.number_format($rs->getheight).'</span>',
			'<input class="form-text -numeric -item" type="text" size="3" name="height['.$rs->qtno.'][short]" value="'.number_format($rs->short).'" autocomplete="off" />',
			'<input class="form-text -numeric -item" type="text" size="3" name="height['.$rs->qtno.'][rathershort]" value="'.number_format($rs->rathershort).'" autocomplete="off" />',
			'<input class="form-text -numeric -item" type="text" size="3" name="height['.$rs->qtno.'][standard]" value="'.number_format($rs->standard).'" autocomplete="off" />',
			'<input class="form-text -numeric -item" type="text" size="3" name="height['.$rs->qtno.'][ratherheight]" value="'.number_format($rs->ratherheight).'" autocomplete="off" />',
			'<input class="form-text -numeric -item" type="text" size="3" name="height['.$rs->qtno.'][veryheight]" value="'.number_format($rs->veryheight).'" autocomplete="off" />',
		];
		$subtotal+=$rs->answer;
	}

	$form->addText($tables->build());

	$form->addField(
		'submit',
		[
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>บันทึกข้อมูลโภชนาการ</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="" data-rel="close"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => array('class'=>'-sg-text-right'),
		]
	);


	$ret .= $form->build();

	//$ret .= print_o($weightInfo,'$weightInfo');
	//$ret .= print_o($orgInfo,'$orgInfo');

	$ret .= '<style type="text/css">
		.item.-input {margin:40px 0 80px 0; border: 2px #9400FF solid;}
		.item.-input caption {background: #9400FF; color: #f8eeff; font-size: 1.4em; padding:8px 0; font-weight: normal;}
		.item.-input td:nth-child(n+2) {width:80px;}
		.item.-input td:nth-child(3) input {font-weight:bold;}
		.item.-input td:nth-child(4) {font-weight:bold;}
		.item.-input input {margin:0 auto; display:block;}
		.item.-input tr:nth-child(2n+1) td, .item.-weight tr:nth-child(2n+1) td {background-color:#FFF7C9;}
		.item.-input h3 {padding-left:10px;text-align:left; background:#ebcfff; color:#694ACE;}
		.item.-input .-error, .item .-error .form-text {color:red;}
		.item.-input .subheader th {background:#fff;padding:0;}

		form>div>.form-item {margin: 0; padding:0;}
		form>div>.form-item:first-child {margin-left:0;}
		form>div>.form-item:last-child {margin-right:0;}
		form>#form-item-edit-weight-submit {display:block; border:none;}
		.container>.row.-flex>.col {float: none; padding: 8px 16px; margin: 16px 16px 16px 0;}

		@media (min-width:45em) { /* 720/16 */
		form>div>.form-item {margin: 16px; padding:0 16px; display: inline-block; border: 1px #ccc solid; vertical-align: top; border-radius:2px;}
		}
	</style>';

	$ret .= '<script type="text/javascript">
		var i=0;
		var formSubmit=false;

		haveRowError();

		// Check total error
		function haveRowError() {
			var isError=false;
			$(".item tr.even, .item tr.odd").each(function(i){
				var $this=$(this);
				var total=parseInt($this.find(".-total").val());
				var itemTotal=parseInt($this.find(".getweight>span").text());
				//console.log("Row="+i+"Total="+total+" itemTotal="+itemTotal);
				if (total<itemTotal) {
					$this.addClass("-error");
					isError=true;
					// console.log("Error row ="+i);
				}
			});
			return isError;
		}

		$(document).on("keydown keyup",".item.-input .form-text",function(event) {
			var keyCode=event.keyCode;
			var keyChar=event.which;
			// console.log("keyChar="+keyChar+" keyCode="+keyCode);

			if (keyCode==13) {
				event.stopPropagation();
				console.log("Enter key was press");
				return false;
			}

			var $this=$(this);
			var $row=$this.closest("tr");
			var total=parseInt($row.find(".-total").val());
			var itemTotal=0;

			if (/\D/g.test(this.value)) {
				// Filter non-digits from input value.
				this.value = this.value.replace(/\D/g, "");
			}

			// console.log("Change to "+$this.val()+" key="+keyCode+" row="+$row.attr("class")+" total="+total);

			var debug="";
			$row.find(".-item").each(function(i){
				debug+=$(this).attr("class")+"="+$(this).val();
				var itemValue=parseInt($(this).val());
				if (isNaN(itemValue)) {
					itemValue=0;
					//$(this).val(0);
					// console.log("ITEMVALUE IS NaN");
				}
				itemTotal+=itemValue;
			});

			if ($this.hasClass("-total")) total=parseInt($this.val());
			// console.log("itemValue="+$this.val()+" itemTotal="+itemTotal);
			$row.find(".getweight>span").text(itemTotal);
			if (total<itemTotal) {
				$row.addClass("-error");
			} else {
				$row.removeClass("-error");
			}
			// console.log(debug);
		});

		// $("#weight-add").submit(function() {
		// 	console.log("SUBMIT")
		// 	return false
		// });

		$("x#weight-add").submit(function() {
			if (formSubmit) return true;
			var $form=$(this);
			var errorField;
			notify();
			if (!$("input[name=\'weight[year]\']:checked").val()) errorField="edit-weight-year";
			else if (!$("input[name=\'weight[termperiod]\']:checked").val()) errorField="edit-weight-termperiod";
			else if ($("#edit-weight-postby").val().trim()=="") errorField="edit-weight-postby";
			else if ($("#edit-weight-dateinput").val().trim()=="") errorField="edit-weight-dateinput";
			if (errorField) {
				var errorFieldLabel=$("#form-item-"+errorField+">label").text();
				notify("กรุณาป้อนข้อมูล :: "+errorFieldLabel,30000);
				$("#"+errorField).focus();
			} else {
				// Check year/termperiod is duplicate
				var para={}
				para.checkdup="yes";
				para.trid=$("#edit-weight-trid").val();
				para.year=$("input[name=\'weight[year]\']:checked").val();
				para.termperiod=$("input[name=\'weight[termperiod]\']:checked").val();
				var url=$(this).attr("action");
				//notify("Check duplicate "+(++i)+url+"?checkdup=yes&year="+para.year+"&termperiod="+para.termperiod);
				$.ajax({
					url: url,
					type: "POST",
					data: para,
					dataType: "json",
					success: function(data) {
							//notify("Result = "+data.isDup+"<br />"+data.para+data.stmt);
							if (haveRowError()) {
								notify("ข้อมูลบางชั้นเรียนไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง",3000);
							} else if (data.isDup) {
								notify("ข้อมูลของปีการศึกษา "+(parseInt(para.year)+543)+" ภาคการศึกษานี้ ได้มีการบันทึกข้อมูลไว้แล้ว ไม่สามารถบันทึกซ้ำได้!!!",30000);
							} else {
								notify("กำลังบันทึกข้อมูล...");
								formSubmit=true;
								$form.submit();
							}
						},
				})
			}
			return false;
		});
		</script>';
	return $ret;
}
?>