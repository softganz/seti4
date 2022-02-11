<?php
/**
* Project :: Message
* Created 2021-02-17
* Modify  2021-02-17
*
* @param Object $self
* @return String
*
* @usage project/app/message
*/

$debug = true;

function project_app_message($self) {
	$currentDate = date('Y-m-d H:i:00');
	$currentTime = date('H:i');

	$ret = '';

	$zoomLink = 'https://zoom.us/j/97707704021?pwd=SkxDWkJCVDJhVFh0ZHZ2V2p1WXdtZz09';

	if ( ($currentDate >= '2021-03-15 07:00' && $currentDate <= '2021-03-15 15:00')
		|| ($currentDate >= '2021-02-22 12:00' && $currentDate <= '2021-02-22 10:45')
		|| ($currentDate >= '2021-02-28 12:00' && $currentDate <= '2021-03-01 10:30') ) {
		$activeDate = '2021-03-15 09:00:00';
		$isJoinZoomActive = $currentDate >= $activeDate;
		$ret .= '<p class="message -hots">อบรมการใช้งานระบบ 1 ตำบล 1  มหาวิทยาลัย ผ่าน ระบบ Zoom โดย สมส.<br />วันที่ <b>'.sg_date($activeDate,'ว ดด ปปปป').' เวลา 9.30 - 15.00 น.</b><br /><b>สนใจเข้าร่วม?</b><br /><br />'
			. ($isJoinZoomActive ? '<a class="'.(R()->appAgent ? 'sg-action ' : '').'btn -success -fill" href="'.$zoomLink.'"'.(R()->appAgent ? ' data-webview="browser"' :' target="_blank"').'><i class="icon -material">notification_important</i><span>เข้าร่วมผ่าน ZOOM</span></a>' : '<a class="sg-action btn -fill" href="'.url('project/app/message').'" data-rel="replace:.message"><i class="icon -material">notification_important</i><span>โหลดใหม่ (เปิดห้อง 9.00 น.)</span></a>')
			. '</p>';
	}

	if (i()->ok) {
		$bankCheckInfo = mydb::select(
			'SELECT
			p.`tpid`, t.`uid`, t.`title`, p.`ownertype`, r.`property`
			, b.`fldref` `bankCheckStatus`, b.`flddata` `bankCheckData`
			, p.`bankaccount`, p.`bankno`, p.`bankname`
			, pn.`cid`
			, pn.`areacode`, pn.`house`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %topic_revisions% r ON r.`revid` = t.`revid`
				LEFT JOIN %db_person% pn ON pn.`userid` = t.`uid`
				LEFT JOIN %bigdata% b ON b.`keyname` = "project.info" AND b.`keyid` = p.`tpid`
			WHERE t.`uid` = :uid AND p.`ownertype` IN ( :ownerType )
				AND (
				b.`fldref` IS NULL
				OR pn.`cid` = "" OR pn.`cid` IS NULL
				OR p.`bankaccount` = "" OR p.`bankaccount` IS NULL
				OR p.`bankno` = "" OR p.`bankno` IS NULL
				OR p.`bankname` = "" OR p.`bankname` IS NULL
				OR pn.`areacode` IS NULL OR pn.`house` = ""
				)
			LIMIT 1',
			':uid', i()->uid,
			':ownerType', 'SET-STRING:'.implode(',', [_PROJECT_OWNERTYPE_GRADUATE, _PROJECT_OWNERTYPE_STUDENT, _PROJECT_OWNERTYPE_PEOPLE])
		);

		if ($bankCheckInfo->count()) {
			if (empty($bankCheckInfo->bankno) || empty($bankCheckInfo->bankaccount) || empty($bankCheckInfo->bankname)) {
				$ret .= '<p class="message -hots -sg-text-center">กรุณาตรวจสอบข้อมูลหมายเลขบัญชีธนาคารสำหรับรับเงินค่าจ้างรายเดือน ขอให้ดำเนินการยืนยันให้เรียบร้อย<br /><br /><a class="sg-action btn -primary" href="'.url('project/'.$bankCheckInfo->tpid.'/info.bank.check').'" data-rel="box" data-width="480" data-webview="บัญชีธนาคาร"><i class="icon -material -sg-block-center">how_to_reg</i><span>ยืนยันบัญชีธนาคารเพื่อรับเงินค่าจ้าง</span></a></p>';
			}

			if (empty($bankCheckInfo->areacode) || empty($bankCheckInfo->house)) {
				$ret .= '<p id="project-message-address" class="message -hots -sg-text-center">กรุณาตรวจสอบและยืนยันที่อยู่เพื่อเป็นข้อมูลสำหรับการออกใบหักภาษี ณ ที่จ่าย<br /><br /><a class="sg-action btn -primary" href="'.url('project/'.$bankCheckInfo->tpid.'/info.address.check').'" data-rel="box" data-width="480" data-webview="ยืนยันที่อยู่"><i class="icon -material -sg-block-center">how_to_reg</i><span>ยืนยันที่อยู่เพื่อออกใบหักภาษี ณ ที่จ่าย</span></a></p>';
			}
		}
		//debugMsg($bankCheckInfo, '$bankCheckInfo');
	}
	return $ret;
}
?>