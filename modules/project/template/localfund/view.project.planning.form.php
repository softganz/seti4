<?php
function view_project_planning_form($fundInfo,$planInfo) {
	$fundid=$fundInfo->fundid;
	$isEdit=true;

	$addBtn='<a class="tran-remove -hidden" href="" data-rel="none" data-removeparent="tr"><i class="icon -cancel -gray"></i></a><a class="add-tran" href="javascript:void(0)" title="เพิ่ม"><i class="icon -addbig -gray -circle"></i></a>';

	$ret.='<h3>ชื่อแผนงาน : '.$planInfo->info->title.'</h3>';
	$ret.='<h3>ชื่อกองทุน : '.$fundInfo->name.'</h3>';
	$ret.='<h3>ประจำปี : '.($planInfo->info->pryear+543).'</h3>';
	//$ret.='<h4>สถานการณ์ปัจจุบันและเป้าหมาย</h4>';
	//$ret.='สถานการณ์'.$sitValue.' ขนาดปัญหาจำนวน ?? % เป้าหมายจำนวน ?? %';




	$ret.='<h4>สถานการณ์ปัจจุบัน</h4>';
	$ret.='<textarea class="form-textarea -fill -line" rows="5" placeholder="รายละเอียดสถานการณ์"></textarea>';




	$ret.='<h4>ขนาดปัญหา</h4>';
	$tables = new Table();
	$tables->thead=array('ชื่อปัญหา','center -size'=>'ขนาดปัญหา(%)','center -target'=>'เป้าหมาย(%)','icons -c1'=>'');
	foreach ($planInfo->problem as $rs) {
		$tables->rows[]=array(
											'<input type="hidden" name="problem['.$rs->trid.'][trid]" value="'.$rs->trid.'">'
											.'<input class="form-text -fill -line" type="text" name="problem['.$rs->trid.'][title]" value="'.$rs->title.'" placeholder="ระบุชื่อปัญหา" />',
											'<input class="form-text -line" type="text" name="problem['.$rs->trid.'][size]" value="'.$rs->size.'" size="3" placeholder="0.00" />',
											'<input class="form-text -line" type="text" name="problem['.$rs->trid.'][target]" value="'.$rs->target.'" size="3" placeholder="0.00" />',
											'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/planning/'.$fundid.'/removeproblem/'.$rs->trid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>'
											);
	}
	$tables->rows[]=array(
										'<input class="form-text -fill -line" type="text" name="problem[-1][year]" placeholder="ระบุชื่อปัญหา" />',
										'<input class="form-text -line" type="text" name="problem[-1][position]" size="5" placeholder="0.00" />',
										'<input class="form-text -line" type="text" name="problem[-1][company]" size="5" placeholder="0.00" />',
										$addBtn,
										'config'=>array('data-idx'=>-1),
										);
	$ret.=$tables->build();




	$ret.='<h4>วัตถุประสงค์</h4>';

	$tables = new Table();
	$tables->thead=array('วัตถุประสงค์','ตัวชี้วัด','icons -c1'=>'');
	foreach ($planInfo->objective as $rs) {
		$tables->rows[]=array(
											'<input type="hidden" name="objective['.$rs->trid.'][trid]" value="'.$rs->trid.'">'
											.'<input class="form-text -fill -line" type="text" name="objective['.$rs->trid.'][title]" value="'.$rs->title.'" placeholder="ระบุวัตถุประสงค์" />',
											'<input class="form-text -fill -line" type="text" name="objective['.$rs->trid.'][size]" value="'.$rs->size.'" placeholder="ระบุตัวชี้วัด" />',
											'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/planning/'.$fundid.'/removeproblem/'.$rs->trid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>'
											);
	}
	$tables->rows[]=array(
										'<input class="form-text -fill -line" type="text" name="objective[-1][year]" placeholder="ระบุวัตถุประสงค์" />',
										'<input class="form-text -fill -line" type="text" name="objective[-1][position]" placeholder="ระบุตัวชี้วัด" />',
										$addBtn,
										'config'=>array('data-idx'=>-1),
										);
	$ret.=$tables->build();




	$ret.='<h4>แนวทาง/วิธีการสำคัญ</h4>';

	$tables = new Table();
	$tables->thead=array('แนวทาง','วิธีการ','icons -c1'=>'');
	foreach ($planInfo->objective as $rs) {
		$tables->rows[]=array(
											'<input type="hidden" name="objective['.$rs->trid.'][trid]" value="'.$rs->trid.'">'
											.'<textarea class="form-textarea -fill -line" name="objective['.$rs->trid.'][title]">'.$rs->title.'</textarea>',
											'<textarea class="form-textarea -fill -line" name="objective['.$rs->trid.'][size]">'.$rs->size.'</textarea>',
											'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/planning/'.$fundid.'/removeproblem/'.$rs->trid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>'
											);
	}
	$tables->rows[]=array(
										'<textarea class="form-textarea -fill -line" name="objective[-1][year]" rows="5" placeholder="ระบุแนวทาง"></textarea>',
										'<textarea class="form-textarea -fill -line" name="objective[-1][position]" rows="5" placeholder="ระบุวิธีการ"></textarea>',
										$addBtn,
										'config'=>array('data-idx'=>-1),
										);
	$ret.=$tables->build();

	$ret.='<h4>งบประมาณ</h4><input class="form-text -money -line" type="text" placeholder="0.00" /> บาท';




	$ret.='<h4>โครงการย่อย</h4>';
	$tables = new Table();
	$tables->thead=array('ชื่อโครงการ','งบประมาณ','icons -c1'=>'');

	foreach ($planInfo->objective as $rs) {
		$tables->rows[]=array(
											'<input type="hidden" name="objective['.$rs->trid.'][trid]" value="'.$rs->trid.'">'
											.'<input class="form-text -fill -line" type="text" name="objective['.$rs->trid.'][title]" value="'.$rs->title.'" placeholder="ระบุวัตถุประสงค์" />',
											'<input class="form-text -fill -line" type="text" name="objective['.$rs->trid.'][size]" value="'.$rs->size.'" placeholder="ระบุตัวชี้วัด" />',
											'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/planning/'.$fundid.'/removeproblem/'.$rs->trid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>'
											);
	}
	$tables->rows[]=array(
										'<input class="form-text -fill -line" type="text" name="objective[-1][year]" placeholder="ระบุชื่อโครงการ" />',
										'<input class="form-text -fill -line -money" type="text" name="objective[-1][position]" placeholder="0.00" />',
										$addBtn,
										'config'=>array('data-idx'=>-1),
										);
	$ret.=$tables->build();

	$ret.='<style type="text/css">
	.inline-edit .item.-line-input>tbody>tr>td {padding:4px 8px; border-bottom:none;}
	.col-icons {vertical-align:middle;}
	td.col-icons.-c3 {width:86px;}
	.col-icons.-c3 a {padding:0px;}
	.page.-main h3 {padding:16px;margin:0 0 4px 0; background:#ddd;}
	.page.-main h4 {padding:16px;margin:64px 0 4px 0; background:#ddd;}
	.form-item.-submit {text-align: right;}
	.form-item.-submit .btn {margin:8px 16px;}
	.qrcode {clear:both;text-align:center;display:none;}
	@media (min-width:50em) {    /* 800/16 = 50 */
		.qrcode {display:block;}
	}
	</style>
	<script type="text/javascript">
	$(document).on("click",\'[role="button"]\',function(){
		$(this).closest("form").trigger("submit");
		return false;
	});
	$(document).on("click",".add-tran",function() {
		var $tr=$(this).closest("tr");
		var row=$tr.html();
		var $tbody=$(this).closest("tbody");
		var currentIdx=$tr.data("idx");
		var nextIdx=currentIdx-1;
		$(this).closest("a").hide();
		$(this).closest("td").find(".tran-remove").removeClass("-hidden");
		row=row.split("["+currentIdx+"]").join("["+nextIdx+"]")
		$tbody.append("<tr data-idx="+nextIdx+">"+row+"</tr>");
		return false;
	});
	$(document).on("click",".tran-remove",function(){
		$(this).closest("tr").remove();
		return false;
	})
	$(".-set-focus").focus();
	</script>';

	return $ret;
}
?>