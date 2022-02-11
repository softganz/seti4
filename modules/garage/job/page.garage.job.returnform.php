<?php
function garage_job_returnform($self,$jobInfo) {
	if (!($jobId = $jobInfo->jobId)) return message('error', 'PROCESS ERROR');

	$shopInfo = $jobInfo->shopInfo;

	new Toolbar($self,$jobInfo->plate,'job',$jobInfo);

	$ret.='<div class="garage-returnform -forprint">';

	$ret.='<address>'.$shopInfo->shopname.'<br />'.$shopInfo->shopaddr.' '.$shopInfo->shopzipcode.' โทร. '.$shopInfo->shopphone.'</address><hr />'._NL;
	$ret.='<h3>ใบตรวจและรับรถยนต์</h3>'._NL;
	$ret.='<p class="-sg-text-right">วันที่ ............................</p>';
	$ret.='<p class="-indent">ข้าพเจ้า .............................................................. ในฐานะ (เจ้าของ/ผู้รับรถ) ได้ตรวจและรับรถยนต์คันหมายเลขทะเบียน <b>'.$jobInfo->plate.'</b> ซึ่งทางศูนย์ได้จัดซ่อมเสร็จเรียบร้อยแล้วตามรายการความเสียหายทุกรายการ อันเกิดจากอุบัติเหตุในครั้งนี้ จึงได้รับรถยนต์คันดังกล่าวไปแต่เวลานี้ และขอสัญญาว่าจะไม่เรียกร้องให้ซ่อมเพิ่มเติม หรือเรียกร้องสิทธิ์ใดๆ เช่น ค่าเสื่อมราคา ค่าเสียเวลา ค่าอื่นๆ จากศูนย์ ('.$shopInfo->shopname.') อันเกิดจากอุบัติเหตุดังกล่าว เพื่อเป็นหลักฐานในการนี้ จึงลงลายมือชื่อไว้เป็นหลักฐานสำคัญต่อหน้าเจ้าหน้าที่ศูนย์</p>'._NL;
	$ret.='<p class="sign -first">ลงชื่อ ........................................... ผู้รับรถ<br /><br />(...........................................................)</p>';
	$ret.='<p class="sign">ลงชื่อ ........................................... เจ้าหน้าที่ศูนย์<br /><br />(...........................................................)</p>';
	$ret.='เลขตัวถัง '.$jobInfo->bodyno.'</p>';
	$ret.='</div>'._NL;
	//$ret.=print_o($jobInfo);
	$ret.='<style type="text/css">
	.garage-returnform h3 {margin:2em 0;text-align:center;}
	.garage-returnform hr {margin:10px 0;}
	.garage-returnform p {margin: 20px 0;}
	.garage-returnform .sign {margin:40px 0;text-align:center;}
	.garage-returnform .sign.-first {margin-top:120px;}
	.garage-returnform address {text-align:center; font-style:normal;}
	.garage-returnform .-indent {text-indent:3em;}
	@media print {
		.garage-job-view>.-side {display:none;}
		.garage-returnform .sign.-first {margin-top:4cm;}
	}
	</style>';
	return $ret;
}
?>