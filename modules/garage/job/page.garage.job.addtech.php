<?php
/**
* Module Method
* Created 2019-12-01
* Modify  2019-12-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_job_addtech($self, $jobId = NULL) {
	$shopInfo = R::Model('garage.get.shop');
	$shopId = $shopInfo->shopid;

	if (!$shopId) return 'PROCESS ERROR';

	if ($jobId) {
		$data = new stdClass();
		$data->tpid = $jobId;
		$data->uid = i()->uid;
		$data->dotype = $shopInfo->position;
		$data->created = date('U');

		$stmt = 'INSERT INTO %garage_do%
			(`tpid`, `uid`, `dotype`, `status`, `created`)
			VALUES
			(:tpid, :uid, :dotype, "OPEN", :created)
			ON DUPLICATE KEY UPDATE
			`dotype` = :dotype
			, `status` = "OPEN"';
		mydb::query($stmt, $data);
		//$ret .= mydb()->_query;
		return $ret;
	}


	//$ret = '<header class="header -box">'._HEADER_BACK.'<h3>เพิ่มใบสั่งงาน</h3></header>';

	$form = new Form(NULL, url(), NULL, '');
	$form->addField(
		'jobid',
		array(
			'type' => 'text',
			'class' => 'sg-autocomplete -fill',
			'placeholder' => 'ค้นทะเบียนรถ หรือ เลขจ็อบ',
			'attr' => array(
				'data-query'=>url('garage/api/job',array('shop' => '*', 'show' => 'notreturned', 'item' => '*')),
				'data-target' => '#joblist',
				'data-minLength' => 2,
				'data-render-start' => 'customerRenderStart',
				'data-render-item' => 'customerRenderItem',
			),
			'posttext' => '<div class="input-append"><span><a id="clear-gis" class="btn -link" href="javascript:void(0)"><i class="icon -material -gray">search</i></a></span></div>',
			'container' => '{class: "-group"}',
		)
	);

	$ret .= '<nav class="nav -page" style="padding: 0 8px;">'.$form->build().'</nav>';

	$ret .= '<script type="text/javascript">
	function customerRenderStart($this, ui) {
		var $target = $($this.data("target"))
		//console.log("START $THIS ",$this)
		//console.log("UI", ui)
		//console.log("TARGET ", $this.data("target"))
		$target.empty().show()
	}

	function customerRenderItem($this, ul, item) {
		var $target = $($this.data("target"))
		var detail
		//console.log("RENDER ITEM ",item)
		if (item.value == "...") {
			detail = item.label
		} else {
			detail = "<i class=\"icon -material -sg-64\">add</i><span>"+item.plate+"<br />"+item.brand+"</span>"
		}
		return $("<a id=\"garage-job-"+item.value+"\" class=\"ui-item sg-action\" href=\"" + rootUrl + "garage/job/addtech/"+item.value+"\" data-rel=\"main\" data-done=\"load:#main:'.url('garage/app').'\" data-title=\"เพิ่มใบสั่งงาน\" data-confirm=\"ต้องการเพิ่มใบสั่งงาน กรุณายืนยัน?\"></a>")
		.append(detail)
		.appendTo($target);
	}
	</script>';




	mydb::where('(j.`shopid` = :shopid OR (j.`shopid` IN (SELECT `shopid` FROM %garage_shop% WHERE `shopparent` = :shopid)))');
	mydb::where('j.`iscarreturned` != "Yes" AND do.`uid` IS NULL', ':uid', i()->uid);

	$stmt = 'SELECT
		j.`tpid`, j.`plate`, j.`brandid`, do.`uid`, do.`dotype`
		FROM %garage_job% j
			LEFT JOIN %garage_do% do ON do.`tpid` = j.`tpid` AND do.`uid` = :uid
		%WHERE%
	--	GROUP BY `tpid`
		ORDER BY `tpid` DESC
		';

	$dbs = mydb::select($stmt, ':shopid', $shopId);

	$jobUi = new Ui('div a', 'ui-card');
	$jobUi->addId('joblist');
	foreach ($dbs->items as $rs) {
		$jobUi->add(
			'<i class="icon -material -sg-64">add</i><span>'.$rs->plate.'<br />'.$rs->brandid.'</span>',
			array(
				'class' => 'sg-action',
				'href' => url('garage/job/addtech/'.$rs->tpid),
				'data-rel' => 'notify',
				'data-done' => 'load:#main:'.url('garage/app'),
				'data-title' => 'เพิ่มใบสั่งงาน',
				'data-confirm' => 'ต้องการเพิ่มใบสั่งงาน กรุณายืนยัน?',
			)
		);
	}

	$ret.='<nav class="nav -master">'.$jobUi->build(true).'</nav>'._NL;


	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($shopInfo,'$shopInfo');
	return $ret;
}
?>