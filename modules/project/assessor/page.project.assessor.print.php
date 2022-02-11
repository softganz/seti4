<?php
/**
 * Assessor List
 *
 * @return String
 */
function project_assessor_print($self) {
	R::View('project.toolbar',$self,'เครือข่ายนักติดตามประเมินผล','assessor');

	$stmt='SELECT
					g.*
					, p.*
					, g.`uid`
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
					WHERE `groupname`="assessor"
				--	LIMIT 10';
	$dbs=mydb::select($stmt);

	foreach ($dbs->items as $rs) {
		$psnInfo=R::Model('person.get',$rs->psnid);
		$ret.='<div class="-forprint">';

		$stmt='SELECT * FROM %topic_files% WHERE `refid`=:psnid AND `tagname`="assessor" ORDER BY `fid` ASC LIMIT 1';
		$prs=mydb::select($stmt,':psnid',$rs->psnid);
		//$ret.=print_o($prs,'$prs');

		// Show photos
		if ($prs->_num_rows && $prs->type=='photo') {
			$photo=model::get_photo_property($prs->file);
			$ret.='<img class="photoitem -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo" width="100%" ';
			$ret.=' />';
		}

		$ret.='<h2>'.$rs->prename.' '.$rs->name.' '.$rs->lname.'</h2>';
		$ret.=R::View('project.assessor.info',$psnInfo,'print');
		$ret.='</div>';
		$ret.='<hr class="pagebreak" />';
	}
	//$ret.=print_o($dbs,'$dbs');
	head(
		'<style type="text/css">
		.col-name {font-size:1.6em;font-family:Mitr;}
		.page.-main h2 {text-align:center;}
		.card.-photo.-assessor {list-style-type:none; margin:0;padding:0; text-align:center; display: none;}
		.card.-photo.-assessor {display: none;}
		.page.-main .photoitem {display:block;height:2.5cm;width:auto; margin:0 auto;}
		@media print {
			.page.-main h4 {padding: 8px; margin:0; background: #fff;}
			.item>tbody>tr>td {border:none; padding:2px 4px;}
			.item>thead>tr>th {border:none;}
		}
		</style>');
	return $ret;
}
?>