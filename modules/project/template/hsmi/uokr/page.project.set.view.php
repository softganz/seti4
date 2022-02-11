<?php
/**
* Project Co-Commune
* Created 2019-09-02
* Modify  2019-09-02
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_set_view($self, $projectInfo) {
	$tpid = $projectInfo->tpid;
	$getMainIssue = trim(post('mainissue'),',');
	$getIssue = trim(post('issue'),',');
	$getInno = trim(post('inno'),',');
	$getOrg = trim(post('org'),',');
	$getTag = trim(post('tag'),',');
	$getArea = trim(post('area'),',');
	$getValue = trim(post('value'),',');
	$getYear = trim(post('year'),',');
	$getSearch = trim(post('q'));

	$isInnovationProject = $projectInfo->settings->type === 'INNO';

	if (post('mode') == 'default') {
		$viewMode = NULL;
		setcookie('viewmode',$viewMode,-1,cfg('cookie.path'),cfg('cookie.domain'));
	} else if (post('mode')) {
		$viewMode = post('mode');
		setcookie('viewmode',$viewMode,time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
	} else {
		$viewMode = SG\getFirst($_COOKIE['viewmode']);
	}





	$ret .= '';
	//$ret .= 'VIEW MODE = '.$viewMode;
	//$ret .= print_o($_COOKIE);

	R::View('project.toolbar', $self, $projectInfo->title, NULL, $projectInfo);

	$ui = new Ui();

	$form = new Form(NULL, url('project/set/'.$tpid), 'project-set-search', 'sg-form project-set-navbar -sg-flex');
	$form->addConfig('method', 'GET');
	$form->addData('rel', 'replace: #result');

	$form->addField('mode',array('type'=>'hidden'));

	$form->addText('<div class="form-item"><a class="sg-action btn" href="'.url('project/'.$tpid.'/info.select.area').'" data-rel="box" data-width="480">พื้นที่:<i class="icon -material">keyboard_arrow_down</i></a></div>');
	$form->addField('area', array('type'=>'hidden'));

	$form->addText('<div class="form-item"><a class="sg-action btn" href="'.url('project/'.$tpid.'/info.select.org').'" data-rel="box" data-width="480">องค์กร:<i class="icon -material">keyboard_arrow_down</i></a></div>');
	$form->addField('org', array('type'=>'hidden'));

	$form->addText('<div class="form-item"><a class="sg-action btn" href="'.url('project/'.$tpid.'/info.select.mainissue').'" data-rel="box" data-width="480">ปัญหา:<i class="icon -material">keyboard_arrow_down</i></a></div>');
	$form->addField('mainissue', array('type'=>'hidden'));

	$form->addText('<div class="form-item"><a class="sg-action btn" href="'.url('project/'.$tpid.'/info.select.issue').'" data-rel="box" data-width="480">ประเด็น:<i class="icon -material">keyboard_arrow_down</i></a></div>');
	$form->addField('issue', array('type'=>'hidden'));

	$form->addText('<div class="form-item"><a class="sg-action btn" href="'.url('project/'.$tpid.'/info.select.tag').'" data-rel="box" data-width="480">คำสำคัญ:<i class="icon -material">keyboard_arrow_down</i></a></div>');
	$form->addField('tag', array('type'=>'hidden'));

	$form->addText('<div class="form-item"><a class="sg-action btn" href="'.url('project/'.$tpid.'/info.select.value').'" data-rel="box" data-width="480">คุณค่า:<i class="icon -material">keyboard_arrow_down</i></a></div>');
	$form->addField('value', array('type'=>'hidden'));

	if ($isInnovationProject) {
		$form->addText('<div class="form-item"><a class="sg-action btn" href="'.url('project/'.$tpid.'/info.select.inno').'" data-rel="box" data-width="480">นวัตกรรม:<i class="icon -material">keyboard_arrow_down</i></a></div>');
		$form->addField('inno', array('type'=>'hidden'));
	}

	$stmt = 'SELECT p.`pryear` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE `parent` = :parent GROUP BY p.`pryear` ORDER BY `pryear` ASC';
	$yearDbs = mydb::select($stmt, ':parent', $tpid);
	$yearSelectOptions = array('' => '==ทุกปี==');
	foreach ($yearDbs->items as $rs) $yearSelectOptions[$rs->pryear] = 'พ.ศ.'.($rs->pryear+543);
	$form->addField(
		'year',
		array(
			'type' => 'select',
			'class' => '-fill',
			'options' => $yearSelectOptions,
			'value' => $getYear,
			'attr' => array('onChange' => '$(this).closest(\'form\').submit()'),
		)
	);

	$form->addField(
		'q',
		array(
			'type' => 'text',
			'class' => '-fill',
			'placeholder' => 'ระบุชื่อ{tr:โครงการ}',
		)
	);

	$form->addField(
		'go',
		array(
			'type' => 'button',
			'class' => '-fill',
			'value' => '<i class="icon -material">search</i>',
		)
	);




	$navBar = $form->build();

	$navBar .= '<style type="text/css">
	.page.-main {display: flex; flex-wrap: wrap;}
	.package-footer, .page.-content-footer {display: none;}
	.navbar.-main {overflow: scroll; flex: 1 0 90%;}
	.project-set-navbar.form.-sg-flex {justify-content: left;}
	#result {flex: 1 0 90%;}
	.form-item.-edit-go {flex: 1 0 100%;}
	@media (min-width:40em){    /* 640/16 = 40 */
	.navbar.-main {overflow: initial; max-width: 160px;}
	.project-set-navbar.form.-sg-flex {flex-wrap: wrap; max-width: 160px;}
	.project-set-navbar .form-item {flex: 1 0 90%;}
	.project-set-navbar .form-item a {display: block;}
	.project-set-navbar .btn.-primary.-fill {width: 100%;}
	#result {flex: 1 0 10%; padding-top: 10px;}
	}
	</style>';


	$self->theme->navbar = '<nav class="nav -page">'.$navBar.'</nav>';


	$ret .= '<div id="result">';

	$ret .= '<header class="header -inno"><h3>รายชื่อ{tr:โครงการ}</h3><nav id="nav-viewmode" class="nav -page -sg-text-right" style="padding-right:16px;"><a class="btn -link" href="'.url('project/set/'.$tpid, array('mode'=>'default')).'" data-mode="default" title="View in List Mode"><i class="icon -material">view_list</i></a> <a class="btn -link" href="'.url('project/set/'.$tpid,array('mode'=>'card')).'" data-mode="card" title="View in Card Mode"><i class="icon -material">view_stream</i></a> <a class="btn -link" href="'.url('project/set/'.$tpid,array('mode'=>'map')).'" data-mode="map" title="View in Map Mode"><i class="icon -material">room</i></a></nav></header>';

	$joinList = array();

	mydb::where('t.`parent` = :coset', ':coset', $tpid);

	if ($getOrg) {
		mydb::where('( t.`orgid` IN ( :org ) OR o.`parent` IN ( :org ) )', ':org', 'SET:'.$getOrg);
	}

	if ($getYear) {
		mydb::where('p.`pryear` = :pryear', ':pryear', $getYear);
	}

	if ($getArea) {
		mydb::where('t.`changwat` IN ( :changwat )', ':changwat', 'SET:'.$getArea);
		$joinList[] = 'LEFT JOIN %project_prov% pv ON pv.`tpid` = p.`tpid` AND pv.`tagname` = "info"';
	}

	$getMainIssueList = array();
	if ($getMainIssue) {
		foreach (explode(',', $getMainIssue) as $value) {
			$getMainIssueList[] = 'mainissue-'.$value;
		}
		mydb::where('m.`fldname` IN ( :mainIssuelist )', ':mainIssuelist', 'SET-STRING:'.implode(',', $getMainIssueList));
		$joinList[] = '-- Search main issue';
		$joinList[] = 'LEFT JOIN %bigdata% m ON m.`keyname` = "project.info" AND m.`keyid` = p.`tpid` AND m.`fldname` LIKE "mainissue-%"';
		$joinList[] = 'LEFT JOIN %tag% ctm ON ctm.`taggroup` = "project:mainissue" AND ctm.`catid` = m.`flddata`';
	}

	$getIssueList = array();
	if ($getIssue) {
		foreach (explode(',', $getIssue) as $value) {
			$getIssueList[] = 'category-'.$value;
		}
		mydb::where('c.`fldname` IN ( :issuelist )', ':issuelist', 'SET-STRING:'.implode(',', $getIssueList));
		$joinList[] = '-- Search category';
		$joinList[] = 'LEFT JOIN %bigdata% c ON c.`keyname` = "project.info" AND c.`keyid` = p.`tpid` AND c.`fldname` LIKE "category-%"';
		$joinList[] = 'LEFT JOIN %tag% ctg ON ctg.`taggroup` = "project:issue" AND ctg.`catid` = c.`flddata`';
	}

	if ($getInno) {
		mydb::where('n.`fldref` IN ( :inno )', ':inno', 'SET:'.$getInno);
		$joinList[] = 'LEFT JOIN %bigdata% n ON n.`keyname` = "project.info" AND n.`keyid` = p.`tpid` AND n.`fldname` = "inno"';
	}

	if ($getTag) {
		mydb::where('tg.`flddata` IN ( :tag )', ':tag', 'SET-STRING:'.$getTag);
		$joinList[] = '-- Search tag';
		$joinList[] = 'LEFT JOIN %bigdata% tg ON tg.`keyname` = "project.info" AND tg.`keyid` = p.`tpid` AND tg.`fldname` = "tag"';
	}

	if ($getValue) {
		mydb::where('SUBSTR(sv.`part`,3,1) IN ( :value )', ':value', 'SET-STRING:'.$getValue);
		$joinList[] = '-- Search valuation';
		$joinList[] = 'LEFT JOIN %project_tr% sv ON sv.`tpid` = p.`tpid` AND sv.`formid` = "valuation" AND `rate1` = 1';
	}

	if ($getSearch) {
		mydb::where('t.`title` LIKE :searchText', ':searchText', '%'.$getSearch.'%');
	}

	mydb::value('$JOIN$', implode(_NL, $joinList), false);

	$stmt = 'SELECT
		a.*
		, o.`name` `orgName`
		, u.`username`, u.`name` `posterName`
		, r.`body`
		, ( SELECT GROUP_CONCAT(`file`) FROM %topic_files% f WHERE f.`tpid` = a.`tpid` AND `type` = "photo" ) `photos`
		, ( SELECT COUNT(*) FROM %bigdata% i WHERE i.`keyname` = "project.info" AND i.`keyid` = a.`tpid` AND i.`fldname` = "inno" ) `totalInno`
		--	, GROUP_CONCAT(DISTINCT ctg.`name`) `categoryName`
		, ( SELECT GROUP_CONCAT(DISTINCT CONCAT("จังหวัด", cop.`provname`) SEPARATOR ", ") FROM %project_prov% pv LEFT JOIN %co_province% cop ON cop.`provid` = pv.`changwat` WHERE `tpid` = a.`tpid` ) `changwatName`
		, ( SELECT GROUP_CONCAT(X(`location`), " ", Y(`location`)) FROM %project_prov% WHERE `tpid` = a.`tpid` ) `locations`
		FROM (
			SELECT
				t.`tpid`, t.`uid`, t.`title`, t.`orgid`
				, t.`created`
				, p.`pryear`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %db_org% o USING(`orgid`)
				$JOIN$
			%WHERE%
			GROUP BY `tpid`
		) a
			LEFT JOIN %users% u USING (`uid`)
			LEFT JOIN %topic_revisions% r USING(`tpid`)
			LEFT JOIN %db_org% o USING(`orgid`)
		ORDER BY `tpid` DESC
		';

	$dbs = mydb::select($stmt);

	//$ret .= htmlview(mydb()->_query);
	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o(post(),'post()');

	/*
	mydb::where('t.`parent` = :coset', ':coset', $tpid);
	if ($getIssue) mydb::where('c.`fldname` = :issue', ':issue', 'category-'.$getIssue);
	if ($getInno) mydb::where('n.`fldref` = :inno', ':inno', $getInno);

	$stmt = 'SELECT
		a.`tpid`, a.`title`, a.`pryear`, a.*
		, (SELECT COUNT(*) FROM %bigdata% i WHERE i.`keyname` = "project.info" AND i.`keyid` = a.`tpid` AND i.`fldname` = "inno") `totalInno`
		FROM (
			SELECT t.`tpid`, t.`title`, p.`pryear`, c.*
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %bigdata% c ON c.`keyname` = "project.info" AND c.`keyid` = p.`tpid` AND (c.`fldname` LIKE "category-%" OR c.`fldname` = "inno")
			%WHERE%
		--	GROUP BY `tpid`
		) a
		ORDER BY `tpid` DESC
		';

	$dbs = mydb::select($stmt);
	$ret .= mydb()->_query;
	$ret .= print_o($dbs,'$dbs');
	*/

	$cardUi = new Ui('div', 'ui-card -inno-list');

	$tables = new Table();
	$tables->addClass('-inno-list');
	$tables->thead = array('date -nowrap'=>'ปี พ.ศ.', 'title -fill' => 'ชื่อ{tr:โครงการ}');

	foreach ($dbs->items as $rs) {
		$cardStr = '';

		$cardStr .= '<div class="header">'
				. '<span>'
				. '<a class="sg-action" href="'.url('project/'.$tpid.'/info.u/'.$rs->uid).'" data-rel="box" data-width="640">'
				. '<img class="poster-photo" src="'.model::user_photo($rs->username).'" width="32" height="32" alt="" />'
				. '<span class="poster-name">'.$rs->posterName.'</a> '
				. '</span>'
				. '<span class="timestamp"> เมื่อ '.sg_date($rs->created,'ว ดด ปปปป H:i').' น.</span>'
				// . '<nav class="nav -header -sg-text-right">'.$headerUi->build().'</nav>'
				. '</div><!-- header -->';


		$photoCard = new Ui(NULL, 'ui-album');
		if ($rs->photos) {
			foreach (explode(',',$rs->photos) as $idx=>$photoFile) {
				if ($idx > 2) break;
				$photoInfo = model::get_photo_property($photoFile);
				$photoCard->add('<a href="'.url('project/'.$rs->tpid).'" target="_blank"><img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" width="200" /></a>');
				//$ret .= print_o($photoInfo,'$photoInfo');
			}
		}

		$cardStr .= '<div class="detail -sg-flex">'
			. ($photoCard->count() ? $photoCard->build() : '')
			//. $rs->photos
			. '<a href="'.url('project/'.$rs->tpid).'"><span class="org">'.$rs->orgName.'</span><span class="title">'.$rs->title.' (ปี '.($rs->pryear+543).')</span>'
			. ($rs->categoryName ? '<span class="issue">'.$rs->categoryName.'</span>' : '')
			. nl2br(strip_tags($rs->body))
			. '</a>'
			. '</div>';

		$cardUi->add($cardStr);

		$tables->rows[] = array(
			$rs->pryear + 543,
			'<a href="'.url('project/'.$rs->tpid).'">'.SG\getFirst($rs->title,'???').'</a><span>'.$rs->orgName.($rs->changwatName ? ' พื้นที่ดำเนินการ '.$rs->changwatName : '').'</span>',
		);
	}

	if ($viewMode == 'map') {
		$ret .= __project_set_view_map($dbs);
	} else if ($viewMode == 'card') {
		$ret .= $cardUi->build();
	} else {
		$ret .= $tables->build();
	}

	//$ret .= print_o($dbs);


	$isCreateProject = user_access('create project content')
		&&  in_array('my/project', explode(',', cfg('PROJECT.PROJECT.ADD_FROM_PAGE')));

	if (1 || $isCreateProject) {
		$ret .= '<nav class="nav btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/create/'.$tpid, array('abtest'=>'float','rel' => 'box','startyear'=>date('Y')-5, 'signret' => i()->ok ? NULL : 'project/set/'.$tpid)).'" data-rel="box" data-width="640" title="เพิ่มข้อมูลใหม่"><i class="icon -addbig -white"></i></a></nav>';
	}

	$ret .= '<script>
	$("#nav-viewmode a").click(function(){
		$("#edit-mode").val($(this).data("mode"))
		$("#project-set-search").submit()
		return false
	})
	</script>
	<style>
	.item.-inno-list td>span {display: block; font-style: italic;}
	.map-card .detail {max-height: 140px; overflow: scroll;}
	</style>';

	//$ret .= print_o(post(),'post()');
	//$ret .= print_o($projectInfo, '$projectInfo');

	$ret .= '<!-- result --></div>';
	return $ret;
}

