<?php
function ibuy_green_supplier($self,$actid=NULL,$action=NULL,$trid=NULL) {
	R::View('org.toolbar',$self,'เครือข่ายผู้ผลิต','supplier');

	if (!is_numeric($actid)) {$action=$actid;unset($actid);}

	if ($actid) $supplierInfo = R::Model('org.supplier.get',$actid);

	$isAdmin=user_access('administrator orgs');

	switch ($action) {
		case 'value':
			# code...
			break;
		
		default:
			if ($actid) {
				$ret.=R::View('ibuy.green.supplier.view',$supplierInfo);
			} else {
				$stmt='SELECT
							q.`qtref`
							, q.`orgid`
							, q.`uid`
							, IFNULL(o.`name`,qtr.`value`) `name`
							FROM %qtmast% q
								LEFT JOIN %db_org% o USING(`orgid`)
								LEFT JOIN %qttran% qtr ON qtr.`qtref`=q.`qtref` AND qtr.`part`="ORG.NAME"
							WHERE `qtgroup`=:qtgroup
							-- AND `orgid` IS NOT NULL
							';
				$dbs=mydb::select($stmt,':qtgroup',_QTGROUP_GOGREEN);
				$tables = new Table();
				$tables->thead=array('ชื่อเครือข่าย','icons -c1'=>'');
				foreach ($dbs->items as $rs) {
					$memu='';
					if ($isAdmin || (i()->ok && $rs->uid==i()->uid)) $menu='<a href="'.url('ibuy/green/supplier/form/'.$rs->qtref).'"><i class="icon -edit"></i></a>';
					$tables->rows[]=array(
														$rs->orgid?'<a href="'.url('ibuy/green/supplier/'.$rs->orgid).'">'.$rs->name.'</a>':$rs->name.' (ยังไม่อนุมัติ)',
														$menu,
														);
				}
				$ret.=$tables->build();
				//$ret.='<a href="https://communeinfo.com/paper/320"><img src="https://communeinfo.com/upload/pics/greenzone-01.jpg" width="100%" /></a>';
			}
			break;
	}
	return $ret;
}
?>