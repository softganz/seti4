<?php
/**
 * Search project
 *
 * @param $para->province String[2]
 * @param $para->order Numeric default t.title
 * @param $para->sort Numeric default ASC
 * @param $para->items Numeric default 1000
 * @param $_REQUEST[q] String
 * @param $_REQUEST[order] String
 * @param $_REQUEST[sort] String
 * @return String
 */
function project_search($self) {
	$para=para(func_get_args(),'order=t.title','sort=ASC','items=10000');

	$projectset=SG\getFirst($para->set,post('set'));
	$year=SG\getFirst($para->year,post('year'));
	$province=SG\getFirst($para->province,post('province'));
	$zone=SG\getFirst($para->province,post('zone'));
	$u=SG\getFirst($para->u,post('u'));

	$zoneList=cfg('zones');

	R::View('project.toolbar',$self,'รายชื่อโครงการ');

	$order=SG\getFirst($_REQUEST['order'],$para->order);
	$sort=htmlspecialchars(SG\getFirst($_REQUEST['sort'],$para->sort));
	$items=htmlspecialchars(SG\getFirst($_REQUEST['items'],$para->items));

	$text[]='รายชื่อโครงการ';
	$where=array();
	$where=sg::add_condition($where,'`prtype`="โครงการ"');
	if ($projectset) $where=sg::add_condition($where,'p.`projectset` IN ( :projectset ) OR t.`parent` IN ( :projectset ) ','projectset','SET:'.$projectset);
	if (post('org')) $where=sg::add_condition($where,'`orgid`=:orgid ','orgid',post('org'));

	if ($province) {
		if (cfg('project.multiplearea')) {
			$where=sg::add_condition($where,'a.changwat=:changwat ','changwat',$province);
		} else {
			$where=sg::add_condition($where,'p.changwat=:changwat ','changwat',$province);
		}
		$text[]='จังหวัด'.mydb::select('SELECT provname FROM %co_province% WHERE provid=:provid LIMIT 1',':provid',$province)->provname;
	}

	if ($zone && $zoneList) {
		$where=sg::add_condition($where,'LEFT(t.`changwat`,1) IN (:zone)','zone','SET:'.$zoneList[$zone]['zoneid']);
		$text[]=' พื้นที่ '.$zoneList[$zone]['name'];
	}
	if ($year && $year != '*') {
		$where=sg::add_condition($where,'`pryear`=:year ','year',$year);
		$text[]=' ปี '.($year+543);
	}
	if ($_REQUEST['q']) {
		$where=sg::add_condition($where,'t.title LIKE :q OR p.agrno LIKE :q OR p.prid LIKE :q','q','%'.$_REQUEST['q'].'%');
		$text[]='ที่มีคำว่า "'.$_REQUEST['q'].'"';
	}

	if ($para->trainer) {
		$dbs=mydb::select('SELECT DISTINCT tu.tpid FROM %topic_user% tu WHERE `membership`="Trainer" AND tu.uid=:uid',':uid',$para->trainer);
		if ($dbs->_num_rows) $where=sg::add_condition($where,'p.tpid IN ('.$dbs->lists->text.')'); else return;
		$text[]='ในการดูแลของพี่เลี้ยง "'.mydb::select('SELECT `name` FROM %users% WHERE `uid`=:uid LIMIT 1',':uid',$para->trainer)->name.'"';
	} else if ($para->owner) {
		$dbs=mydb::select('SELECT DISTINCT tu.tpid FROM %topic_user% tu WHERE `membership`="Owner" AND tu.uid=:uid',':uid',$para->owner);
		if ($dbs->_num_rows) $where=sg::add_condition($where,'p.tpid IN ('.$dbs->lists->text.')'); else return;
		$text[]='ในการดูแลของ "'.mydb::select('SELECT `name` FROM %users% WHERE `uid`=:uid LIMIT 1',':uid',$para->owner)->name.'"';
	} else if ($u) {
		$dbs=mydb::select('SELECT DISTINCT p.`tpid` FROM %project% p LEFT JOIN %topic_user% tu USING (`tpid`) WHERE tu.`uid`=:uid',':uid',$u);
		if ($dbs->_num_rows) $where=sg::add_condition($where,'p.tpid IN ('.$dbs->lists->text.')'); else return;
		$text[]='ในการดูแลของ "'.mydb::select('SELECT `name` FROM %users% WHERE `uid`=:uid LIMIT 1',':uid',$u)->name.'"';
		$order='CASE WHEN t.`uid`='.$u.' THEN CONCAT(pryear,0) ELSE CONCAT(pryear,1) END';
		$sort='DESC';
	}

	if ($order=='projectset') {
		$order='projectset_name';
		$sort='ASC';
	}

	$items=100;
	$page=post('page');
	$firstRow=$page>1 ? ($page-1)*$items : 0;

	$subquery = 'p.`tpid`, p.`agrno`, p.`prid`, p.`pryear`
		, p.`prtype`
		, p.`project_status`, p.`project_status`+0 project_statuscode
		, t.`title`, p.`date_from`, p.`date_end`
		, t.`reply`,t.`last_reply`, p.`area`
		, t.`status`
		, p.`projectset`, pset.`title` projectset_name
		, t.`uid`, u.`username`
		, u.`name` ownerName
		, t.`created`
		, t.`orgid`, o.`name` `departmentName`
		FROM %project% p
			LEFT JOIN %topic% t ON t.tpid=p.tpid
			LEFT JOIN %users% u ON u.uid=t.uid
			'.(cfg('project.multiplearea')?'LEFT JOIN %project_prov% a ON a.`tpid`=t.`tpid`':'').'
			LEFT JOIN %topic% pset ON p.projectset=pset.tpid
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
		'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
		GROUP BY `tpid`';

	$stmt = 'SELECT
		  t.*
		, (SELECT COUNT(*) FROM %calendar% pc WHERE pc.`tpid`=t.`tpid`) calendar_totals
		, (SELECT COUNT(*) FROM %project_tr% tres WHERE tres.`tpid`=t.`tpid` AND tres.`formid`="ประเมิน") estimationAmt
		, (SELECT MAX(`created`) FROM %project_tr% lr WHERE lr.`tpid`=t.`tpid` AND formid="activity") last_report
		,	(SELECT COUNT(*) FROM %project_tr% otr WHERE otr.`tpid`=t.`tpid` AND otr.`formid`="activity" AND otr.`part`="owner") owner_reply
		, (SELECT COUNT(*) FROM %project_tr% ctr WHERE ctr.`tpid`=t.`tpid` AND ctr.`formid`="activity" AND ctr.`part`="trainer") trainer_reply
		, (SELECT fo1.`flag` FROM %project_tr% fo1 WHERE fo1.`tpid`=t.`tpid` AND fo1.`formid`="follow" AND fo1.`part`="title" AND fo1.`period`=1 LIMIT 1) `followPeriod1`
		, (SELECT fo2.`flag` FROM %project_tr% fo2 WHERE fo2.`tpid`=t.`tpid` AND fo2.`formid`="follow" AND fo2.`part`="title" AND fo2.`period`=3 LIMIT 1) `followPeriod2`
		, (SELECT fo3.`flag` FROM %project_tr% fo3 WHERE fo3.`tpid`=t.`tpid` AND fo3.`formid`="follow" AND fo3.`part`="title" AND fo3.`period`=3 LIMIT 1) `followPeriod3`
		'.(projectcfg::enable('ส.1')?', (SELECT GROUP_CONCAT(`trid`,"|",`period`) FROM %project_tr% s1 WHERE s1.tpid=t.tpid AND s1.formid="ส.1" AND s1.`part`="title" ORDER BY `period` ASC) s1Text':'').'
		'.(projectcfg::enable('ส.2')?', (SELECT GROUP_CONCAT(`trid`,"|",`period`) FROM %project_tr% s2 WHERE s2.tpid=t.tpid AND s2.formid="ส.2" ORDER BY `period` ASC) s2Text':'').'
		'.(projectcfg::enable('ส.2')?', (SELECT COUNT(*) FROM %project_tr% s3 WHERE s3.tpid=t.tpid AND s3.formid="ส.2") s3amt':'').'
		'.(projectcfg::enable('ง.1')?', (SELECT GROUP_CONCAT(`trid`,"|",`period`,"|",`flag`) FROM %project_tr% m1 WHERE m1.tpid=t.tpid AND m1.formid="info" && `flag` IS NOT NULL ORDER BY `period` ASC) m1Text':'').'
		'.(projectcfg::enable('ง.2')?', (SELECT COUNT(*) FROM %project_tr% m2 WHERE m2.tpid=t.tpid AND m2.formid="ง.2") m2amt':'').'
		'.(projectcfg::enable('trainer')?', (SELECT COUNT(*) FROM %topic_files% ffo WHERE ffo.tpid=t.tpid AND ffo.tagname="Follow") Follows':'').'
		FROM
			(
			SELECT DISTINCT '.$subquery.'
			ORDER BY CONVERT('.$order.' USING tis620) '.$sort.'
			LIMIT '.$firstRow.' , 100
			) t';
	$dbs=mydb::select($stmt,$where['value']);
	//debugMsg('<pre>'.mydb()->_query.'</pre>');
	$dbs->_start_row = $firstRow;

	// For calculate FOUND_ROWS() because SQL_CALC_FOUND_ROWS cannot use in subquery
	$stmt="SELECT SQL_CALC_FOUND_ROWS DISTINCT $subquery LIMIT 1; -- PROJECT SEARCH";
	$fdbs = mydb::select($stmt,$where['value']);
	$totals = $fdbs->_found_rows;

	if ($para->year) $pagePara['year']=$para->year;
	if (post('province')) $pagePara['province']=post('province');
	if (post('org')) $pagePara['org']=post('org');
	$pagePara['q']=post('q');
	$pagePara['page']=$page;
	$pagenv = new PageNavigator($items,$page,$totals,q(),false,$pagePara);
	$no=$pagenv?$pagenv->FirstItem():0;

	//$ret.='First item='.$pagenv->FirstItem();
	//$sql_cmd .= '  LIMIT '.$pagenv->FirstItem().','.$items;

	//$ret.='Total = '.$totals;

	//$ret.=print_o($dbs,'$dbs');

	$text[]='('.($totals?'จำนวน '.$totals.' โครงการ' : 'ไม่มีโครงการ').')';
	if ($text) $self->theme->title=implode(' ',$text);

	if ($order=="year") $dbs->_group='pryear';
	else if ($order=='projectset_name') $dbs->_group='projectset_name';
	//		if (i()->username=='softganz') $ret.=print_o($dbs,'$dbs');
	if ($dbs->_empty) {
		$ret.=message('error','ไม่มีรายชื่อโครงการตามเงื่อนไขที่ระบุ');
	} else {
		$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
		$ret .= R::View('project.list',$dbs,$para);
		$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
		$ret.='<p>รวมทั้งสิ้น <strong>'.$totals.'</strong> โครงการ</p>';
	}
	return $ret;
}
?>