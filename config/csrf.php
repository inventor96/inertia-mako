
<?php
return [
	/*
	 * Whether to use the errors prop for token validation
	 * errors. By default, an exception will be thrown. If
	 * the error should be returned via the Inertia `errors`
	 * prop, set this to true.
	 */
	'use_errors_prop' => false,

	/*
	 * The lifetime of the cookie in seconds. 0 means "until
	 * the browser is closed".
	 */
	'cookie_ttl' => 0,

	'cookie_options' => [
		/*
		 * The path on the server in which the cookie will
		 * be available on. If set to '/', the cookie will
		 * be available within the entire domain. If set to
		 * '/foo/', the cookie will only be available within
		 * the /foo/ directory and all sub-directories.
		 */
		'path' => '/',

		/*
		 * The domain that the cookie is available to. To
		 * make the cookie available on all subdomains of
		 * example.org (including example.org itself) then
		 * you'd set it to '.example.org'.
		 */
		'domain' => '',

		/*
		 * Indicates that the cookie should only be
		 * transmitted over a secure HTTPS connection from
		 * the client. When set to TRUE, the cookie will
		 * only be set if a secure connection exists. On
		 * the server-side, it's on the programmer to send
		 * this kind of cookie only on secure connection
		 * (e.g. with respect to $this->request->isSecure()).
		 */
		'secure' => false,

		/*
		 * When TRUE the cookie will be made accessible only
		 * through the HTTP protocol. This means that the
		 * cookie won't be accessible by scripting languages,
		 * such as JavaScript. Since the cookie is likely needed
		 * by the JavaScript HTTP library in the frontend, it is
		 * highly recommended to set this to FALSE.
		 */
		'httponly' => false,

		/*
		 * The supported values are 'Lax', 'Strict' and 'None'.
		 *
		 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite
		 */
		'samesite' => 'Lax',
	],
];