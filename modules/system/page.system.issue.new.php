<?php
/**
* System  :: Save New Issue
* Created :: 2022-10-14
* Modify  :: 2022-10-20
* Version :: 2
*
* @return Widget
*
* @usage system/issue/new
*/

class SystemIssueNew extends Page {
	function __construct() {
		$reportUrl = post('url');
		list($scheme, $host) = $hosts = parse_url($reportUrl);
		// debugMsg(parse_url($reportUrl), 'aa');
		// debugMsg(gettype($a));
		// debugMsg($a['host'], '$a');
		// debugMsg($scheme.$host);

		parent::__construct([
			'reportUrl' => $reportUrl,
			'host' => $hosts['scheme'].'://'.$hosts['host'],
			'path' => $hosts['path'],
			'query' => $hosts['query'],
			'file' => post('file'),
			'line' => post('line'),
			'reportDate' => post('date'),
			'reportUser' => post('user'),
			'reportBy' => post('name'),
			'referer' => post('referer'),
			'agent' => post('agent'),
			'description' => post('description'),
		]);
	}

	function build() {
		// $api = SG\api([
		// 	'url' => 'https://localfund.happynetwork.org/api/ampur/90',
		// 	'result' => 'text',
		// ]);
		// debugMsg(gettype($api));
		// http://localhost/seti/softganz.com/system/issue/new?file=/Users/httpdocs/cms/seti4.00/modules/project/info/page.project.info.view.php&line=32&date=2022-10-14%2008:52:34user=&url=http://localhost/hsmi/localfund.com/project/141880

		mydb::query(
			'INSERT INTO %system_issue%
			(
				`host`, `path`, `query`, `file`, `line`
				, `reportDate`, `reportUser`, `reportBy`
				, `referer`, `agent`
				, `description`
				, `created`
			)
			VALUES
			(
				:host, :path, :query, :file, :line
				, :reportDate, :reportUser, :reportBy
				, :referer, :agent
				, :description
				, :created
			)',
			[
				':host' => $this->host,
				':path' => $this->path,
				':query' => $this->query,
				':file' => $this->file,
				':line' => $this->line,
				':reportDate' => $this->reportDate,
				':reportUser' => $this->reportUser,
				':reportBy' => $this->reportBy,
				':referer' => $this->referer,
				':agent' => $this->agent,
				':description' => $this->description,
				':created' => date('U'),
			]
		);

		// debugMsg(mydb()->_query);
		// debugMsg($this, '$this');

		return [
			'issueId' => mydb()->insert_id,
			'url' => $this->reportUrl,
		];
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Issue Report',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new DebugMsg($api, '$api'),
				], // children
			]), // Widget
		]);
	}
}
?>