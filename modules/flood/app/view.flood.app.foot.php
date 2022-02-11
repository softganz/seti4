<?php
function view_flood_app_foot() {
	$ret.='</div><!-- primary -->'._NL;
	$ret.='</div><!-- main -->'._NL;

	if (cfg('firebase')) {
		$ret.='<script src="https://www.gstatic.com/firebasejs/4.7.0/firebase.js"></script>'._NL;
		$ret.='<script>
	  // Initialize Firebase
	  firebaseConfig = '.json_encode(cfg('firebase')).';
	  firebase.initializeApp(firebaseConfig);
		</script>'._NL;
	}

	//$ret.=print_o(cfg('tracking'),'tra');
	$ret.='<script type="text/javascript">'._NL;
	if (cfg('tracking')) {
		foreach (cfg('tracking') as $tracker=>$track_id) {
			switch ($tracker) {
				case 'google' :
					if (is_string($track_id)) {
						$ret.='// Load Google analytics
(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,"script","//www.google-analytics.com/analytics.js","ga");
ga("create", "'.$track_id.'", "auto");
ga("send", "pageview");'._NL._NL;
					} else if (is_array($track_id)) {
						$ret.='// Load Google analytics
(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,"script","//www.google-analytics.com/analytics.js","ga");
ga("create", "'.$track_id['id'].'", "'.$track_id['site'].'");
ga("send", "pageview");'._NL._NL;
					}
					break;
			}
		}
	}
	$ret.='</script>'._NL;

	$ret.='</body>'._NL.'</html>';
	return $ret;
}
?>