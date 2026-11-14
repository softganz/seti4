<?php
function search_google($self) {
	$self->theme->header->text = 'Search result by Google';
	//		if (!preg_match('/\-\"index\.php\"/',$_GET['q'])) $_GET['q'].=' -"index.php"';

	$ret =
		'<div id="cse-search-results"></div>
<script type="text/javascript">
  let googleSearchIframeName = "cse-search-results";
  let googleSearchFormName = "cse-search-box";
  let googleSearchFrameWidth = '
		. cfg('search.google.framewidth')
		. ';
  let googleSearchDomain = "www.google.co.th";
  let googleSearchPath = "/cse";
</script>
<script type="text/javascript" src="http://www.google.com/afsonline/show_afs_search.js"></script>';

	return $ret;
}
?>
