<?php
/**
* Buy a new car into stock
*
* @param Integer $id
* @return String
*/

$debug = true;

/**
 * Buy a new car into stock
 *
 * @param Integer $id
 * @return String
 */
function icar_buy($self) {
	$shopInfo=icar_model::get_my_shop();


	if (!user_access('administer icars,create icar content')) return $ret.message('error','access denied');
	else if ($shopInfo->shopstatus != 'ENABLE') {
		return message('error', 'ร้านค้าหมดอายุการใช้งาน กรุณาติดต่อผู้ดูแลระบบเพื่อต่ออายุการใช้งาน');
	}


	R::View('icar.toolbar', $self, $shopInfo->name);
	$self->theme->title = SG\getFirst($shopInfo->name,'No Shop');

	$post=(object)post('icar');

	$myshop=icar_model::get_shop(icar_model::get_my_shop()->shopid);

	$isAdmin = user_access('administer icars');
	$isShopOfficer = $isAdmin || $myshop->iam;
	$isCreatable = $isShopOfficer && $myshop->iam != 'VIEWER';


	//$ret .= print_o($myshop,'$myshop');

	$self->theme->title = SG\getFirst($myshop->shopname,'No Shop');

	if (!$isCreatable) return $ret;


	if ($post->tpid) 	R::View('icar.toolbar', $self, NULL, NULL, $post);


	//$ret.='<div id="icar-sidebar">';
	//$ret.=icar_view::shop_info($shopInfo);
	//$ret.='</div><!--icar-sidebar-->';


	//$ret.='<div id="icar-detail">';
	$form = new Form('icar', url('icar/create'), 'icar-buy-form', 'sg-form icar-buy-form');
	$form->addData('checkValid', true);
	$form->config->title=tr('Car Buying Information');

	$form->buydate=array('type'=>'text', 'label'=>tr('Buy Date'), 'size'=>10, 'require'=>true,'class'=>'-fill','value'=>SG\getFirst($post->buydate?sg_date($post->buydate,'d/m/Y'):NULL,date('d/m/Y')));
	$form->plate=array('type'=>'text', 'label'=>tr('License Plate'), 'size'=>20, 'require'=>true,'class'=>'-fill', 'value'=>htmlspecialchars($post->plate),'placeholder'=>'กก 0000 สงขลา');

	$form->addField('cartype',
		array(
			'type' => 'select',
			'label' => tr('Car Type').':',
			'class' => '-fill',
			'require' => true,
			'options' => array(''=>'==={tr:Select Type}===')+icar_model::category('icar:cartype',NULL,NULL,'{key: "catid"}'),
		)
	);

	$form->addField('brand',
		array(
			'type'=>'select',
			'label'=>tr('Brand').':',
			'require'=>true,
			'class'=>'-fill',
			'options'=>array(''=>'==={tr:Select Brand}===')+icar_model::category('icar:brand',$myshop->shopid),
			'value'=>$post->brand,
			'container' => array('class'=>'has-icons-right'),
			'posttext'=>'<span class="icons is-right"><a class="btn -link" href="'.url('icar/edit/add/brand').'"><i class="icon -add -gray"></i><span>{tr:Add new brand}</span></span></a><!-- <nav class="-hidden -sg-text-right"><a class="btn -link" href="'.url('icar/edit/add/brand').'"><i class="icon -add -gray"></i><span>{tr:Add New Brand}</span></a></nav>-->'
		)
	);

	$form->addField('model',
		array(
			'type'=>'text',
			'label'=>tr('Model').':',
			'size'=>20,
			'class'=>'-fill',
			'value'=>htmlspecialchars($post->model),
			'autocomplete-url'=>url('icar/api/autocomplete/model','action=get'),
			'placeholder'=>'แจ๊ส 1.6AT',
		)
	);

	$form->addField('partner',
		array(
			'type'=>'select',
			'label'=>tr('Partnership').':',
			'class'=>'-fill',
			'value'=>htmlspecialchars($post->partner),'options'=>array(tr('No Partnership'))+icar_model::category('partner',$myshop->shopid),
			'container' => array('class'=>'has-icons-right'),
			'posttext'=>'<span class="icons is-right"><a class="btn -link" href="'.url('icar/edit/add/partner').'"><i class="icon -add -gray"></i><span>{tr:Add new partner}</span></span></a><!-- <nav class="nav -sg-text-right -hidden"><a class="btn -link" href="'.url('icar/edit/add/partner').'"><i class="icon -add -gray"></i><span>{tr:Add new partner}</span></a></nav>-->',
		)
	);

	$form->addField('save',
		array(
			'type' => 'button',
			'name' => 'save',
			'value' => '<i class="icon -save -white"></i><span>{tr:BUY NEW CAR}</span>',
			'container' => array('class' => '-sg-text-right'),
		)
	);

	$ret .= $form->build();
	//$ret.='</div><!--icar-detail-->';

	$ret.='<script type="text/javascript">
	$(document).ready(function() {
		$("a[href$=\'icar/edit/add/brand\']").click(function() {
			$(this).closest("div").find("select").replaceWith("<input type=\"text\" name=\"icar[brandname]\" class=\"form-text -fill -require\" placeholder=\"ยี่ห้อรถ\" />");
			$("[name=\'icar[brandname]\']").focus();
			return false;
		});
		$("a[href$=\'icar/edit/add/partner\']").click(function() {
			$(this).closest("div").find("select").replaceWith("<input type=\"text\" name=\"icar[partnername]\" class=\"form-text -fill\" placeholder=\"ชื่อผู้ร่วมทุน\" />");
			$("[name=\'icar[partnername]\']").focus();
			return false;
		});

		$("#icar-buy").submit(function() {
			var $this=$(this);
			var error;
			$this.find(".require").each(function(i) {
				var $require=$(this);
				if ($require.val().empty()) {
					error="กรุณาป้อน "+$require.prevAll("label").text();
					$require.focus();
					return false;
				}
			});
			if (error) {
				notify(error);
				return false;
			}
		});

		$("#edit-icar-buydate").datepicker({
			dateFormat: "dd/mm/yy",
			disabled: false,
			monthNames: thaiMonthName
		});

		$("#edit-icar-model")
		.autocomplete({
			source: function(request, response) {
				notify("กำลังค้นหา...");
				$.get($("#edit-icar-model").attr("autocomplete-url"),{q:encodeURIComponent(request.term)}, function(data){
					notify();
					response($.map(data, function(item){
					return {
						label: item.label,
					}
					}))
				}, "json");
			},
			minLength: 1,
			dataType: "json",
			cache: false,
			select: function(event, ui) {
				$(this).val(ui.item.label);
				return false;
			}
		});

	});
	</script>';
	//		$ret.=print_o($post,'$post');
	//		$ret.=print_o($_POST,'$_POST');
	return $ret;
}
?>