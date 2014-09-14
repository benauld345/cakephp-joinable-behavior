CakePHP Joinable Behavior
=========================

The CakePHP Joinable Behavior allows you to easily join models similar to CakePHP's Containable Behavior. Rather than manually configuring joins, the Joinable Behavior allows you to simply 'Join' a model and it takes care of the rest.

Installation
------------

1. Clone repository into `yourpluginsdir/Joinable`
2. Load Plugin in your `bootstrap.php` file:

	`CakePlugin::load('DebugKit');`

3. In your model or AppModel add the behavior:

	`public $actsAs = array(
		'Joinable.Joinable'
	)`
4. That's it Joinable is now installed

Usage
-----

To use Joinable simply use the 'join' option in your find conditions array for example:

```
$this->BlogPost->find('first', array(
	'join' => array(
		'BlogCategory'
	),
	'fields' => array(
		'BlogPost.*',
		'BlogCategory.*'
	)
)
```

This will LEFT join BlogCategory to BlogPost. Please note you have to declare the fields you wish to load in the fields array.

You can change the join type like:

```
$this->BlogPost->find('first', array(
	'join' => array(
		'BlogCategory' => array(
			'type' => 'inner'
		)
	),
	'fields' => array(
		'BlogPost.*',
		'BlogCategory.*'
	)
)
```
Contributing/Reporting issues
-----------------------------

If you find any issues please report them in GitHub's Issue Tracker and I'll do my best to fix as quickly as possible. Alternatively I welcome pull requests for fixes or new features :-).
