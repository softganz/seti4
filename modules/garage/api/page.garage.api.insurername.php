<?php
/**
 * Search from repair code
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function garage_api_insurername($q = NULL, $n = NULL, $p = NULL) {
	sendheader('text/html');
	$q=SG\getFirst(trim($q),trim(post('q')));
	$n=intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p=intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));

	$retIdField = SG\getFirst(post('id'), 'insuid');
	$retNameField = SG\getFirst(post('name'), 'insuname');

	$shopInfo = R::Model('garage.get.shop');
	$shopid = $shopInfo->shopid;
	$shopbranch = array_keys(R::Model('garage.shop.branch',$shopid));

	$ret .= '<header class="header">'._HEADER_BACK.'<h3>บริษัทประกัน</h3></header>';
	$stmt = 'SELECT
		i.`insurerid`, i.`insurername`, i.`insurerphone`
		FROM %garage_insurer% i
		WHERE i.`shopid` IN (:shopbranch)
		ORDER BY CONVERT(i.`insurername` USING tis620) ASC
		';

	$dbs = mydb::select($stmt, ':shopbranch', 'SET:'.implode(',',$shopbranch), ':q','%'.$q.'%');

	$ui = new Ui(NULL, 'ui-menu');
	$ui->addClass('-orglist');
	$ui->addId('orglist');

	foreach ($dbs->items as $rs) {
		$cardStr = '<a class="sg-action btn -link" href="javascript:void(0)" data-rel="back" data-orgid="'.$rs->insurerid.'"><i class="icon -material -hidden">done</i><span>'.$rs->insurername.'</span></a>';
		$ui->add($cardStr);
	}

	$ret .= $ui->build();

	$ret .= '
		<script type="text/javascript">
		$("#orglist a").click(function() {
			var $thisOrg = $(this)
			var $targetId = $("#'.$retIdField.'")
			var $targetName = $("#'.$retNameField.'")
			//console.log("Click "+$thisOrg.data("orgid")+$thisOrg.html())
			$targetId.val($thisOrg.data("orgid"))
			$targetName.val($thisOrg.children("span").text())
		})
		</script>
		<style type="text/css">
		.ui-menu.-orglist .ui-item a {display: block; text-align: left;}
		.ui-menu.-orglist .ui-item a:hover .icon {display: inline-block;}
		</style>';

	$tables = new Table();

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			'value' => $rs->insurerid,
			'label' => $rs->insurername,
			'phone' => $rs->insurerphone,
			);
	}
	if ($dbs->_num_rows>=$n) $result[]=array('value'=>'...','label'=>'ยังมีอีก','desc'=>'');
	if (debug('api')) {
		$result[]=array('value'=>'shopid','label'=>$shopid);
		$result[]=array('value'=>'query','label'=>$dbs->_query);
		if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
		$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	if (debug('html')) return print_o($result,'$result');

	//$ret .= $tables->build();
	return $ret;
}
?>