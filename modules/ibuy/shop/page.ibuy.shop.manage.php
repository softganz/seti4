<?php
function ibuy_shop_manage($self, $shopId = NULL, $action = NULL) {
	$self->theme->title='จัดการหน้าร้าน';
	R::Page('ibuy.shop.toolbar',$self,$shopId);

	$tpid=post('tpid');
	$listType='grid'; // grid or view . Example from http://www.simon.com/mall/great-mall/stores/list

	if (!user_access('create own shop')) {
		return message('error','access denied:ขออภัยค่ะ ท่านยังไม่ได้รับสิทธิ์ในการเปิดหน้าร้าน กรุณาเข้าสู่ระบบสมาชิกหรือสมัครสมาชิกก่อนค่ะ');
	}

	if ($shopId) {
		$stmt='SELECT *
					FROM %org_officer% of
						LEFT JOIN %db_org% o USING(`orgid`)
					WHERE of.`orgid`=:orgid LIMIT 1';
		$rs=mydb::select($stmt,':orgid',$shopId);
	} else {
		$stmt='SELECT *
					FROM %org_officer% of
						LEFT JOIN %db_org% o USING(`orgid`)
					WHERE of.`uid`=:uid AND of.`membership`="ShopOwner" ';
		$dbs=mydb::select($stmt,':uid',i()->uid);
	}


	if ($dbs->_empty) {
		$ret.='<p class="notify">ท่านยังไม่เคยสร้างหน้าร้าน</p>';
		$ret.='<p>คลิก <a class="btn -primary" href="'.url('ibuy/shop/create').'">สร้างหน้าร้านใหม่</a> เพื่อสร้างหน้าร้านใหม่</p>';
		return $ret;
	}

	if ($shopId && ($rs->uid==i()->uid || user_access('administer ibuys'))) {
		$self->theme->sidebar=R::Page('ibuy.shop.menu',$rs);
		$self->theme->title=$rs->name;
		switch ($action) {
			case 'product':
				$ret.=__ibuy_shop_manage_product($self,$shopId);
				return $ret;
				break;

			case 'addmember':
				$ret.=__ibuy_shop_manage_addmember($shopid);
				return $ret;
				break;

			case 'editdetail':
				$ret.=__ibuy_shop_manage_editdetail($shopId,$tpid);
				return $ret;
				break;

			case 'addcategory':
				$ret.=__ibuy_shop_manage_addcategory($shopId,$tpid);
				return $ret;
				break;

			default:
				$ret.=__ibuy_shop_manage_info($shopId,$rs);
				break;
		}
	} else if (!$shopId && $dbs->_num_rows==1) {
		$rs=$dbs->items[0];
		$shopId=$rs->orgid;
		$self->theme->title=$rs->name;
		$self->theme->sidebar=R::Page('ibuy.shop.menu',$rs);
		R::Page('ibuy.shop.toolbar',$self,$shopId);
		$ret.=__ibuy_shop_manage_info($shopId,$rs);
	} else if (!$shopId && $dbs->_num_rows>1) {
		R::Page('ibuy.shop.toolbar',$self,$shopId);
		$ret.='<p>ท่านมีหน้าร้านจำนวน '.$dbs->_num_rows.' ร้าน กรุณาเลือกร้านที่ต้องการจัดการหน้าร้าน</p>';
		$ret.='<ul class="listing ibuy-shop-list -'.$listType.'">';
		foreach ($dbs->items as $rs) {
			$ret.='<li><div class="-header"><h3 class="-title"><a href="'.url('ibuy/shop/manage/'.$rs->orgid).'">'.$rs->name.'</a></h3><a href="'.url('ibuy/shop/manage/'.$rs->orgid).'"><img class="-logo" src="'.ibuy_model::shop_logo($rs->orgid).'" width="96" height="96" /></a></div><p class="-info">Address : <br />Phone : <br />Fax : </p></li>';
		}
		$ret.='</ul>';
	} else {
		$ret.=message('error','access denied');
	}
	//$ret.=print_o($rs,'$rs');
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}

/**
 * Shop information
 *
 * @param Int $shopId
 * @param $rs
 */
