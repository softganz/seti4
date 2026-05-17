<h3>Class AppBar Widget</h3>

<ul class="docs-page-nav-list">
<li>Contents</li>
<li>Before you begin</li>
<li><a href="{widget/widget}">Extends from Widget</a></li>
<li>Property</li>
<li>Method</li>
</ul>




<h4>Class constructor</h4>
<code>
new AppBar([
	String id,
	String class,
	String title,
	Widget leading,
	Widget trailing,
	Widget navigator,
	Widget dropbox,
	Boolean removeOnApp = false,
	Boolean boxHeader = false,
	Widget[] children,
]);
</code>

<h4>Property :</h4>
<code>
title ↔ String : default NULL

removeOnApp ↔ Boolean default false

boxHeader ↔ Boolean default false

navigator ↔ Array
	Ex.
			- <a>text</a> : String
	 	- [1, 2, 3] : Array
	 	- [[1, 2], [3, 4]] : Array of Array
	 	- [[1, 2], widget, dropbbox] : Array of widget

dropBox ↔ Dropbox widget
</code>
</code>

<h4>Method :</h4>
<code>
AppBar::build() → String
</code>