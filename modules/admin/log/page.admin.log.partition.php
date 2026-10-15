<?php
/**
 * Admin   :: Log Partition Management
 * Author   :: Little Bear<softganz@gmail.com>
 * Created :: 2026-06-28
 * Modify  :: 2026-06-28
 * Version :: 1
 *
 * @return Widget
 *
 * @uses admin/log/partition
 */

use Softganz\DB;
use Softganz\SetDataModel;

class AdminLogPartition extends Page {
	private $counterTables = ['counter_log', 'counter_bot'];

	function __construct($arg1 = null) {
		parent::__construct([
		]);
	}

	function rightToBuild() {
		return true;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Log Partition Management.',
			]), // AppBar
			'body' => $this->listPartition(),
		]);
	}

	function listPartition() {
		return new Widget([
			'children' => array_map(
				function($table) {
					return new Card([
						'children' => [
							new Header([
								'crossAxisAlignment' => 'start',
								'title' => 'Table: ' . $table,
								'subTitle' => ' Start record ID ' . number_format($this->getStartId($table)),
								'leading' => new Icon('database'),
								'trailing' => new Button([
									'type' => 'secondary',
									'class' => 'sg-action',
									'href' => Url::link('admin/log/partition..create', ['table' => $table]),
									'text' => 'Re-create partition',
									'icon' => new Icon('tab_new_right'),
									'rel' => 'box',
									'boxWidth' => 320
								])
							]),
							new Table([
								'thead' => ['no' => '#', 'Partition', 'Expression', 'rows -amt' => 'Rows', 'data -amt' => 'Data length (KiB)'],
								'children' => array_map(
									function($part) {
										static $no =0;

										return [
											++$no,
											$part->PARTITION_NAME,
											$part->PARTITION_EXPRESSION  . ' < ' . $part->PARTITION_DESCRIPTION,
											number_format($part->TABLE_ROWS),
											number_format($part->dataSize, 2) . ' KiB'
										];
									},
									$this->getPartition($table)->items
								)
							]),
						]
					]);
				},
				$this->counterTables
			)
		]);
	}

	function create() {
		$tableName = Request::all('table');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Re-Create Partition of ' . $tableName,
				'leading' => new Icon('tab_new_right'),
				'boxHeader' => true
			]),
			'body' => new Form([
				'class' => 'sg-form',
				'action' => Url::link('api/admin/log..partition.create'),
				'disableOnSubmit' => 'กำลังดำเนินการ',
				'rel' => 'notify',
				'done' => 'close | load',
				'children' => [
					'table' => ['type' => 'hidden', 'value' => $tableName],
					'numberOfPartition' => [
						'type' => 'select',
						'class' => '-fill',
						'label' => 'Number of partitions to create',
						'choices' => ['20' => 20, '30' => 30, '40' => 40, '50' => 50, '60' => 60],
					],
					'numberOfRecord' => [
						'type' => 'select',
						'class' => '-fill',
						'label' => 'Number of record in each partition',
						'choices' => (Object) [
							1000000 => '1,000,000',
							2000000 => '2,000,000',
							3000000 => '3,000,000',
							4000000 => '4,000,000',
							5000000 => '5,000,000',
							6000000 => '6,000,000'
						],
					],
					'submit' => [
						'type' => 'button',
						'class' => '-primary -fill',
						'text' => 'Start Create Partiton',
						'icon' => new Icon('arrow_circle_right')
					]
				]
			])
		]);
	}

	private function getPartition($table) {
		return DB::select([
			'SELECT 
				PARTITION_NAME, 
				SUBPARTITION_NAME, 
				PARTITION_EXPRESSION, 
				PARTITION_DESCRIPTION, 
				TABLE_ROWS, 
				ROUND(DATA_LENGTH / 1024, 2) AS `dataSize`
				FROM 
				information_schema.PARTITIONS 
				WHERE 
				TABLE_SCHEMA = :dbname
				AND
				TABLE_NAME = :tablename;',
				'var' => [
					':dbname' => R()->myDb->db,
					':tablename' => cfg('db.prefix') . $table
				]
		]);
	}

	private function getStartId($table) {
		return DB::select([
			'SELECT MIN(`id`) AS `minId` FROM $TABLE$ LIMIT 1',
			'var' => [
				'$TABLE$' => cfg('db.prefix') . $table,
			]
		])->minId;
	}

}
?>