function __ibuy_shop_manage_info($shopId,$rs) {
	$ret.='<h3>ข้อมูลกลุ่ม/ร้านค้า</h3>';
	$tables = new Table();
	$tables->rows[]=array('ชื่อร้าน',$rs->name);
	$tables->rows[]=array('ที่อยู่',$rs->address);
	$tables->rows[]=array('โทรศัพท์',$rs->phone);
	$tables->rows[]=array('แฟกซ์',$rs->fax);
	$tables->rows[]=array('สินค้า',$rs->productCount.' รายการ');
	$ret.=$tables->build();

	$ret.='<h3>พนักงาน</h3>';
	$stmt='SELECT * FROM %org_officer% of LEFT JOIN %users% u USING(`uid`) WHERE `orgid`=:orgid';
	$dbs=mydb::select($stmt,':orgid',$shopId);
	$tables = new Table();
	$tables->thead=array('ชื่อ','ตำแหน่ง','<a class="sg-action" href="'.url('ibuy/shop/manage/'.$shopId.'/addmember').'" data-rel="box"><i class="icon -material">add_circle</i></a>');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->name,$rs->membership,'<a class="sg-action" href="'.url('ibuy/shop/manage/'.$shopId.'/removemember').'" data-rel="this" data-confirm="ต้องการลบสมาชิกนี้ออกจากการเป็นพนักงานร้าน กรุณายืนยัน?" data-removeparent="tr"><i class="icon -material">delete</i></a>');
	}
	$ret.=$tables->build();
	return $ret;
}

/**
 * Shop add member
 *
 * @param Int $shopId
 * @return String
 */
function __ibuy_shop_manage_addmember($shopId) {
	$ret.='<h3>เพิ่มสมาชิก</a>';
	return $ret;
}

/**
 * Shop product management
 *
 * @param Object $self
 * @param Int $shopId
 */
