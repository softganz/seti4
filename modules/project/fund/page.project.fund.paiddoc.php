<?php
/**
* Project :: Fund Paiddoc
* Created 2020-06-08
* Modify  2020-06-08
*
* @param Object $self
* @param Object $fundInfo
* @return String
*
* @usage project/fund/$orgId/paiddoc[/$action][/$trid]
*/

$debug = true;

function project_fund_paiddoc($self, $fundInfo = NULL, $action = NULL,$trid = NULL) {
	if (!$fundInfo) return R::Page('project.fund.paiddoc.home', $self);

	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$isAdmin = $fundInfo->right->admin;
	$isAccessFinancial = $fundInfo->right->accessFinancial;

	R::view('project.toolbar',$self,'ใบเบิกเงิน - กองทุนตำบล','fund',$fundInfo);

	if (!$isAccessFinancial) return '<p class="notify">ขออภัย : สำหรับเจ้าหน้าที่'.$fundInfo->name.'เท่านั้น</p>';

	$dropboxmenu = new ui();

	$ret .= R::Model('project.financial.summary',$fundInfo);
	$dropboxmenu->add('<a class="" href="'.url('project/fund/'.$orgId.'/paiddoc').'" rel="nofollow"><i class="icon -list"></i><span>รายการใบเบิกเงิน</span></a>');

	if ($isAdmin) $dropboxmenu->add('<a class="" href="'.url('project/fund/paiddoc').'" rel="nofollow"><i class="icon -list"></i><span>ทั้งหมด</span></a>');

	$ret.='<nav class="nav -page -no-print" style="position:relative;margin:16px 0; padding-right:50px;">';
	$ui=new ui(NULL,'iconset -sg-text-right');

	$ui->add('<a class="button" href="'.url('project/fund/'.$orgId.'/paiddoc').'"><i class="icon -list"></i><span>รายการ</span></a>');

	if ($isAdmin) $ui->add('<a class="button" href="'.url('project/fund/paiddoc').'" rel="nofollow"><i class="icon -list"></i><span>ทั้งหมด</span></a>');

	$ret.=$ui->build();

	$ret.='<span style="position:absolute;right:10px; top:0;">'.($dropboxmenu?sg_dropbox($dropboxmenu->build()):'').'</span></nav>'._NL;

	$self->theme->title='ใบเบิกเงิน - '.$fundInfo->name;
	//$self->theme->toolbar.='<ul><li><a>บันทึกรายรับ</a></li></ul>';
	if (!$isAccessFinancial) return '<p class="notify">ขออภัย : สำหรับเจ้าหน้าที่'.$fundInfo->name.'เท่านั้น</p>';

	$ret.='<div id="project-financial-info">';

	mydb::where('t.`orgid` = :orgid',':orgid',$fundInfo->orgid);

	$stmt = 'SELECT pd.*
			, t.`title` `projectTitle`
			FROM %project_paiddoc% pd
				LEFT JOIN %topic% t USING(`tpid`)
		%WHERE%
		ORDER BY pd.`paiddate` DESC';

	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('date -date'=>'วันที่','เลขที่ใบเบิก','รหัสอ้างอิง','ชื่อโครงการ','money expense'=>'จำนวนเงิน<br />(บาท)', 'create -date' => 'สร้างเมื่อ','');

	foreach ($dbs->items as $rs) {
		//  Menu for each GL Transaction
		$menu='';
		if ($rs->refcode!=$prevrs->refcode) {
			$menu='<nav class="nav -icons"><a href="'.url('project/'.$rs->tpid.'/info.paiddoc/'.$rs->paidid).'" rel="nofollow"><i class="icon -material">find_in_page</i><span class="-hidden">รายละเอียด</span></a></nav>';
		}
		$tables->rows[] = array(
			sg_date($rs->paiddate,'ว ดด ปปปป'),
			$rs->docno,
			$rs->refcode,
			'<a href="'.url('project/'.$rs->tpid).'">'.$rs->projectTitle.'</a>',
			number_format($rs->amount,2),
			sg_date($rs->created,'ว ดด ปป H:i'),
			$menu,
		);

	}
	$ret.=$tables->build();

	$ret.='</div><!-- project-financial-info -->';

	head('<style type="text/css">
	.project-summary {padding:10px;background:#1565C0; color:#fff;}
	.project-summary p {margin:0; padding:0 0 0 16px;}
	.project-summary>div {width:33%; display:inline-block;vertical-align: top;}
	.project-summary>div>span {display:block;}
	.project-summary .itemvalue {font-size: 1.2em; line-height:1.2em;}
	</style>');

	return $ret;
}
?>