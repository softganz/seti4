<?php
function org_volunteer($self,$tpid) {
	$isAdmin=user_access('administrator orgs');
	if ($isAdmin) $self->theme->title='อาสาสมัคร';
	else {
		unset($self->theme->title);
		$ret.='<h2 class="title">อาสาสมัคร</h2>';
	}
	$interest=post('i');
	$email=post('email');


	$interestList=array(
							'teacher'=>'ครูอาสา',
							'disaster'=>'ด้านการจัดการภัยพิบัติ',
							'environment'=>'ด้านอนุรักษ์สิ่งแวดล้อม',
							'swiming'=>'ด้านการเป็นครูฝึกการว่ายน้ำเพื่อเอาชีวิตรอด',
							'child'=>'กิจกรรมกับเด็ก',
							'community'=>'ด้านการพัฒนาชุมชน',
							'healthcare'=>'เยี่ยมผู้ป่วย',
							'other'=>'ด้านอื่น ๆ'
						);
	$ret.='<div class="toolbar">';
	$ret.='<form method="get" action="'.url(q()).'">'._NL;
	$ret.='<select class="form-select" name="i">'._NL.'<option value="">==ทุกประเภท==</option>'._NL;
	foreach ($interestList as $k=>$v) {
		$ret.='<option value="'.$k.'" '.($k==$interest?'selected="selected"':'').'>'.$v.'</option>'._NL;
	}
	$ret.='</select>'._NL;
	if ($isAdmin) $ret.='<input type="checkbox" name="email" '.($email?'checked="checked"':'').' /> รวมอีเมล์ ';
	$ret.='<button>ดู</button>';
	$ret.='</form>';
	$ret.='</div>';
	$where=array();
	//$where=sg::add_condition('i.`fldname`="volunteer"');
	if ($interest) $where=sg::add_condition($where,'i.`fldname`=:interest','interest','interest:'.$interest);
	$stmt='SELECT p.`psnid`, p.`name`, p.`lname`,
						GROUP_CONCAT(i.`fldname`) `interest`,
						p.`email`, p.`phone`, p.`website`, p.`created`
					FROM %db_person% p
						-- RIGHT JOIN %bigdata% b ON b.`keyname`="org" AND b.`fldname`="volunteer" AND b.`keyid`=p.`psnid`
						RIGHT JOIN %bigdata% i ON i.`keyname`="org" AND i.`fldname` LIKE "interest%" AND i.`keyid`=p.`psnid` AND i.`flddata`>0
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					GROUP BY p.`psnid`
					HAVING p.`psnid`>0
					ORDER BY CONVERT(p.`name` USING tis620) ASC ';
	$dbs=mydb::select($stmt,$where['value']);

	$tables = new Table();
	$tables->thead['no']='';
	$tables->thead['name -nowrap']='ชื่อ-สกุล';
	$tables->thead[]='ประเภท';
	if ($isAdmin) {
		$tables->thead[]='โทรศัพท์';
		$tables->thead[]='อีเมล์';
	}
	$tables->thead[]='เว็บไซท์';
	$tables->thead['date']='เมื่อวันที่';
	foreach ($dbs->items as $rs) {
		if ($isAdmin && $email && !empty($rs->email)) $emailList[]=$rs->name.' '.$rs->lname.' &lt;'.$rs->email.'&gt;';
		$interestAll='';
		foreach (explode(',',$rs->interest) as $item) {
			list($x,$interestType)=explode(':', $item);
			$interestAll[]=$interestList[$interestType];
		}
		unset($row);
		preg_match('~^(http|ftp)(s)?\:\/\/((([a-z0-9]{1,25})(\.)?){2,7})($|/.*$)~i', $rs->website,$web);
		$row[]=++$no;
		$row[]='<a href="'.url('org/volunteer/info/'.$rs->psnid).'">'.$rs->name.' '.$rs->lname.'</a>';
		$row[]=implode(' , ',$interestAll);
		if ($isAdmin) {
			$row[]=$rs->phone;
			$row[]=$rs->email;
		}
		$row[]=$rs->website?'<a href="'.$rs->website.'" target="_blank">'.$web[3].'</a>':'';
		$row[]=sg_date($rs->created,'ว ดด ปปปป');
		$tables->rows[]=$row;
	}
	if ($isAdmin && $email) $ret.='<h3>รวมอีเมล์</h3><p>'.implode(' , ', $emailList).'</p>';
	$ret .= $tables->build();
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>