# Kohana - derivation
- Version: 0.0.1
This module allows some kind of plugin system and unlimited amount of extending classes dynamically.

Normally in kohana you can extend classes by folders.
This allows to extend Kohana_Response many times.

This is a very early version and and will be improved in the near future.

## Requirements
PHP 5.3

##Installation
You need to add 2 modules to your Bootstrap. The first is a fake module to add some pathes for including cached proxy classes.
The second is this module:

```
	'cached_classes' => APPPATH.'cache/classes/',			
	'derivation' => MODPATH.'derivation',
```

After adding the modules Derivation you have to call:
```
	Derivation::create_derivations();
```
in the Bootstrap.

##Usage
You can overwrite nearly every class which gehts normal included by the autoloader.

```
Derivation::add_derivation('Kohana_A', 'A', 'B');
```

Kohana_A: is  the base class to extend.
A: is the class which should come out at the end
B: is the class which should extend the base class.

You should add your derivations in the init files of your modules.

You can setup an example:

```
Derivation::add_derivation('Kohana_A', 'A', 'B');
```

Adding that before the create_derivations() call
and creating the class maybe in the controller:

```
$test = new A();
$test->test();
```

Should print out some stuff.

After that look at your APPATH.'cache/classes/classes' folder. There you'll find a a.php

Whith this Content:

```
 defined('SYSPATH') or die('No direct script access.'); 

class B extends Kohana_A {
	public function test() {
	parent::test();
		var_dump('test_Class_B');
	}

}

class A extends B {}
```


##TODO
- Cache time put into variable
- Code Formatting
- adding more comments

