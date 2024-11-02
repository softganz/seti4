<?php
/**
* Photo   :: Photo Model
* Created :: 2024-11-02
* Modify  :: 2024-11-02
* Version :: 1
*
* @param Array $args
* @return Object
*
* @usage import('model:module.modelname.php')
* @usage new PhotoModel([])
* @usage PhotoModel::function($conditions)
*/

class PhotoModel {
	function __construct($args = []) {
	}

	static function resize($srcFile, $dstFile, $options = []) {
		$options = array_merge(
			[
				'width' => NULL,
				'height' => NULL ,
				'autoSave' => false,
				'quality' => 50,
				'log' => false,
			],
			(Array) $options
		);

		if ( file_exists($srcFile) and is_file($srcFile) ) {
			$srcTypes = getimagesize($srcFile);
			$srcSize = FileSize($srcFile);
		} else {
			return false;
		}

		$srcWidth = $srcTypes[0];
		$srcHeight = $srcTypes[1];
		$srcType = $srcTypes['mime'];

		if ( empty($dstFile) ) $dstFile = $srcFile;
		// if ( !$options['autoSave'] ) $dstFile = dirname($srcFile)."/auto_image_resize.jpg";

		if ( $options['width'] and empty($options['height']) ) $options['height'] = round((double)($srcHeight*$options['width'] / $srcWidth));

		// if ( $options['autoSave'] ) {
			// debugMsg('SAVE @'.date('H:i:s').' to '.$dstFile);
			$result = false;
			if ($srcWidth >= $options['width'] && $srcHeight >= $options['height']) {
				// ini_set('memory_limit', '1024MB');
				if ($options['log']) {
					BasicModel::watch_log('system', 'Photo Resize', \SG\json_encode(['imageType' => $srcType, 'width' => $srcWidth, 'height' => $srcHeight,'size' => $srcSize, 'file' => $srcFile]));
				}

				// Copy file that size over 6MB to upload/error folder
				if ($srcSize > 6000000) {
					$tmpDescFile = 'upload/error/'.basename($srcFile);
					// debugMsg($tmpDescFile);
					copy($srcFile, $tmpDescFile);
				}

				try {
					if (($srcType == "image/jpeg" or $srcType == "image/pjpeg") and function_exists("imagecreatefromjpeg")) {
						$handle = @imagecreatefromjpeg($srcFile);
					} else if ($srcType == "image/png" and function_exists("imagecreatefrompng")) {
						$handle = @imagecreatefrompng($srcFile);
					} else if ($srcType == "image/gif" and function_exists("imagecreatefromgif") ) {
						$handle = @imagecreatefromgif($srcFile);
					} else {
						return false;
					}
				} catch (Exception $e) {
					if ($options['log']) {
						BasicModel::watch_log('system', 'Photo Resize', \SG\json_encode(['error' => 'YES', 'imageType' => $srcType, 'width' => $srcWidth, 'height' => $srcHeight,'size' => $srcSize, 'file' => $srcFile]));
					}
					return false;
				}
				if (!$handle) return false;

				if ( !function_exists("imagecopyresampled") or !function_exists("imagejpeg") ) return false;
				$srcWidth  = @imagesx($handle);
				$srcHeight = @imagesy($handle);

				$newHandle = @imagecreatetruecolor($options['width'], $options['height']);
				if (!$newHandle) return false;

				if (!@imagecopyresampled($newHandle, $handle, 0, 0, 0, 0, $options['width'], $options['height'], $srcWidth, $srcHeight)) return false;
				@imagedestroy($handle);

				if ($srcType == "image/jpeg" or $srcType == "image/pjpeg") {
					$result = @imagejpeg($newHandle, $dstFile, $options['quality']);
				} else if ($srcType == "image/png") {
					$result = @imagepng($newHandle, $dstFile);
				} else if ($srcType == "image/gif") {
					$result = @imagegif($newHandle, $dstFile);
				} else {
					$result = false;
				}

				@imagedestroy($newHandle);
			}
			return $result;
		}
	// }
}
?>