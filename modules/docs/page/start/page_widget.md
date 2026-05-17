<h3>Page Widget</h3>

<ul class="docs-page-nav-list">
<li>Contents</li>
<li>Before you begin</li>
<li><a href="{widget/widget}">Extends from Widget</a></li>
<li>Property</li>
<li>Method</li>
</ul>

<h4>Using</h4>
<h5>Url call:</h5>
http://example.com/module/[{id}/]sub[?arg1=1&arg2=2&...]

<h5>PageWidget call:</h5>
R::Pagewidget('module/[{id}/]sub', [arg1=1, arg2=2])


<h4>Page widget extends</h4>
<code>
namespace Module\Template;
// Ex. Project\localfund

import('package:module/folder/filename.php');
// Ex. package:project/proposal/api/api.project.proposal.create.follow.php

Class ModuleSub extends \Module\ModuleSub {
	// Ex. Class ProjectProposalCreateFollowApi extends \Project\ProjectProposalCreateFollowApi

}
</code>

<h4>Class constructor</h4>
<code>
new ModuleSub([
	String arg1,
	String arg2,
	...
]);
</code>

<h4>Property :</h4>
<code>
arg1 ↔ Mixed : default NULL
</code>
</code>

<h4>Method :</h4>
<code>
AppBar::build() → String
</code>