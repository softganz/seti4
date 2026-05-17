<h3>Class Nav Widget</h3>

<ul class="docs-page-nav-list">
<li>Contents</li>
<li>Before you begin</li>
<li><a href="{widget/widget}">Extends from Widget</a></li>
<li><a href="#Property">Property</a></li>
<li><a href="#Method">Method</a></li>
</ul>

<h4>Class constructor</h4>
<code>
new Nav([
	String id,
	String class,
	Array children,
	Widget child,
]);
</code>

<a name="Property"></a>
<h4>Property :</h4>
<code>
children ↔ Array
	Ex.
		- <a>text</a> : String
	 	- [1, 2, 3] : Array
	 	- [[1, 2], [3, 4]] : Array of Array
	 	- [[1, 2], widget, dropbbox] : Array of widget
</code>

<a name="Method"></a>
<h4>Method :</h4>
<code>
AppBar::build() → String
</code>