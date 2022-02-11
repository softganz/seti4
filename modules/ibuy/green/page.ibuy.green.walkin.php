<?php
/**
* Project Action Person Join by Register, Invite or Walkin
* Created 2019-06-12
* Modify  2019-07-30
*
* @param Object $self
* @param Int $tpid
* @param Int $calId
* @param String $action
* @param Int $tranId
* @param _POST Array $reg
* @return String
*/

// TODO : Edit information by register using refcode

function ibuy_green_walkin($self, $action = NULL, $tranId = NULL) {
	$myToken = '023c3decd88cb056b2cde38c7f3cd62d';
	$keyName = 'ibuy.register';
	$fldName = 'hygreensmile';


	$isAdmin = is_admin();
	$isAuthRefCode = $_SESSION['auth.join.refcode'];
	$isProjectMember = $projectInfo->info->membershipType;
	$isAcces = $projectInfo->RIGHT & _IS_ACCESS;


	$tokenList = array(
		'023c3decd88cb056b2cde38c7f3cd62d',
		'260badea826c74f775069a0eb44c26e1',
		'xxxxx',
	);

	switch ($action) {

		case 'register':
			//header('Access-Control-Allow-Origin: *');
			//$headerResult = http_response_code(200);

			// Check multiple origin
			$http_origin = $_SERVER['HTTP_ORIGIN'];
			if (in_array($http_origin, array("http://localhost","https://communeinfo.com"))) {
				header("Access-Control-Allow-Origin: $http_origin");
			}

			header('SG-Access-Origin: '.$http_origin);

			$post = post();
			array_shift($post);

			$retData = new stdClass;
			$retData->origin = $http_origin;

			$token = $post['tk'];
			unset($post['tk']);

			if (in_array($token, $tokenList) && $post) {
				$postData->token = $token;
				$postData->data = (Object) $post;
				$postData->data->uid = i()->uid;
				$postData->data->name = i()->name;
				$postData->data->ip = getenv('REMOTE_ADDR');
				$data->keyname = $keyName;
				$data->keyid = mydb::select('SELECT MAX(`keyid`) `lastid` FROM %bigdata% WHERE `fldname` = :fldname LIMIT 1', ':fldname', $fldName)->lastid+1;
				$data->fldtype = 'json';
				$data->fldname = $fldName;
				do {
					$data->fldref = sg_generate_token(6);//strtoupper(substr(md5(uniqid(rand(), true)),0,10));
				} while (mydb::select('SELECT `fldref` FROM %bigdata% WHERE `keyname` = :keyname AND `fldref` = :fldref LIMIT 1', ':keyname', $keyName, ':fldref', $data->fldref)->fldref);

				$data->flddata = sg_json_encode($postData);
				$data->created = date('U');
				$stmt = 'INSERT INTO %bigdata%
					(`keyname`, `keyid`, `fldname`, `fldtype`, `fldref`, `flddata`, `created`)
					VALUES
					(:keyname, :keyid, :fldname, :fldtype, :fldref, :flddata, :created)
					';
				mydb::query($stmt, $data);
				$ret .= mydb()->_query;

				$retData->id = $calId;
				$retData->key = $data->fldref;
				$retData->created = sg_date($data->created,'Y-m-d H:i:s');
				$retData->data = $postData->data;
			} else {
				$retData->error = 'Invalid token or no data';
			}
			sendHeader('application/json');
			die(sg_json_encode($retData));
			//$ret .= print_o($data,'data');
			//$ret .= print_o($post,'post');
			break;



		case 'get':
			$retData = new stdClass;
			if (post('tk') && post('key')) {
				$stmt = 'SELECT * FROM %bigdata% WHERE `keyname` = "project.join.walkin" AND `fldname` = :fldname AND `fldref` = :fldref LIMIT 1';
				$rs = mydb::select($stmt, ':fldname', $calId, ':fldref', post('key'));
				if ($rs->_num_rows) {
					$data = json_decode($rs->flddata);
					if ($data->token == post('tk')) $retData->data = $data;
					//$ret .= print_o($retData,'$retData');
					//$ret .= print_o($rs,'$rs');
				}
			} else {
				$retData->error = 'Not found';
			}
			sendHeader('application/json');
			die(sg_json_encode($retData));
			break;



		case 'list':
			//$ret .= sg_generate_token(32);
			//$ret .= '<nav class="nav -page"><a class="sg-action btn" href="'.url('project/join/walkin/'.$calId.'/form').'" data-rel="box" data-width="480">แบบฟอร์มทดสอบ</a> <a class="btn sg-action" href="'.url('project/join/walkin/'.$calId.'/qrcode').'" data-rel="box" data-width="480" data-height="400">Generate QR Code</a></nav>';
			if ($isAdmin || $isProjectMember) {
				$stmt = 'SELECT * FROM %bigdata% WHERE `keyname` = :keyname AND `fldname` = :fldname ORDER BY `bigid` DESC';
				$dbs = mydb::select($stmt, ':keyname', $keyName, ':fldname', $fldName);

				$ret .= '<p>จำนวนลงทะเบียน <b>'.$dbs->count().'</b> ราย</p>';
				$tables = new Table();
				$tables->thead = array('Key','Data','Created');
				foreach ($dbs->items as $rs) {
					$data = SG\json_decode($rs->flddata)->data;
					$tables->rows[] = array($rs->fldref, SG\getFirst($data->name,'ไม่ระบุ').'<br />('.$data->ip.')', sg_date($rs->created,'Y-m-d H:i:s'));
				}
				$ret .= $tables->build();
			}
			break;



		case 'qrcode':
			$ret .= '<header class="header -box"><h3>Generate QR Code</h3></header>';
			$form = new Form(NULL, url('project/join/walkin/'.$calId.'/qrcode'),NULL,'sg-form');
			$form->addData('rel','box');
			$form->addField('url',array('type'=>'text','label'=>'Website','class'=>'-fill','value'=>htmlspecialchars(post('url')),'placeholder'=>'eg. https://www.example.com/path'));
			$form->addField('gen',array('type'=>'button','value'=>'Generate QR Code','container'=>'{class: "-sg-text-right"}'));
			$ret .= $form->build();

			if (post('url')) {
				$linkUrl = url('project/join/walkin/'.$calId.'/form');
				$qrcodeUrl=_DOMAIN.urlencode($linkUrl);

				$qrcodeUrl = post('url');
				$ret .= '<img class="qrcode" src="https://chart.googleapis.com/chart?cht=qr&chl='.$qrcodeUrl.'&chs=160x160&choe=UTF-8&chld=L|2" alt="" style="display: block; margin:0 auto;">';
				$ret .= '<p class="-sg-text-center">'.post('url').'</p>';
			}
			//$ret .= print_o($_POST);
			break;



		case 'form':
			$ret .= '<header class="header -box"><h3>ลงทะเบียน WALK IN</h3></header>';
			$form = new Form(NULL, url('project/join/walkin/'.$calId.'/register'),NULL,'sg-form');
			//$form = new Form(NULL, 'http://hsmi2.psu.ac.th/scac/project/join/walkin/'.$calId.'/register',NULL,'sg-form');
			$form->addData('checkValid',true);
			$form->addData('rel','console');
			//$form->addData('data-type','json');
			$form->addData('done','notify: บันทึกเรียบร้อย | load:#main:'.url('project/join/walkin/'.$calId.'/list').' | load->replace:this:'.url('project/join/walkin/'.$calId.'/form'));
			$form->addField('tk',array('type'=>'hidden','value'=>$myToken));
			$form->addField(
				'name',
				array(
					'type'=>'text',
					'label'=>'ชื่อ',
					'class'=>'-fill',
					'require'=>true,
				)
			);
			$form->addField(
				'lname',
				array(
					'type'=>'text',
					'label'=>'นามสกุล',
					'class'=>'-fill',
					'require'=>true,
				)
			);
			$form->addField(
				'save',
				array(
					'type'=>'button',
					'value'=>'<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
					'container'=>'{class: "-sg-text-right"}',
				)
			);
			$ret .= $form->build();
			break;



		default:
			//if (empty($action)) $action='home';
			//$ret = R::Page('project.join.'.$action, $self, $projectInfo, $calendarInfo, $action, $tranId);

			if (empty($projectInfo)) $projectInfo = $tpid;
			if (empty($calendarInfo)) $calendarInfo = $calId;
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
								'project.join.walkin.'.$action,
								$self,
								$calendarInfo,
								func_get_arg($argIndex),
								func_get_arg($argIndex+1),
								func_get_arg($argIndex+2),
								func_get_arg($argIndex+3),
								func_get_arg($argIndex+4)
							);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			break;
	}

	//$ret .= print_o($doingInfo, '$doingInfo');
	//$ret .= print_o($projectInfo, '$projectInfo');
	//$ret .= print_o($calendarInfo, '$calendarInfo');

	return $ret;
}
?>