<?php
/**
* Project :: Follow Area Information
* Created 2022-01-06
* Modify  2022-02-06
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.area
*/

import('widget:project.follow.nav.php');

class ProjectInfoArea extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		$projectInfo = $this->projectInfo;
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;// && $action == 'edit';

		$options = (Object) [
			'multipleArea' => true,
		];


		//$ret.=print_o($projectInfo,'$projectInfo');

		$provinceAreaList[0] = 'ทั้งประเทศ';
		$provinceAreaList[1] = 'ภาคกลาง';
		$provinceAreaList[3] = 'ภาคตะวันออกเฉียงเหนือ';
		$provinceAreaList[5] = 'ภาคเหนือ';
		$provinceAreaList[8] = 'ภาคใต้';

		if ($isEdit) {
			$ret.='<form class="sg-form -area" method="post" action="'.url('project/'.$this->projectId.'/info/area.save').'" data-rel="notify" data-done="load->replace:#project-info-area:'.url('project/'.$this->projectId.'/info.area').'">';
		}

		$provinceOptions = '';
		$provinceOptions .= '<optgroup label="----------"></optgroup>';
		$provinceOptions .= '<option value="0">++ ทั้งประเทศ</option>';
		$provinceOptions .= '<optgroup label="ระดับภาค">';
		$provinceOptions .= '<option value="1">++ ภาคกลาง</option>';
		$provinceOptions .= '<option value="3">++ ภาคตะวันออกเฉียงเหนือ</option>';
		$provinceOptions .= '<option value="5">++ ภาคเหนือ</option>';
		$provinceOptions .= '<option value="8">++ ภาคใต้</option>';
		$provinceOptions .= '</optgroup>';
		$provinceOptions .= '<optgroup label="ระดับจังหวัด/อำเภอ/ตำบล">';

		$stmt ='SELECT * FROM %co_province% ORDER BY CONVERT(`provname` USING tis620) ASC';

		$dbs = mydb::select($stmt);

		foreach ($dbs->items as $rs) {
			$provinceOptions .= '<option value="'.$rs->provid.'">'.$rs->provname.'</option>';
		}
		$provinceOptions .= '</optgroup>';

		$areaTypeOptions = '';
		$areaTypeOptionsList = array('ในเมือง','ชนบท','ชานเมือง','พื้นที่เฉพาะ:ลุ่มน้ำ','พื้นที่เฉพาะ:ชายแดน','พื้นที่เฉพาะ:พื้นที่สูง','พื้นที่เฉพาะ:ชุมชนแออัด','อื่น ๆ');
		foreach ($areaTypeOptionsList as $item) {
			$areaTypeOptions .= '<option value="'.$item.'">'.$item.'</option>';
		}


		$tables = new Table();
		$tables->addClass('project-info-area-item');
		$tables->thead=array('จังหวัด','อำเภอ','ตำบล','area'=>'ลักษณะพื้นที่','a -icons -c4 -nowrap -hover-parent'=>'');

		if ($options->multipleArea) {
			$stmt = 'SELECT
				  p.*
				, c.`provname` `changwatName`
				, d.`distname` `ampurName`
				, s.`subdistname` `tambonName`
				, AsText(p.`location`) location, X(p.`location`) lat, Y(p.`location`) lnt
				FROM %project_prov% p
					LEFT JOIN %co_province% c ON c.`provid` = p.`changwat`
					LEFT JOIN %co_district% d ON d.`distid` = CONCAT(p.`changwat`,p.`ampur`)
					LEFT JOIN %co_subdistrict% s ON s.`subdistid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
				WHERE `tpid` = :tpid AND `tagname` = :tagname';

			$dbs = mydb::select($stmt,':tpid',$this->projectId, ':tagname', _PROJECT_TAGNAME);


			foreach ($dbs->items as $rs) {
				$ui = new Ui();
				if ($isEdit) {
					$ui->add('<a class="sg-action" href="'.url('project/'.$this->projectId.'/info/area.delete/'.$rs->autoid).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบพื้นที่" data-confirm="ต้องการลบพื้นที่ดำเนินการ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');
				}

				$menu = '<nav class="nav -icons -hover -no-print">'.$ui->build().'</nav>';

				$row = array(
					SG\getFirst($rs->changwatName,$provinceAreaList[$rs->changwat]),
					$rs->ampurName,
					$rs->tambonName,
					$rs->areatype,
					'<a id="project-info-area-pin-link-'.$rs->autoid.'" class="sg-action" href="'.url('project/'.$this->projectId.'/info.map/'.$rs->autoid).'" data-rel="box" data-width="640" data-class-name="-map"><i class="icon -material '.($rs->location ? '-green' : '-gray').'">place</i></a> '
					. '<a href="https://maps.google.com/maps?daddr='.$rs->lat.','.$rs->lnt.'" target="_blank"><i class="icon -material -gray">directions</i></a>'.($isEdit ? ' <a class="btn -link"></a> <a class="btn -link"></a>' : '')
					. $menu
				);

				$tables->rows[] = $row;
			}

			if ($isEdit) {
				$tables->rows[] = array(
					'<div class="form-item"><select id="changwat" class="form-select -fill -showbtn sg-changwat" name="changwat"><option value="">** เลือกจังหวัด **</option>'.$provinceOptions.'</select></div>',
					'<div class="form-item" style="display:none;"><select id="ampur" class="form-select -fill -hidden sg-ampur" name="ampur" style="display:none;"><option value="">** เลือกอำเภอ **</option></select></div>',
					'<div class="form-item" style="display:none;"><select id="tambon" class="form-select -fill sg-tambon -hidden" name="tambon" style="display:none;"><option value="">** เลือกตำบล **</option></select><select id="village" class="form-select -hidden" style="display:none;"></select></div>',
					'<div class="form-item" style="display:none;"><select class="form-select -fill" name="areatype"><option value="">** เลือกลักษณะพื้นที่ **</option>'.$areaTypeOptions.'</select></div>',
					'<div class="form-item -sg-text-right" style="display:none;"><button class="btn -link -nowrap" type="submit" title="เพิ่มพื้นที่"><i class="icon -material">add_circle_outline</i><span class="-hidden">เพิ่มพื้นที่</span></button></div>',
					'config' => array('class' => '-no-print'),
					);
			}


		} else {

			if ($isEdit) {
				$tables->rows[] = array(
					'<div class="form-item"><select id="changwat" class="form-select -fill -showbtn sg-changwat" name="changwat"><option value="">** เลือกจังหวัด **</option>'.$provinceOptions.'</select></div>',
					'<div class="form-item" style="display:none;"><select id="ampur" class="form-select -fill -hidden sg-ampur" name="ampur" style="display:none;"><option value="">** เลือกอำเภอ **</option></select></div>',
					'<div class="form-item" style="display:none;"><select id="tambon" class="form-select -fill sg-tambon -hidden" name="tambon" style="display:none;"><option value="">** เลือกตำบล **</option></select><select id="village" class="form-select -hidden" style="display:none;"></select></div>',
					'<div class="form-item" style="display:none;"><select class="form-select -fill" name="areatype"><option value="">** เลือกลักษณะพื้นที่ **</option>'.$areaTypeOptions.'</select></div>'
					,'<div class="form-item -sg-text-right" style="display:none;"><button class="btn -link -nowrap" type="submit" title="เพิ่มพื้นที่"><i class="icon -material">done</i><span class="-hidden">บันทึกพื้นที่</span></button></div>',
					'config' => array('class' => '-no-print')
					);
			}

		}

		$ret .= $tables->build();


		if ($isEdit) $ret .= '</form>';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
				'navigator' => new ProjectFollowNavWidget($this->projectInfo),
			]),
			'body' => new Container([
				'id' => 'project-info-area',
				'class' => 'project-info-area',
				'children' => [
					$ret,

					$this->_script(),
				], // children
			]), // Container
		]);
	}

	function _script() {
		return '<style type="text/css">
		.project-info-area-item td {width: 25%;}
		</style>';
	}
}
?>