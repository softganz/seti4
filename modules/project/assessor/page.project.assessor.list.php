<?php
/**
 * Assessor List
 *
 * @return String
 */
function project_assessor_list($self) {
	R::View('project.toolbar',$self,'เครือข่ายนักติดตามประเมินผล','assessor');

	$provinceSelect = SG\getFirst(post('pv'),NULL);
	$ampurSelect = SG\getFirst(post('am'), NULL);


	$isAdmin = user_access('access administrator pages');

	$navBar = '<nav class="nav -page">';

	$form = new Form(NULL, url('project/assessor/list'),'project-assessor-nav');
	$form->addConfig('class', '-inlineitem');
	$form->addConfig('method', 'GET');


	$provList = array('' => '== ทุกจังหวัด ==');
	mydb::where('f.`changwat`!=""');
	$stmt = 'SELECT f.*
					FROM %people% f
					%WHERE%
					ORDER BY CONVERT(`namechangwat` USING tis620)';

	$stmt = 'SELECT
					  g.*
					, copv.`provid` `changwat`
					, copv.`provname` `changwatName`
					FROM %person_group% g
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %co_province% copv ON p.`changwat` = copv.`provid`
					WHERE `groupname` = "assessor" AND `provname` != "" AND `status` > 0
					ORDER BY CONVERT(`changwatName` USING tis620) ASC
					';

	$provDbs = mydb::select($stmt);

	foreach ($provDbs->items as $item) {
		$provList[$item->changwat] = $item->changwatName;
	}
	$form->addField(
					'pv',
					array(
						'type' => 'select',
						'options' => $provList,
						'value' => $provinceSelect,
					)
				);

	if ($provinceSelect) {
		$ampurList = array('' => '== ทุกอำเภอ ==');
		foreach ($dbs=mydb::select('SELECT SUBSTRING(`distid`,3,2) `ampurId`, `distname` `ampurName` FROM %co_district% WHERE LEFT(`distid`,2) = :changwat ORDER BY CONVERT(`ampurName` USING tis620)', ':changwat', $provinceSelect)->items as $item) {
			$ampurList[$item->ampurId] = $item->ampurName;
		}
		//$ret .= print_o($dbs);
		$form->addField(
						'am',
						array(
							'type' => 'select',
							'options' => $ampurList,
							'value' => $ampurSelect,
						)
					);
	}

	$form->addField('go', array('type' => 'button', 'value' => '<i class="icon -search -white"></i><span>GO</span>'));
	$navBar .= $form->build();
	$navBar .= '</nav>';

	$ret .= $navBar;




	mydb::where('g.`groupname` = "assessor"');
	if (post('show') != 'all') mydb::where('g.`status` > 0');
	if ($provinceSelect) mydb::where('p.`changwat` = :changwat', ':changwat',$provinceSelect);
	if ($ampurSelect) mydb::where('p.`ampur` = :ampur', ':ampur',$ampurSelect);
	$stmt='SELECT
					  g.*
					, p.*
					, g.`uid`
					, CONCAT(p.`name`," ",p.`lname`) `fullname`
					, u.`username`
					, cosub.`subdistname` `tambonName`
					, codist.`distname` `ampurName`
					, copv.`provname` `changwatName`
					FROM %person_group% g
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %users% u ON u.`uid`=g.`uid`
						LEFT JOIN %co_district% codist ON codist.distid=CONCAT(p.changwat,p.ampur)
						LEFT JOIN %co_subdistrict% cosub ON cosub.subdistid=CONCAT(p.changwat,p.ampur,p.tambon)
						LEFT JOIN %co_village% covi ON covi.villid=CONCAT(p.changwat,p.ampur,p.tambon,IF(LENGTH(p.village)=1,CONCAT("0",p.village),p.village))
						LEFT JOIN %co_province% copv ON p.changwat=copv.provid
					%WHERE%
					ORDER BY CONVERT(`fullname` USING tis620) ASC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('name'=>'ชื่อ-นามสกุล','center -tambon'=>'ตำบล','center -ampur'=>'อำเภอ','center -changwat -hover-parent'=>'จังหวัด');
	foreach ($dbs->items as $rs) {
		$ui = new Ui();
		if ($isAdmin) {
			$ui->add('<a class="sg-action" href="'.url('project/assessor/'.$rs->uid.'/cancel').'" data-rel="refresh"><i class="icon '.($rs->status>0 ? '-cancel' : '-save').'"></i></a>');
		}
		$ui->add('<a href="'.url('project/assessor/'.$rs->uid).'"><i class="icon -viewdoc"></i></a>');
		$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';

		$tables->rows[]=array(
											'<a href="'.url('project/assessor/'.$rs->uid).'">'
											.'<img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="64" height="64" alt="" title="" />'
											.trim($rs->name.' '.$rs->lname)
											.'</a>',
											$rs->tambonName,
											$rs->ampurName,
											$rs->changwatName
											.$menu,
											'config' => array('class'=>'status -status-'.$rs->status),
											);
	}
	$ret.=$tables->build();

	$ret.='<p>ทั้งหมด '.$dbs->_num_rows.' คน</p>';
	//$ret.=print_o($dbs,'$dbs');
	head(
		'<style type="text/css">
		.col-name {font-size:1.6em;font-family:Mitr;}
		.row.status.-status-0 {text-decoration: line-through; color: gray; }
		</style>');
	return $ret;
}
?>