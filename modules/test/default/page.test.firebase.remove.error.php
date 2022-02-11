<?php
/**
* Module :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class TestFirebaseRemoveError extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		if (post('get')) return $this->_str();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Title',
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'class' => 'sg-form',
						'action' => url('test/firebase/remove/error', ['get' => 'yes']),
						'rel' => 'none',
						'children' => [
							'text' => ['type' => 'text', 'label' => 'Enter text'],
							'save' => ['type' => 'button', 'value' => '<span>SAVE</span>'],
						],
					]),
					$this->_script(),
				],
			]),
		]);
	}

	function _script() {

	}

	function _str() {
		return '
<html><head>
<meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\">
<title>500 Server Error</title>
</head>
<body text=#000000 bgcolor=#ffffff>
<h1>Error: Server Error</h1>
<h2>The server encountered an error and could not complete your request.<p>Please try again in 30 seconds.</h2>
<h2></h2>
</body></html>
{
    "qtRef": 19578,
    "uid": 2,
    "qtdate": "2021-09-05",
    "qtgroup": "3",
    "qtform": "SMIV",
    "orgId": null,
    "psnId": 81858,
    "seqId": 45224,
    "value": null,
    "data": "{}",
    "collectname": "Momo",
    "created": "1630833091",
    "tpid": null,
    "qtstatus": 1,
    "updateCollectname": "Momo",
    "updateValue": null,
    "updateData": "{}",
    "msg": "INSERT INTO `sgz_qtmast`\n  (`qtRef`, `qtgroup`, `qtform`, `psnId`, `tpid`, `orgId`, `uid`, `seq`, `qtdate`, `qtstatus`, `collectname`, `value`, `data`, `created`)\n  VALUES\n  (NULL, \"3\", \"SMIV\", 81858, NULL, NULL, 2, 45224, \"2021-09-05\", 1, \"Momo\", NULL, \"{}\", \"1630833091\")\n  ON DUPLICATE KEY UPDATE\n  `collectname` = \"Momo\"\n  , `value` = NULL\n  , `data` = \"{}\"\n  , `qtdate` = \"2021-09-05\"; <font color=\"green\">-- in <b>3.639<\/b> ms.<\/font> <strong>1<\/strong> affected rows<br \/>\r\n"
}';
	}
}
?>