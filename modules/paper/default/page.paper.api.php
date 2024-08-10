<?php
/**
* Paper   :: Call Paper API that save in api field of topic revision
* Created :: 2024-08-10
* Modify  :: 2024-08-10
* Version :: 1
*
* @param String $nodeId
* @param String $apiMethod
* @return Widget
*
* @usage paer/{nodeId}/api/{apiMethod}
*/

use Softganz\DB;

class PaperApi extends Page {
	var $nodeId;
	var $apiClassName;
	var $apiMethod;
	var $modelClassName;
	var $nodeInfo;

	function __construct($nodeInfo = NULL, $apiMethod = NULL) {
		parent::__construct([
			'nodeId' => $nodeInfo->nodeId,
			'apiMethod' => preg_replace_callback('/\.(\w)/', function($matches) {return strtoupper($matches[1]);}, $apiMethod), // Change .\w to uppercase
			'apiClassName' => 'Paper'.$nodeInfo->nodeId.'Api',
			'modelClassName' => 'Paper'.$nodeInfo->nodeId.'Model',
			'nodeInfo' => $nodeInfo
		]);

		$this->apiCode = DB::select([
			'SELECT `phpBackend` FROM %topic_revisions% WHERE `tpid` = :nodeId LIMIT 1',
			'var' => [':nodeId' => $this->nodeId]
		])->phpBackend;

		if (!preg_match('/^\<\?php/', $this->apiCode)) $this->apiCode = '<?php'._NL.$this->apiCode._NL.'?>';

		eval('?>'.$this->apiCode.'<?php'._NL);

		// $this->testPhpScript();
	}

	function build() {
		if (!class_exists($this->apiClassName)) return apiError(_HTTP_ERROR_NOT_FOUND, 'API not found');

		$api = new $this->apiClassName($this->nodeInfo);

		if (!method_exists($api, $this->apiMethod)) return apiError(_HTTP_ERROR_NOT_FOUND, 'API not found');

		return $api->{$this->apiMethod}();
	}

	private function testPhpScript() {
		eval('
			use Softganz\DB;
			class Paper2486Api extends PageApi {
				var $nodeInfo;
				var $action;
				var $nodeId;

				function __construct($nodeInfo = NULL, $action = NULL) {
					parent::__construct([
						"nodeId" => $nodeInfo->nodeId,
						"nodeInfo" => $nodeInfo
					]);
					// DebugMsg($this);
				}
				function test() {
					$modelResult = Paper2486Model::get($this->nodeInfo->nodeId);
					// DebugMsg("AAAAA");
					// DebugMsg($modelResult);
					$books = Paper2486Model::getBooks($this->nodeId);
					// debugMsg($books, "$books");

					return apiSuccess([
						"text" => "TEST API OK with ".$modelResult->id,
						"nodeId" => $this->nodeInfo->nodeId,
						"modelResult" => $modelResult,
						"books" => $books,
						// "nodeInfo" => $this->nodeInfo
					]);
				}
				function clickSave() {return apiSuccess("CLICK SAVE API OK");}
			}
			class Paper2486Model {
				static function get($id) {
					return (Object) ["id" => $id];
				}
				static function getBooks($nodeId) {
					$dbs = DB::select([
						"SELECT `tpid`, `title` FROM %topic% LIMIT 10",
					]);
					return $dbs;
				// 	$dbs = DB::select([
				// 		\'SELECT
				// 		-- `rev`.`data` ->> "$.books" `books`
				// 		JSON_EXTRACT(`rev`.`data`, "$.books") `books`
				// 		FROM %topic_revisions% `rev`
				// 		WHERE `rev`.`tpid` = :nodeId
				// 		LIMIT 1\',
				// 		"var" => [":nodeId" => $nodeId]
				// 	]);
				// 	print_r(mydb()->_query);
				// 	echo "<pre>".print_r($dbs)."</pre>";
				// 	return $dbs;
				}
			}
			'
		);
	}
}
?>