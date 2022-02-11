<?php
function green_app_supplier_form($self,$qtref=NULL) {
	R::View('org.toolbar',$self,'เครือข่ายผู้ผลิต','supplier.app');
	if (!i()->ok) return R::View('signform');

	$formId=6;
	$formGroup=_QTGROUP_GOGREEN; // เครือข่าย Go Green

	//$result->msg.=print_o($_FILES,'$_FILES');
	//return json_encode((array)$result);

	$data=(object)post('data');

	//$ret.='post(save)='.post('save');
	//$ret.=print_o($data,'$data');

	// Create new quatation
	if ( empty($qtref) && $data->{'qt:ORG.NAME'} )  {
		// Create new qt
		$data->appsrc='Android';
		$result=R::Model('green.supplier.save',$data);
		//$ret.='Create new';
		//$ret.=print_o($data,'$data');
		//$ret.=print_o($result,'$result');
		if ($result->isDupPerson) {
			// Show form again
			$error.='<p class="notify">ชื่อ <b>"'.$data->{'qt:ORG.NAME'} .'"</b> มีอยู่ในฐานข้อมูลแล้ว ไม่สามารถสร้างซ้ำได้</p>';
		//} else if (i()->username=='softganz') {
		//	$ret.=print_o($result,'$result');
		} else if ($result->qtref) {
			location('green/app/supplier/form/'.$result->qtref);
		}
	}




	// Create new quatation form
	if (empty($qtref)) {

		$ret.='<h3 class="header -sub">ลงทะเบียนเครือข่ายรายใหม่</h3>';
		$form=new Form('data',url('green/app/supplier/form'),'imed-poorman-form','sg-form imed-poorman-form');
		$form->addData('checkValid',true);
		$form->addField('orgid',array('type'=>'hidden','id'=>'orgid'));
		$form->addField('qtgroup',array('type'=>'hidden','value'=>$formGroup));
		$form->addField('qtform',array('type'=>'hidden','value'=>SG\getFirst(post('fmid'),$formId)));

		$form->addField('qt:ORG.NAME',
							array(
								'type'=>'text',
								'label'=>'ชื่อเครือข่าย',
								'require'=>true,
								'class'=>'-fill',
								'value'=>$data->{'qt:ORG.NAME'},
								'placeholder'=>'ป้อนชื่อเครือข่าย',
								)
							);

		if ($error) $form->addField('error',$error);
		$form->addField('save',
							array(
								'type'=>'button',
								'name'=>'save',
								'items'=>array(
													'type'=>'submit',
													'class'=>'-primary',
													'value'=>'<i class="icon -addbig -white"></i><span>ลงทะเบียนเครือข่าย</span>',
													),
								)
							);

		$form->description='กรณีที่ได้ลงทะเบียนเครือข่ายไว้แล้ว กรุณาคลิกเลือกที่ชื่อเครือข่ายด้านบนเพื่อบันทึกข้อมูลอื่น ๆ เพิ่มเติม';
		$ret.=$form->build();


		// If already has register
		$regDbs=mydb::select('SELECT q.*,o.*,tr.`value` `qtname` FROM %qtmast% q LEFT JOIN %db_org% o USING(`orgid`) LEFT JOIN %qttran% tr ON tr.`qtref`=q.`qtref` AND tr.`part`="ORG.NAME" WHERE q.`qtgroup`=:qtgroup AND q.`uid`=:uid',':qtgroup',$formGroup,':uid',i()->uid);
		$ret.='<h3>เครือข่ายที่ลงทะเบียนแล้ว</h3>';
		$ui=new Ui(NULL,'ui-card');
		foreach ($regDbs->items as $rs) {
			if ($rs->orgid) {
				$url=url('green/app/supplier/'.$rs->qtref.'/view');
			} else {
				$url=url('green/app/supplier/form/'.$rs->qtref);
			}
			$ui->add('<a href="'.$url.'"><img src="https://softganz.com/img/img/shop-01.png" width="96" /></a><h4><a href="'.$url.'">'.SG\getFirst($rs->name,$rs->qtname).'</a></h4>','{class:"-sg-text-center"}');
		}
		$ret.=$ui->build();
		//$ret.=print_o($regDbs,'$regDbs');

		return $ret;
	}



	// Save quatation data
	if (post('save')) {
		$result=R::Model('green.supplier.save',$data);
		location('green/app/supplier/list');
		return $ret;
	} else if ($_FILES) {
		$photoKey=key($_FILES);
		//$ret.=print_o($_FILES,'$_FILES');
		// Para : $data->seq from post
		$data->prename=$photoKey.'_'.$data->psnid.'_'.date('ymdhis').'_';
		$data->tagname=$photoKey;
		// $data->deleteurl=url('imed/api/visit/'.$data->psnid.'/deletephoto/');
		$uploadResult=R::Model('imed.visit.photo.upload',$_FILES[$photoKey],$data);
		$ret.=$uploadResult['link'];

		//$ret.='<div class="notify">Upload photo underconstruction @'.date('H:i:s').'</div><img src="/library/img/dialog-warning.png" width="100%" />';
		//$ret.='<br />'.$_FILES['photocommuneneed']['name'].'<br />'.$_FILES['photocommuneneed']['tmp_name'];
		//$ret.=print_o($uploadResult,'$uploadResult');
		return $ret;
	} else if (post('data')) {
		$result=R::Model('green.supplier.save',$data);
		if (post('publish')) {
			mydb::query('UPDATE %qtmast% SET `qtstatus`=:qtstatus WHERE `qtref`=:qtref LIMIT 1',':qtref',$qtref,':qtstatus',_WAITING);
			if ($data->orgid) {

			}
		}
		$ret=json_encode((array)$result);
		return $ret;
	}






	// Get quatation data
	$qtInfo=R::Model('green.qt.get',$qtref);

	// Show full quatation form
	R::View('org.toolbar',$self,$qtInfo->name,'supplier.app');
	$ret.=R::View('green.app.supplier.form.'.$qtInfo->qtform,$qtInfo);

	//$ret.=print_o($qtInfo,'$qtInfo');




	$head='<style type="text/css">
	.form {margin:0;padding:8px;}
	.form h3 {padding:16px 16px;background:green; color:#fff;}
	.imed-poorman-form .form-item {clear:both}
	.imed-poorman-form .option {padding: 4px 0 4px 16px;}
	.imed-poorman-form .option:hover {background-color:#eee;}
	.card-item.-upload {box-shadow:none;}
	.form-item.-bigheader {margin-top:48px;}
	.form-item.-bigheader.-first {margin-top:0;}
	.form-item.-last {margin-bottom:48px;}
	.card.-photo>li {position:relative;}
	.card.-photo .photoitem {height:140px;}
	.card.-photo .iconset {position:absolute;top:0;right:0;}
	.card.-photo .iconset .icon {background-color:red;border-radius:50%;}
	.form-item.-edit-data-qt-POOR-HELP-ORG-LIST {padding-left:32px;}

	.imed-poorman-form-approve {margin:32px 8px; padding:0; border:1px green solid;}
	.imed-poorman-form-approve .form-item {padding:16px;}
	</style>';




	$head.='<script type="text/javascript">
	var lastid=0;
	$(document).on("change","#edit-data-qt-PSNL-PRENAME",function(){
		console.log($(this).val())
		if ($(this).val()=="อื่นๆ") {
			$(this).next().show().focus()
		} else {
			$(this).next().hide()
		}
	});


	$(document).on("click","input[name=\'data[qt:PSNL.HOME.NOTSAMEADDRESS]\']",function() {
		if($(this).is(":checked")) {
			$("#imed-poorman-form-regishome").show();
		} else {
			$("#imed-poorman-form-regishome").hide();
		}
	});

	$(document).on("submit", "#imed-poorman-form", function() {
		console.log("Form "+$(this).attr("id")+" submit");
		return true;
	})
	.on("keydown", "#imed-poorman-form input:text", function(event) {
		var n = $("input:text").length
		if(event.keyCode == 13) {
			event.preventDefault()
			var nextIndex = $("input:text,textarea").index(this) + 1
			if(nextIndex < n)
				$("input:text,textarea")[nextIndex].focus()
			return false
		}
	});

	var isFormChangeWaitng=true;
	$(document).on("change","#imed-poorman-form input, #imed-poorman-form textarea",function(){
		//if (!isFormChangeWaitng) return;
		var $this=$(this);
		var addPara="";
		isFormChangeWaitng=false;

		if ($this.attr("type")=="file") return false;
		console.log("Update change of "+$this.attr("type")+" "+$this.attr("name"));
		if ($this.attr("type")=="checkbox") {
			console.log("Check value = "+$this.val())
			$this.data("old",$this.val());
			if ($this.is(":checked")) {
				;
			} else {
				addPara="&"+$this.attr("name")+"=";
				//$this.val("");
			}
		}
		//console.log("ID "+$this.attr("id")+" change.");
		var $form=$this.closest("form");
		var para=$form.serialize()+addPara;
		//console.log(para)
		$.post($form.attr("action"),para, function(data) {
			console.log("qtref="+data.qtref+" psnid="+data.psnid);
			console.log("Save result "+data.msg);

			$("#qtref").val(data.qtref);
			$("#psnid").val(data.psnid);
			$("#qtrefno").val(data.qtrefno);
			//if ($this.attr("type")=="checkbox") $this.val($this.data("old"));
			isFormChangeWaitng=true;
		},
		"json");
		return false;
	});


	$(document).on("change","#edit-data-birth-year",function(){
		console.log("Age change")
		var age=new Date().getFullYear()-$(this).val();
		$("#age").text(age);
	});


	function imedAppPoormanGetPerson($this,ui) {
		console.log("Callback "+ui.item.value);
		$("#psnid").val(ui.item.value);
	}


	$(document).on("change","#imed-poorman-form .inline-upload",function(){
		var $this=$(this);
		var $form=$this.closest("form");
		var id="photo-"+(++lastid);
		console.log("Poorman inline upload file start and show result "+id)
		console.log("Inline action "+$form.attr("action"));
		var insertElement="<li id=\""+id+"\"><img class=\"photoitem\" src=\"/library/img/loading.gif\" /></li>";
		$this.closest("li").before(insertElement);
		$form.ajaxSubmit({
			success: function(data) {
				console.log("Inline upload Save result :: "+data);
				$("#"+id).html(data);
				$this.val("");
				$this.replaceWith($this.clone(true));
			}
		})
		//.submit();
		return false;
	});
	</script>';
	head($head);
	return $ret;
}

function __imed_app_poorman_form_tranvalue($key,$qttran) {
	$values=array();
	foreach ($qttran as $k => $item) {
		if (preg_match('/^'.$key.'[0-9]/',$k)) $values[]=$item->value;
	}
	return $values;
}

function __imed_app_poorman_form_photo($photoList,$tagname) {
	$isEditable=true;
	foreach ($photoList as $rs) {
		if ($rs->tagname!=$tagname) continue;
		$photoUrl=imed_model::upload_photo($rs->file);
		$ret.='<li>';
		//$ret.=$rs->file;
		$ret.='<a class="sg-action" href="'.$photoUrl.'" data-rel="img"><img src="'.$photoUrl.'" height="140" /></a>';
		if ($isEditable) {
			$ui=new Ui('span','iconset -hover');
			// $ui->add('<a class="sg-action -no-print" href="'.url('imed/api/visit/'.$rs->psnid.'/deletephoto/'.$rs->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -delete"></i></a>');
			$ret.=$ui->build();
		}
		$ret.='</li>'._NL;
	}
	/*
	if (i()->username=='softganz') {
		$ret.='Tagname='.$tagname.'<br />';
		$ret.=print_o($photoList,'$photoList');
	}
	*/
	return $ret;
}
?>