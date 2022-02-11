<?php
function imed_poorman_list($self) {
	$prov=post('prov');
	$ampur=post('ampur');
	$tambon=post('tambon');
	$status=post('status');


	if (!i()->ok) {
		R::View('imed.toolbar',$self,'คนยากลำบาก@Secure Log in','poorman');
		return R::View('signform');
	}


	R::View('imed.toolbar',$self,'คนยากลำบาก','poorman');

	$start=0;
	$showItems=100;

	if (post('s')) $start=intval(post('s'));

	$self->theme->container->{'data-url'}=url('imed/poorman/list');

	$isAdmin=user_access('administer imeds');
	$qtstatus=R::Model('imed.qt.status');

	$zones=imed_model::get_user_zone(i()->uid,'imed.poorman');

	if ($start==0) {
		$statusList=array(_START=>'กำลังป้อน', _DRAFT=>'แก้ไข', _WAITING=>'รอตรวจ', _COMPLETE=>'อนุมัติ', _CANCEL=>'ยกเลิก', _REJECT=>'ไม่ผ่าน');

		$nav.='<nav class="nav -page">'._NL;

		$nav.='<form class="sg-form form -report" method="get" action="'.url('imed/app/poorman/list').'" data-rel="#result">';
		$nav.='<ul>';

		// Select province
		$nav.='<li class="ui-nav">';

		$options='<option value="">==ทุกสถานะ==</option>';
		foreach ($statusList as $key => $value) {
			$options.='<option value="'.$key.'">'.$value.'</option>';
		}
		$nav.='<select class="form-select" name="status">'.$options.'</select> ';
		$nav.='<select id="changwat" class="form-select sg-changwat" name="prov"><option value="">==ทุกจังหวัด==</option>';

		// Select province
		mydb::where('q.`qtgroup`=4 AND q.`qtstatus`>=0');
		if (!$isAdmin) mydb::where('(q.`uid`=:uid'.($zones?' OR ('.R::Model('imed.person.zone.condition',$zones).')':'').')',':uid',i()->uid);
		$stmt='SELECT `changwat`,`provname`,COUNT(*)
					FROM %qtmast% q
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
					%WHERE%
					GROUP BY `changwat`
					HAVING `provname`!=""
					ORDER BY CONVERT(`provname` USING tis620) ASC';
		$provDb=mydb::select($stmt);
		foreach ($provDb->items as $item) {
			$nav.='<option value="'.$item->changwat.'" '.($item->changwat==$prov?'selected="selected"':'').'>'.$item->provname.'</option>';
		}
		$nav.='</select> ';

		$nav.='<select id="ampur" class="form-select sg-ampur'.($prov?'':' -hidden').'" name="ampur"><option value="">== ทุกอำเภอ ==</option>';
		if ($prov) {
			$stmt='SELECT * FROM %co_district% WHERE LEFT(`distid`,2)=:prov';
			$dbs=mydb::select($stmt,':prov',$prov);
			foreach ($dbs->items as $rs) {
				$nav.='<option value="'.substr($rs->distid,2,2).'" '.($ampur==substr($rs->distid,2,2)?'selected="selected"':'').'>'.$rs->distname.'</option>';
			}
		}
		$nav.='</select> ';
		$nav.='<select id="tambon" class="form-select sg-tambon'.($ampur?'':' -hidden').'" name="tambon"><option value="">== ทุกตำบล ==</option>';
		if ($ampur) {
			$stmt='SELECT * FROM %co_subdistrict% WHERE LEFT(`subdistid`,4)=:ampur';
			$dbs=mydb::select($stmt,':ampur',$prov.$ampur);
			//debugMsg($dbs,'$dbs');
			foreach ($dbs->items as $rs) {
				$nav.='<option value="'.substr($rs->subdistid,4,2).'" '.($tambon==substr($rs->subdistid,4,2)?'selected="selected"':'').'>'.$rs->subdistname.'</option>';
			}
		}
		$nav.='</select>';


		$nav.='&nbsp;&nbsp;<button type="submit" class="btn -primary"><i class="icon -search -white"></i></button>';
		//if ($ampur) $nav.='&nbsp;&nbsp;<button type="submit" class="btn" name="export" value="excel"><i class="icon -download"></i><span>Export</span></button>';
		$nav.='</li>';
		//$nav.='<li>';
		//$nav.='<select class="form-select" name="sex"><option value="" />ทุกเพศ</option><option value="1">ชาย</option><option value="2">หญิง</option></option></select>';
		//$nav.='</li>';
		$nav.='</ul></form>';
		//$nav.=$ui->build();
		$nav.='</nav>';
		$self->theme->navbar=$nav;
		$ret.='<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('imed/poorman/form').'"><i class="icon -addbig -white"></i></a></div>';
	}

	$ret.='<div id="result">'._NL;
	//$ret.=print_o(post(),'post()');

	mydb::where('q.`qtgroup`=4');
	if ($status!='') {
		mydb::where('q.`qtstatus`=:status',':status',$status);
	} else {
		mydb::where('q.`qtstatus`>=0');
	}
	if (!$isAdmin) mydb::where('(q.`uid`=:uid'.($zones?' OR ('.R::Model('imed.person.zone.condition',$zones).')':'').')',':uid',i()->uid);

	if ($prov) mydb::where('p.`changwat`=:changwat',':changwat',$prov);
	if ($ampur) mydb::where('p.`ampur`=:ampur',':ampur',$ampur);
	if ($tambon) mydb::where('p.`tambon`=:tambon',':tambon',$tambon);

	$stmt='SELECT
					q.`qtref`, q.`qtstatus`, q.`qtform`
				, p.`psnid`, CONCAT(p.`name`," ",p.`lname`) `fullname`
				, cop.`provname`
				, q.`created`
				, q.`uid`, u.`name` `poster`
				, COUNT(f.`fid`) `photos`
				, f.`file` `photo`
				FROM %qtmast% q
					LEFT JOIN %db_person% p USING(`psnid`)
					LEFT JOIN %imed_files% f USING(`seq`)
					LEFT JOIN %users% u ON u.`uid`=q.`uid`
					LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
				%WHERE%
				GROUP BY `qtref`
				ORDER BY `qtref` DESC
				LIMIT '.$start.','.$showItems.'
				;';
	$dbs=mydb::select($stmt);
	//$ret.='<pre>'.mydb()->_query.'</pre>';

	$no=$start;

	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','ชื่อ นามสกุล','จังหวัด','center -status'=>'สถานะ','');
	foreach ($dbs->items as $rs) {
		$isEdit=($isAdmin || i()->uid==$rs->uid) && $rs->qtstatus<_COMPLETE;
		$tables->rows[]=array(
											++$no,
											'<a class="sg-action" href="'.url('imed/app/poorman/form/'.$rs->qtref).'" data-rel="box">'.$rs->fullname.'</a>'
											.'<br /><span class="timestamp">โดย '.$rs->poster
											.' @'.sg_date($rs->created,'ว ดด ปป H:i')
											.' ('.$rs->qtform.')'
											.($rs->photos?'<i class="icon -image"></i> '.$rs->photos.' ภาพ':'')
											.'</span>',
											$rs->provname,
											$qtstatus[$rs->qtstatus],
											$isEdit?'<a class="sg-action" href="'.url('imed/poorman/edit/'.$rs->qtref.'/cancel').'" data-rel="box" title="ยกเลิกแบบสอบถาม"><i class="icon -cancel"></i></a>':'',
											);
	}
	$ret.=$tables->build();


	if ($dbs->_num_rows==$showItems) {
		$ret.='<p><a class="sg-action btn -primary" href="'.url('imed/poorman/list',array('status'=>$status,'prov'=>$prov,'ampur'=>$ampur?$ampur:NULL,'tambon'=>$tambon?$tambon:NULL,'s'=>$start+$dbs->_num_rows)).'" data-rel="replace" style="margin:0 16px;display:block;text-align:center;"><span>More</span><i class="icon -forward -white"></i></a></p>';
	}

	//$ret.=print_o($dbs,'$dbs');
	$ret.='</div><!-- result -->'._NL;
	return $ret;
}
?>