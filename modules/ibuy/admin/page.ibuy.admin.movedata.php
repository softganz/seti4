<?php
/**
 * Print order form
 *
 * @return String
 */
function ibuy_admin_movedata($self) {
	$self->theme->title='ย้ายข้อมูล';
	$self->theme->sidebar=R::Page('ibuy.admin.menu');

	$month=post('mo');
	$targetDb=cfg('ibuy.backup.db');

	if (!$targetDb) return message('error','ยังไม่ได้กำหนดฐานข้อมูลปลายทาง');

	$tableNotCopy=array('sgz_counter_log','sgz_watchdog');
	// Start move data
	if ($month) {
		$error=false;
		// Create destination table
		$dbTables=mydb::table_list();
		foreach ($dbTables as $dbTable) {
			$stmt='CREATE TABLE IF NOT EXISTS `'.$targetDb.'`.`'.$dbTable.'` LIKE `'.$dbTable.'`';
			mydb::query($stmt);
			//$ret.=mydb()->_query.'<br />';
			if (mydb()->_error) {
				$error=mydb()->_query;
				break;
			}
		}
		// Start copy all data
		if (!$error) {
			foreach ($dbTables as $dbTable) {
				if (in_array($dbTable,$tableNotCopy)) continue;
				$stmt='REPLACE INTO '.$targetDb.'.`'.$dbTable.'` SELECT * FROM `'.$dbTable.'`';
				mydb::query($stmt);
				$ret.=mydb()->_query.'<br />';
				if (mydb()->_error) {
					$error=mydb()->_query;
					break;
				}
			}
		}
		// Start delete data
		if (!$error) {
			$lastId=10;
			$lastDate=sg_date($month.'-01','Y-m-t');
			$ret.='Last Date ='.$lastDate.'<br />';
			$lastId=mydb::select('SELECT MAX(`oid`) `lastId` FROM %ibuy_order% WHERE FROM_UNIXTIME(`orderdate`,"%Y-%m-%d")<=:lastDate LIMIT 1',':lastDate',$lastDate)->lastId;
			$ret.='Last ID='.$lastId.'<br />';

			if ($lastId) {
				$ret.='<h3>ลบข้อมูล oid <= '.$lastId.'</h3><ul>';
				$stmt='DELETE FROM %ibuy_log% WHERE `kid`=0 OR `kid` IN (SELECT `oid` FROM %ibuy_order% WHERE `oid`<=:lastId AND `status` IN (50,-1))';
				mydb::query($stmt,':lastId',$lastId);
				$ret.='<li>'.mydb()->_query.'</li>';

				$stmt='DELETE FROM %ibuy_ordertr% WHERE `oid` IN (SELECT `oid` FROM %ibuy_order% WHERE `oid`<=:lastId AND `status` IN (50,-1))';
				mydb::query($stmt,':lastId',$lastId);
				$ret.='<li>'.mydb()->_query.'</li>';

				$stmt='DELETE FROM %ibuy_order% WHERE `oid` <=:lastId AND `status` IN (50,-1)';
				mydb::query($stmt,':lastId',$lastId);
				$ret.='<li>'.mydb()->_query.'</li>';
				$ret.='</ul>';
			}

			//DELETE FROM sgz_ibuy_log WHERE `kid`<=72432;
			//DELETE FROM sgz_ibuy_ordertr WHERE `oid`<=72432;
			//DELETE FROM sgz_ibuy_order WHERE `oid`<=72432;
		}
		//$ret.=print_o($dbTables,'$dbTables');
	}

	$months=mydb::select('SELECT DISTINCT FROM_UNIXTIME(`orderdate`,"%Y-%m") `orderMonth` FROM %ibuy_order% ORDER BY `orderMonth` ASC');
	//$ret.=mydb()->_query;
	//$ret.=print_o($months,'$months');

	$navbar.='<header class="header -hidden"><h3>Member Management</h3></header>'._NL;
	$navbar.='<form method="get" action="'.url('ibuy/admin/movedata').'">'._NL;
	$navbar.='<ul>'._NL;
	$navbar.='<li>ย้ายข้อมูลจนถึงเดือน ';
	$navbar.='<label></label><select class="form-select" name="mo"><option value="">**เลือกเดือน**</option>';
	foreach ($months->items as $item) $navbar.='<option value="'.$item->orderMonth.'"'.($item->orderMonth==$month?' selected="selected"':'').'>'.sg_date($item->orderMonth.'-01','ดดด ปปปป').'</option>';
	$navbar.='</select>';
	$navbar.='</li>'._NL;
	$navbar.='<li> <input type="submit" class="button floating" value="ดำเนินการย้ายข้อมูล" /></li>'._NL;
	$navbar.='</form>'._NL;

	$self->theme->navbar=$navbar;

	if ($error) $ret.='เกิดข้อผิดพลาดระหว่างย้ายข้อมูล : กรุณาติดต่อผู้ดูแลระบบ<br />'.$error;

	//$ret.=print_o(post(),'post()');
	return $ret;
}
?>