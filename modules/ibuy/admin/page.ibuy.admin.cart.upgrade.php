<?php
function ibuy_admin_cart_upgrade($self) {
	$self->theme->title='Current Cart';
	$self->theme->sidebar=R::Page('ibuy.admin.menu','member');

	$dbs=mydb::select('SELECT * FROM %session% WHERE `sess_data`!="" ORDER BY `sess_last_acc` DESC');

	$tables = new Table();
	$tables->thead=array('date last'=>'ล่าสุด','date'=>'วันที่','สมาชิก','amt'=>'รายการ','สินค้า','amt item'=>'จำนวน');
	foreach ($dbs->items as $rs) {
		$session=unserialize_session_data($rs->sess_data);
		$cart=$session['mycart'];
		if (empty($cart->items)) continue;
		$cartTr=mydb::select('SELECT t.`tpid`,t.`title`,(SELECT `name` FROM %users% WHERE `uid`=:uid) `ownerName` FROM %topic% t WHERE `tpid` IN (:trid)',':uid',$cart->uid,':trid','SET:'.implode(',',array_keys($cart->items)));
		//$ret.=print_o($cartTr,'$cartTr');
		foreach ($cartTr->items as $key=>$cartRs) {
			if (post('upgrade')) {
				$upgrade['tpid']=$cartRs->tpid;
				$upgrade['amt']=$cart->items[$cartRs->tpid];
				$upgrade['uid']=$cart->uid;
				$upgrade['date_added']=sg_date($cart->created,'Y-m-d H:i:s');
				$stmt='INSERT INTO %ibuy_cart% (`tpid`,`amt`,`uid`,`date_added`) VALUES (:tpid, :amt, :uid, :date_added)';
				mydb::query($stmt,$upgrade);
				$ret.=mydb()->_query.'<br />';
				//$ret.=print_o($upgrade,'$upgrade');
			}
			$tables->rows[]=array(
												$key==0?sg_date($rs->sess_last_acc,'d-m-ปปปป H:i'):'',
												$key==0?sg_date($cart->created,'d-m-ปปปป H:i'):'',
												$key==0?'<a class="sg-action" href="'.url('ibuy/admin/member',array('id'=>$cart->uid)).'" data-rel="box">'.$cartRs->ownerName.'</a>':'',
												$key==0?count($cart->items):'',
												'<a class="sg-action" href="'.url('paper/'.$cartRs->tpid).'" data-rel="box">'.$cartRs->title.'</a>',
												$cart->items[$cartRs->tpid],
												);
		}
		//$ret.=print_o($cart,'$cart');
		//$ret.=$rs->sess_data.'<br />'.print_o($rs,'$rs');
	}
	$ret .= $tables->build();
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}

function unserialize_session_data( $serialized_string ) 
{
    $variables = array();
    $a = preg_split( "/(\w+)\|/", $serialized_string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
	
    for( $i = 0; $i<count($a); $i = $i+2 )
	{
        if(isset($a[$i+1]))
		{
                $variables[$a[$i]] = unserialize( $a[$i+1] );
		}
    }
    return( $variables );
}
?>