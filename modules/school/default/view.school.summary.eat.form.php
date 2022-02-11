<?php
define(_KAMSAIINDICATOR,'schooleat');

function view_school_summary_eat_form($orgid) {
	$form=new Form('eat',url('school/summary/eat/add/'.$orgid),'eat-add');

	$form->config->class='container';
	$form->title='<h3>แบบประเมินการกินอาหารและการออกกำลังกายของนักเรียน - ที่'.$at.'</h3>';

	if ($trid) $form->trid=array('type'=>'hidden','value'=>$trid);

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

	$form->area=array('type'=>'hidden','value'=>SG\getFirst(post('area'),$post->area));

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


	$tables=new table('item -std3');

	if ($at=='โรงเรียน') {
		$tables->thead=array('no'=>'','พฤติกรรมการกินและการออกกำลังกายที่โรงเรียน (เฉพาะมื้อกลางวัน)','amt total'=>'จำนวนนักเรียน<br />(คน)','amt bad'=>'ทำได้น้อย<br />(0-1 วันต่อสัปดาห์)<br />(คน)','amt fair'=>'ทำได้ปานกลาง<br />(2-3 วันต่อสัปดาห์)<br />(คน)','amt good'=>'ทำได้ดี<br />(4-5 วันต่อสัปดาห์)<br />(คน)');
	} else {
		$tables->thead=array('no'=>'','พฤติกรรมการกินและการออกกำลังกายที่บ้าน','amt total'=>'จำนวนนักเรียน<br />(คน)','amt bad'=>'ทำได้น้อย<br />(0-2 วันต่อสัปดาห์)<br />(คน)','amt fair'=>'ทำได้ปานกลาง<br />(3-5 วันต่อสัปดาห์)<br />(คน)','amt good'=>'ทำได้ดี<br />(6-7 วันต่อสัปดาห์)<br />(คน)');
	}

	$stmt='SELECT
					  qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, tr.`parent`
					, tr.`part`
					, tr.`sorder`
					, tr.`num1` total
					, tr.`num5` bad
					, tr.`num6` fair
					, tr.`num7` good
					, qt.`description`
				FROM %qt% qt
					LEFT JOIN %project_tr% tr
						ON tr.`tpid`=:tpid AND tr.`parent`=:trid AND tr.`formid`=:formid AND tr.`part`=:formid
							AND tr.`part`=qt.`qtgroup` AND tr.`sorder`=qt.`qtno`
				WHERE `qtgroup`=:formid
				ORDER BY `qtgroup` ASC, `qtno` ASC';
	$qtResultDbs=mydb::select($stmt,':trid',$trid,':tpid',$tpid,':formid',_KAMSAIINDICATOR);

	$tables->rows[]='<tr><td colspan="8"><h4>'.$stdName.'</h4></td></tr>';
	foreach ($qtResultDbs->items as $rs) {
		$radioName='qt['.$stdKey.']['.$rs->qtno.'][2]';
		$tables->rows[]=array(
											$rs->qtno,
											$rs->question
											//.'<br />'.$stdKey.print_o($rs,'$rs')
											,
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][total]" value="'.number_format($rs->total,0,'.','').'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][bad]" value="'.number_format($rs->bad,0,'.','').'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][fair]" value="'.number_format($rs->fair,0,'.','').'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][good]" value="'.number_format($rs->good,0,'.','').'" />',
											);
		$subtotal+=$rs->answer;
	}
	$form->std3Table=$tables->build();

	$form->addField(
					'save',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>{tr:Save}</span>',
						)
					);


	$ret .= $form->build();

	$ret.='<style type="text/css">
	form>div>.form-item {margin: 0; padding:0;}
	form>div>.form-item:first-child {margin-left:0;}
	form>div>.form-item:last-child {margin-right:0;}
	form>#form-item-edit-eat-submit {display:block; border:none;}
	@media (min-width:45em) { /* 720/16 */
	form>div>.form-item {margin: 16px; padding:0 16px; display: inline-block; border: 1px #ccc solid; vertical-align: top; border-radius:2px;}
	}
	</style>';
	$ret.='<script type="text/javascript">
	var i=0;
	var formSubmit=false;
	$("#eat-add").submit(function() {
		if (formSubmit) return true;
		var $form=$(this);
		var errorField;
		notify();
		if (!$("input[name=\'eat[year]\']:checked").val()) errorField="edit-eat-year";
		else if (!$("input[name=\'eat[termperiod]\']:checked").val()) errorField="edit-eat-termperiod";
		else if ($("#edit-eat-postby").val().trim()=="") errorField="edit-eat-postby";
		else if ($("#edit-eat-dateinput").val().trim()=="") errorField="edit-eat-dateinput";
		if (errorField) {
			var errorFieldLabel=$("#form-item-"+errorField+">label").text();
			notify("กรุณาป้อนข้อมูล :: "+errorFieldLabel,30000);
			$("#"+errorField).focus();
		} else {
			// Check year/termperiod is duplicate
			var para={}
			para.checkdup="yes";
			para.trid=$("#edit-eat-trid").val();
			para.year=$("input[name=\'eat[year]\']:checked").val();
			para.termperiod=$("input[name=\'eat[termperiod]\']:checked").val();
			para.area=$("#edit-eat-area").val();
			var url=$(this).attr("action");
			//notify("Check duplicate "+(++i)+url+para.year+para.termperiod);
			$.ajax({
				url: url,
				type: "POST",
				data: para,
				dataType: "json",
				success: function(data) {
						//notify("Result = "+data.isDup+"<br />"+data.para+data.stmt);
						if (data.isDup) {
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

	//$ret.=print_o($qtResultDbs,'$qtResultDbs');
	return $ret;
}
?>