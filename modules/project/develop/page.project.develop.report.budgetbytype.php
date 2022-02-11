<?php
function project_develop_report_budgetbytype($self,$tpid) {
	$tagname='develop';
	R::View('project.toolbar', $self, 'รายงานงบประมาณแยกตามประเภท', 'develop', $rs);

	$stmt='SELECT
					ec.`name` expName
				, e.`trid`, e.`parent`, e.`gallery` `costid`, e.`num1` amt, e.`num2` `unitprice`, e.`num3` `times`, e.`num4` `total`
				, e.`detail1` `unitname`, e.`text1` detail
				, m.`detail1` `mainActTitle`
				, cg.`name` `expGroupName`
				FROM %project_tr% e
					LEFT JOIN %tag% ec ON ec.`taggroup`="project:expcode" AND ec.`catid`=e.`gallery`
					LEFT JOIN %project_tr% m ON m.`trid`=e.`parent`
					LEFT JOIN %tag% cg ON cg.`taggroup`="project:expgr" AND cg.`catid`=ec.`catparent`
				WHERE e.`tpid`=:tpid AND e.`formid`=:tagname AND e.`part`="exptr"
				ORDER BY `expGroupName` ASC';
	$dbs=mydb::select($stmt,':tpid',$tpid, ':tagname',$tagname);

	$tables = new Table();
	$tables->caption='รายงานงบประมาณแยกตามประเภท';
	$tables->thead=array('no'=>'','รายการ','amt 1'=>'จำนวน','amt 2'=>'บาท','amt 3'=>'ครั้ง','amt 4'=>'รวมเงิน');
	$curGroupName='';
	$total=$subTotal=0;
	foreach ($dbs->items as $rs) {
		if ($curGroupName!=$rs->expGroupName) {
			if ($curGroupName!='') $tables->rows[]=array('<td></td>','','','','รวมเงิน',number_format($subTotal,2),'config'=>array('class'=>'subheader'));
			$tables->rows[]=array('<td colspan="6"><strong>'.$rs->expGroupName.'</strong></td>');
			$curGroupName=$rs->expGroupName;
			$subTotal=0;
		}
		$tables->rows[]=array(
											++$no,
											$rs->expName.'<br />กิจกรรม : '.$rs->mainActTitle.($rs->detail?'<br />'.$rs->detail:''),
											number_format($rs->amt).' '.$rs->unitname,
											number_format($rs->unitprice,2),
											number_format($rs->times),
											number_format($rs->total,2),
											);
		//$tables->rows[]=array('<td></td>','<td colspan="5">กิจกรรม : '.$rs->mainActTitle.($rs->detail?'<br />'.$rs->detail:'').'</td>');
		$subTotal+=$rs->total;
		$total+=$rs->total;
	}
	if ($curGroupName!='') $tables->rows[]=array('<td></td>','','','','รวมเงิน',number_format($subTotal,2),'config'=>array('class'=>'subheader'));

	$tables->tfoot[]=array('<td></td>','','','','รวมทั้งสิ้น',number_format($total,2));

	$ret.=$tables->build();
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>