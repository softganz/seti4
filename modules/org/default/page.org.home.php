<?php
/**
* Org :: Home Page
* Created 2019-04-08
* Modify  2021-08-10
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

import('widget:org.nav.php');

class OrgHome extends Page {
	function build() {
		$order=SG\getFirst(post('o'),'name');
		$sort=SG\getFirst(post('s'),'asc');

		$ret .= '<form class="sg-form search-box" method="get" action="'.url('org').'" style="margin: 8px 0;">';
		$ret .= '<input type="text" class="form-text sg-autocomplete -fill -highlight" name="name" placeholder="ป้อนชื่อองค์กรที่ต้องการค้นหา" data-query="'.url('org/api/org').'" data-callback="'.url('org/').'" /><button class="btn -link" type="submit" name="" value=""><i class="icon -search"></i></button>';
		$ret .= '</form>';

		if (post('name')) {
				$dbs = R::Model('org.search',post('name'),NULL,'{order:"'.$order.'",sort:"'.$sort.'",debug:false}');
		} else {
			$stmt = 'SELECT
				  *
				, GROUP_CONCAT(DISTINCT `membership` SEPARATOR " , ") `membership`
				FROM
					(SELECT
					1 `first`, o.`orgid`, o.`name`, o.`shortname`, UPPER(of.`membership`) `membership`
						FROM %org_officer% of
							LEFT JOIN %db_org% o USING(`orgid`) WHERE of.`uid` = :uid
					UNION
					SELECT 2, o.`orgid`, o.`name`, o.`shortname`, "Is Owner"
						FROM %db_org% o WHERE o.`uid` = :uid
					) a
				GROUP BY `orgid`
				ORDER BY `first`,CONVERT(`name` USING tis620) ASC';

			$dbs = mydb::select($stmt , ':uid', i()->uid);
		}

		$tables = new Table();
		$tables->thead = array('','x -hover-parent'=>'');
		foreach ($dbs->items as $rs) {
			$nav = '<nav class="nav -icons -hover"><ul><li><a href="'.url('org/'.$rs->orgid).'"><i class="icon -view"></i></a></li><li><a href="'.url('org/'.$rs->orgid.'/member').'"><i class="icon -people"></i></a></li></nav>';
			$tables->rows[] = array(
				'<a href="'.url('org/'.$rs->orgid).'">'.$rs->name.($rs->shortname ? ' ('.$rs->shortname.')' : '').'</a>',
				$rs->membership
				.$nav
			);
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Org'.(i()->ok ? ' @'.i()->name : ''),
				'trailing' => '<form method="get" action="'.url('org/member').'" id="search" class="search-box" data-query="'.url('org/api/person').'" role="search"><input type="text" name="qn" id="search-box" class="form-text" size="20" value="'.post('qn').'" placeholder="ป้อน ชื่อ นามสกุล หรือ เบอร์โทร"><button class="btn -link"><i class="icon -search"></i></button></form>',
				'navigator' => [new OrgNavWidget()],
			]),
			'body' => new Widget([
				'children' => [
					'<form class="sg-form search-box" method="get" action="'.url('org').'" style="margin: 8px 0;">'
					. '<input type="text" class="form-text sg-autocomplete -fill -highlight" name="name" placeholder="ป้อนชื่อองค์กรที่ต้องการค้นหา" data-query="'.url('org/api/org').'" data-callback="'.url('org/').'" /><button class="btn -link" type="submit" name="" value=""><i class="icon -search"></i></button>'
					. '</form>',
					$tables,
				],
			]),
		]);
	}
}
?>

<?php
// /**
// * My Organization
// *
// * @param Object $self
// * @param Int $orgId
// * @return String
// */

// $debug = true;

// function org_home($self,$orgid=NULL) {
// 	R::View('org.toolbar',$self, 'Org @'.i()->name);



// 	$ret .= $tables->build();

// 	//$ret.=print_o($dbs,'$dbs');
// 	return $ret;
// }
?>