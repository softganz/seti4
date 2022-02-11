<?php
/**
 * ibuy_model class for ibuy model
 *
 * @package ibuy
 * @subpackage ibuy_manage
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2009-09-09
 * @modify 2009-12-09
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

class ibuy_define {
	public function order_type($tid) {
	static $items=array(__IBUY_TYPE_ORDER=>'สั่งสินค้า',__IBUY_TYPE_FRANCHISE=>'สมัครเฟรนไชส์');
		return $items[$tid];
	}

	public function status_text($sid = NULL) {
	static $items=array(-1=>'ยกเลิก',0=>'คอยยืนยัน',10=>'รับทราบการสั่งสินค้า',20=>'ยืนยันการโอนเงิน',30=>'เตรียมสินค้า',40=>'ส่งสินค้าแล้ว',50=>'รับสินค้าแล้ว');
		return isset($sid)?$items[$sid]:$items;
	}

	public function custtype($type) {
		if ($type=='franchise') $ret='เฟรนไชส์';
		else if ($type=='resaler') $ret='ตัวแทนจำหน่าย';
		else if ($type=='franchiser') $ret='เจ้าของเฟรนไชส์';
		else if ($type=='wtlshop') $ret='WTL Shop';
		else $ret='';
		return $ret;
	}

} // end of class ibuy_define

class ibuy_model {

	/**
	 * Process & status log
	 *
	 * @param String $keyword
	 * @param Integer $kid
	 * @param BigInt $date
	 * @param Integer $status
	 * @param String $detail
	 */
	public function log() {
		$para=para(func_get_args(),'keyword=','kid=0','status=-1','created='.date('U'),'detail=','process=-1','amt=func.NULL');
		$para->uid=i()->uid;
		unset($para->_src);
		$stmt='INSERT INTO %ibuy_log% (`uid`,`keyword`,`kid`,`status`,`amt`,`process`,`created`,`detail`) VALUES (:uid,:keyword,:kid,:status,:amt,:process,:created,:detail)';
		mydb::query($stmt,$para);
	}

	/**
	 * Get product information
	 *
	 * @param Integer $prid
	 * @return Record Set
	 */
	function get_product($prid) {
		$stmt='SELECT t.`tpid`, t.`revid`, t.`title`, p.*, r.`body`
						FROM %topic% t
							LEFT JOIN %ibuy_product% p USING(`tpid`)
							LEFT JOIN %topic_revisions% r USING(`revid`)
						WHERE t.`type`="ibuy" AND t.`tpid`=:tpid LIMIT 1';
		$rs=mydb::select($stmt,':tpid',$prid);
		return $rs;
	}

	/**
	 * Get franchise information
	 *
	 * @param Integer $fid
	 * @return Object
	 */
	public function get_franchise($fid) {
		$stmt='SELECT f.*, u.`username`, u.`name`, u.`email`, u.`roles`, p.`name` province
					FROM %ibuy_customer% f
						LEFT JOIN %users% u ON u.`uid`=f.`uid`
						LEFT JOIN %province% p ON p.`pid`=f.`pid`
					WHERE f.`uid`=:fid LIMIT 1';
		$rs=mydb::select($stmt,':fid',$fid);
		return $rs;
	}


	/**
	 * Get order information
	 *
	 * @param Integer $oid
	 * @return Object
	 */
	function get_order($oid) {
		$stmt='SELECT o.* , f.custname , u.name , f.custaddress , f.custzip , f.custphone , f.custattn
					FROM %ibuy_order% o
						LEFT JOIN %users% u ON u.uid=o.uid
						LEFT JOIN %ibuy_customer% f ON f.uid=o.uid
					WHERE oid=:oid ORDER BY oid DESC LIMIT 1';
		$order=mydb::select($stmt,':oid',$oid);
		return $order;
	}


	/**
	 * Clear shoping cart
	 */
	function empty_cart($uid) {
		if (empty($uid)) return;
		mydb::query('DELETE FROM %ibuy_cart% WHERE `uid`=:uid',':uid',$uid);
	}

	function calculate_order($oid) {
		$order=mydb::select('SELECT * FROM %ibuy_order% WHERE `oid`=:oid LIMIT 1',':oid',$oid);

		$stmt='SELECT SUM(`subtotal`) sumSubtotal, SUM(`discount`) sumDiscount, SUM(`total`) sumTotal, SUM(`leveldiscount`) sumLevelDiscount, SUM(`marketvalue`) sumMarketvalue, SUM(IF(p.`isfranchisor`,`subtotal`,0)) sumFranchisorvalue
					FROM %ibuy_ordertr% tr
						LEFT JOIN %ibuy_product% p USING(`tpid`)
					WHERE `oid`=:oid LIMIT 1';
		$trDbs=mydb::select($stmt,':oid',$oid);

		$order->subtotal=$trDbs->sumSubtotal;
		$order->discount=$trDbs->sumDiscount;
		$order->total=$trDbs->sumTotal+$order->shipping;
		$order->leveldiscount=$trDbs->sumLevelDiscount;
		$order->marketvalue=$trDbs->sumMarketvalue;
		$order->franchisorvalue=$trDbs->sumFranchisorvalue;

		//$order->tr=$trDbs;

		foreach ($order as $key=>$value) if (substr($key,0,1)=='_') unset($order->{$key});

		/*
		// Calculate discount
		$order->discount=0;
		if ($cartinfo->discount_summary>0 && $_POST['discount']=='yes') {
		//$order->discount=$cartinfo->discount_summary<$order->subtotal?$cartinfo->discount_summary:$order->subtotal;
			$order->discount=$cartinfo->discount_summary<$cartinfo->discount_yes?$cartinfo->discount_summary:$cartinfo->discount_yes;
			if (!$simulate) mydb::query('UPDATE %ibuy_customer% SET `discount`=`discount`-:discount WHERE `uid`=:uid LIMIT 1',':discount',$order->discount,':uid',$order->uid);
		}
		$order->total=$order->balance=$cartinfo->total-$order->discount+$cartinfo->shipping;
		$order->leveldiscount=$cartinfo->leveldiscount-$order->discount;
		$order->marketvalue=$cartinfo->marketvalue-$order->discount;
		$order->franchisorvalue=$cartinfo->franchisorvalue;

		$order->shipcode=post('shipcode');
		$order->shipto=post('shipto');
		if (empty($order->shipto) && $order->shipcode==14) $order->shipto='EMS ด่วนพิเศษ';
		else if (empty($order->shipto) && $order->shipcode==13) $order->shipto='ไปรษณีย์ลงทะเบียน';

		foreach ($cartinfo->items as $rs) {
			$ordertr->tpid=is_numeric($rs->tpid)?$rs->tpid:0;
			$ordertr->amt=$rs->amt;
			$ordertr->price=$rs->price;
			$ordertr->subtotal=$rs->subtotal;
			$ordertr->discount=$rs->discount;
			$ordertr->total=$rs->total;
			$ordertr->leveldiscount=$rs->leveldiscount;
			$ordertr->marketvalue=$rs->marketvalue;
		}
		*/

		return $order;
	}

	/**
	 * Listing product
	 *
	 * @param $dbs
	 * @return String
	 */
	function product_listing($dbs, $style = 'full', $page_nv = NULL) {
		$brandname = '';

		$ui = new Ui(NULL, 'ibuy-product-list ibuy-product-list-'.$style);

		if ($page_nv) $ui->add($page_nv, '{class: "-page-nv"}');

		foreach ($dbs->items as $rs) {
			$cardStr = '';
			$containerClass = '';
			$url = '<a href="'.url('ibuy/'.$rs->tpid).'" title="'.htmlspecialchars($rs->title).'">';

			if ($style == 'short') {
				$cardStr = $url.$rs->title.'</a><span>'.substr($rs->body,0,200).'</span>';
			} else {
				if ($rs->brandname!=$brandname) {
					$brandname=$rs->brandname;
					//$ret.='<li class="brand"><h3 class="'.$brandname.'">'.$brandname.'</h3></li>'._NL;
				}
				if ($rs->brandname) {
					$containerClass .= 'brand-'.$rs->brandname;
				}

				$cardStr .= '<h3>'.$url.$rs->title.'</a></h3>'._NL;

				$photo = $rs->photo ? model::get_photo_property($rs->photo) : null;
				$cardStr .= '<div class="photo-th'.($photo->_url ? '' : ' -no-photo').'">';
				if ($photo->_url) {
					$cardStr .= $url.'<img class="photo" src="'.$photo->_url.'" height="140" /></a>';
				}
				/*
				$cardStr .= '<div class="photo">'.$url;
				if ($rs->photo) {
					$photo = model::get_photo_property($rs->photo);
					$cardStr .= '<img src="'.$photo->_url.'" alt="'.htmlspecialchars($rs->title).'" />';
				}
				$cardStr .= '</a></div>';
				*/
				$cardStr .= '</div>'._NL;

				$cardStr .= '<div class="productcode">รหัสสินค้า : '.$rs->tpid.'</div>';
				// Create product price and sale label
				$cardStr .= R::View('ibuy.price.label',$rs)._NL;
				$cardStr .= R::View('ibuy.sale.label', $rs, NULL, true)._NL;

				$cardStr .= '<div class="summary"><p>'.$rs->title.'</p><p><a class="btn -link" href="'.url('ibuy/'.$rs->tpid).'">'.tr('Details').'</a></p></div>'._NL;
			}

			$ui->add($cardStr);

		}

		if ($page_nv) $ui->add($page_nv, '{class: "-page-nv"}');

		$ret .= $ui->build()._NL;

		$ret.=ibuy_model::ajax_buy();
		return $ret;
	}

	/**
	 * Get shop information
	 *
	 * @param Int $shopId
	 * @return RecordSet
	 */
	function get_shop($shopId) {
		$stmt='SELECT *
					FROM %db_org% o
					WHERE o.`orgid`=:orgid LIMIT 1';
		$rs=mydb::select($stmt,':orgid',$shopId);
		return $rs;
	}

	/**
	 * Get org photo
	 *
	 * @param String $id
	 * @return String
	 */
	function shop_logo($id=NULL) {
		$photo_file='upload/org/org-logo-'.$id.'.jpg';
		if (file_exists($photo_file)) {
			return _URL.$photo_file;
		} else {
			return _img.'photography.png';
		}
		return $photo;
	}

	/**
	 * Ajax buy click button
	 *
	 * @return String
	 */
	function ajax_buy() {
		$ret.='<script type="text/javascript">
$(document).ready(function() {
	$(".ibuy-sale-label form").each(function(index) {
		var $this=$(this);
		$this.submit(function() {
			var url=$this.attr("action");
			var amt=$this.find("[name=\'amt\']").val();
			var confirm=$this.find("[name=\'confirm\']").val();
//			notify($this.attr("id")+"<br />"+url+"<br />"+"amt = "+amt+" confirm = "+confirm);
			para={amt: amt, confirm: "yes"};
			$.post(url,para,function(html) {
				notify(html,3000);
				$this.find("[name=\'amt\']").val(1);
				// Update cart items
				var $cartItems=$("#cart-items");
				var cartItemsUri=$cartItems.data("url");
				if (cartItemsUri) {
					$.get(cartItemsUri,function(html) {
						$cartItems.html(html);
					});
				}
			});
			return false;
		});
	});
});
</script>'._NL;
		return $ret;
	}
} // end of class ibuy_model
?>