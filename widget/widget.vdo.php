<?php
/**
 * Widget   :: Inline Video content Widget
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2014-05-25
 * Modified :: 2026-07-29
 * Version  :: 2
 *
 * @param String $para
 * 	data-header=Header
 * @return String
 * Exp. <div class="widget vdo" data-src="http://softganz.com/vdo.mp3" data-img="http://softganz.com/upload/pics/photo.jpg" data-size="240p"></div>
 */

function widget_vdo() {
	$para=para(func_get_args(),'data-header=Video','data-width=640','data-height=360','option-header=0');
	switch ($para->{'data-size'}) {
		case '240p' : $width=426; $height=240; break;
		case '360p' : $width=640; $height=360; break;
		case '480p' : $width=853; $height=480; break;
		case '720p' : $width=1028; $height=720; break;
		default : $width=$para->{'data-width'}; $height=$para->{'data-height'}; break;
	};

	$ret='<!--widget stat --><object type="application/x-shockwave-flash" width="'.$width.'" height="'.$height.'" data="https://softganz.com/library/mediaplayer.swf?file='.$para->{'data-src'}.'&autostart=false&stretching=exactfit&amp;'.( $para->{'data-img'} ? 'image='.$para->{'data-img'} : '' ).'">
<param name="movie" value="https://softganz.com/library/mediaplayer.swf?file='.$para->{"data-src"}.'&autostart=false&stretching=exactfit&amp;image=http://epay4u.com/upload/easy_investment.jpg" width="'.$width.'" height="'.$height.'" />
<embed src="https://softganz.com/library/mediaplayer.swf" width="'.$width.'" height="'.$height.'" flashvars="file='.$para->{"data-src"}.'&autostart=false&stretching=exactfit&amp;image=http://epay4u.com/upload/easy_investment.jpg" />
</object><!--end of widget stat-->';
	return array($ret,$para);
}
?>