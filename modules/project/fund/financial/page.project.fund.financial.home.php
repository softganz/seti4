<?php
/**
* Project Fund Financial Home Page
* Created 2019-10-03
* Modify  2019-10-03
*
* @param Object $self
* @param Int $var
* @return String
*
* @call project/fund/financial
*/

$debug = true;

function project_fund_financial_home($self) {
	$year = post('yr');
	$area = post('area');
	$prov = post('prov');
	$ampur = post('ampur');

	$isAdmin = user_access('administer projects');

	if (!$isAdmin) return message('error','access denied');

	// Show summary report
	$thisYearSum=mydb::select('SELECT LEFT(`glcode`,1) `glGroup`,ABS(SUM(`amount`)) `amount` FROM %project_gl% WHERE YEAR(`refdate`)=YEAR(CURDATE()) GROUP BY `glGroup`;-- {key:"glGroup"}')->items;
	$prevYearSum=mydb::select('SELECT LEFT(`glcode`,1) `glGroup`,ABS(SUM(`amount`)) `amount` FROM %project_gl% WHERE YEAR(`refdate`)=YEAR(CURDATE())-1 GROUP BY `glGroup`;-- {key:"glGroup"}')->items;
	$allYearSum=mydb::select('SELECT LEFT(`glcode`,1) `glGroup`,ABS(SUM(`amount`)) `amount` FROM %project_gl% GROUP BY `glGroup`;-- {key:"glGroup"}')->items;

	$ret.='<div class="project-summary">';
	$ret.='<div class="thisyearprojects"><span>รายรับ/รายจ่ายปีนี้</span><p>รายรับ <span class="itemvalue">'.number_format($thisYearSum[4]->amount).'</span><span> บาท</span></p><p>รายจ่าย <span class="itemvalue">'.number_format($thisYearSum[5]->amount).'</span><span> บาท</span></p><p>คงเหลือ <span class="itemvalue">'.number_format($thisYearSum[4]->amount-$thisYearSum[5]->amount).'</span><span> บาท</span></p></div>';
	$ret.='<div class="lastyearprojects"><span>รายรับ/รายจ่ายปีที่แล้ว</span><p>รายรับ <span class="itemvalue">'.number_format($prevYearSum[4]->amount).'</span><span> บาท</span></p><p>รายจ่าย <span class="itemvalue">'.number_format($prevYearSum[5]->amount).'</span><span> บาท</span></p><p>คงเหลือ <span class="itemvalue">'.number_format($prevYearSum[4]->amount-$prevYearSum[5]->amount).'</span><span> บาท</span></p></div>';
	$ret.='<div class="totalprojects"><span>รายรับ/รายจ่ายทั้งหมด</span><p>รายรับ <span class="itemvalue">'.number_format($allYearSum[4]->amount).'</span><span> บาท</span></p><p>รายจ่าย <span class="itemvalue">'.number_format($allYearSum[5]->amount).'</span><span> บาท</span></p><p>คงเหลือ <span class="itemvalue">'.number_format($allYearSum[4]->amount-$allYearSum[5]->amount).'</span><span> บาท</span></p></div>';
	$ret.='</div>';


	$form='<form id="condition" action="'.url('project/fund/financial').'" method="get">';
	$form.='<span>ตัวเลือก </span>';
	
	// Select year
	$stmt='SELECT DISTINCT YEAR(`refdate`)+IF(MONTH(`refdate`)>=10,1,0) `budgetYear` FROM %project_gl% ORDER BY `budgetYear` DESC';
	$yearList=mydb::select($stmt);
	$form.='<select id="year" class="form-select" name="yr" onChange="this.form.submit();">';
	$form.='<option value="">ทุกปีงบประมาณ</option>';
	foreach ($yearList->items as $rs) {
		$form.='<option value="'.$rs->budgetYear.'" '.($year && $rs->budgetYear==$year?'selected="selected"':'').'>'.($rs->budgetYear ? 'พ.ศ.'.($rs->budgetYear+543) : 'ไม่ระบุ').'</option>';
	}
	$form.='</select> ';

	// Select area
	$form.='<select id="area" class="form-select" name="area" onChange="this.form.submit();">';
	$form.='<option value="">ทุกเขต</option>';
	$areaList=mydb::select('SELECT `areaid`,`areaname` FROM %project_area% WHERE `areatype`="nhso" ORDER BY `areaid`+0 ASC');
	foreach ($areaList->items as $rs) {
		$form.='<option value="'.$rs->areaid.'" '.($rs->areaid==$area?'selected="selected"':'').'>เขต '.$rs->areaid.' '.$rs->areaname.'</option>';
	}
	$form.='</select> ';

	// Select province
	if ($area) {
		$stmt='SELECT DISTINCT f.`changwat`, cop.`provname` FROM %project_fund% f LEFT JOIN %co_province% cop ON cop.`provid`=f.`changwat` WHERE f.`areaid`=:areaid';
		$provList=mydb::select($stmt,':areaid',$area);
		$form.='<select id="province" class="form-select" name="prov" onChange="this.form.submit();">';
		$form.='<option value="">ทุกจังหวัด</option>';
		foreach ($provList->items as $rs) {
			$form.='<option value="'.$rs->changwat.'" '.($rs->changwat==$prov?'selected="selected"':'').'>'.$rs->provname.'</option>';
		}
		$form.='</select> ';
	}

	// Select province
	if ($prov) {
		$stmt='SELECT DISTINCT `distid`, `distname` FROM  %co_district% WHERE LEFT(`distid`,2)=:prov';
		$ampurList=mydb::select($stmt,':prov',$prov);
		$form.='<select id="ampur" class="form-select" name="ampur" onChange="this.form.submit();">';
		$form.='<option value="">ทุกอำเภอ</option>';
		foreach ($ampurList->items as $rs) {
			$form.='<option value="'.substr($rs->distid,2).'" '.(substr($rs->distid,2)==$ampur?'selected="selected"':'').'>'.$rs->distname.'</option>';
		}
		$form.='</select> ';
	}

	$form.='</form>'._NL;

	$ret .= '<nav class="nav -page">'.$form.'</nav>';

	if ($year) mydb::where('YEAR(`refdate`)+IF(MONTH(`refdate`) >= 10,1,0) = :year',':year',$year);

	if ($ampur) {
		mydb::where('f.`changwat` = :prov AND f.`ampur` = :ampur',':prov',$prov,':ampur',$ampur);
		$label='f.`fundname`';
	} else if ($prov) {
		mydb::where('f.`changwat` = :prov',':prov',$prov);
		$label='f.`nameampur`';
	} else if ($area) {
	 mydb::where('f.`areaid` = :areaid',':areaid',$area);
	 $label='f.`namechangwat`';
	}

	$itemPerPage = SG\getFirst(post('item'), 100);
	$page = post('page');
	if ($itemPerPage == -1) {
		mydb::value('$LIMIT$', '');
	} else {
		$firstRow = $page > 1 ? ($page-1)*$itemPerPage : 0;
		mydb::value('$LIMIT$', 'LIMIT '.$firstRow.' , '.$itemPerPage);
	}

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		g.*, gc.`gltype`, gc.`glname`
		, o.`orgid`, o.`name`, o.`shortname` `fundid`
		, DATE_FORMAT(g.`refdate`,"%Y-%m") `refmonth`
		, t.`title` `projectTitle`
		FROM %project_gl% g
			LEFT JOIN %glcode% gc USING(`glcode`)
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %project_fund% f USING(`orgid`)
			LEFT JOIN %topic% t USING(`tpid`)
		%WHERE%
		ORDER BY g.`created` DESC, `refcode` DESC, g.`amount` DESC
		$LIMIT$;
		';

	$dbs = mydb::select($stmt);

	//$ret.=mydb()->_query;

	$totals = $dbs->_found_rows;

	$pagePara['yr'] = $year;
	$pagePara['area'] = $area;
	$pagePara['prov'] = $prov;
	$pagePara['ampur'] = $ampur;
	$pagePara['item'] = $itemPerPage != 100 ? $itemPerPage : NULL;
	$pagePara['page']=$page;
	$pagenv = new PageNavigator($itemPerPage,$page,$totals,q(),false,$pagePara);

	$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;


	$tables = new Table();
	$tables->thead = array(
		'trdate -date'=>'วันที่',
		'รหัสอ้างอิง',
		'glclode -nowrap'=>'รหัสบัญชี',
		'รายการ',
		'dr -money -nowrap'=>'เดบิท(บาท)',
		'cr -money -nowrap -hover-parent'=>'เครดิต(บาท)',
	);

	$prevRs = NULL;
	foreach ($dbs->items as $rs) {
		if (empty($prevRs) || sg_date($rs->refdate,'Y-m')!=sg_date($prevRs->refdate,'Y-m')) {
			$tables->rows[] = array('<td colspan="7">'.sg_date($rs->refdate,'ดดด ปปปป').'</td>','config'=>array('class'=>'subheader'));
		}

		$ui = new Ui();
		$ui->addConfig('nav', '{class: "nav -icons -hover"}');

		if ($isAdmin && $rs->refcode != $prevRs->refcode) {
			if (substr($rs->refcode,0,3) == 'RCV') {
				$ui->add('<a class="sg-action" href="'.url('project/fund/'.$rs->orgid.'/financial.view/'.$rs->pglid).'" data-rel="box" data-width="640"><i class="icon -material">find_in_page</i></a>');
			} else if (substr($rs->refcode,0,3) == 'PAY') {
				$ui->add('<a class="sg-action" href="'.url('project/'.$rs->tpid.'/info.paiddoc/'.$rs->actid).'" data-rel="box" data-width="640"><i class="icon -material">find_in_page</i></a>');
			} else if (substr($rs->refcode,0,3) == 'RET') {
				$ui->add('<a class="sg-action" href="'.url('project/'.$rs->tpid.'/info.moneyback/'.$rs->actid).'" data-rel="box" data-width="640"><i class="icon -material">find_in_page</i></a>');
			}
		}

		if ($rs->refcode != $prevRs->refcode) {
			$tables->rows[] = array(
				$rs->refdate ? sg_date($rs->refdate,'ว ดด ปปปป') : '???',
				$rs->refcode,
				'<td colspan="2"><a href="'.url('project/fund/'.$rs->orgid.'/financial').'"><b>'.$rs->name.'</b></a></td>',
				'<td class="col -hover-parent -sg-text-right" colspan="2">'.sg_date($rs->created,'d/m/ปปปป H:i:s').'</td>',
			);
		}

		$tables->rows[] = array(
			'',
			'',
			$rs->glcode,
			$rs->glname
			.(in_array(substr($rs->glcode,0,1), array('4','5')) && $rs->projectTitle?'<br />(<a href="'.url('project/'.$rs->tpid).'" target="_blank">'.$rs->projectTitle.'</a>)':''),
			$rs->amount > 0 ? number_format($rs->amount,2) : '',
			($rs->amount < 0 ? number_format(abs($rs->amount),2) : '')
			. $ui->build()
		);

		$prevRs = $rs;

	}

	$ret .= $tables->build();

	$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;

	//$ret.=print_o($dbs,'$dbs');
	head('<style type="text/css">
	.project-summary {padding:10px;background:#1565C0; color:#fff;}
	.project-summary p {margin:0; padding:0 0 0 16px;}
	.project-summary>div {width:33%; display:inline-block;vertical-align: top;}
	.project-summary>div>span {display:block;}
	.project-summary .itemvalue {font-size: 1.2em; line-height:1.2em;}
	.project-report-section {margin: 16px; padding:8px; float: left; box-shadow: 2px 2px 10px #ccc;}
	.graph-section {width:480px; height:320px;}
	.item.-category {width: 360px; float:left;}
	</style>');
	return $ret;
}
?>