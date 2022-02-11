<?php
/**
* Organization Mapping
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function project_map($self) {
	R::View('project.toolbar',$self, 'แผนที่โครงการ', 'map');

	$ret = '';

	$mapMenu = array(
							array(
								'title'=>'แผนที่ติดตามโครงการ',
								'url' => url('project/map/follow'),
								'desc' => 'Show Cluster Mapping',
							),
							array(
								'title'=>'แผนที่แผนงาน',
								'url' => url('project/map/planning'),
								'desc' => 'Show Cluster Mapping',
							),
							array(
								'title'=>'แผนที่พัฒนาโครงการ',
								'url' => url('project/map/proposal'),
								'desc' => 'Show Cluster Mapping',
							),
							array(
								'title'=>'แผนที่โครงการดี ๆ',
								'url' => url('project/map/goodproject'),
								'desc' => 'โครงการดี ๆ จากการประเมิน',
							),
							array(
								'title'=>'แผนที่องค์กร',
								'url' => url('project/map/org'),
								'desc' => 'องค์กรที่เข้าร่วมโครงการอยู่ที่ไหน',
							),
							array(
								'title'=>'แผนที่ติดตามโครงการ',
								'url' => url('project/map/pin'),
								'desc' => 'Show Pin Mapping',
							),
						);

	$cardUi = new Ui(NULL, 'ui-card -flex');
	foreach ($mapMenu as $rs) {
		$imgBanner = SG\getFirst($rs['banner'],'//img.softganz.com/img/map-1272165_640.png');
		$cardStr = '<a href="'.$rs['url'].'"><span>';
		$cardStr .= '<h3>'.$rs['title'].'</h3>';
		$cardStr .= '<img src="'.$imgBanner.'" width="100%" />';
		$cardStr .= '</span></a>';
		$cardStr .= '<nav class="nav -card"><a class="btn -link -fill" href="'.$rs['url'].'"><i class="icon -pin"></i><span>View Mapping</span></a></nav>';
		$cardStr .= '<p>'.$rs['desc'].'</p>';
		$cardUi->add($cardStr);
	}
	$ret .= $cardUi->build();

	$ret .= '<style type="text/css">
	.ui-card h3 {width: 100%; padding: 8px; font-size: 1.4em; position: absolute; top: 0; background-color: #eee; opacity: 0.8;}
	.ui-card.-flex {display: flex; flex-wrap: wrap; justify-content: space-between;}
	.ui-card.-flex>.ui-item {width: 240px; height: 200px; overflow: hidden; margin: 16px; padding-top: 40px; padding-bottom: 0; position: relative;}
	.ui-card .nav.-card {margin:0; position: absolute; bottom: 0px; width: 100%}
	</style>';

	return $ret;
}
?>