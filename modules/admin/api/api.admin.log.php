<?php
/**
 * Admin    :: Log API
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2026-06-28
 * Modified :: 2026-06-28
 * Version  :: 1
 *
 *  @usage api/admin/log..[action]
 */

use Softganz\DB;

class AdminLogApi extends PageApi {
	/**
	 * Right to build
	 *
	 * @return object|bool
	 */
	function rightToBuild(): object|bool {
		if (!is_admin()) return apiError(_HTTP_ERROR_FORBIDDEN, _ERROR_MSG_ACCESS_DENIED);

		return true;
	}

	/**
	 * Create partition
	 *
	 * @return object
	 */
	public static function partitionCreate(): object {
		$post = (Object) [
			'table' => Request::all('table', 'en'),
			'numberOfPartition' => Request::all('numberOfPartition', 'int'),
			'numberOfRecord' => Request::all('numberOfRecord', 'int')
		];

		if ($post->table === '') return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่ระบุตาราง');
		if (empty($post->numberOfPartition)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่ระบุจำนวน partition');
		if (empty($post->numberOfRecord)) return apiError(_HTTP_ERROR_BAD_REQUEST, 'ไม่ระบุจำนวน record');

		$startRecordId = DB::select([
			'SELECT MIN(`id`) AS `minId` FROM $TABLE$ LIMIT 1',
			'var' => [
				'$TABLE$' => cfg('db.prefix') . $post->table,
			]
		])->minId;

		// Start of first million id
		$startRecordId = floor($startRecordId / $post->numberOfRecord) * $post->numberOfRecord;

		$stmt = 'ALTER TABLE `$TABLE$`  PARTITION BY RANGE (`id`) PARTITIONS :partions (' . _NL;

		for ($i = 1; $i <= $post->numberOfPartition; $i++) {
			$startRecordId += $post->numberOfRecord;
			$stmt .= 'PARTITION p_' . ($startRecordId / 1000000)  . ' VALUES LESS THAN ( ' . $startRecordId . ' ),' . _NL;
		}
		$stmt .= 'PARTITION p_future VALUES LESS THAN MAXVALUE
		)';

		// debugMsg('<pre>'.$stmt.'</pre>');

		DB::query([
			$stmt,
			'var' => [
				'$TABLE$' => cfg('db.prefix') . $post->table,
				':partions' => $post->numberOfPartition + 1
			]
		]);

		// debugMsg('<pre>' . R('query') . '</pre>');

		return apiSuccess('Partition created');
	}
}
?>