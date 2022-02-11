<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_job_home($self) {
	$shopInfo = R::Model('garage.get.shop');

	$getShow = post('show');
	$getOrderBy = post('o');
	$getShop = post('shop');

	R::Model('garage.verify',$self, $shopInfo,'JOB');

	$packageJobRemain = $shopInfo->packageJobRemain;

	$ret = '';

	mydb::where('(j.`shopid` = :shopid OR (s.`shopparent` = :shopid AND s.`shopinclude` = "YES"))',':shopid', $shopInfo->shopid);
	if ($getShop) mydb::where('j.`shopid` = :selectShop', ':selectShop', $getShop);
	if (empty($getShow)) mydb::where('j.`isjobclosed` != "Yes"');
	else if ($getShow=='notin') mydb::where('j.`carindate` IS NULL');
	else if ($getShow=='returned') mydb::where('j.`iscarreturned` = "Yes"');
	else if ($getShow=='notreturned') mydb::where('j.`iscarreturned` = "No"');
	else if ($getShow=='recieved') mydb::where('j.`isrecieved` = "Yes"');
	else if ($getShow=='notrecieved') mydb::where('j.`isrecieved` = "No"');
	else if ($getShow=='noretdate') mydb::where('j.`datetoreturn` IS NULL AND j.`iscarreturned` = "No"');
	else if ($getShow=='retdate') mydb::where('j.`datetoreturn` IS NOT NULL AND j.`iscarreturned` = "No"');
	else if ($getShow=='closed') mydb::where('j.`isjobclosed` = "Yes"');
	else if ($getShow=='notclosed') mydb::where('j.`isjobclosed` = "No"');

	if (post('month')) mydb::where('DATE_FORMAT(j.`rcvdate`,"%Y-%m") = :month',':month',post('month'));
	if (post('insu')) mydb::where('j.`insurerid` = :insurerid',':insurerid',post('insu'));
	if (post('q')) mydb::where('j.`jobno` LIKE :q OR j.`plate` LIKE :q',':q','%'.post('q').'%');

	mydb::value('$ORDER$', 'j.`tpid` DESC');
	if ($getOrderBy == 'date') mydb::value('$ORDER$', 'j.`rcvdate` DESC');
	else if ($getOrderBy == 'in') mydb::value('$ORDER$', 'j.`carindate` DESC');
	else if ($getOrderBy == 'toret') mydb::value('$ORDER$', 'IF(j.`iscarreturned` = 2 && j.`datetoreturn` IS NOT NULL,0,1) ASC, j.`datetoreturn` ASC');

	$stmt = 'SELECT
		j.*
		, s.`shopparent`
		, s.`shortname` `shopShortName`
		, b.`brandname`
		, c.`customername`
		, COUNT(tr.`jobtrid`) `totalCmd`
		, SUM(tr.`totalsale`) `totalPrice`
		, (SELECT SUM(`replyprice`) FROM %garage_qt% q WHERE q.`tpid` = j.`tpid`) `replyPrice`
		FROM %garage_job% j
			LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %garage_brand% b ON b.`shopid` = j.`shopid` AND b.`brandid` = j.`brandid`
			LEFT JOIN %garage_customer% c USING(`customerid`)
			LEFT JOIN %garage_jobtr% tr USING(`tpid`)
		%WHERE%
		GROUP BY j.`tpid`
		ORDER BY $ORDER$
		';

	$dbs = mydb::select($stmt);

	//$ret.=mydb()->_query;
	//$ret.=print_o($dbs,'$dbs');

	new Toolbar($self, 'สั่งซ่อม ('.$dbs->_num_rows.' รายการ)', 'job', $shopInfo);

	$ret.='<form id="garage-job-code-new" class="sg-form" method="post" action="'.url('garage/job').'" data-checkvalid="true">'._NL;

	$tables = new Table();
	$tables->addClass('garage-job-list -center');
	$tables->addConfig('showHeader', false);
	$tables->thead = array(
		'jobno -nowrap'=>'เลขใบซ่อม <a href="'.url('garage/job',array('show'=>$getShow)).'"><i class="icon -sort'.($getOrderBy == '' ? '' : ' -gray').'"></i></a>',
		'date -rcvdate'=>'วันรับรถ <a href="'.url('garage/job',array('show'=>$getShow,'o'=>'in')).'"><i class="icon -sort'.($getOrderBy == 'in' ? '' : ' -gray').'"></i></a>',
		'date -toret -nowrap'=>'วันนัดรับรถ <a href="'.url('garage/job',array('show'=>$getShow,'o'=>'toret')).'"><i class="icon -sort'.($getOrderBy == 'toret' ? '' : ' -gray').'"></i></a>',
		'ทะเบียน',
		'รายละเอียดรถ',
		'ลูกค้า',
		'amt -tr -nowrap'=>'รายการซ่อม',
		'money -totalprice'=>'ราคาเสนอ',
		'money -replyprice'=>'ราคาตกลง',
		'status -nowrap'=>'สถานะ',
		''
	);

	if ($packageJobRemain > 0) {
		$tables->rows[]=array(
			'<input type="hidden" name="newjob" value="yes" />'
			.'<input id="codeid" class="form-text -jobno -fill -uppercase -require" type="text" name="jobno" value="'.R::Model('garage.nextno',$shopInfo->shopid,'job')->nextNo.'" placeholder="เลขใบซ่อม" size="5" maxlength="10" readonly="readonly" />',
			'<input id="rcvdate" class="form-text sg-datepicker -date -fill -require" type="text" name="rcvdate" value="'.sg_date('d/m/Y').'" placeholder="31/12/'.date('Y').'" size="7" maxlength="10" />',
			'<td colspan="2"><label for="plate" class="-hidden">ทะเบียนรถ</label><input id="plate" class="form-text -fill -require -uppercase" type="text" name="plate" value="" placeholder="ทะเบียนรถ" size="7" /></td>',
			'<label for="brandid" class="-hidden">ยี่ห้อรถ</label><input id="brandid" class="form-text sg-autocomplete -fill -require -uppercase" type="text" name="brandid" value="" placeholder="ยี่ห้อรถ" size="7" data-query="'.url('garage/api/brand').'" data-select="value" />',
			'<td class="form-item -group -customer-name" colspan="3">'
				. '<span class="form-group">'
				. '<label for="customerid" class="-hidden">รหัสลูกค้า</label><input id="customerid" class="form-text -require" type="hidden" name="customerid" value="" />'
				. '<input id="custname" class="form-text sg-autocomplete -fill -require" type="text" name="custname" value="" placeholder="ชื่อลูกค้า" size="7" data-query="'.url('garage/api/customer').'" data-altfld="customerid" data-select="label" />'
				. '<div class="input-append">'
				. '<span><a class="sg-action btn -add-customer" href="'.url('garage/code/customer/form',array('callback' => 'updateCustomerId')).'" data-rel="box" data-width="480" title="เพิ่มลูกค้าใหม่"><i class="icon -material">person_add</i><span class="-hidden">เพิ่มลูกค้าใหม่</span></a></span>'
				. '</div>'
				. '</span>'
				. '</td>',

			//'<td class="-customer-name" colspan="3"><input id="customerid" type="hidden" name="customerid" value="" /><input id="customername" class="form-text sg-autocomplete -fill -require" type="text" name="customername" value="" placeholder="ชื่อลูกค้า" size="7" data-query="'.url('garage/api/customer').'" data-altfld="customerid" data-select="label" /><a class="sg-action btn -add-customer" href="/busi/abcg.com/garage/code/customer/form?callback=updateCustomerId" data-rel="box" data-width="480" title="เพิ่มลูกค้าใหม่"><i class="icon -material">person_add</i><span class="-hidden">เพิ่มลูกค้าใหม่</span></a></td>',
			'<td colspan="3"><button class="btn -primary -nowrap" type="submit"><i class="icon -material">add</i><span>สร้างใบสั่งซ่อมใหม่</span></button>'
			.($action=='edit'?'<a class="sg-action button-cancel" href="'.url('garage/code/brand/list').'" data-rel="#garage-code-trans">ยกเลิกแก้ไข</a>':'')
			.'</td>',
			'config'=>array('class'=>'-input -no-print'),
		);
	} else {
		$tables->rows[] = array('<td colspan="11"><b>ไม่สามารถเพิ่มใบสั่งซ่อมได้</b></td>');
	}

	head('<script type="text/javascript">
	function updateCustomerId($this, data) {
		$("#customerid").val(data.customerid)
		$("#customername").val(data.customername)
	}
	</script>');

	$tables->rows[]='<header>';

	foreach ($dbs->items as $rs) {
		$class = '-shop-'.$rs->shopShortName.' ';
		if (empty($rs->carindate)) $class .= '-carnotin ';
		if ($rs->datetoreturn && strtoupper($rs->iscarreturned) != 'YES') {
			$dateToReturn = new DateTime($rs->datetoreturn);
			$dateToday =  new DateTime(date('Y-m-d'));
			$diff = $dateToReturn->diff($dateToday)->format("%r%a");
			if ($diff < -3) $class .= '-remain-many ';
			else if ($diff == -3) $class .= '-remain-3 ';
			else if ($diff == -2) $class .= '-remain-2 ';
			else if ($diff == -1) $class .= '-remain-1 ';
			else if ($diff == 0) $class .= '-remain-today ';
			else $class .= '-remain-late ';
		}
		if ($rs->jobstatus == 1 && empty($rs->carindate)) $rs->jobstatus = 0;
		$tables->rows[]=array(
			$rs->jobno,
			$rs->carindate?sg_date($rs->carindate,'d/m/ปปปป'):'*',
			($rs->datetoreturn ? sg_date($rs->datetoreturn,'d/m/ปปปป') : ''),
			$rs->plate,
			$rs->brandname.($rs->modelname	?' '.$rs->modelname	:'').($rs->colorname?' สี'.$rs->colorname:''),
			$rs->customername,
			$rs->totalCmd?$rs->totalCmd:'',
			$rs->totalPrice?number_format($rs->totalPrice,2):'',
			$rs->replyPrice>0?number_format($rs->replyPrice,2):'',
			GarageVar::$jobStatusList[$rs->jobstatus],
			'<a href="'.url('garage/job/'.$rs->tpid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>',
			'config'=>array('class'=>$class),
		);
	}
	$ret.=$tables->build();
	return $ret;
}
?>