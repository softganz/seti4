<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_rehab_visit($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;
	$showItems=10;
	$start = SG\getFirst(post('start'),0);

	if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $isAdmin || $orgInfo->is->socialtype;
	$isRemovePatient = $isAdmin || in_array($orgInfo->is->socialtype,array('MODERATOR','CM'));
	$isCareManager = $isAdmin || $orgInfo->is->socialtype == 'CM';
	$isViewHomeVisit = $isAdmin || in_array($orgInfo->is->socialtype, array('ADMIN','MODERATOR','CM','PHYSIOTHERAPIST'));

	$configGroupViewMemberVisit = true;

	if (!($isAdmin || $isMember)) return message('error','ขออภัย ท่านไม่ได้อยู่ในกลุ่มนี้');

	mydb::query('SET @@group_concat_max_len = 4096;');

	//$ret.='<h3>Welcome home '.$owner->name.'</h3>'._NL;

	//TODO:: Not Secure because admin can add all member of web ทางแก้ต้องให้สมาชิกตอบรับก่อนที่จะเข้าร่วมเป็นสมาชิก หรือ ตอนเยี่ยมต้องระบุว่าเป็นการเยี่ยมของกลุ่มไหน หรือ กำหนดให้แค่เพียงบางกลุ่มที่สามารถเห็นการเยี่ยมของสมาชิกได้

	mydb::where('(s.`pid` IN (SELECT `psnid` FROM %imed_socialpatient% WHERE `orgid` = :orgid)'. ($configGroupViewMemberVisit && $isViewHomeVisit ? ' OR s.`uid` IN (SELECT `uid` FROM %imed_socialmember% WHERE `orgid` = :orgid OR `orgid` IN (SELECT `orgid` FROM %imed_socialparent% WHERE `parent` = :orgid)) )' : ''), ':orgid', $orgId);



	$stmt = 'SELECT
		  s.`pid` `psnid`, s.*
		, u.`username`, u.`name`, CONCAT(p.`name`," ",p.`lname`) `patient_name`
		, b.`score`
		, GROUP_CONCAT(CONCAT(`fid`,"|"),`file`) `photos`
		, (SELECT GROUP_CONCAT(`needid`) FROM %imed_need% WHERE `seq` = s.`seq`) `needItems`
		FROM %imed_service% s
			LEFT JOIN %users% u USING (`uid`)
			LEFT JOIN %db_person% p ON p.`psnid` = s.`pid`
			LEFT JOIN %imed_barthel% b USING(`seq`)
			LEFT JOIN %imed_files% f ON f.`seq` = s.`seq` AND f.`type` = "photo"
		%WHERE%
		GROUP BY `seq`
		ORDER BY `seq` DESC
		LIMIT '.$start.','.$showItems.';';

	$dbs = mydb::select($stmt,':uid',$uid);
	//$ret.=print_o($dbs,'$dbs');

	$ui = new Ui('div','ui-card imed-my-note');
	$ui->addId('imed-my-note');

	foreach ($dbs->items as $rs) {
		$ui->add(R::View('imed.visit.render',$rs, '{class: "", id: "noteUnit-'.$rs->seq.'", page: "app"}'));
	}
	$ret .= $ui->build().'<!-- imed-my-note -->';

	if ($dbs->_num_rows && $showItems >= $dbs->_num_rows) {
		$ret.='<div id="getmore" style="flex: 1 0 100%;"><a class="sg-action btn -primary" href="'.url('imed/rehab/'.$orgId.'/visit',array('start'=>$start+$dbs->_num_rows)).'" data-rel="replace:#getmore" style="margin:0 16px 48px 16px;display:block;text-align:center;"><span>มีอีก</span><i class="icon -forward -white"></i></a></div>';
	}

	//$ret .= print_o($orgInfo,'$orgInfo');

	return $ret;
}
?>