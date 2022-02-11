<?php
/**
* Paper event when paper/$tpid (paper view) page call on module project :: event onView
* @param Object $self
* @param Object $topicInfo
* @param Object $para
* @param Object $body
* Change property of $body will effect on page show
*/
function on_ibuy_paper_view($self,$topicInfo,$para,$body) {
	$tpid = $topicInfo->tpid;
	$productInfo = R::Model('ibuy.product.get', $tpid);

	$ibuy_tags = mydb::select('SELECT `vid` FROM %vocabulary_types% WHERE `type`="ibuy" LIMIT 1')->vid;
	$category_info = model::get_taxonomy($topicInfo->tags[0]->tid);
	$self->theme->class .= ' ibuy-view';
	cfg('page_id','ibuy');
	page_class('-view');

	if (user_access('administer contents,administer papers','edit own paper',$topicInfo->uid)) {
		user_menu(
			'edit',
			'edit_ibuy',
			'แก้ไขข้อมูลสินค้า',
			url('ibuy/'.$tpid.'/edit.info'),
			'{class: "sg-action", "data-rel": "box", "data-width": 480, "data-height": "80%"}'
		);
		user_menu(
			'edit',
			'edit_cancel',
			$productInfo->info->outofsale == 'N' ? 'งดการจำหน่ายสินค้าชั่วคราว' : 'ยกเลิกการงดจำหน่ายสินค้าชั่วคราว',
			url('ibuy/'.$tpid.'/edit.cancel'),
			'{class:"sg-action", "data-rel": "box", "data-width": 320}'
		);
		user_menu(
			'edit',
			'edit_qrcode',
			'QR Code',
			url('ibuy/'.$tpid.'/edit.qrcode'),
			'{class:"sg-action", "data-rel": "box", "data-width": 480, "data-height": 320}'
		);
	}
	user_menu('home:remove');
	user_menu('tag:remove');
	user_menu('paper_id:remove');
	user_menu('member:remove');
	user_menu('type:remove');
	user_menu('new:remove');
	user_menu('signin:remove');

	if (cfg('ibuy.showshoptoolbar')) R::Page('ibuy.shop.toolbar',$self,$topicInfo->orgid);
	if (!user_access('buy ibuy product') && cfg('ibuy.showfor.public')=='PUBLIC' && $productInfo->showfor=='MEMBER') {
		//$topicInfo->info->status=_BLOCK;
		//$body->a=print_o($topicInfo,'$topicInfo');
		//unset($body);
		//$body->detail='<p class="notify">ไม่มีรายการสินค้า</p>';
		//die('<p class="notify">ไม่มีรายการสินค้า</p>');
		//return;
	}

	//		$body->title='<h2 class="title">'.$topicInfo->title.'</h2>';
	//		property_reorder($body,'title','top');

	$body->category_nav='<div class="ibuy-category-nav">Back to: <a href="'.url().'">Homepage </a> >';
	foreach (array_reverse($category_info->parents,true) as $tid=>$tname) $body->category_nav.=' <a href="'.url('ibuy/category/'.$tid).'">'.$tname.'</a> >';
	$body->category_nav.=' <a href="'.url('ibuy/category/'.$topicInfo->tags[0]->tid).'">'.$topicInfo->tags[0]->name.'</a> >';
	$body->category_nav.=' <a href="'.url('ibuy/'.$tpid).'">'.$topicInfo->title.'</a></div>';
	property_reorder($body,'category_nav','top');

	if (empty($body->photo)) {
		$body->photo = '<div class="photo"></div><!--photo-->'._NL._NL;
		property_reorder($body,'photo','before detail');
	}

	if ($_SESSION['message']) {
		$body->add2cart='<script type="text/javascript">$(document).ready(function(){notify("'.htmlspecialchars($_SESSION['message']).'",5000);});</script>'; //$_SESSION['message'];
		property_reorder($body,'add2cart','after category_nav');
		unset($_SESSION['message']);
	}

	// show timestamp
	if ($topicInfo->property->option->timestamp) {
		$body->timestamp = '<div class="timestamp">';
		$body->timestamp .= 'by <span class="poster'.($topicInfo->uid==i()->uid?' owner':'').'">';
		$body->timestamp .= $topicInfo->uid && user_access('access user profiles')?'<a href="'.url('profile/'.$topicInfo->uid).'" title="view poster profile">'.SG\getFirst($topicInfo->info->poster,$topicInfo->info->owner).'</a>' : SG\getFirst($topicInfo->info->poster,$topicInfo->info->owner);
		$body->timestamp .= '</span> ';
		$body->timestamp .= '<span class="timestamp">@'.sg_date($topicInfo->info->created,cfg('dateformat')).'</span>';
		if ($topicInfo->tags) {
			foreach ($topicInfo->tags as $tag ) {
				$tags[] = '<a href="'.url('ibuy/category/'.$tag->tid).'">'.$tag->name.'</a>';
				user_menu('cat'.$tag->tid,$tag->name,url('ibuy/category/'.$tag->tid), '{class: "-tag-name"}');
			}
			$body->timestamp .= ' | <span class="tags">Category : '.implode(' , ',$tags).'</span>';
		}
		$body->timestamp .= '</div>'._NL._NL;
	}

	// show detail
	$body->ribbon='<div class="ibuy-category-ribbon">'.user_menu().'</div>'._NL;
	if ($topicInfo->property->option->container) $body->detail='<div class="detail">'._NL;

	$body->detail .= '<div class="ibuy-product-title">';
	$body->detail .= 'ชื่อสินค้า : '.$topicInfo->title.'<br />';
	$body->detail .= 'รหัสสินค้า : '.$tpid.'<br />';

	$body->detail .= '<nav class="nav -like-status -sg-text-right -no-print">'.R::Page('ibuy.like.status', NULL, $tpid).'</nav>';

	$body->detail .= '</div>';

	// Create price label
	$body->detail .= R::View('ibuy.price.label', $productInfo->info)._NL;
	$body->detail .= R::View('ibuy.sale.label', $productInfo->info,NULL,true)._NL;

	//debugMsg($productInfo,'$productInfo');
	$body->detail.='<div class="ibuy-spec">'._NL;

	if (user_access('administer ibuys')) {
		$tables = new Table();
		$tables->addClass('ibuy-price-table -admin');
		foreach (cfg('ibuy.price.use') as $key => $item) {
			$tables->thead['money -'.$key] = $item->label;
			$tables->rows[0][] = number_format($productInfo->info->{$key},2);
		}

		$body->detail .= $tables->build();

		if ($productInfo->info->remember!='') $body->detail.='<p><strong>เตือนความจำ : '.$productInfo->info->remember.'</strong></p>';
	}

	$body->detail.='<h2>'.$topicInfo->title.'</h2>'._NL;
	if ($topicInfo->info->forbrand) $body->detail.='<h3>สำหรับรุ่น :</h3><strong>'.$productInfo->info->forbrand.'</strong>';
	$body->detail.='<h3 class="ibuy-spec-header">คุณสมบัติ / รายละเอียดสินค้า</h3>'._NL;
	if ($productInfo->info->minsaleqty > 1) $body->detail .= '<p>** จำนวนสั่งขั้นต่ำอย่างน้อย '.$productInfo->info->minsaleqty.' ชิ้น **</p>';
	$body->detail .= $topicInfo->info->body._NL;
	$body->detail .= '</div><!--ibuy-spec-->'._NL;

	//$body->detail .= print_o($productInfo,'productInfo');
	//$body->detail .= print_o(i(),'i').print_o($topicInfo,'$topicInfo');

	if ($topicInfo->property->option->ads && isset($GLOBALS['ad']->detail_bottom)) $body->detail.='<div id="ad-detail_bottom" class="ads"><h3>ผู้สนับสนุน</h3>'.$GLOBALS['ad']->detail_bottom.'</div>';
	if ($topicInfo->property->option->container) $body->detail.='</div><!--detail-->'._NL._NL;

	$body->detail .= '<div class="product-detail"><h3>Product details of '.$productInfo->title.'</h3>'.$productInfo->info->full_description.'</div>'._NL;

	$body->detail .= '<div class="product-ratings"><h3>Ratings & Reviews of '.$productInfo->title.'</h3>';
	$body->detail .= '<div class="product-rating"><h4>Product Ratings</h4></div>';
	$body->detail .= '<div class="product-review"><h4>Product Reviews</h4></div>';
	$body->detail .= '</div>'._NL;

	$body->detail .= '<div class="product-questions"><h3>Questions about this product</h3>';
	$body->detail .= '</div>'._NL;

	$body->detail .= '<div class="product-alsoview"><h3>People Who Viewed This Item Also Viewed</h3>';
	$body->detail .= '</div>'._NL;




	property_reorder($body,'ribbon','before detail');

	//property_reorder($body,'cart','after detail');
	//$body->cart.=ibuy_model::ajax_buy();

	// แสดงรายการสินค้า 1. สินค้าล่าสุดของหมวด 2. สินค้าขายดี 3. สินค้าล่าสุด 4. สินค้าสุ่ม
	/*
	$stmt='SELECT * FROM %topic% WHERE `type`="ibuy" ORDER BY `tpid` DESC LIMIT 10';
	$dbs=mydb::select($stmt);

	$ret.=print_o($dbs,'$dbs');
	$body->newproduct=$ret;
	property_reorder($body,'newproduct','before comment');
	*/


	$topicInfo->property->option->header=false;
	//		$topicInfo->property->option->title=false;
	$topicInfo->property->option->ribbon=false;
	unset($body->timestamp,$self->theme->navigator);

}