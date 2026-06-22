<!--
Dropbox Widget
Created :: 2025-08-26
Modify  :: 2025-08-26
Version :: 1
-->
# Class Dropbox Widget

<ul class="docs-page-nav-list">
<li>Contents</li>
<li>Before you begin</li>
<li><a href="{widget/widget}">Extends from Widget</a></li>
<li><a href="#Property">Property</a></li>
<li><a href="#Method">Method</a></li>
</ul>

## Class constructor
```
new Dropbox([
	String id,
	String class = 'widget-dropbox sg-dropbox click leftside -no-print',

	String type = 'click', // click,hover
	String text,
	String title = 'มีเมนูย่อย',
	String/Widget icon,
	String iconText = 'more_vert',
	String submitText,
	String url,
	String link,
	String $position = 'left', // left, right, center
	Bool hideOnNoChild Default false
	Bool print = false,
	Array $childrenContainer = ['tagName' => 'ul'];
	Array $childContainer = ['tagName' => 'li', 'class' => '-item'];
]);
```

<a name="Property"></a>

## Property
<code>
```
editMode ↔ Boolean
action ↔ String
```

<a name="Method"></a>

## Method :
```
Report::build() → String
```