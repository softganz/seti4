<?php
/**
 * Project Application
 *
 * @param Object $topic
 */
function saveup_app_main($self = NULL) {
	saveup_model::init_app_mainpage($self);
	unset($self->theme->toolbar);

	$mid=post('mid');

	$ret.='<div style="text-align:right;color:#ccc;background:transparent;padding:8px 2px;font-size:0.8em;position:absolute;width:50px;right:0;z-index:1;">@'.date("H:i:s").'</div>'._NL;

	head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
	//head('<script type="text/javascript">var debugSignIn=true;</script>');
	if (!i()->ok) return R::View('signform');

	$ret.='<h2>ยินดีต้อนรับ '.i()->name.' สมาชิกกลุ่มออมทรัพย์นักพัฒนาภาคใต้</h2>';

	$memberInfo=R::Model('saveup.member.getbyuserid',i()->uid);
	if ($memberInfo) {
		$lineTree=R::Model('saveup.line.tree',$memberInfo->mid);
		if ($lineTree) {
			$ret.='<form class="sg-form" action="'.url('saveup/app/main').'" data-rel="#primary" style="margin:8px;"><select name="mid" class="form-select -fill" onchange="$(this).parent().submit()"><option value="">==เลือกสมาชิกในสาย===</option>';
			foreach ($lineTree as $item) {
				$ret.='<option value="'.$item->mid.'">'.$item->name.'</option>';
			}
			$ret.='</select></form>';
		}
		if ($mid && !array_key_exists($mid, $lineTree)) $mid='';
		else if (!$mid) $mid=$memberInfo->mid;

		if ($mid!=$memberInfo->mid) $memberInfo=R::Model('saveup.member.get',$mid);

		$ret.='<h3>ข้อมูลสมาชิกกลุ่มออมทรัพย์ '.$memberInfo->name.'</h3>';
		$tables = new Table();
		$tables->rows[]=array('รหัสสมาชิก',$memberInfo->mid);
		$tables->rows[]=array('สถานะสมาชิก',$memberInfo->status);
		$tables->rows[]=array('หมายเลขบัตรประชาชน',$memberInfo->idno);
		$tables->rows[]=array('คำนำหน้าชื่อ',$memberInfo->prename);
		$tables->rows[]=array('ชื่อ',$memberInfo->firstname);
		$tables->rows[]=array('นามสกุล',$memberInfo->lastname);
		$tables->rows[]=array('ชื่อเล่น',$memberInfo->nickname);
		$tables->rows[]=array('ที่อยู่ตามทะเบียนบ้าน',$memberInfo->address.' อ.'.$memberInfo->amphure.' จ.'.$memberInfo->province.' '.$memberInfo->zip);
		$tables->rows[]=array('ที่อยู่ปัจจุบัน',$memberInfo->caddress.' อ.'.$memberInfo->camphure.' จ.'.$memberInfo->cprovince.' '.$memberInfo->czip);
		$tables->rows[]=array('โทรศัพท์',$memberInfo->phone);
		$tables->rows[]=array('วันที่เริ่มเป็นสมาชิก',$memberInfo->date_regist?sg_date($memberInfo->date_regist,'ว ดดด ปปปป'):'');
		$tables->rows[]=array('วันที่อนุมัติ',$memberInfo->date_approve?sg_date($memberInfo->date_approve,'ว ดดด ปปปป'):'');
		$tables->rows[]=array('วันเกิด',$memberInfo->birth?sg_date($memberInfo->birth,'ว ดดด ปปปป'):'');
		$tables->rows[]=array('ผู้รับผลประโยชน์',$memberInfo->beneficiary_name);
		$tables->rows[]=array('ที่อยู่',$memberInfo->beneficiary_addr);
		$tables->rows[]=array('งวดการจ่ายสัจจะ',$memberInfo->savepayperiod.' เดือน');
		$tables->rows[]=array('เฟซบุ๊ค',$memberInfo->facebook);
		/*
		foreach ($memberInfo as $key => $value) {
			$tables->rows[]=array($key,$value);
		}
		*/
		$ret.=$tables->build();
		if (post('mid')) return $ret;
	} else {
		$ret.='<p class="notify">ยังไม่มีข้อมูลอ้างอิงกับฐานข้อมูลสมาชิกกลุ่มออมทรัพย์ หากท่านเป็นสมาชิกกลุ่มออมทรัพย์ กรุณาแจ้งรายละเอียดให้กับทางเจ้าหน้าที่กลุ่มผ่านทางช่องทางต่าง ๆ ที่มีอยู่ เพื่อเชื่อมโยงข้อมูลให้เรียบร้อย ซึ่งจะทำให้ท่านสามารถดูข้อมูลอื่น ๆ ได้เพิ่มเติม</p>';
	}
	//$ret.=print_o($memberInfo,'$memberInfo');

	$stmt='SELECT IF(`province`="","ไม่ระบุ",`province`) `province`, COUNT(*) `total` FROM %saveup_member% WHERE `status`="active" GROUP BY `province` ORDER BY `total` DESC; -- {sum:"total"}';
	$dbs=mydb::select($stmt);

	$ret.='<h3>สถานะของกลุ่มออมทรัพย์นักพัฒนาภาคใต้</h3><p>จำนวนสมาชิกปัจจุบัน <b>'.$dbs->sum->total.'</b> คน</p>';
	$tables = new Table();
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			'string:จังหวัด'=>$rs->province,
			'number:สมาชิก'=>intval($rs->total)
		);
	}

	$ret.='<div id="year-project" class="sg-chart -project" data-chart-type="pie" style="height:300px; overflow:auto;"><h3>สมาชิกแต่ละจังหวัด</h3>'._NL.$tables->build().'</div>';


	//$ret.='<div class="sg-graph">'.$tables->build().'</div>';
	//$ret.=print_o($dbs,'$dbs');


	$ret.='<p align="center"><a class="sg-action button" href="'.url('signout').'" data-rel="#primary" data-ret="'.url('saveup/app/main').'">ออกจากระบบ</a></p>';
	//$ret.=R::Page('project.app.activity.form');
	//$ret.=R::Page('project.app.activity.show');

	//$ret.=print_o(i(),'i()');
	//$ret.=print_o(post(),'post()');
	head('<script type="text/javascript">
		var debugSignIn=true;
		$(document).ready(function() {
			$("div.photo>.photoitem>li").each(function() {
				var width=$(this).width();
				$(this).height(width+"px");
				$(this).children().width((width-2)+"px").height((width-2)+"px");
			});
		})
	</script>');
	return $ret;
}
?>