<?php

	/**
	 * iMed report วันหมดอายุบัตร
	 *
	 */
	function imed_report_regexpire($self) {
		$prov=SG\getFirst($_REQUEST['p'],'90');
		$ampur=$_REQUEST['a'];
		$tambon=$_REQUEST['t'];
		$village=$_REQUEST['v'];
		$todate=sg_date(SG\getFirst(post('todate'),date('Y-m-d')),'Y-m-d');
		$showRawDate=false;

		$isAdmin=user_access('administer imeds');
		$zones=imed_model::get_user_zone(i()->uid,'imed');

		$self->theme->title='วันที่บัตรหมดอายุ';
		if (post('todate')=='') {
			$ret.='<form method="get" action="'.url(q()).'" class="report-form sg-form" data-rel="#report-output">';
			$ret.='<h3>รายงานวันที่บัตรหมดอายุ</h3>';
			$ret.='<div class="form-item">'._NL;
			$provdbs=mydb::select('SELECT DISTINCT `provid`, `provname` FROM %imed_disabled_defect% df LEFT JOIN %db_person% p ON p.`psnid`=df.`pid` LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` HAVING `provname` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC');
			$ret.='<label for="prov">จังหวัด : </label>'._NL.'<select name="p" id="prov" class="form-select">'._NL.'<option value="">--ทุกจังหวัด--</option>'._NL;
			foreach ($provdbs->items as $rs) $ret.='<option value="'.$rs->provid.'"'.($rs->provid==$prov?' selected="selected"':'').'>'.$rs->provname.'</option>'._NL;
			$ret.='</select>'._NL;
			if ($prov) {
				$stmt='SELECT DISTINCT `distid`, `distname` FROM %co_district% cod WHERE SUBSTR(`distid`,1,2)=:prov ORDER BY CONVERT(`distname` USING tis620) ASC';
				$ret.='<label for="ampur"> อำเภอ : </label>'._NL.'<select name="a" id="ampur" class="form-select">'._NL.'<option value="">--ทุกอำเภอ--</option>'._NL;
				foreach (mydb::select($stmt,':prov',$prov)->items as $rs) $ret.='<option value="'.substr($rs->distid,2,2).'"'.(substr($rs->distid,2,2)==$ampur?' selected="selected"':'').'>'.$rs->distname.'</option>'._NL;
				$ret.='</select>'._NL;
				$ret.='<label for="tambon"> ตำบล : </label>'._NL.'<select name="t" id="tambon" class="form-select">'._NL.'<option value="">--ทุกตำบล--</option>'._NL.'</select>'._NL;
				$ret.='<label for="village"> หมู่บ้าน : </label>'._NL.'<select name="v" id="village" class="form-select">'._NL.'<option value="">--ทุกหมู่บ้าน--</option>'._NL.'</select>'._NL;
			}
			$ret.='<button class="btn -primary -main" value="ดูรายงาน" type="submit"><i class="icon -forward -white"></i><span>ดูรายงาน</span></button>'._NL;
			$ret.='</div>'._NL;
			$ret.='<div class="optionbar"><ul>';
			$ret.='<li><label>บัตรหมดอายุภายใน </label><input type="text" name="todate" class="form-text sg-datepicker" size="10" value="'.sg_date($todate,'d/m/ปปปป').'" /></li>';

			if ($isAdmin) {
				$ret.='<li><input type="checkbox" name="showerror" > แสดงวันที่ผิดพลาด</li><li><input type="checkbox" name="fixed" > ซ่อมแซมวันที่ผิดพลาด</li>';
			}
			$ret.='</ul></div>';
			$ret.='</form>';
		}

		$ret.='<div id="report-output">';

		if ($isAdmin && (post('showerror') || post('fixed'))) {
			mydb::where('qt.`part`="PSNL.1.9.1.3" && qt.`value`!="" AND (DATE_FORMAT(qt.`value`,"%Y-%m-%d") IS NULL OR DATE_FORMAT(rd.`value`,"%Y-%m-%d") IS NULL) ');
		} else {
			mydb::where('qt.`part`="PSNL.1.9.1.3" && qt.`value`!="" ');
			if ($todate) mydb::where('qt.`value`<=:todate',':todate',$todate);


			if ($prov) mydb::where('p.`changwat`=:prov',':prov',$prov);
			if ($ampur) mydb::where('p.`ampur`=:ampur',':ampur',$ampur);
			if ($tambon) mydb::where('p.`tambon`=:tambon',':tambon',$tambon);
			if ($village) mydb::where('p.`village`=:village',':village',$village);

			if ($isAdmin) {

			} else  if ($zones) {
				mydb::where('('.'p.`uid`=:uid OR '.R::Model('imed.person.zone.condition',$zones).')',':uid',i()->uid);
			} else {
				mydb::where('p.`uid`=:uid',':uid',i()->uid);
			}
		}
		$stmt='SELECT qt.`pid`,DATE_FORMAT(qt.`value`,"%Y-%m-%d"),
							CONCAT(IFNULL(`prename`,"")," ",p.`name`," ",`lname`) fullname,
							qt.`part`, qt.`qid`, qt.`value` expiredate,
							rd.`qid` rdQid, rd.`value` registerdate,
							p.`changwat`, p.`ampur`, p.`tambon`,
							cosd.`subdistname`, codist.`distname`, copv.`provname`
						FROM %imed_qt% qt
							LEFT JOIN %imed_qt% rd ON rd.`pid`=qt.`pid` AND rd.`part`="PSNL.1.9.1.2"
							LEFT JOIN %db_person% p ON p.`psnid`=qt.`pid`
							LEFT JOIN %co_province% copv ON copv.`provid`=p.`changwat`
							LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
							LEFT JOIN %co_subdistrict% cosd ON cosd.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
						%WHERE%
						ORDER BY expiredate ASC, `provname` ASC, `distname` ASC, `subdistname` ASC
						';
		$dbs=mydb::select($stmt);

		// Show register date error of fix it
		if ($isAdmin && (post('showerror') || post('fixed'))) {
			$showRawDate=true;
			if (post('fixed')) {
				foreach ($dbs->items as $key=>$rs) {
					$newExpireDate=sg_date($rs->expiredate.' 00:00:00','Y-m-d');
					$newRegDate=sg_date($rs->registerdate,'Y-m-d');
					$dbs->items[$key]->registerdate=$newRegDate;
					$dbs->items[$key]->expiredate=$newExpireDate;
					if ($newRegDate) mydb::query('UPDATE %imed_qt% SET `value`=:newRegDate WHERE `qid`=:qid LIMIT 1',':newRegDate',$newRegDate, ':qid',$rs->rdQid);
					if ($newExpireDate) mydb::query('UPDATE %imed_qt% SET `value`=:newExpDate WHERE `qid`=:qid LIMIT 1',':newExpDate',$newExpireDate, ':qid',$rs->qid);
				}
			}
		}

		$tables = new Table();
		$tables->thead=array('','ชื่อ-สกุล','date regdate'=>'วันต่อบัตรล่าสุด','date expdate'=>'วันที่บัตรหมดอายุ','พื้นที่');
		$no=0;
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				++$no,
				'<a href="'.url('imed', ['pid' => $rs->pid]).'" role="patient" data-pid="'.$rs->pid.'">'.SG\getFirst($rs->fullname,'...').'</a>',
				//$rs->registerdate,
				//$rs->expiredate,
				$showRawDate?$rs->registerdate:$rs->registerdate?sg_date($rs->registerdate,'ว ดด ปปปป'):'',
				$showRawDate?$rs->expiredate:sg_date($rs->expiredate,'ว ดด ปปปป'),
				SG\implode_address($rs,'short'),
			);
		}

		$ret .= $tables->build();
		if ($dbs->_empty) $ret.='ไม่มีข้อมูล';
		//$ret.=print_o($dbs,'$dbs');
		$ret.='</div>';
		return $ret;
	}
?>