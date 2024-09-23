# Inertia.js Mako Adapter
An [Inertia.js](https://inertiajs.com/) server-side adapter for the PHP [Mako framework](https://makoframework.com/).

## Installation
1. Install the package:
	```bash
	composer require inventor96/inertia-mako
	```

2. Enable the package in Mako:  
	`app/config/application.php`:
	```php
	[
		'packages' => [
			'web' => [
				inventor96\Inertia\InertiaPackage::class,
			],
		],
	];
	```

## Configuration
If you would like to override the default configuration, create a new file at `app/config/packages/inertia/inertia.php`.

The following configuration items and their defaults are as follows:
```php
<?php
return [
	/**
	 * The full HTML page for the initial response to the browser.
	 */
	'html_template' => 'inertia::default',

	/**
	 * The initial title for the page.
	 * Only applicable if using the default view.
	 */
	'title' => 'Loading...',

	/**
	 * The path to the css for the browser to load.
	 * Only applicable if using the default view.
	 */
	'css_path' => '/css/app.css',

	/**
	 * The path to the js for the browser to load.
	 * Only applicable if using the default view.
	 */
	'js_path' => '/js/app.js',
];
```

## Asset Versioning
Inertia.js features [asset versioning](https://inertiajs.com/the-protocol#asset-versioning) to mitigate stale client-side caching. To indicate the server-side version, create a file at `app/config/packages/inertia/version.php` that functions like the following:
```php
return '1.0';
```

You can make that file do whatever you need to come up with your verison. The only requirement is that it ultimately returns a string.