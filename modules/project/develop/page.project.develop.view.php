<?php
/**
 * Project Development View
 * @param Object $self
 * @param Int $tpid
 * @param String $action
 * @return String
 */

// TODO : เพิ่มเจ้าของโครงการพัฒนา (จากเดิมเพิ่มได้เฉพาะพี่เลี้ยง)

import('widget:project.like.status.php');

function project_develop_view($self, $tpid, $action = NULL, $tranId = NULL) {
	head('<meta name="robots" content="noindex,nofollow">');

	$devInfo = is_object($tpid) ? $tpid : R::Model('project.develop.get',$tpid);
	$tpid = $devInfo->tpid;

	$info = $devInfo->info;

	if ($info->topicStatus == _BLOCK && !user_access('administer contents,administer papers')) {
		header('HTTP/1.0 404 Not Found');
		head('<meta name="robots" content="noarchive" />');
		return message('error','This topic was blocked.');
	}

	//R::On('project.proposal.change',$tpid,'update',$ret);

	R::View('project.toolbar', $self, $info->title, 'develop', $devInfo);
	head('js.project.proposal.js','<script type="text/javascript" src="project/js.project.proposal.js"></script>');

	if (empty($info))
		return $ret.message('error','ขออภัย : ไม่มีโครงการที่กำลังพัฒนาอยู่ในระบบ');


	$isAdmin = $devInfo->RIGHT & _IS_ADMIN;
	$isTrainer = $devInfo->RIGHT & _IS_TRAINER;
	$isEditable = $devInfo->RIGHT & _IS_EDITABLE;
	$isFullView = $devInfo->RIGHT & _IS_RIGHT;

	$is_comment_sss = user_access('comment project');
	$is_comment_hsmi = user_access('administer papers,administer projects') || $isTrainer;


	R::Model('reaction.add', $tpid, 'TOPIC.VIEW');

	$ret .= (new ScrollView([
		'child' => new ProjectLikeStatusWidget([
			'action' => 'PDEV',
			'projectInfo' => $devInfo,
		]),
	]))->build();


	$isEdit = $action == 'edit' && $isEditable;

	if ($info->date_from) $info->date_from=sg_date($info->date_from,'d/m/Y');
	if ($info->date_end) $info->date_end=sg_date($info->date_end,'d/m/Y');



	$cfg['domain']=cfg('domain');
	$cfg['url']=cfg('url');
	$cfg['tpid']=$tpid;
	$cfg['prid']=$info->prid;
	$cfg['orgname']=$info->orgName;
	if ($isEdit) {
		$cfg['action']=$action?'/'.$action:'';
		$cfg['para-action']=$action;
	}
	$cfg['hidden']=$isEdit?'-show':'-hidden';


	// Get develop template file
	$devTemplate=cfg('template');
	$devTemplateFileName=SG\getFirst($info->template?'file.develop.'.$info->template.'.html':NULL,cfg('project.develop.file'),'file.develop.default.html');
	foreach (explode(';', cfg('template')) as $devTemplate) {
		$devFileName = dirname(__FILE__).'/../template/'.($devTemplate?'/'.$devTemplate:'').'/'.$devTemplateFileName;
		//$ret .= dirname(__FILE__).'<br />'.$devFileName.'<br />';
		if (file_exists($devFileName)) {
			break;
		}
		unset($devFileName);
	}
	if (empty($devFileName)) {
		$devFileName=dirname(__FILE__).'/'.$devTemplateFileName;
	}

	if (!file_exists($devFileName)) return $ret.message('error','ไม่สามารถเปิดแฟ้มข้อมูลพัฒนาโครงการได้');



	$body=file_get_contents($devFileName);

	$data=project_model::explode_body($info->body);

	foreach ($info as $key => $value) {
		if (substr($key, 0,1)=='_') continue;
		$data[$key]=$value;
	}
	$data['title']=$info->title;

	$stmt='SELECT SUM(`flddata`) rating FROM %bigdata% WHERE `keyid`=:tpid AND `keyname`="project.develop" AND `fldname` LIKE "rating-indicator-%" LIMIT 1';
	$cfg['ratingIndicator']=number_format(mydb::select($stmt,':tpid',$tpid)->rating);
	$cfg['ratingPercent']=number_format($cfg['ratingIndicator']*100/45,2);

	$stmt='SELECT `fldname`,`flddata` FROM %bigdata% WHERE `keyid`=:tpid AND `keyname`="project.develop"';
	foreach (project_model::get_develop_data($tpid) as $key=>$value) {
		$data[$key]=$value;
	}
	if ($action=='showdata') {
		$ret.='<div style="width:50%;float:left;"><p>'.str_replace("\n", '</p><p>', $info->body).'</p></div><div style="width:50%;float:left;">'.print_o($data,'$data').'</div>';
		return $ret;
	}

	preg_match_all('|(<span(.*?data-fld=\"([a-zA-Z0-9\-].*?)\".*?)>)(.*?)(</span>)|s',$body,$matchesField);
	//$ret.=print_o($matchesField,'$matches');

	foreach ($matchesField[3] as $keyField => $fieldName) {
		$fields[$fieldName]=sg_explode_attr($matchesField[2][$keyField]);
		if ($data[$fieldName]!='') {
			if ($fields[$fieldName]['type']=='textarea') {
			} else if ($fields[$fieldName]['ret']=='numeric') {
				$data[$fieldName]=number_format(preg_replace('/[^0-9\.\-]/','',$data[$fieldName]),2);
			}
			//$data[$fieldName]=nl2br($data[$fieldName]);
			//$ret.=$fieldName.'='.htmlspecialchars($data[$fieldName]).'<br />';
		}
	}

	$problemWeight=array();
	foreach ($data as $key => $value) {
		list($a,$b,$c,$d,$e)=explode('-',$key);
		if ($a=="commune" && $b=="problem" && $e=="weight") $problemWeight[$c][]=$value;
	}
	foreach ($problemWeight as $key => $value) {
		$weightTotal=1;
		foreach ($value as $wv) $weightTotal*=$wv;
		$data['commune-problem-'.$key.'-weight']=$weightTotal;
	}



	// Remove ข้อมูล่วนบุคค
	if (!$isFullView) {
		$data['name-leader-cid']='*************';
		$data['name-leader-address']='**';
		$data['name-leader-phone']='********';
		$data['name-leader-mobile']='********';
		$data['name-leader-fax']='********';
		$data['name-leader-email']='********';

		$data['owner-cid']='*************';
		$data['owner-address']='**';
		$data['owner-phone']='********';
		$data['owner-mobile']='********';
		$data['owner-fax']='********';
		$data['owner-email']='********';

		for ($i=1;$i<=5;$i++) {
			$data['coowner-'.$i.'-cid']='*************';
			$data['coowner-'.$i.'-address']='**';
			$data['coowner-'.$i.'-phone']='********';
			$data['coowner-'.$i.'-mobile']='********';
			$data['coowner-'.$i.'-fax']='********';
			$data['coowner-'.$i.'-email']='********';
		}
	}



	//$ret.=print_o($fields,'$fields');
	//$ret.=print_o($data,'$data');
	//$ret.=print_o($problemWeight,'$problemWeight');



	// Replace date into data-value
	$body=preg_replace_callback(
		'|(<span .*?data-fld=\"([a-zA-Z0-9\-].*?)\".*?)>(.*?)(</span>)|s',
		function($m) use ($data) {
			$value=$data[$m[2]]!=''?$data[$m[2]]:$m[3];
			return $m[1].(' data-value="'.htmlspecialchars($value).'"').'><span>'.nl2br($value).'</span>'.$m[4];
		},
		$body
	);

	$body=preg_replace_callback(
		'/(<input type=\"radio\".*?data-fld=\"([a-zA-Z0-9\-].*?)\".*? value=\"(.*?)\")(.*?)(\/>)/s',
		function($m) use ($data) {
			// $m[1]=เริ่มจาก <input .... value="..."
			// $m[2]=ชื่อฟิลด์
			// $m[3]=value
			// $m[4]=attribute หลัง value
			// $m[5]=/>
			$dataField=$m[2];
			$radioValue=$m[3];
			$dataValue=$data[$m[2]];
			//echo 'm1='.htmlspecialchars($m[1]).' m2='.$m[2].' m3="'.$m[3].'" m4='.htmlspecialchars($m[4]).' m5='.htmlspecialchars($m[5]).'<br />'._NL;
			$checked='';
			if (array_key_exists($dataField, $data) && $dataValue==$radioValue) {
				$checked=' checked="checked"';
			}
			//echo '$dataValue="'.$dataValue.'" radioValue="'.$radioValue.'" '.$checked.'<br />';
			return $m[1].$checked.$m[4].$m[5];
		},
		$body
	);

	$body=preg_replace_callback(
		'/(<input type=\"checkbox\".*?data-fld=\"([a-zA-Z0-9\-].*?)\".*? value=\"(.*?)\")(.*?)(\/>)/s',
		function($m) use ($data,$isEdit) {
			// $m[1]=เริ่มจาก <input .... value="..."
			// $m[2]=ชื่อฟิลด์
			// $m[3]=value
			// $m[4]=attribute หลัง value
			// $m[5]=/>
			$dataField=$m[2];
			$radioValue=$m[3];
			$dataValue=$data[$m[2]];
			//echo 'm1='.htmlspecialchars($m[1]).' m2='.$m[2].' m3="'.$m[3].'" m4='.htmlspecialchars($m[4]).' m5='.htmlspecialchars($m[5]).'<br />'._NL;
			$checked='';
			if (array_key_exists($dataField, $data) && $dataValue) {
				$checked=' checked="checked"';
			}
			if (!$isEdit) $checked.=' disabled="disabled"';
			//echo '$dataValue="'.$dataValue.'" radioValue="'.$radioValue.'" '.$checked.'<br />';
			return $m[1].$checked.$m[4].$m[5];
		},
		$body
	);

	for ($i=1; $i<=20; $i++) {
		if ($data['commune-problem-'.$i.'-title']==''
				&& $data['commune-problem-'.$i.'-size']==''
				&& $data['commune-problem-'.$i.'-violence']==''
				&& $data['commune-problem-'.$i.'-awareness']==''
				&& $data['commune-problem-'.$i.'-difficulty']=='') {
			$cfg['commune-problem-'.$i.'-class']='noprint';
		}

		if ($data['objective-'.$i.'-title']==''
				&& $data['objective-'.$i.'-indicators']==''
				&& $data['objective-'.$i.'-indicators-qu']=='') {
			$cfg['objective-'.$i.'-class']='noprint';
		}

		if ($data['plan-'.$i.'-objective']==''
				&& $data['plan-'.$i.'-indicator']==''
				&& $data['plan-'.$i.'-target']==''
				&& $data['plan-'.$i.'-activity']==''
				&& $data['plan-'.$i.'-period']==''
				&& $data['plan-'.$i.'-activity']==''
				&& $data['plan-'.$i.'-output']==''
				&& $data['plan-'.$i.'-budget']==''
				&& $data['plan-'.$i.'-parties']==''
				) {
			$cfg['plan-'.$i.'-class']='noprint';
		}
	}

	$cfg['planhidden']=post('showplan')?'show':'hidden';
	$cdate=date('Y-m-d H:i:s');

	if ($isEdit) $cfg['datainput']='inline-edit-field'; else $cfg['datainput']='datainput-disable';
	if ($is_comment_hsmi) $cfg['comment-hsmi-input']='inline-edit-field';
	if ($is_comment_sss) $cfg['comment-sss-input']='inline-edit-field';
	$cfg['css-print']= post('o')=='word' ? '' : '@media print {';
	$cfg['css-print-end']= post('o')=='word' ? '' : '}';

	$cfg['historyurl']=url('project/history');//,array('tpid'=>$tpid,'k'=>'tr,info,mainact,detail1,'.$mainact->trid));
	$cfg['removeOnNoEdit']=$isEdit?'':'yes';

	$body=preg_replace_callback('|(\{\$([a-zA-Z0-9\-].*?)\})|',
		function($m) use ($cfg) {
		//	echo $m[1];
			return $cfg[$m[2]];
		},
		$body
	);

	// Remove link with attribute data-remove="yes"
	$body=preg_replace('/<a.+?data-remove=\"yes\".+?>.+?<\/a>/i', "", $body);



	if ($isEdit || $is_comment_sss || $is_comment_hsmi) {
		head('<script>var tpid='.$tpid.'</script>');

		$inlinePara['class'] = 'sg-inline-edit';
		$inlinePara['data-tpid'] = $tpid;
		$inlinePara['data-update-url'] = url('project/develop/update/'.$tpid);
		if (debug('inline')) $inlinePara['data-debug']='inline';
	}

	foreach ($inlinePara as $k => $v) {
		$inlineStr.=$k.'="'.$v.'" ';
	}

	// move style tag to head section
	$styleText = '';
	if (preg_match_all('/<style.*?>.*?<\/style>/si',$body,$out)) {
		foreach ($out[0] as $style) $styleText .= $style._NL;
		$body = preg_replace('/(<style.*?>.*?<\/style>)/si','',$body);
	}

	if (post('a')=='download') {
		sendheader('application/octet-stream');
		header('Content-Disposition: attachment; filename="'.$info->title.'.doc"');
	}
	if (post('o')=='word') {
		$ret='<HTML>
		<HEAD>
		<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=utf-8">
		<TITLE>'.$info->title.'</TITLE>
		'.$styleText.'
		</HEAD>
		<BODY>
		'.$body.'
		</BODY>
		</HTML>';
		cfg('Content-Type','text/xml');
		return $ret;
	}

	head($styleText);


	$ret.='<div id="project-develop" '.$inlineStr.'>'._NL;
	$ret.=$body;
	$ret.='</div>';


	if ($isViewOnly) {
		// Do nothing
	} else if ($isEdit) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('project/develop/'.$tpid,array('debug'=>post('debug'))).'" '.($action == 'edit' ? '' : 'data-rel="#main"').'><i class="icon -save -white"></i></a></div>';
	} else if ($isEditable) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/develop/'.$tpid.'/view/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
	}

	//$ret.=print_o($info,'$info');
	//$ret.=print_o($devInfo,'$devInfo');
	return $ret;
}
?>