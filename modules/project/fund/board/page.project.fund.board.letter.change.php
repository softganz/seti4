<?php
/**
* Project :: Fund Board Change Letter
* Created 2019-05-07
* Modify  2020-06-11
*
* @param Object $self
* @param Object $fundInfo
* @param Int $tranId
* @return String
*
* @call project/fund/$orgId/board.letter.change/$tranId
*/

$debug = true;

function project_fund_board_letter_change($self, $fundInfo, $tranId) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');
	if (!($isEdit = $fundInfo->right->edit)) return message('error', 'Access Denied');

	$ret = '';

	mydb::where('tr.`trid` = :trid AND tr.`refcode` = "change" AND tr.`formid` = "fund" AND tr.`part` = "boardletter" AND `refid` = :orgid', ':trid', $tranId, ':orgid',$orgId);

	$letterInfo = mydb::select(
		'SELECT
			tr.`trid`, tr.`refid`, tr.`refcode`, f.`fundid`, f.`fundname`, f.`nameampur`, f.`namechangwat`
		, tr.`detail1` `orgName`
		, tr.`detail2` `nayokName`
		, tr.`detail3` `positionName`
		, tr.`detail4` `docNo`
		, tr.`text1` `docDate`
		FROM %project_tr% tr
			LEFT JOIN %project_fund% f ON tr.`refid` = f.`orgid`
		%WHERE%
		LIMIT 1'
	);

	$boardInfo = R::Model('project.fund.board.get', array('orgId' => $orgId, 'refId' => $tranId));

	// debugMsg($letterInfo,'$letterInfo');

	if ($letterInfo->_empty) return message('error','ไม่มีข้อมูลหนังสือแต่งตั้ง');

	$ret .= message('notify','อยู่ระหว่างดำเนินการพัฒนา');

	$inlineAttr['class']='project-fund-board-letter';
	if ($isEdit) {
		$inlineAttr['class'].=' sg-inline-edit';
		$inlineAttr['data-update-url']=url('project/edit/tr');
		$inlineAttr['data-tpid'] = -1;
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}

	$ret.='<!-- แบบฟอร์มสำหรับพิมพ์ใบเบิกเงินเพื่อนำไปเซ็นต์ชื่อ -->'._NL;
	$ret.='<div id="project-fund-board-letter" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret .= '<div class="-letter -forprint">';
	$ret .= '<img class="logo-krut" src="//img.softganz.com/img/logo-krut.jpg" width="64" />';
	$ret .= '<h3>คำสั่ง '.View::inlineedit(array('group'=>'fund:boardletter','fld'=>'detail1','tr'=>$letterInfo->trid, 'orgid'=>$orgId, 'refid'=>$orgId,'class'=>'sign -filldata -tonayok','placeholder'=>'เทศบาล/นคร/เมือง/ตำบล/องค์การบริหารส่วนตำบล','callback'=>'replaceOrgName'),$letterInfo->orgName,$isEdit).'<br />
ที่ '.View::inlineedit(array('group'=>'fund:boardletter','fld'=>'detail4','tr'=>$letterInfo->trid, 'orgid'=>$orgId, 'refid'=>$orgId,'placeholder'=>'ค.๐๐๐๐๐/๒๕๖๑','callback'=>'replaceDocNo'),$letterInfo->docNo,$isEdit).'<br />
เรื่อง เปลี่ยนแปลงคณะกรรมการกองทุนหลักประกันสุขภาพ</h3>
<hr />
<p class="-indent">ตามที่ <span class="orgname">'.$letterInfo->orgName.'</span> ได้มีคำสั่ง.................................ลงวันที่...............................เรื่อง แต่งตั้งคณะกรรมการกองทุนหลักประกันสุขภาพระดับท้องถิ่นหรือพื้นที่ เพื่อให้เป็นไปตามประกาศคณะกรรมการหลักประกันสุขภาพแห่งชาติ เรื่อง การกำหนดหลักเกณฑ์เพื่อสนับสนุนให้องค์กรปกครองส่วนท้องถิ่น ดำเนินงานและบริหารจัดการกองทุนหลักประกันสุขภาพในระดับท้องถิ่นหรือพื้นที่ พ.ศ. ๒๕๖๑ ลงวันที่ ๑ ตุลาคม พ.ศ. ๒๕๖๑ นั้น</p>
<p class="-indent">อาศัยอำนาจตามความในมาตรา ๓๑ มาตรา ๓๖ และมาตรา ๔๗ แห่งพระราชบัญญัติหลักประกันสุขภาพแห่งชาติ พ.ศ. ๒๕๔๕ ประกอบกับข้อ ๑๒ แห่งประกาศคณะกรรมการหลักประกันสุขภาพแห่งชาติ เรื่อง การกำหนดหลักเกณฑ์เพื่อสนับสนุนให้องค์กรปกครองส่วนท้องถิ่น ดำเนินงานและบริหารจัดการกองทุนหลักประกันสุขภาพในระดับท้องถิ่นหรือพื้นที่ พ.ศ. ๒๕๖๑ ลงวันที่ ๑ ตุลาคม พ.ศ. ๒๕๖๑ <span class="orgname">'.$letterInfo->orgName.'</span> จึงออกคำสั่งไว้ ดังต่อไปนี้</p>
<p class="-indent">ข้อ ๑ เปลี่ยนแปลงคณะกรรมการกองทุนหลักประกันสุขภาพในระดับท้องถิ่นหรือพื้นที่ ดังนี้ </p>';

