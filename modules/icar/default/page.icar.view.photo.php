<?php
/**
* Show car information
*
* @param Object $self
* @param Record Set $carInfo
* @param Boolean $isEdit
* @return String
*/

$debug = true;

function icar_view_photo($self, $carId, $isEdit = NULL) {
	$carInfo = is_object($carId) ? $carId : R::Model('icar.get',$carId, '{initTemplate: true}');
	$carId = $carInfo->tpid;

	$isAdmin = user_access('administer icars');
	$isShopOfficer = $isAdmin || $carInfo->iam;
	$isShopPartner = icar_model::is_partner_of($carInfo);
	$isEdit = $isShopOfficer && empty($carInfo->sold);

	$tables->class='item'.($isEdit?' sg-inline-edit':'');
	$tables->attr['url']=url('icar/edit/info');
	$tables->caption='รายละเอียดรถ';

	// Show photo
	$ret.='<div class="photo">'._NL;
	$ret.='<ul>'._NL;
	if ($carInfo->photo) {
		foreach ($carInfo->photo as $photo) {
			$ret .= '<li class="-hover-parent">';
			$ret.='<a class="sg-action" href="'.$photo->_src.'" data-rel="img">';
			$ret.='<img class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="'.$photo->title.'" />';
			$ret.='</a>';
	//				$ret.=view::inlineedit('photo-detail','...',$isEdit);
			$photomenu=array();
			if ($isEdit) {
				$ui = new Ui('span');
				$ui->add('<a class="sg-action" href="'.url('icar/edit/info',array('action'=>'cover','f'=>$photo->fid)).'" title="As Cover" data-rel="none"><i class="icon -save"></i></a>');
				$ui->add('<a class="sg-action" href="'.url('icar/edit/info',array('action'=>'delphoto','f'=>$photo->fid)).'" data-title="DELETE PHOTO" data-confirm="{tr:Are you sure to DELETE PHOTO} ?" data-rel="none" data-removeparent="li"><i class="icon -cancel"></i></a>');
				$ret.='<nav class="nav -icons -hover -top-right">'.$ui->build().'</nav>';
			}
			$ret .= '</li>'._NL;
		}
	}
	$ret.='</ul>'._NL;
	if ($isEdit) {
		$ret.='<br clear="all" /><form class="inline-upload" method="post" enctype="multipart/form-data" action="'.url('icar/edit/info',array('action'=>'addphoto','tr'=>$carInfo->tpid)).'"><div><span><i class="icon -camera"></i><span>{tr:Send Car Photo}</span><input type="file" name="photo" /></span></div></form>';
	}
	$ret.='</div><!--photo-->'._NL;

	$ret.='<script type="text/javascript">
	$(document).ready(function() {
		// Send new photo
		$("form.inline-upload>div>span>input").change(function() {
			var $this=$(this);
			var $target=$(this).closest("div.photo").children("ul");
			notify("<img src=\"/library/img/loading.gif\" alt=\"Uploading....\"/> กำลังอัพโหลดภาพถ่าย กรุณารอสักครู่....");
			$this.closest("form").ajaxForm({
				success: function(data) {
					$target.append("<li class=\"-hover-parent\">"+data+"</li>");
					notify("ดำเนินการเสร็จแล้ว.",5000);
					$this.val("");
					$this.replaceWith($this.clone(true));
				}
			}).submit();
		});
	});
	</script>';
	return $ret;
}
?>