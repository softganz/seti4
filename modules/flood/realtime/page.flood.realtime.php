<?php
function flood_realtime($self, $setid = 'REALTIME') {
  if (!i()->ok) return message('error','access denied');

  $cameraList = cfg('flood.camera')->{$setid}->camera;

  $cams = R::Model('flood.camera.list',$cameraList);

  $ret.='<ul class="flood-hot-monitor">'._NL;
  foreach ($cams as $rs) {
    $ad='';
    if ($rs->sponsor_name=='no') ; // no sponsor
    else if ($rs->sponsor_logo) {
      $ad='<img src="'.$rs->sponsor_logo.'" alt="" />';
    } else {
      $ad='<img src="https://www.hatyaicityclimate.org/file/ad/youradhere.jpg" alt="" />';
    }
    $ret.='<li id="camera-'.$rs->name.'" class="cam-'.$rs->name.'">'
      .'<h3><a href="'.url('flood/cam/'.$rs->camid).'">'.$rs->title.'</a></h3>'
      //.'<a href="'.url('flood/cam/'.$rs->camid).'" title="'.($rs->sponsor_name?'สนับสนุนภาพจาก '.$rs->sponsor_name:'').'">'
      .'<a>'
      .'<img id="'.$rs->name.'" src="/library/img/none.gif" />'
      .'</a>'
      //.($ad?'<div class="flood-camera-monitor-ad" id="ad-'.$rs->name.'">'.$ad.'</div>':'')
      .'<p id="'.$rs->name.'-time" class="flood-timestamp -'.$rs->name.'">'
      .'<span class="date -date">'.sg_date($rs->last_updated,'ว ดด ปป').'</span>'
      .'<span class="date -time">'.sg_date($rs->last_updated,'H:i').'</span>'
      .'</p>'
      .'<p class="flood-cam-error not-update">รออัพเดทภาพ</p>'
      .'</li>'._NL;
  }
  $ret.='</ul>'._NL;

  $ret.='<style type="text/css">
    .flood-hot-monitor .flood-timestamp {width:48px; height:48px; border-radius:50%; line-height:48px;}
    .flood-timestamp .date.-date {display:none;}
    .flood-timestamp .date.-time {font-size:1.6em;}
    body#flood .flood-hot-monitor>li {position: relative; margin:0 1% 32px; max-width: none; height: auto;}
    body#flood .flood-hot-monitor>li:before {content:""; display: block; padding-top: 100%;}
    body#flood .flood-hot-monitor>li>a {position: absolute; top: 0; left: 0; right: 0; bottom: 0;}
    body#flood .flood-hot-monitor>li>a>img {width: 100%; height:100%;}
    body#flood .flood-hot-monitor>li h3 {position: absolute; bottom: 0; z-index: 1; text-align: center; background-color: green; width: 100%; color:#fff;}

    @media (min-width:40em){    /* 640/16 = 40 */
    body#flood .flood-hot-monitor>li {width:31.33%; }
    }
  </style>';

  return $ret;
}
?>