function __project_set_view_map($data) {
	$ret .= '<div id="map" class="app-output">กำลังโหลดแผนที่!!!!</div>'._NL;

	$locations = array();
	foreach ($data->items as $rs) {
		if (!$rs->locations) continue;
		foreach (explode(',', $rs->locations) as $item) {
			list($rs->lat,$rs->lng) = explode(' ', $item);
			$rs->lat = (double) $rs->lat;
			$rs->lng = (double) $rs->lng;

			unset($rs->photos);

			$locations[] = (Object) array(
				'title' => $rs->title,
				'content' => '<div class="map-card"><h3><a href="'.url('project/'.$rs->tpid).'" target="_blank">'.$rs->title.'</a></h3>'
					. '<p><em>'.$rs->orgName.'</em></p>'
					. '<div class="detail">'.nl2br($rs->body).'</div>'
					. '<nav class="nav -sg-text-right"><a class="btn -link" href="'.url('project/'.$rs->tpid).'" target="_blank"><i class="icon -material">find_in_page</i><span>รายละเอียด{tr:โครงการ}</span></a></nav></div>',
				'lat' => $rs->lat,
				'lng' => $rs->lng,
			);

		}
	}

	//$ret .= print_o($locations, '$locations');

	$ret .= '<script type="text/javascript">
		var mapType = "Pin Map"
		var map
		var markerCluster
		var markers
		var pinMarkers = {}
		var locations = '.json_encode($locations).';
		var infoWindow = null

		function initMap() {
			map = new google.maps.Map(document.getElementById("map"), {
				zoom: 6,
				center: {lat: 13.000, lng: 100.000}
			});
			infoWindow = new google.maps.InfoWindow()
			google.maps.event.addListener(map, "click", function(event) {
				infoWindow.close()
			});

			processMap()
		}

		function processMap() {

			markers = locations.map(function(location, i) {
				var marker = new google.maps.Marker({
					position: location,
					title : location.title,
					content : location.content,
					//label: location.title, //labels[i % labels.length]
				})

				return marker
			});

			// Add a marker clusterer to manage the markers.
			if (mapType == "Cluster Map") {
				markerCluster = new MarkerClusterer(map, markers,
					{
						imagePath: "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
						maxZoom : 9,
					});
			} else {
				$.each( markers, function(i, marker) {
						pinMarkers[i] = new google.maps.Marker({
							position: marker.position,
							map: map,
							title: marker.title
						});

						//var infoWindow = new google.maps.InfoWindow({
						//	content: marker.content
						//});

						pinMarkers[i].addListener("click", function() {
							if (infoWindow) infoWindow.close()
							infoWindow.setContent(marker.content)
							infoWindow.open(map, pinMarkers[i])

							//openInfoWindow = infoWindow.open(map, pinMarkers[i])
						});
				})
			}

		}


		$.getScript("https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js", function(data, textStatus, jqxhr) {
			loadGoogleMaps("initMap")
		})
	</script>';
	return $ret;
}
?>