//<p class="-indent">ลำดับที่ ................ จาก(ตำแหน่ง หรือ กรณีชื่อ-สกุล) ........................................เป็น .....................................เป็น กรรมการและผู้ช่วยเลขานุการ</p>

	$showPositionList = array(7,8,9,10,15,16,20,21,23,24,27,28,30,31,32,34,35,36);

	foreach ($boardInfo as $rs) {
		if (in_array($rs->position, $showPositionList)) {
			$name = $rs->positionName.' '.$rs->fromorg;
			$position = '';
		} else {
			$name = $rs->prename.$rs->name;
			$position = $rs->positionName;
		}

		$ret .= '<p class="-indent">'
			. 'ลำดับที่ '.SG::arabic2Thai($rs->posno)
			. ' จาก <span class="-no-print">(ตำแหน่ง หรือ กรณีชื่อ-สกุล) </span> ..........'
			. ' เป็น '.$name.' '.$position.' เป็น '.$rs->boardName
			. '</p>';
	}

	//$ret .= print_o($boardInfo,'$boardInfo');


	$ret .= '<p class="-indent">ข้อ ๒ อำนาจ หน้าที่ และวาระการดำรงตำแหน่ง ให้ถือปฏิบัติตามคำสั่ง.......................................เลขที่คำสั่ง............../๒๕๖..... ลงวันที่................ เดือน.....................๒๕๖๑</p>
<p class="-indent">ข้อ ๓ กรรมการที่ได้รับการแต่งตั้งใหม่ตามคำสั่งนี้ ให้มีผลตั้งแต่บัดนี้เป็นต้นไป เว้นแต่เข้าประชุมคณะกรรมการกองทุนหลักประกันสุขภาพในระดับท้องถิ่นหรือพื้นที่ ก่อนมีคำสั่งนี้ให้ถือว่าการแต่งตั้งมีผลตั้งแต่วันที่มีการประชุม</p>
<div class="signbox">
<p>สั่ง ณ วันที่  '.View::inlineedit(array('group'=>'fund:boardletter','fld'=>'text1','tr'=>$letterInfo->trid, 'orgid'=>$orgId, 'refid'=>$orgId,'placeholder'=>'๑ มกราคม พ.ศ.๒๕๖๑','callback'=>'replaceDocDate'),$letterInfo->docDate,$isEdit).'</p>
<p>&nbsp;<br />&nbsp;</p>
<p>('.View::inlineedit(array('group'=>'fund:boardletter','fld'=>'detail2','tr'=>$letterInfo->trid, 'orgid'=>$orgId, 'refid'=>$orgId,'class'=>'sign -filldata -tonayok','placeholder'=>'ชื่อนายก'),$letterInfo->nayokName,$isEdit).')</p>
<p>'.View::inlineedit(array('group'=>'fund:boardletter','fld'=>'detail3','tr'=>$letterInfo->trid, 'orgid'=>$orgId, 'refid'=>$orgId,'class'=>'sign -filldata -tonayok', 'options' => '{placeholder: "ตำแหน่ง"}','callback'=>'replaceOrgName'),$letterInfo->positionName,$isEdit).'</p>
</div>';
	$ret .= '</div>';

	/*
	$showPositionList = array(7,8,9,10,15,16,20,21,23,24,27,28,30,31,32,34,35,36);

	$tables = new Table();
	$no = 0;
	foreach ($boardInfo as $rs) {
		if (in_array($rs->position, $showPositionList)) {
			$name = $rs->positionName.' '.$rs->fromorg;
			$position = '';
		} else {
			$name = $rs->prename.$rs->name;
			$position = $rs->positionName;
		}

		$tables->rows[] = array(
			SG::arabic2Thai(++$no).'.',
			$name,
			$position,
			'เป็น'.$rs->boardName,
		);
	}
	$ret .= $tables->build();
	*/

	$ret .= '</div><!-- -->';
	//$ret .= $orgId.print_o($boardInfo, '$boardInfo');
	//$ret .= print_o($fundInfo,'$fundInfo');

	$ret .= '<style type="text/css">
	.project-fund-board-letter {}
	.logo-krut {display: block; margin: 0 auto 16px;}
	.project-fund-board-letter h3 {margin:0; padding: 16px 0; text-align: center;}
	.project-fund-board-letter p.-indent {text-indent: 2cm; line-height: 1.8em;}
	.project-fund-board-letter.-namelist {line-height: 1.2em;}
	.project-fund-board-letter .signbox {width: 500px; margin: 32px 0 0 auto; text-align: center;}
	.project-fund-board-letter hr {width: 200px; margin: 32px auto;}
	@media print {
		.project-fund-board-letter p.-indent {line-height: 1.4em;}
		.project-fund-board-letter.-namelist .item td {border: none;}
	}
	</style>';

	$ret.='<script type="text/javascript">
	function replaceOrgName($this,data,$parent) {
		$(".orgname").text(data.value)
	}
	function replaceDocNo($this,data,$parent) {
		$(".docno").text(data.value)
	}
	function replaceDocDate($this,data,$parent) {
		$(".docdate").text(data.value)
	}
	</script>';
	return $ret;
}
?>