function __ibuy_shop_manage_product($self,$shopId) {
	$self->theme->title='สินค้า';

	$q=post('q');
	$id=SG\getFirst(post('id'),$id);
	$action=post('action');
	$order=SG\getFirst($para->order,post('o'),'tpid');
	$sort=SG\getFirst($para->sort,post('s'),2);
	$year=post('year');
	$status=post('st');
	$itemPerPage=SG\getFirst(post('i'),100);
	$type=post('t');
	$org=SG\getFirst(post('org'),'');

	$orders=array(
						'tpid'=>array('รหัสสินค้า','p.`tpid`'),
						'date'=>array('วันที่สร้าง','t.`created`'),
						'title'=>array('ชื่อสินค้า','CONVERT(t.`title` USING tis620)'),
						);
	$statusList=array('N'=>'ทำงาน', 'Y'=>'ไม่ทำงาน', 'O'=>'สินค้าหมด',);
	$showforList=array('PUBLIC'=>'ทุกคน', 'MEMBER'=>'สมาชิก');

	$navbar.='<header class="header -hidden"><h3>iBuy Management</h3></header>'._NL;
	$navbar.='<form id="search-member" method="get" action="'.url('ibuy/shop/manage/'.$shopId.'/product').'">'._NL;
	$navbar.='<input type="hidden" name="id" id="id" />'._NL;
	$navbar.='<ul>'._NL;
	$navbar.='<li>เงื่อนไข ';
	$navbar.='<label></label><select class="form-select" name="st"><option value="">** ทุกสถานะ **</option>';
	foreach ($statusList as $key => $value) $navbar.='<option value="'.$key.'"'.($key==$status?' selected="selected"':'').'>'.$value.'</option>';
	$navbar.='</select>';
	$navbar.='</li>'._NL;
	$navbar.='<li><span class="search-box"><input class="sg-autocomplete" data-query="'.url('ibuy/shop/manage/'.$shopId.'/product').'" data-callback="submit" data-altfld="id" type="text" name="q" id="search-box" size="30" value="'.$q.'" placeholder="ค้นชื่อสินค้า"></span></li>'._NL;
	$navbar.=' <input type="submit" class="button" value="แสดง" />'._NL;
	//$navbar.='<li class="navbar--add"><a href="'.url('paper/post/ibuy').'" class="floating circle32" title="เพิ่มสินค้าใหม่">+</a></li>'._NL;
	$navbar.='</ul>'._NL;
	$navbar.='<br />เรียงลำดับ <select class="form-select" name="o">';
	foreach ($orders as $key=>$item) $navbar.='<option value="'.$key.'" '.($key==$order?' selected="selected"':'').'>'.$item[0].'</option>';
	$navbar.='</select> '._NL;
	$navbar.='<input type="radio" name="s" value="1"'.($sort==1?' checked="checked"':'').' /> น้อยไปมาก <input type="radio" name="s" value="2"'.($sort!=1?' checked="checked"':'').' /> มากไปน้อย '._NL;
	$navbar.='<label>จำนวน</label><select class="form-select" name="i"><option value="-1">** ทั้งหมด **</option>';
	for($i=100;$i<=1000;$i=$i+100) $navbar.='<option value="'.$i.'"'.($i==$itemPerPage?' selected="selected"':'').'>'.$i.' รายการ</option>';
	$navbar.='</select>'._NL;
	$navbar.='</form>'._NL;

	$self->theme->navbar=$navbar;

	if ($_POST['addproduct']) {
		$ret.=__ibuy_shop_manage_add_product($shopId,$_POST);
		location('ibuy/shop/manage/'.$shopId.'/product');
	}
	if ($id) {
		$ret.=__project_admin_project_info($id);
	} else {
		switch (post('r')) {
			case 'delete':
				$ret.=__project_admin_delete();
				break;

			default:
				$where = array();
				$where=sg::add_condition($where,'t.`orgid`=:orgid','orgid',$shopId);
				if ($year) $where=sg::add_condition($where,'YEAR(t.`created`)=:year','year',$year);
				if ($type=='new') $where=sg::add_condition($where,'p.`isnew`=1');
				if ($q) {
					$q=preg_replace('/\s+/', ' ', $q);
					if (preg_match('/^code:(\w.*)/',$q,$out)) {
						$where=sg::add_condition($where,'t.`tpid`=:q','q',$out[1]);
					} else {
						$searchList=explode('+',$q);
						$qLists=array();
						foreach ($searchList as $key=>$str) {
							$str=trim($str);
							if ($str=='') continue;
							$qLists[]='(t.title RLIKE :q'.$key.' OR p.`forbrand` RLIKE :q'.$key.')';
							$where=sg::add_condition($where,'','q'.$key,str_replace(' ', '|', $str));
						}
						if ($qLists) $where=sg::add_condition($where,'('.(is_numeric($q)?'t.`tpid`=:q OR ':'').implode(' AND ', $qLists).')','q',$q);
					}
				}

				if ($status) $where=sg::add_condition($where,'p.outofsale=:outofsale','outofsale',$status);

				$page=post('page');
				if ($itemPerPage==-1) {
				} else {
					$firstRow=$page>1 ? ($page-1)*$itemPerPage : 0;
					$limit='LIMIT '.$firstRow.' , '.$itemPerPage;
				}

				$stmt = 'SELECT SQL_CALC_FOUND_ROWS
						t.`tpid`, t.`title`, t.`uid`, t.`created`
						, p.*
						, f.`file` `photo`
						, tg.`tid`, GROUP_CONCAT(DISTINCT cat.`name` SEPARATOR " , ") categoryName
					FROM %ibuy_product% AS p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %topic_files% f ON p.`tpid`=f.`tpid` AND f.`type`="photo"
						LEFT JOIN %tag_topic% tg ON tg.`tpid`=t.`tpid`
						LEFT JOIN %tag% cat USING(`tid`)
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					GROUP BY p.`tpid`
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

				$text[]='สินค้า';
				if ($q) $text[]='ที่มีคำว่า "'.$q.'"';
				$text[]='('.($totals?'จำนวน '.$totals.' รายการ' : 'ไม่มีรายการ').')';
				if ($text) $self->theme->title=implode(' ',$text);

				if ($dbs->_empty) {
					$ret.=message('error','ไม่มีรายชื่อสินค้าตามเงื่อนไขที่ระบุ');
				} else {
					$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
				}


				$tables = new Table();
				$tables->addClass('ibuy-stock-list sg-inline-edit');
				$tables->thead['photo']='';
				$tables->thead['title'.($order=='title'?' order':'')]='ชื่อสินค้า / หมวดสินค้า';
				$tables->thead['money p0']='หน้าร้าน';
				$tables->thead['money p1']='ทั่วไป';
				$tables->thead['money p2']='VIP';
				$tables->thead['money p3']='Gold';
				$tables->thead['money p4']='Platinum';
				$tables->thead['money p5']='Diamon';
				$tables->thead['money p6']='Resaler';
				$tables->thead['money p7']='Franchise';
				$tables->thead['money cost']='ราคาทุน';
				$tables->thead['status']='สถานะ';
				$tables->thead['showfor']='แสดงสินค้า';
				$tables->thead['rember']='เตือนความจำ';
				$tables->thead[($order=='date'?'orders':'')]='เมื่อ';

				$photoUrl=cfg('paper.upload.photo.url');

				// Add form
				$ret.='<form class="ibuy--add" method="post" action="'.url('ibuy/shop/manage/'.$shopId.'/product').'" enctype="multipart/form-data">';

				$tree = model::get_taxonomy_tree(cfg('ibuy.vocab.category'));
				foreach ($tree as $term) {
					$cate.='<option value="'.$term->tid.'"'.($term->depth==0?' disabled="disabled"':'').'>'.str_repeat('--', $term->depth).$term->name.'&nbsp;&nbsp;'.'</option>';
				}

				$tables->rows[]=array(
					'', //'<span class="fileinput-button"><input type="file" name="photo" class="" />ไฟล์ภาพ</span>',
					'<td colspan="15"><input type="text" name="title" class="form-text" placeholder="ป้อนชื่อสินค้าที่ต้องการเพิ่ม" /></td>',
				);
				$tables->rows[]=array(
					'',
					'<select name="category" class="form-select" style="width:100px;">'.$cate.'</select>',
					'<input type="text" name="listprice" class="form-text -money" placeholder="ราคาหน้าร้าน" />',
					'<input type="text" name="price1" class="form-text -money" placeholder="ราคาทั่วไป" />',
					'<input type="text" name="price2" class="form-text -money" placeholder="ราคาVIP" />',
					'<input type="text" name="price3" class="form-text -money" placeholder="ราคาGOLD" />',
					'<input type="text" name="price4" class="form-text -money" placeholder="ราคาPlatinum" />',
					'<input type="text" name="price5" class="form-text -money" placeholder="ราคาDiamon" />',
					'<input type="text" name="resalerprice" class="form-text -money" placeholder="ราคาResaler" />',
					'<input type="text" name="retailprice" class="form-text -money" placeholder="ราคาFranchise" />',
					'<input type="text" name="cost" class="form-text -money" placeholder="ราคาทุน" />',
					'<td colspan="4"><button type="submit" name="addproduct" class="btn -primary" value="เพิ่มสินค้า"><i class="icon -material">add</i><span>เพิ่มสินค้า</span></button></td>',
				);

				$isEdit=true;
				foreach ($dbs->items as $rs) {
					$tables->rows[]=array(
						'<td rowspan="2">'.($rs->photo ? '<a class="sg-action" href="'.$photoUrl.$rs->photo.'" data-rel="img"><img class="profile left" src="'.$photoUrl.$rs->photo.'" width="48" height="48" /></a>' : '').'<a class="sg-action" href="'.url('paper/info/api/'.$rs->tpid.'/photo.upload',array('target'=>'box')).'" data-rel="box" title="เพิ่มภาพสินค้า"><i class="icon -material">add_photo_alternate</i></a>'.'<br />('.$rs->tpid.')</td>',
						'<td colspan="13">'.view::inlineedit(array('group'=>'topic','fld'=>'title','tpid'=>$rs->tpid),$rs->title,$isEdit).'</td>',
						'<td style="white-space:nowrap"><a class="" href="'.url('paper/'.$rs->tpid).'" title="รายละเอียดสินค้า" target="_blank"><i class="icon -material">find_in_page</i></a> <a class="sg-action icon -edit" href="'.url('ibuy/shop/manage/'.$shopId.'/editdetail',array('tpid'=>$rs->tpid)).'" data-rel="box" title="แก้ไขรายละเอียดสินค้า" target="_blank"><i class="icon -material">edit</i></a></td>'
					);
					$tables->rows[]=array(
						$rs->categoryName.'<br /><a class="sg-action" href="'.url('ibuy/shop/manage/'.$shopId.'/addcategory',array('tpid'=>$rs->tpid)).'" data-rel="box" title="เพิ่มหมวดสินค้า"><i class="icon -material">add_circle_outline</i></a>',
						view::inlineedit(array('group'=>'product','fld'=>'listprice','tpid'=>$rs->tpid,'ret'=>'money'),$rs->listprice,$isEdit),
						view::inlineedit(array('group'=>'product','fld'=>'price1','tpid'=>$rs->tpid,'ret'=>'money'),$rs->price1,$isEdit),
						view::inlineedit(array('group'=>'product','fld'=>'price2','tpid'=>$rs->tpid,'ret'=>'money'),$rs->price2,$isEdit),
						view::inlineedit(array('group'=>'product','fld'=>'price3','tpid'=>$rs->tpid,'ret'=>'money'),$rs->price3,$isEdit),
						view::inlineedit(array('group'=>'product','fld'=>'price4','tpid'=>$rs->tpid,'ret'=>'money'),$rs->price4,$isEdit),
						view::inlineedit(array('group'=>'product','fld'=>'price5','tpid'=>$rs->tpid,'ret'=>'money'),$rs->price5,$isEdit),
						view::inlineedit(array('group'=>'product','fld'=>'resalerprice','tpid'=>$rs->tpid,'ret'=>'money'),$rs->resalerprice,$isEdit),
						view::inlineedit(array('group'=>'product','fld'=>'retailprice','tpid'=>$rs->tpid,'ret'=>'money'),$rs->retailprice,$isEdit),
						view::inlineedit(array('group'=>'product','fld'=>'cost','tpid'=>$rs->tpid,'ret'=>'money'),$rs->cost,$isEdit),
						view::inlineedit(array('group'=>'product','fld'=>'outofsale','tpid'=>$rs->tpid),$statusList[$rs->outofsale],$isEdit,'select',$statusList),
						view::inlineedit(array('group'=>'product','fld'=>'showfor','tpid'=>$rs->tpid),$showforList[$rs->showfor],$isEdit,'select',$showforList),
						view::inlineedit(array('group'=>'product','fld'=>'remember','tpid'=>$rs->tpid),$rs->remember,$isEdit),
						'<td style="white-space:nowrap">'.($rs->created?sg_date($rs->created,'d-m-ปปปป'):'').'</td>',
						'config'=>array('class'=>'ibuy-status-'.$rs->outofsale,'title'=>$rs->outofsale)
					);
				}


				$inlinePara['data-update-url']=url('ibuy/shop/update');
				if (post('debug')) $inlinePara['data-debug']='yes';
				$tables->attr=$inlinePara;

				$ret .= $tables->build();

				$ret.='</form>';
				if ($dbs->_num_rows) {
					$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
					$ret.='<p>รวมทั้งสิ้น <strong>'.$totals.'</strong> รายการ</p>';
				}
				$ret.='<style>
				.ibuy--add .form-text {width:95%; padding-left:0; padding-right:0;}
				.fileinput-button {margin:0;}
				.inline-edit-field {min-width:95%;}
				.ibuy-stock-list th {white-space:nowrap;padding:4px 0;}
				.ibuy-stock-list td:nth-child(2n+1) {background:#f5f5f5;}
				.ibuy-stock-list td:nth-child(1) {background:transparent;}
				</style>';
				break;
		}
	}

	return $ret;
}

function __ibuy_shop_manage_add_product($shopId,$post) {
	if ($post['title']) {
		$post['orgid']=$shopId;
		$post['type']='ibuy';
		$post['uid']=i()->uid;
		$post['status']=_PUBLISH;
		$post['created']=date('Y-m-d H:i:s');
		$post['ip']=ip2long(GetEnv('REMOTE_ADDR'));
		mydb::query('INSERT INTO %topic% (`orgid`, `type`, `status`, `uid`, `title`, `created`, `ip`) VALUES (:orgid, :type, :status, :uid, :title, :created, :ip)',$post);

		if (!mydb()->_error) {
			$post['tpid']=mydb()->insert_id;
			$post['timestamp']=date('Y-m-d H:i:s');
			mydb::query('INSERT INTO %topic_revisions% (`tpid`, `uid`, `title`, `timestamp`) VALUES (:tpid, :uid, :title, :timestamp)',$post);
			$post['revid']=mydb()->insert_id;
			mydb::query('UPDATE %topic% SET `revid`=:revid WHERE `tpid`=:tpid LIMIT 1',$post);

			$post['available']=1;
			$post['outofsale']='N';
			$post['isnew']=0;
			mydb::query('INSERT INTO %ibuy_product% (`tpid`, `available`, `listprice`, `retailprice`, `price1`, `price2`, `price3`, `price4`, `price5`, `resalerprice`, `cost`, `outofsale`, `isnew`) VALUES (:tpid, :available, :listprice, :retailprice, :price1, :price2, :price3, :price4, :price5, :resalerprice, :cost, :outofsale, :isnew)',$post);

			$post['vid']=cfg('ibuy.vocab.category');
			mydb::query('INSERT INTO %tag_topic% (`tpid`, `vid`, `tid`) VALUES (:tpid, :vid, :category)',$post);
		}
	}
	return $ret;
}

function __ibuy_shop_manage_editdetail($shopId,$tpid) {
	$ret.='<h3>แก้ไขรายละเอียดสินค้า</h3>';
	$topic=ibuy_model::get_product($tpid);

	if ($_POST['topic']) {
		$post=(object)post('topic');
		$stmt='UPDATE %topic_revisions% SET `body`=:body WHERE `revid`=:revid LIMIT 1';
		mydb::query($stmt,$post,':revid',$topic->revid);
		$stmt='UPDATE %ibuy_product% SET `forbrand`=:forbrand, `isnew`=:isnew WHERE `tpid`=:tpid LIMIT 1';
		mydb::query($stmt,$post);
	} else if ($_POST['cancel']) location('ibuy/'.$topic->tpid);


	$form = new Form([
		'variable' => 'topic',
		'action' => url('ibuy/shop/manage/'.$shopId.'/editdetail',array('tpid'=>$tpid)),
		'class' => 'sg-form',
		'rel' => _AJAX ? 'this' : NULL,
		'done' => 'close',
		'children' => [
			'tpid' => ['type' => 'hidden', 'value' => $tpid],
			'body' => [
				'type' => 'textarea',
				'label' => 'รายละเอียดสินค้า',
				'rows' => 10,
				'value' => $topic->body,
			],
			'forbrand' => [
				'type' => 'textarea',
				'label' => 'สำหรับรุ่น',
				'rows' => 3,
				'value' => $topic->forbrand,
			],
			'balance' => cfg('ibuy.stock.use') ? [
				'type' => 'text',
				'label' => 'ยอดคงเหลือ (ชิ้น)',
				'maxlength' => 7,
				'value' => htmlspecialchars($topic->balance),
			] : NULL,
			'isdiscount' => cfg('ibuy.resaler.discount')>0 ? [
				'type' => 'radio',
				'label' => 'การคำนวณส่วนลด',
				'options' => [0 => 'ไม่ ไม่นำมาคำนวณส่วนลดเมื่อมีการสั่งซื้อสินค้า', 1 => 'ไช่ นำมาคำนวณส่วนลดเมื่อมีการสั่งซื้อสินค้า'],
				'value' => $topic->isdiscount,
			] : NULL,
			'ismarket' => cfg('ibuy.franchise.marketvalue')>0 ? [
				'type' => 'radio',
				'label' => 'คำนวณค่าการตลาด',
				'options' => [0 => 'ไม่ ไม่นำมาคำนวณค่าการตลาดเมื่อมีการสั่งซื้อสินค้า', 1 => 'ไช่ นำมาคำนวณค่าการตลาดเมื่อมีการสั่งซื้อสินค้า'],
				'value' => $topic->ismarket,
			] : NULL,
			'isfranchisor' => cfg('ibuy.franchise.franchisor')>0 ? [
				'type' => 'radio',
				'label' => 'คำนวณยอดสำหรับเจ้าของเฟรนส์ไชน์',
				'options' => [0 => 'ไม่ ไม่นำมาคำนวณยอดสำหรับเจ้าของเฟรนส์ไชน์เมื่อมีการสั่งซื้อสินค้า', 1 => 'ไช่ นำมาคำนวณยอดสำหรับเจ้าของเฟรนส์ไชน์เมื่อมีการสั่งซื้อสินค้า'],
				'value' => $topic->isfranchisor,
			] : NULL,
			'isnew' => [
				'type' => 'radio',
				'label' => 'แสดงในรายการสินค้ามาใหม่',
				'options' => [1 => 'แสดง', 0 => 'ไม่แสดง'],
				'display' => 'inline',
				'value' => $topic->isnew,
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'container' => '{class: "-sg-text-right"}',
			],
		],
	]);

	$ret .= $form->build();
	//$ret.=print_o($_POST,'$_POST');
	//$ret.=print_o($topic,'$topic');
	return $ret;
}

function __ibuy_shop_manage_addcategory($shopId,$tpid) {
	$vid=cfg('ibuy.vocab.category');
	$tree = model::get_taxonomy_tree($vid);

	if (post('remove')) {
		$stmt='DELETE FROM %tag_topic% WHERE `tpid`=:tpid AND `vid`=:vid AND `tid`=:tid LIMIT 1';
		mydb::query($stmt,':tpid',$tpid, ':vid',$vid, ':tid',post('remove'));
		//$r=mydb()->_query;
	}
	if (post('category')) {
		$stmt='INSERT INTO %tag_topic% (`tpid`, `vid`, `tid`) VALUES (:tpid, :vid, :tid) ON DUPLICATE KEY UPDATE `tid`=:tid';
		mydb::query($stmt,':tpid',$tpid, ':vid',$vid, ':tid',post('category'));
		//$r=mydb()->_query;
	}
	$stmt='SELECT * FROM %tag_topic% t LEFT JOIN %tag% tg USING(`tid`) WHERE `tpid`=:tpid';
	$dbs=mydb::select($stmt,':tpid',$tpid);
	$tables = new Table();
	$tables->thead=array('หมวดสินค้า','');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->name,'<a class="sg-action icon -delete" href="'.url('ibuy/shop/manage/'.$shopId.'/addcategory',array('tpid'=>$tpid,'remove'=>$rs->tid)).'" data-rel="this" data-removeparent="tr">ลบ</a>');
	}
	$ret.='<div id="m1">';
	$ret.='<div class="sidebar">';
	$ret.=$tables->build();
	$ret.='</div>';
	$ret.='<div id="main" class="main--withsidebar">';
	$ret.='<form method="post" action="'.url('ibuy/shop/manage/'.$shopId.'/addcategory',array('tpid'=>$tpid)).'" class="sg-form" data-rel="#m1">';
	$cate='<option value="">===เลือกหมวดสินค้า===</option>';
	foreach ($tree as $term) {
		$cate.='<option value="'.$term->tid.'"'.($term->depth==0?' disabled="disabled"':'').'>'.str_repeat('--', $term->depth).$term->name.'&nbsp;&nbsp;'.'</option>';
	}
	$ret.='<select name="category" class="form-select" style="width:100%;">'.$cate.'</select>';
	$ret.='<br /><br /><input type="submit" class="button" value="เพิ่มหมวด" />';
	$ret.='</form>';
	$ret.='</div>';
	//$ret.=$r;
	//$ret.=print_o($_POST,'$_POST');
	$ret.='</div>';
	return $ret;
}
?>