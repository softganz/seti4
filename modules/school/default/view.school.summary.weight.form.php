<?php
function view_school_summary_weight_form($orgid,$trid) {
	$form=new Form('weight',url('school/summary/weight/add/'.$orgid),'weight-add');

	$form->config->class='container';
	$form->title='<h3>สถานการณ์ภาวะโภชนาการนักเรียน - ดัชนีมวลกาย</h3>';

	for ($i=2015;$i<=date('Y');$i++) $yearOptions[$i]=$i+543;
	if (date('m')>=10) $yearOptions[date('Y')]=date('Y')+543;

	$form->f1='<div class="row -flex">';
	$form->year->type='radio';
	$form->year->label='ปีการศึกษา :';
	$form->year->require=true;
	$form->year->options=$yearOptions;
	$form->year->value=$post->year;
	$form->year->containerclass='col -md-4';

	$form->termperiod->type='radio';
	$form->termperiod->label='ภาคการศึกษา :';
	$form->termperiod->require=true;
	$form->termperiod->options=array(
													'1:1'=>'ภาคการศึกษา 1 ต้นเทอม',
													'1:2'=>'ภาคการศึกษา 1 ปลายเทอม',
													'2:1'=>'ภาคการศึกษา 2 ต้นเทอม',
													'2:2'=>'ภาคการศึกษา 2 ปลายเทอม');
	$form->termperiod->value=$post->termperiod;
	$form->termperiod->containerclass='col -md-4';

	/*
	$form->period->type='radio';
	$form->period->label='ช่วงเวลา :';
	$form->period->require=true;
	$form->period->options=array('1'=>'ก่อนทำโครงการ','2'=>'ระหว่างทำโครงการ','3'=>'หลังทำโครงการ');
	$form->period->value=$post->period;
	*/

	$form->f2='<div class="form-item col -md-4">';
	$form->postby->type='text';
	$form->postby->label='ผู้ประเมิน';
	$form->postby->require=true;
	$form->postby->value=htmlspecialchars($post->postby);

	$form->dateinput->type='text';
	$form->dateinput->label='วันที่ชั่ง/วัด';
	$form->dateinput->class='sg-datepicker';
	$form->dateinput->require=true;
	$form->dateinput->value=htmlspecialchars($post->dateinput?sg_date($post->dateinput,'d/m/Y'):'');
	$form->f2e='</div>';

	$form->f1e='</div>';

	$tables->thead=array('ครั้งที่','ปีการศึกษา','ภาคการศึกษา','ช่วงเวลา','ผู้ประเมิน','วันที่ชั่ง/วัด','ผอม<br />(%)','ค่อนข้างผอม<br />(%)','สมส่วน<br />(%)','ท้วม<br />(%)','เริ่มอ้วน<br />(%)','อ้วน<br />(%)','');


	$tables=new table('item -input -weight');
	$tables->caption='สถานการณ์ภาวะโภชนาการนักเรียน - น้ำหนักตามเกณฑ์ส่วนสูง';
	$tables->thead=array('ชั้น','amt total'=>'จำนวนนักเรียน<br />(คน)','amt getweight'=>'จำนวนนักเรียนที่ชั่งน้ำหนัก/วัดส่วนสูง<br />(คน)','ผอม<br />(คน)','ค่อนข้างผอม<br />(คน)','สมส่วน<br />(คน)','ท้วม<br />(คน)','เริ่มอ้วน<br />(คน)','อ้วน<br />(คน)');

	$stmt='SELECT
					  qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, tr.`parent`
					, tr.`part`
					, tr.`sorder`
					, tr.`num1` total
					, tr.`num2` getweight
					, tr.`num5` thin
					, tr.`num6` ratherthin
					, tr.`num7` willowy
					, tr.`num8` plump
					, tr.`num9` gettingfat
					, tr.`num10` fat
					, qt.`description`
				FROM %qt% qt
					LEFT JOIN %project_tr% tr
						ON tr.`tpid`=:tpid AND tr.`parent`=:trid AND tr.`formid`=:formid AND tr.`part`=:formid
							AND qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
				WHERE `qtgroup`="schoolclass"
				ORDER BY `qtgroup` ASC, `qtno` ASC';
	$qtResultDbs=mydb::select($stmt,':trid',$trid,':tpid',$tpid,':formid',_KAMSAIINDICATOR);

	$i=0;
	foreach ($qtResultDbs->items as $rs) {
		$i++;
		if (in_array($rs->qtno,array(21,31))) $tables->rows[]='<header>';
		if (in_array($rs->qtno,array(11,21,31))) {
			$tables->rows[]='<tr class="subheader"><th colspan="9"><h3>'.rtrim(substr($rs->question,0,strpos($rs->question,' ')),'ปีที่').'</h3></th></tr>';
		}
		$tables->rows[]=array(
						$rs->question
						//.'<br />'.$stdKey.print_o($rs,'$rs')
						,
						'<input class="form-text -numeric -total" type="text" size="3" name="qt['.$rs->qtno.'][total]" value="'.number_format($rs->total).'" autocomplete="off" />',
						'<span id="schoolclass'.$rs->qtno.'">'.number_format($rs->getweight).'</span>',
						'<input class="form-text -numeric -item" type="text" size="3" name="qt['.$rs->qtno.'][thin]" value="'.number_format($rs->thin).'" autocomplete="off" />', 
						'<input class="form-text -numeric -item" type="text" size="3" name="qt['.$rs->qtno.'][ratherthin]" value="'.number_format($rs->ratherthin).'" autocomplete="off" />',
						'<input class="form-text -numeric -item" type="text" size="3" name="qt['.$rs->qtno.'][willowy]" value="'.number_format($rs->willowy).'" autocomplete="off" />',
						'<input class="form-text -numeric -item" type="text" size="3" name="qt['.$rs->qtno.'][plump]" value="'.number_format($rs->plump).'" autocomplete="off" />',
						'<input class="form-text -numeric -item" type="text" size="3" name="qt['.$rs->qtno.'][gettingfat]" value="'.number_format($rs->gettingfat).'" autocomplete="off" />',
						'<input class="form-text -numeric -item" type="text" size="3" name="qt['.$rs->qtno.'][fat]" value="'.number_format($rs->fat).'" autocomplete="off" />',
						);
		$subtotal+=$rs->answer;
	}
	$form->weight=$tables->build();



	$tables=new table('item -input -height');
	$tables->caption='สถานการณ์ภาวะโภชนาการนักเรียน - ส่วนสูงตามเกณฑ์อายุ';
	$tables->thead=array('ชั้น','amt total'=>'จำนวนนักเรียนทั้งหมด<br />(คน)','amt getweight'=>'จำนวนนักเรียนที่วัดส่วนสูง<br />(คน)','เตี้ย<br />(คน)','ค่อนข้างเตี้ย<br />(คน)','สูงตามเกณฑ์<br />(คน)','ค่อนข้างสูง<br />(คน)','สูง<br />(คน)');
	$stmt='SELECT
					  qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, tr.`parent`
					, tr.`part`
					, tr.`sorder`
					, tr.`num1` total
					, tr.`num2` getheight
					, tr.`num5` short
					, tr.`num6` rathershort
					, tr.`num7` standard
					, tr.`num8` ratherheight
					, tr.`num9` veryheight
					, qt.`description`
				FROM %qt% qt
					LEFT JOIN %project_tr% tr
						ON tr.`tpid`=:tpid AND tr.`parent`=:trid AND tr.`formid`=:formid AND tr.`part`=:formid
							AND qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
				WHERE `qtgroup`="schoolclass"
				ORDER BY `qtgroup` ASC, `qtno` ASC';
	$qtResultDbs=mydb::select($stmt,':trid',$trid,':tpid',$tpid,':formid',_INDICATORHEIGHT);

	$i=0;
	foreach ($qtResultDbs->items as $rs) {
		$i++;
		if (in_array($rs->qtno,array(21,31))) $tables->rows[]='<header>';
		if (in_array($rs->qtno,array(11,21,31))) $tables->rows[]='<tr class="subheader"><th colspan="8"><h3>'.rtrim(substr($rs->question,0,strpos($rs->question,' ')),'ปีที่').'</h3></th></tr>';
		$tables->rows[]=array(
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
						);
		$subtotal+=$rs->answer;
	}
	$form->height=$tables->build();

	$form->addField(
					'save',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>{tr:Save}</span>',
						)
					);


	$ret.=$form->build();

	//$ret.=print_o($qtResultDbs,'$qtResultDbs');
	$ret.='<style type="text/css">
	.item.-input {margin:40px 0 80px 0;}
	.item.-input caption {background: #FFAE00; color: #333; font-size: 1.4em; padding:8px 0;}
	.item.-input td:nth-child(n+2) {width:80px;}
	.item.-input td:nth-child(3) input {font-weight:bold;}
	.item.-input td:nth-child(4) {font-weight:bold;}
	.item.-input input {margin:0 auto; display:block;}
	.item.-input tr:nth-child(2n+1) td, .item.-weight tr:nth-child(2n+1) td {background-color:#FFF7C9;}
	.item.-input h3 {padding-left:10px;text-align:left; background:#9400FF; color:#fff;}
	.item.-input .-error, .item .-error .form-text {color:red;}
	.item.-input .subheader th {background:#fff;padding:0;}

	form>div>.form-item {margin: 0; padding:0;}
	form>div>.form-item:first-child {margin-left:0;}
	form>div>.form-item:last-child {margin-right:0;}
	form>#form-item-edit-weight-submit {display:block; border:none;}
	@media (min-width:45em) { /* 720/16 */
	form>div>.form-item {margin: 16px; padding:0 16px; display: inline-block; border: 1px #ccc solid; vertical-align: top; border-radius:2px;}
	}
	</style>';

	$ret.='<script type="text/javascript">
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
				console.log("Error row ="+i);
			}
		});
		return isError;
	}

	$(document).on("keydown keyup",".item.-input .form-text",function(event) {
		var keyCode=event.keyCode;
		var keyChar=event.which;
		console.log("keyChar="+keyChar+" keyCode="+keyCode);

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

		console.log("Change to "+$this.val()+" key="+keyCode+" row="+$row.attr("class")+" total="+total);

		var debug="";
		$row.find(".-item").each(function(i){
			debug+=$(this).attr("class")+"="+$(this).val();
			var itemValue=parseInt($(this).val());
			if (isNaN(itemValue)) {
				itemValue=0;
				//$(this).val(0);
				console.log("ITEMVALUE IS NaN");
			}
			itemTotal+=itemValue;
		});

		if ($this.hasClass("-total")) total=parseInt($this.val());
		console.log("itemValue="+$this.val()+" itemTotal="+itemTotal);
		$row.find(".getweight>span").text(itemTotal);
		if (total<itemTotal) {
			$row.addClass("-error");
		} else {
			$row.removeClass("-error");
		}
		console.log(debug);
	});

	$("#weight-add").submit(function() {
		console.log("Form submit "+formSubmit);

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
			console.log("Submit to "+url)
			notify("Check duplicate "+(++i)+url+"?checkdup=yes&year="+para.year+"&termperiod="+para.termperiod);
			$.ajax({
				url: url,
				type: "POST",
				data: para,
				dataType: "json",
				success: function(data) {
						console.log("Result = "+data.isDup+"<br />"+data.para+data.stmt);
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