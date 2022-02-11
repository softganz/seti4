<?php
/**
* Project :: View Financial Book Control
* Created 2020-06-07
* Modify  2020-06-07
*
* @param Object $self
* @param Object $fundInfo
* @return String
*
* @call project/fund/$orgId/financial.book
*/

$debug = true;

function project_fund_financial_book($self, $fundInfo) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	R::view('project.toolbar',$self,'สมุดคุมรับจ่าย - '.$fundInfo->name,'fund',$fundInfo);

	$isAccess = $fundInfo->right->accessFinancial;
	$isEdit = $fundInfo->right->editFinancial;

	if (!$isAccess) return '<p class="notify">ขออภัย : สำหรับเจ้าหน้าที่'.$fundInfo->name.'เท่านั้น</p>';

	$glcodeForInput=cfg('project.fund.rcvglcode');

	mydb::where('g.`orgid` = :orgid AND LEFT(g.`glcode`,1) IN ("4","5")',':orgid',$orgId);

	$stmt = 'SELECT g.*
			, LEFT(g.`glcode`,1) `glGroup`
			, gc.`gltype`, gc.`glname`
			, DATE_FORMAT(g.`refdate`,"%Y-%m") `refmonth`
			, t.`title` `projectTitle`
			FROM %project_gl% g
				LEFT JOIN %glcode% gc USING(`glcode`)
				LEFT JOIN %topic% t USING(`tpid`)
		%WHERE%
		ORDER BY
		  `refmonth` DESC
		, g.`refdate` ASC, `refcode`
		, CASE
				WHEN g.`amount`>=0 THEN 1
				WHEN g.`amount`<0 THEN 2
			END ASC 
		, g.`glcode` ASC';
	$dbs=mydb::select($stmt,$where['value']);
	//$ret .=mydb()->_query;
	//$ret .=print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->thead=array('date'=>'วันที่','รหัสอ้างอิง', 'glcode -nowrap' => 'รหัสบัญชี','รายการ','money rev'=>'รับ<br />(บาท)','money expense'=>'จ่าย<br />(บาท)','');

	$prevrs=NULL;

	foreach ($dbs->items as $rs) {
		if (empty($prevrs) || sg_date($rs->refdate,'Y-m')!=sg_date($prevrs->refdate,'Y-m')) {
			$tables->rows[]=array('<td colspan="7">'.sg_date($rs->refdate,'ดดด ปปปป').'</td>','config'=>array('class'=>'subheader'));
		}

		//  Menu for each GL Transaction
		$menu = '';
		if ($isEdit && $rs->refcode!= $prevrs->refcode) {
			if (substr($rs->refcode,0,3)== 'RCV') {
				$menu = '<span class="iconset"><a href="'.url('project/fund/'.$orgId.'/financial.view/'.$rs->pglid).'"><i class="icon -material">find_in_page</i><span class="-hidden">รายละเอียด</span></a></span>';
			} else if (substr($rs->refcode,0,3)== 'PAY') {
				$menu = '<span class="iconset"><a href="'.url('project/'.$rs->tpid.'/info.paiddoc/'.$rs->actid).'" rel="nofollow"><i class="icon -material">find_in_page</i><span class="-hidden">รายละเอียด</span></a></span>';
			} else if (substr($rs->refcode,0,3)== 'RET') {
				$menu = '<span class="iconset"><a class="sg-action" href="'.url('project/'.$rs->tpid.'/info.moneyback/'.$rs->actid).'" data-rel="box" data-width="640"><i class="icon -material">find_in_page</i><span class="-hidden">รายละเอียด</span></a></span>';
			}
		}

		// Show GL Transation
		$tables->rows[]=array(
			$rs->refdate!= $prevrs->refdate?sg_date($rs->refdate,'ว ดด ปปปป'):'',
			$rs->refcode!= $prevrs->refcode?$rs->refcode:'',
			$rs->glcode,
			$rs->glname
			.(in_array(substr($rs->glcode,0,1),array('5','4')) && $rs->projectTitle?'<br />(<a href="'.url('project/'.$rs->tpid).'">'.$rs->projectTitle.'</a>)':''),
			$rs->glGroup== '4'?number_format(abs($rs->amount),2):'',
			$rs->glGroup== '5'?number_format(abs($rs->amount),2):'',
			$menu,
		);
		$prevrs= $rs;
	}
	$ret .= $tables->build();

	//$ret .=print_o($dbs,'$dbs');
	return $ret;
}
?>