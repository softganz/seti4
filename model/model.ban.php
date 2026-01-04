<?php
/**
 * Ban     :: Ban Model
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2025-12-26
 * Modify  :: 2026-01-04
 * Version :: 2
 *
 * @param Array $args
 * @return Object
 *
 * @usage import('model:module.modelname.php')
 * @usage new BanModel([])
 * @usage BanModel::function($conditions)
 */

use Softganz\DB;

class BanModel {
	public static function createTable() {
		DB::query([
			'CREATE TABLE IF NOT EXISTS %ban% (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`ip` VARCHAR(45) DEFAULT NULL,
				`host` VARCHAR(255) DEFAULT NULL,
				`start` DATETIME NOT NULL,
				`end` DATETIME DEFAULT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `ip` (`ip`),
				UNIQUE KEY `host` (`host`),
				INDEX `end` (`end`)
			)',
		]);
	}

	public static function save($data) {
		$data = (Object) array_merge(
			[
				'id' => NULL,
				'ip' => NULL,
				'host' => NULL,
				'start' => date('Y-m-d H:i:s'),
				'end' => NULL,
			],
			(Array) $data
		);

		DB::query([
			'INSERT INTO %ban% ( `id`, `ip` , `host` , `start` , `end` ) VALUES ( :id, :ip, :host, :start, :end )
			ON DUPLICATE KEY UPDATE
				`start` = :start,
				`end` = :end',
			'var' => [
				':id' => $data->id,
				':ip' => $data->ip,
				':host' => $data->host,
				':start' => $data->start,
				':end' => $data->end
			],
		]);
	}

	public static function getBanByHost($ip, $host) {
		$banId = DB::select([
			'SELECT `id` FROM %ban%
			%WHERE%
			ORDER BY `id` ASC
			LIMIT 1',
			'where' => [
				'%WHERE%' => [
					[
						'(`ip` = :ip OR :ip REGEXP `ip` OR :host REGEXP `host`)',
						':ip' => $ip,
						':host' => $host
					],
				],
			]
		])->id;

		return $banId;
	}

	public static function deleteExpiredBan() {
		DB::query([
			'DELETE FROM %ban% WHERE `end` IS NOT NULL AND :currentTime > `end`',
			'var' => [':currentTime' => date('Y-m-d H:i:s')],
		]);
	}

	// Insert ban from configuration
	public static function copyFromConfig() {
		foreach ((Array) cfg('ban.ip') as $ban) {
			try {
				DB::query([
					'INSERT INTO %ban% ( `ip` , `host` , `start` , `end` ) VALUES ( :ip, :host, :start, :end )',
					'var' => [
						':ip' => property_exists($ban, 'ip') ? $ban->ip : NULL,
						':host' => property_exists($ban, 'host') ? $ban->host : NULL,
						':start' => property_exists($ban, 'start') ? $ban->start : date('Y-m-d H:i:s'),
						':end' => property_exists($ban, 'end') ? $ban->end : NULL,
					],
				]);
			} catch (Exception $e) {}
		}

		// Clear ban configuration
		// cfg_db_delete('ban.ip');		
	}

	public static function getList($conditions = []) {
		$conditions = (Object) array_merge_recursive(
			[],
			(Array) $conditions
		);

		$banList = DB::select([
			'SELECT * FROM %ban% ORDER BY `id` ASC',
		]);

		return (Object) [
			'items' => $banList->items,
			'total' => $banList->count,
		];
	}

	public static function remove($id) {
		if (empty($id)) return;

		DB::query([
			'DELETE FROM %ban% WHERE `id` = :id LIMIT 1',
			'var' => [
				':id' => $id,
			],
		]);
	}
}
?>