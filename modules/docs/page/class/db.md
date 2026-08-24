## class DB()

```
=========================================
class DB()
=========================================
```

### DB::select()

#### DB::Select(String $query)
#### # Parameter:

`String $query`

#### DB::Select(Array $mixed)

```php
DB::select([
	query,
	where,
	var,
	options,
	connection,
]);
```
##### Parameter:

```php
String $query
Array $where
Array $var
Array $options
Array | String $connection
```

##### Result

Property:

```php
Int $count
Array $items
```

###Parameter



#### where

```php
%WHERE% in query statement

where => [
	'%WHERE%' => [
		[condition1, ":variable1" => value1],
		[condition2, ":variable2" => value2],
	]
]
```

#### var

```php
var => [
	'::variable::'  => String,                    // Value quote but remove " at leading and trailing
	':`variable`'   => String,                    // Value is field and quote and add ` at leading and trailing
	'$variable$'    => String,                    // Value not qoute
	'$variable'     => String,                    // Value quote with '
	':variable'     => String,                    // Value quote with '
	':variable'     => new DataModel(),           // Value is DataModel class
	':variable'.    => new SetDataModel(),        // Value is SetDataModel class
	':variable'.    => new JsonDataModel(),       // Value is JsonDataModel class
	':variable'.    => new JsonArrayDataModel(),  // Value is JsonArrayDataModel class
	':variable'.    => NULL                       // Replace with NULL
	':variable'.    => Object,                    // Value is object
	':variable'.    => Array,                     // Value is array
	':variable'.    => func.funcName(),           // Value is function and not quote
	':variable'.    => Numeric,                   // Value not quote

	// Deprecate
	':variable' => :JSON_OBJECT:xxx,
]
```

#### options

```php
options => [
	"sum"        => "fieldName1,fieldName2",
	"group"      => "fieldName",
	"key"        => "fieldName",
	"value"      => "fieldName",
	"log"        => boolean,                  // Default is true
	"history"    => boolean,                  // Default is true
	"multiple"   => boolean,                  // Multiple query, default is false
	"showResult" => boolean,                  // Default is false
	"debug"      => boolean,                  // Default is false
	"jsonDecode" => [
		['field' => 'fieldName', 'type' => 'merge,default']
		...
	],
]
```

### Method :

```php
DB::Constructor ( $dburi )

DB::query ([
	$stmt ,
	'%WHERE%' => [
		[condition, ':key' => $value],
		[condition, ':key' => $value],
	],
	'where' => [
		'%WHERE%' => [
			[condition, ':key' => $value],
			[condition, ':key' => $value],
		],
	],
	'var' => [
		':key' => $value,
		':key' => $value,
		':key' => $value,
	]
])

DB::select ([
	$stmt ,
	'%WHERE%' => [
		[condition, ':key' => $value],
		[condition, ':key' => $value],
	],
	'where' => [
		'%WHERE%' => [
			[condition, ':key' => $value],
			[condition, ':key' => $value],
		],
	],
	'var' => [
		':key' => $value,
		':key' => $value,
		':key' => $value,
	]
])
```

### Method description

### Data Model

```php
class DataModel()
class SetDataModel()
class JsonDataModel()
class JsonArrayDataModel()
```

### Property :
