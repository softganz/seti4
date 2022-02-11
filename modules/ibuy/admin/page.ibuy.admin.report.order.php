<?php
/**
* Admin Order report
* Created 2019-02-28
* Modify  2020-01-29
*
* @param Object $self
* @param Int $oid
* @return String
*/

$debug = true;

function ibuy_admin_report_order($self, $oid = NULL) {
	$self->theme->title = 'รายงานใบสั่งซื้อสินค้า'.($oid?' - ใบสั่งซื้อหมายเลข '.$oid:'');
	$self->theme->sidebar = R::Page('ibuy.admin.menu', 'report');

	$isfranchiseInstall = cfg('ibuy.install.franchise');

	$q = post('q');
	$id = SG\getFirst(post('id'),$id);
	$action = post('action');
	$order = SG\getFirst($para->order,post('o'),'oid');
	$sort = SG\getFirst($para->sort,post('s'),2);
	$year = post('year');
	$status = post('st');
	$itemPerPage = SG\getFirst(post('i'),100);
	$type = post('t');
	$org = SG\getFirst(post('org'),'');

	$orders = array(
		'oid' => array('เลขที่ใบสั่งสินค้า','`oid`'),
	);
	$statusList = ibuy_define::status_text();

	$navbar .= '<nav class="nav -page"><header class="header -hidden"><h3>Project Management</h3></header>'._NL;
	$navbar .= '<form class="form report-form -sg-flex" id="search-member" method="get" action="'.url('ibuy/admin/report/order').'">'._NL;
	$navbar .= '<input type="hidden" name="id" id="id" />'._NL;
	$navbar .= '<div class="form-item">';
	$navbar .= '<select class="form-select" name="st"><option value="">** ทุกสถานะ **</option>';
	foreach ($statusList as $key => $value) $navbar.='<option value="'.$key.'"'.($status!='' && $key==$status?' selected="selected"':'').'>'.$value.'</option>';
	$navbar .= '</select>';
	$navbar .= '</div>'._NL;
	$navbar .= '<div class="form-item -fill"><input class="form-text -fill" type="text" name="q" size="20" value="'.$q.'" placeholder="ค้นเลขที่ใบสั่งซื้อหรือชื่อสมาชิก"></div>'._NL;
	$navbar .= '<div class="form-item"><button type="submit" class="btn -primary">แสดง</button></div>'._NL;
	$navbar .= '<div class="form-item -full">เรียงลำดับ <select class="form-select" name="o">';
	foreach ($orders as $key=>$item) $navbar.='<option value="'.$key.'" '.($key===$order?' selected="selected"':'').'>'.$item[0].'</option>';
	$navbar .= '</select> '._NL;
	$navbar .= '<input type="radio" name="s" value="1"'.($sort==1?' checked="checked"':'').' /> น้อยไปมาก <input type="radio" name="s" value="2"'.($sort!=1?' checked="checked"':'').' /> มากไปน้อย '._NL;
	$navbar .= '<select class="form-select" name="i"><option value="-1">** ทั้งหมด **</option>';
	for($i=100;$i<=1000;$i=$i+100) $navbar.='<option value="'.$i.'"'.($i==$itemPerPage?' selected="selected"':'').'>'.$i.' รายการ</option>';
	$navbar .= '</select>'._NL;
	$navbar .= '</div>'._NL;
	$navbar .= '</form>'._NL;
	$navbar .= '</nav>'._NL;

	$self->theme->navbar = $navbar;


	if ($_POST['addproduct']) $ret.=__ibuy_add_product($_POST);
	$where = array();
	if (post('uid')) $where=sg::add_condition($where,'o.`uid`=:uid','uid',post('uid'));
	if ($year) $where=sg::add_condition($where,'YEAR(o.`orderdate`)=:year','year',$year);
	if ($q) {
		$q=preg_replace('/\s+/', ' ', $q);
		if (is_numeric($q)) {
			$where=sg::add_condition($where,'o.`oid`=:q OR o.`orderno`=:q','q',$q);
		} else {
			$where=sg::add_condition($where,'(u.`username` LIKE :q OR u.`name` LIKE :q OR f.`custname` LIKE :q)','q','%'.$q.'%');
		}
	}

	if ($status!='') $where=sg::add_condition($where,'o.`status`=:status','status',$status);

	$page=post('page');
	if ($itemPerPage==-1) {
	} else {
		$firstRow=$page>1 ? ($page-1)*$itemPerPage : 0;
		$limit='LIMIT '.$firstRow.' , '.$itemPerPage;
	}

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		o.* , u.`username`, u.`name` , f.`custname`,f.`custtype`
		FROM %ibuy_order% o
			LEFT JOIN %ibuy_customer% f ON f.`uid`=o.`uid`
			LEFT JOIN %users% u ON u.`uid`=f.`uid`
		'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
		GROUP BY `oid`
		ORDER BY '.$orders[$order][1].($sort==1?'ASC':'DESC').'
		'.$limit;
	$dbs= mydb::select($stmt,$where['value']);
	//$ret.=mydb()->_query;

	$totals = $dbs->_found_rows;

	$pagePara['q']=post('q');
	$pagePara['st']=$status;
	$pagePara['o']=$order;
	$pagePara['s']=$sort;
	$pagePara['i']=$itemPerPage;
	$pagePara['page']=$page;
	$pagenv = new PageNavigator($itemPerPage,$page,$totals,q(),false,$pagePara);
	$no=$pagenv?$pagenv->FirstItem():0;

	$text[]='ใบสั่งสินค้า';
	if ($q) $text[]='ที่มีคำว่า "'.$q.'"';
	$text[]='('.($totals?'จำนวน '.$totals.' รายการ' : 'ไม่มีรายการ').')';
	if ($text) $self->theme->title=implode(' ',$text);

	if ($dbs->_empty) {
		$ret.=message('error','ไม่มีข้อมูลตามเงื่อนไขที่ระบุ');
	} else {
		$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;

		$tables = new Table();
		$tables->header[]='Order no';
		$tables->header['date']='Date';
		$tables->header[]='Franchise shop';
		$tables->header[]='T';
		$tables->header['money subtotal']='Subtotal';
		$tables->header['money discount']='Discount';
		$tables->header['money total']='Total';
		$tables->header['money balance']='ค้างชำระ';
		if ($isfranchiseInstall) {
			$tables->header['money money-market']='ค่าส่วนแบ่งการตลาด';
			$tables->header['money money-level']='ส่วนลดขั้นบันได';
		}
		$tables->header[]='Action';
		$tables->header['status']='Status';
		$tables->header[]='';
		foreach ($dbs->items as $rs) {
			$status=ibuy_define::status_text($rs->status);
			unset($row);
			$row[]=$rs->orderno;
			$row[]=date('d-m-Y H:i',$rs->orderdate);
			$row[]='<a href="'.url('ibuy/franchise/'.$rs->username,array('id'=>$rs->uid)).'" title="ดูรายละเอียด '.htmlspecialchars($rs->custname).'">'.SG\getFirst($rs->custname,'ไม่ระบุ').'</a><br />('.$rs->name.')';
			$row[]=strtoupper(substr($rs->custtype,0,1));
			$row[]=number_format($rs->subtotal,2);
			$row[]=number_format($rs->discount,2);
			$row[]=number_format($rs->total,2);
			$row[]=$rs->balance?number_format($rs->balance,2):'-';
			if ($isfranchiseInstall) {
				$row[]=number_format($rs->marketvalue,2);
				$row[]=number_format($rs->leveldiscount,2);
			}
			$row[]=$rs->emscode.($rs->emsdate?'<br />('.sg_date($rs->emsdate,'ว ดด ปป').')':'');
			$row[]=$status;
			$row[]='<a class="" href="'.url('ibuy/report/order/'.$rs->oid).'" title="ดูรายละเอียดใบสั่งสินค้าหมายเลข '.$rs->oid.'"><i class="icon -material">find_in_page</i></a>';
			$row['config']=array('class'=>'status-'.$rs->status);
			$tables->rows[]=$row;
			if ($rs->remark) $tables->rows[]='<tr><td colspan="2"></td><td colspan="7">หมายเหตุ : '.$rs->remark.'</td></tr>';
		}


		$ret .= $tables->build();

		if ($dbs->_num_rows) {
			$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
			$ret.='<p>รวมทั้งสิ้น <strong>'.$totals.'</strong> รายการ</p>';
		}
		$ret.='<style>
		.ibuy--add .form-text {width:100%; padding-left:0; padding-right:0;}
		.fileinput-button {margin:0;}
		.inline-edit-field {min-width:100%;}
		</style>';
	}

	return $ret;
}
?>