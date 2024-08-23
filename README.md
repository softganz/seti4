# seti4.00 Project

Read Me

Folder structure

<pre>
seti4
├── core
⎪   ├── assets
⎪   ⎪   ├── conf
⎪   ⎪   └── template
⎪   ├── css
⎪   ├── js
⎪   ├── lib
⎪   ├── models
⎪   ├── modules
⎪   ⎪   ├── module1
⎪   ⎪   └── module2
⎪   ├── po
⎪   ├── ui
⎪   ├── upgrade
⎪   ├── view
⎪   ├── widgets
⎪   └── core.php
└── modules
    ├── module1
    └── module2
</pre>

Forder description

seti4/core/modules is for system modules
seti4/modules is for user modules

Website structure
<pre>
domain folder
├── index.php
├── conf.d
⎪   ├── conf.web.php
⎪   ├── conf.core.json
⎪   └── conf.{moduleName}.json
├── conf.local
⎪   ├── conf.web.php
⎪   ├── conf.core.json
⎪   └── conf.{moduleName}.json
├── file
├── theme
⎪   ├── default
⎪   └── theme1
└── upload
    ├── forum
    └── pics
</pre>

index.php
<pre>
<?php
$include_path = ['/server/folder/seti4', '/local/folder/seti4', ini_get('include_path')];
ini_set('include_path', implode(PATH_SEPARATOR, $include_path));

require 'core/core.php';

controller();
?>
</pre>
History
- @2022-12-17 Change model class from model => BasicModel and other module_model => ModuleModel