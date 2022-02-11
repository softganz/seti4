<?php
function ibuy_admin_product($self) {
	$self->theme->title='สินค้า';
	$self->theme->sidebar=R::Page('ibuy.admin.menu','product');

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

	$navbar.='<nav class="nav -page"><header class="header -hidden"><h3>Project Management</h3></header>'._NL;
	$navbar.='<form id="search-member" method="get" action="'.url('ibuy/admin/product').'">'._NL;
	$navbar.='<input type="hidden" name="id" id="id" />'._NL;
	$navbar.='<ul class="ui-nav">'._NL;
	$navbar.='<li class="ui-item">เงื่อนไข ';
	$navbar.='<label></label><select class="form-select" name="st"><option value="">** ทุกสถานะ **</option>';
	foreach ($statusList as $key => $value) $navbar.='<option value="'.$key.'"'.($key==$status?' selected="selected"':'').'>'.$value.'</option>';
	$navbar.='</select>';
	$navbar.='</li>'._NL;
	$navbar.='<li class="ui-item"><input class="sg-autocomplete" data-query="'.url('ibuy/admin/product').'" data-callback="submit" data-altfld="id" type="text" name="q" id="search-box" size="30" value="'.$q.'" placeholder="ค้นชื่อสินค้า"></li>'._NL;
	$navbar.='<li class="ui-item"><button class="btn -primary" type="submit"><i class="icon -material">search</i></button></li>'._NL;
	//$navbar.='<li class="navbar--add"><a href="'.url('paper/post/ibuy').'" class="floating circle32" title="เพิ่มสินค้าใหม่">+</a></li>'._NL;
	$navbar.='</ul><br />'._NL;
	$navbar.='เรียงลำดับ <select class="form-select" name="o">';
	foreach ($orders as $key=>$item) $navbar.='<option value="'.$key.'" '.($key==$order?' selected="selected"':'').'>'.$item[0].'</option>';
	$navbar.='</select> '._NL;
	$navbar.='<input type="radio" name="s" value="1"'.($sort==1?' checked="checked"':'').' /> น้อยไปมาก <input type="radio" name="s" value="2"'.($sort!=1?' checked="checked"':'').' /> มากไปน้อย '._NL;
	$navbar.='<label>จำนวน</label><select class="form-select" name="i"><option value="-1">** ทั้งหมด **</option>';
	for($i=100;$i<=1000;$i=$i+100) $navbar.='<option value="'.$i.'"'.($i==$itemPerPage?' selected="selected"':'').'>'.$i.' รายการ</option>';
	$navbar.='</select>'._NL;
	$navbar.='</form>'._NL;
	$navbar.='</nav><!--navbar-->'._NL;

	$self->theme->navbar=$navbar;

	if ($_POST['title']) {
		$ret .= __ibuy_add_product($_POST);
		location('ibuy/admin/product');
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
				$tables->thead = array();
				$tables->thead['photo']='';
				$tables->thead['title'.($order=='title'?' order':'')]='ชื่อสินค้า / หมวดสินค้า';
				foreach (cfg('ibuy.price.use') as $key => $item) {
					$tables->thead['money -'.$key] = $item->label;
				}
				$tables->thead['status']='สถานะ';
				$tables->thead['showfor']='แสดงสินค้า';
				$tables->thead['rember']='เตือนความจำ';
				$tables->thead[($order=='date'?'orders':'')]='เมื่อ';

				$tablesColCount = count($tables->thead);

				$photoUrl = cfg('paper.upload.photo.url');

				// Add form
				$ret .= '<form class="ibuy--add" method="post" action="'.url('ibuy/admin/product').'" enctype="multipart/form-data">';

				$tree = model::get_taxonomy_tree(cfg('ibuy.vocab.category'));

				foreach ($tree as $term) {
					$cate.='<option value="'.$term->tid.'"'.($term->depth==0?' disabled="disabled"':'').'>'.str_repeat('--', $term->depth).$term->name.'&nbsp;&nbsp;'.'</option>';
				}

				$tables->rows[]=array(
													'', //'<span class="fileinput-button"><input type="file" name="photo" class="" />ไฟล์ภาพ</span>',
													'<td colspan="'.$tablesColCount.'"><input type="text" name="title" class="form-text" placeholder="ป้อนชื่อสินค้าที่ต้องการเพิ่ม" /></td>',
													);

				unset($row);
				$row = array(
								'',
								'<select name="category" class="form-select" style="width:100px;">'.$cate.'</select>'
							);
				foreach (cfg('ibuy.price.use') as $key => $item) {
					$row[] = '<input type="text" name="'.$key.'" class="form-text" placeholder="'.$item->label.'" />';
				}

				$row[] = '<td colspan="4"><button class="btn -primary" type="submit" name="addproduct"><i class="icon -material">add</i><span>เพิ่มสินค้า</span></td>';

				$tables->rows[] = $row;

				$isEdit=true;
				foreach ($dbs->items as $rs) {
					$tables->rows[]=array(
													'<td rowspan="2">'.($rs->photo ? '<a class="sg-action" href="'.$photoUrl.$rs->photo.'" data-rel="img"><img class="profile left" src="'.$photoUrl.$rs->photo.'" width="48" height="48" /></a>' : '').'<br />('.$rs->tpid.')</td>',
													'<td colspan="'.($tablesColCount-2).'">'.view::inlineedit(array('group'=>'topic','fld'=>'title','tpid'=>$rs->tpid),$rs->title,$isEdit).'</td>',
													'<a href="'.url('paper/'.$rs->tpid).'" title="Project Information" target="_blank"><i class="icon -view"></i></a>'
													);


					unset($row);
					$row = array($rs->categoryName);

					foreach (cfg('ibuy.price.use') as $key => $item) {
						$row[] = view::inlineedit(array('group'=>'product','fld'=>$key,'tpid'=>$rs->tpid,'ret'=>'money'),$rs->{$key},$isEdit);
					}

					$row[] = view::inlineedit(array('group'=>'product','fld'=>'outofsale','tpid'=>$rs->tpid),$statusList[$rs->outofsale],$isEdit,'select',$statusList);
					$row[] = view::inlineedit(array('group'=>'product','fld'=>'showfor','tpid'=>$rs->tpid),$showforList[$rs->showfor],$isEdit,'select',$showforList);
					$row[] = view::inlineedit(array('group'=>'product','fld'=>'remember','tpid'=>$rs->tpid),$rs->remember,$isEdit);
					$row[] = '<td style="white-space:nowrap">'.($rs->created?sg_date($rs->created,'d-m-ปปปป'):'').'</td>';
					$row['config'] = array('class'=>'ibuy-status-'.$rs->outofsale,'title'=>$rs->outofsale);

					$tables->rows[] = $row;
				}


				$inlinePara['data-update-url']=url('ibuy/admin/update');
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
				.ibuy-stock-list td:nth-child(1), .ibuy-stock-list td:last-child {background:transparent;}
				</style>';
				break;
		}
	}

	return $ret;
}

function __ibuy_add_product($post) {
	if ($post['title']) {
		$post['type']='ibuy';
		$post['uid']=i()->uid;
		$post['status']=_PUBLISH;

		$post['listprice'] = SG\getFirst($post['listprice']);
		$post['retailprice'] = SG\getFirst($post['retailprice']);
		$post['price1'] = SG\getFirst($post['price1']);
		$post['price2'] = SG\getFirst($post['price2']);
		$post['price3'] = SG\getFirst($post['price3']);
		$post['price4'] = SG\getFirst($post['price4']);
		$post['price5'] = SG\getFirst($post['price5']);
		$post['resalerprice'] = SG\getFirst($post['resalerprice']);

		$post['created']=date('Y-m-d H:i:s');
		$post['ip']=ip2long(GetEnv('REMOTE_ADDR'));

		mydb::query('INSERT INTO %topic% (`type`, `status`, `uid`, `title`, `created`, `ip`) VALUES (:type, :status, :uid, :title, :created, :ip)',$post);

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
	return $ret;
}
?>