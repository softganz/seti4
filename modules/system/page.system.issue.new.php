<?php
/**
* System  :: Save New Issue
* Created :: 2022-10-14
* Modify  :: 2025-06-07
* Version :: 3
*
* @return Widget
*
* @usage system/issue/new
*	Example call:
* http://localhost/seti/softganz.com/system/issue/new?file=/Users/httpdocs/cms/seti4.00/modules/project/info/page.project.info.view.php&line=32&date=2022-10-14%2008:52:34user=&url=http://localhost/hsmi/localfund.com/project/141880&type=Test%20Log
*/

use Softganz\DB;

class SystemIssueNew extends Page {
	var $reportUrl;
	var $host;
	var $path;
	var $query;
	var $issueType;
	var $file;
	var $line;
	var $reportDate;
	var $reportUser;
	var $reportBy;
	var $referer;
	var $agent;
	var $description;

	function __construct() {
		// $post = json_decode(post());
		$reportUrl = post('url');
		list($scheme, $host) = $hosts = parse_url($reportUrl);

		parent::__construct([
			'reportUrl' => $reportUrl,
			'host' => $hosts['scheme'].'://'.$hosts['host'],
			'path' => $hosts['path'],
			'query' => $hosts['query'],
			'issueType' => post('type'),
			'file' => post('file'),
			'line' => post('line'),
			'reportDate' => post('date'),
			'reportUser' => post('user'),
			'reportBy' => post('name'),
			'referer' => post('referer'),
			'agent' => post('agent'),
			'description' => post('description'),
			'data' => json_encode(SG\getFirst(post('data'), (Object)[])),
		]);
	}

	function build() {
		DB::query([
			'INSERT INTO %system_issue%
			(
				`host`
				, `path`
				, `query`
				, `file`
				, `line`
				, `issueType`
				, `reportDate`
				, `reportUser`
				, `reportBy`
				, `referer`
				, `agent`
				, `description`
				, `data`
				, `created`
			)
			VALUES
			(
				:host
				, :path
				, :query
				, :file
				, :line
				, :issueType
				, :reportDate
				, :reportUser
				, :reportBy
				, :referer
				, :agent
				, :description
				, :data
				, :created
			)',
			'var' => [
				':host' => $this->host,
				':path' => $this->path,
				':query' => $this->query,
				':issueType' => $this->issueType,
				':file' => $this->file,
				':line' => $this->line,
				':reportDate' => $this->reportDate,
				':reportUser' => $this->reportUser,
				':reportBy' => $this->reportBy,
				':referer' => $this->referer,
				':agent' => $this->agent,
				':description' => $this->description,
				':data' => $this->data,
				':created' => date('U'),
			]
		]);

		// debugMsg(R()->_query);
		// debugMsg($this, '$this');

		return [
			'issueId' => mydb()->insert_id,
			'url' => $this->reportUrl,
			// 'post' => post(),
			// 'this'=> $this,
			// 'query' => '<pre>'.R()->_query.'</pre>'
		];
	}
}
?>