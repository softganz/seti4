<?php
function ibuy_admin_cart($self) {
	$self->theme->title='Current Cart';
	$self->theme->sidebar=R::Page('ibuy.admin.menu','member');

	$dbs=mydb::select('SELECT DISTINCT `uid` FROM %ibuy_cart% ORDER BY `crtid` DESC');

	$tables = new Table();
	$tables->thead=array('สมาชิก','amt'=>'รายการ','date'=>'วันที่','สินค้า','amt item'=>'จำนวน');
	foreach ($dbs->items as $rs) {
		$cartTr=mydb::select('SELECT t.`tpid`,t.`title`, u.`name` `ownerName`, c.* FROM %ibuy_cart% c LEFT JOIN %topic% t USING(`tpid`) LEFT JOIN %users% u ON u.`uid`=c.`uid` WHERE c.`uid`=:uid ORDER BY `crtid` DESC',':uid',$rs->uid);
		//$ret.=print_o($cartTr,'$cartTr');
		foreach ($cartTr->items as $key=>$cartRs) {
			$tables->rows[]=array(
												$key==0?'<a class="sg-action" href="'.url('ibuy/admin/member',array('id'=>$cartRs->uid)).'" data-rel="box">'.$cartRs->ownerName.'</a>':'',
												$key==0?$cartTr->_num_rows:'',
												sg_date($cartRs->date_added,'d-m-ปปปป H:i:s'),
												'<a class="sg-action" href="'.url('paper/'.$cartRs->tpid).'" data-rel="box">'.$cartRs->title.'</a>',
												number_format($cartRs->amt),
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