<?php
/**
* Module Method
* Created 2019-09-01
* Modify  2019-09-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_info_select_org($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;

	$ret = '';

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>องค์กร</h3></header>';


	$form = new Form(NULL, url(), NULL, 'search-box');
	$form->addField(
		'orgname',
		array(
			'type' => 'text',
			'class' => 'sg-autocomplete -fill',
			'placeholder' => 'ค้นชื่อองค์กร',
			'attr' => array(
				'data-altfld'=>'edit-org-parent',
				'data-query'=>url('api/org',array('sector'=>10,'n'=>1000)),
				'data-tpid' => $tpid,
				'data-minLength' => 1,
				'data-render-item' => 'projectOrgRender',
				'data-render-complete' => 'projectOrgRenderComplete'
			),
		)
	);

	$ret .= '<nav class="nav -page">'.$form->build().'</nav>';

	$ret .= '<style type="text/css">
	.ui-menu.-orglist .icon.-material {color: transparent;}
	.ui-menu.-orglist a:hover>.icon.-material {color: #333;}
	</style>';

	$ret .= '<script type="text/javascript">
	$("#edit-orgname").keyup(function() {
		$("#orglist").empty()
	})

	function projectOrgRenderComplete($this) {
		$(".ui-menu.-orglist input[type=\'checkbox\']").change(function() {
			orgValue = ""
			$(".ui-menu.-orglist input[type=\'checkbox\']").each(function(index){
				if ($(this).is(":checked")) {
					orgValue += $(this).val()+","
				}
			})
			//console.log(orgValue)
			$("#edit-org").val(orgValue)
			$("#project-set-search").submit()
		})
	}

	function projectOrgRender($this, ul, item) {
		var target = document.getElementById("orglist")
		//console.log(item)
		return $("<li class=\"ui-item\"></li>")
		.append(\'<abbr class="checkbox -block"><label><input type="checkbox" value="\'+item.value+\'" data-orgid="\'+item.value+\'"><span>\'+item.label+\'</span></label></abbr>\')
		.appendTo( target );
	}

	projectOrgRenderComplete()

	$(document).ready(function() {
		var orgValue = $("#edit-org").val()
		//console.log(orgValue)
		//console.log(orgValue.split(","))
		orgValue.split(",").map(function(orgId){
			console.log(orgId)
			$(".ui-menu.-orglist input[value=\'"+orgId+"\']").prop("checked", true)	
		})
	})
	</script>';


	$stmt = 'SELECT
		*
		, ( SELECT COUNT(*) FROM %db_org% WHERE `parent` = o.`orgid` ) `childs`
		FROM %db_org% o
		WHERE o.`sector` IN (10)
		ORDER BY CONVERT(`name` USING tis620) ASC;
		-- {key: "orgid", group: "parent"}';

	$dbs = mydb::select($stmt);

	$ui = new Ui(NULL, 'ui-menu -orglist');
	$ui->addClass('-orglist');
	$ui->addId('orglist');

	foreach ($dbs->items as $groupId => $groupItem) {
		//$cardStr = '<abbr class="checkbox -block"><label><input type="checkbox" value="'.$rs->orgid.'" data-orgid="'.$rs->orgid.'" /><span>'.$rs->name.$rs->parent.'</span></label></abbr>';
		//	$ui->add($cardStr, '{class: "'.($rs->childs ? '-has-child' : '').'"}');
		foreach ($groupItem as $rs) {
			if (in_array($rs->parent, array_keys($dbs->items[268]))) continue;

			$cardStr = '<abbr class="checkbox -block"><label><input type="checkbox" value="'.$rs->orgid.'" data-orgid="'.$rs->orgid.'" /><span>'.$rs->name.'</span></label></abbr>';
			$ui->add($cardStr, '{class: "'.($rs->childs ? '-has-child' : '').'"}');
			if ($rs->childs) {
				foreach ($dbs->items[$rs->orgid] as $value) {
					$cardStr = '<abbr class="checkbox -block"><label><input type="checkbox" value="'.$value->orgid.'" data-orgid="'.$value->orgid.'" /><span>'.$value->name.'</span></label></abbr>';
					$ui->add($cardStr, '{class: "'.($value->childs ? '-has-child' : '').'"}');
					
				}
				unset($dbs->items[$rs->orgid]);
			}
		}
	}

	$ret .= $ui->build();

	//$ret .= print_o($dbs,'$dbs');

	$ret .= '<nav class="nav -page -sg-text-right"><a class="sg-action btn -primary" data-rel="close"><i class="icon -material">done_all</i><span>เรียบร้อย</span></a></nav>';

	$ret .= '<style type="text/css">
	#ui-id-1 {display: none; border: none; padding: 0;}
	.ui-menu.-orglist .ui-item label {display: block; text-align: left;}
	.ui-menu.-orglist .-has-child {font-weight: bold; font-size: 1.1em;}
	</style>';

	return $ret;
}
?>