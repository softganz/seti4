<?php
/**
* API     :: Image API
* Created :: 2022-11-19
* Modify  :: 2022-11-19
* Version :: 2
*
* @return Array
*
* @usage api/image?src=url
*/

class ImageApi extends PageApi {
	var $queryText;
	var $page;
	var $items;

	function __construct() {
		parent::__construct([
			'queryText' => SG\getFirst(post('q')),
			'page' => SG\getFirst(post('page'), post('p'), 1),
			'items' => SG\getFirst(post('item'), post('n'), 50),
		]);
	}

	function build() {
		return api_image();
	}
}
?>
<?php
/**
URL function : function for call by url address
parameter :
src = image source file
dsttype = result file type jpg,png,gif default is jpg
w = result image width in pixel
h = result image height in pixel
q = result image qulity when dsttype is jpg default is 80
debug = yes for debuging program
*/

function api_image() {
	$para=para(func_get_args(),'q/80');
	$para->src=$_GET['src'];
	$src_file = cfg('folder.abs').$para->src;
	$debug = isset($_GET['debug']) ? $_GET['debug'] : NULL;

	if ( $debug) {
		print_o($para,'$para',1);
		echo 'source file location <em>'.$src_file.'</em><br />';
	}

	$srcTypes = NULL;
	$dstWidth = SG\getFirst($para->w,$srcTypes[0]);
	$dstHeight = SG\getFirst($para->h,$srcTypes[1]);
	$quality = $para->q;
	
	if ( file_exists($src_file) and is_file($src_file) ) $srcTypes = getimagesize($src_file);
	else {
		if ( $debug ) echo 'image file <em>'.$src_file.'</em> not exists.<br />';
		else {
			Header('Content-type: image/jpeg');
			$im = @ImageCreate ($dstWidth, $dstHeight);
			$background_color = ImageColorAllocate ($im, 255, 255, 255);
			$text_color = ImageColorAllocate ($im, 233, 14, 91);
			ImageString ($im, 2, round($dstWidth/2)-20, round($dstHeight/2)-8, 'no image', $text_color );
			ImageJPEG ($im,NULL,160);
			@imagedestroy($im);
		}
		return false;
	}

	if ( $debug ) print_o($srcTypes,'$srcTypes',1);

	$dstWidth = SG\getFirst($para->w,$srcTypes[0]);
	$dstHeight = SG\getFirst($para->h,$srcTypes[1]);
	$srcType = $srcTypes['mime'];
	$dstType = SG\getFirst($para->dsttype,'jpg');
	
	if ( $debug ) echo 'destination type is <em>'.$dstType.'</em><br />';

	$srcImg = '<img src="'.url(q(),'src='.$para->src).'">';
	if ( $debug ) echo 'image url is <em>'.htmlspecialchars($srcImg).'</em><br />';

	if (($srcType == "image/jpeg" or $srcType == "image/pjpeg") and function_exists("imagecreatefromjpeg"))
		$handle = @imagecreatefromjpeg($src_file);
	else if ($srcType == "image/png" and function_exists("imagecreatefrompng"))
		$handle = @imagecreatefrompng($src_file);
	else if ($srcType == "image/gif" and function_exists("imagecreatefromgif") )
		$handle = @imagecreatefromgif($src_file);
	else {
		if ( $debug ) {
			echo 'image type <em>'.$srcType.'</em> not support.<br />'.$srcImg.'<br />'._NL;
		} else __api_image_dumpFile($src_file);
	   return false;
   }
	if (!$handle) {
		if ( $debug ) {
			echo 'cannot create image handle.<br />'.$srcImg.'<br />'._NL;
		} else __api_image_dumpFile($src_file);
		return false;
	}

	if ( !function_exists("imagecopyresampled") or !function_exists("imagejpeg") ) {
		if ( $debug ) {
			echo "function <b>imagecopyresampled</b> or <b>imagejpeg</b> not support.<br>$srcImg<br>\n";
		} else __api_image_dumpFile($src_file);
		return false;
	}
   $srcWidth  = @imagesx($handle);
   $srcHeight = @imagesy($handle);


	$newHandle = @imagecreatetruecolor($dstWidth, $dstHeight);
	if (!$newHandle) {
		if ( $debug ) {
			echo "function imagecreatetruecolor not support.<br>$srcImg<br>\n";
		} else __api_image_dumpFile($src_file);
		return false;
	}

	if($srcHeight < $srcWidth) {
	   $ratio = (double)($srcHeight / $dstHeight);

	   $cpyWidth = round($dstWidth * $ratio);
	   if ($cpyWidth > $srcWidth) {
		   $ratio = (double)($srcWidth / $dstWidth);
		   $cpyWidth = $srcWidth;
		   $cpyHeight = round($dstHeight * $ratio);
		   $xOffset = 0;
		   $yOffset = round(($srcHeight - $cpyHeight) / 2);
	   } else {
		   $cpyHeight = $srcHeight;
		   $xOffset = round(($srcWidth - $cpyWidth) / 2);
		   $yOffset = 0;
	   }

	} else {
	   $ratio = (double)($srcWidth / $dstWidth);

	   $cpyHeight = round($dstHeight * $ratio);
	   if ($cpyHeight > $srcHeight) {
		   $ratio = (double)($srcHeight / $dstHeight);
		   $cpyHeight = $srcHeight;
		   $cpyWidth = round($dstWidth * $ratio);
		   $xOffset = round(($srcWidth - $cpyWidth) / 2);
		   $yOffset = 0;
	   } else {
		   $cpyWidth = $srcWidth;
		   $xOffset = 0;
		   $yOffset = round(($srcHeight - $cpyHeight) / 2);
	   }
	}

	if (!@imagecopyresampled($newHandle, $handle, 0, 0, $xOffset, $yOffset, $dstWidth, $dstHeight, $cpyWidth, $cpyHeight)) {
		if ( $debug ) {
			echo "imagecopyresampled not support.<br>$srcImg<br>\n";
		} else __api_image_dumpFile($src_file);
		return false;
	}
	@imagedestroy($handle);

	if ( $debug ) {
		echo _NL._NL.'<p><img src="'.url(q(),'src='.$para->src).'"></p>'._NL._NL;
		echo '<p><img src="'._URL.$para->src.'"><br />source file is '.$src_file.'</p>'._NL._NL;
		return true;
	}

	if ($dstType == "png") {
		Header("Content-type: image/png");
		@imagepng($newHandle);
	} else if ($dstType == "jpg") {
		Header("Content-type: image/jpeg");
		@imagejpeg($newHandle, "", $quality);
	} else if ($dstType == "gif") {
		Header("Content-type: image/gif");
		@imagegif($newHandle);
	} else {
		__api_image_dumpFile($src_file);
		return false;
	}
	@imagedestroy($newHandle);
	return true;

}

function __api_image_dumpFile($src_file=NULL) {
	if ( file_exists($src_file) and is_file($src_file) ) {
		$srcTypes = getimagesize($src_file);
		Header("Content-type: {$srcTypes["mime"]}");
		readfile($src_file);
		return true;
	} else return false;
}

?>