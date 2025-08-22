<?php

namespace inventor96\Inertia;

use Closure;
use mako\config\Config;
use mako\http\exceptions\InvalidTokenException;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\senders\Redirect;
use mako\http\routing\middleware\MiddlewareInterface;
use mako\http\routing\URLBuilder;
use mako\session\Session;
use mako\utility\Arr;

class InertiaCsrf implements MiddlewareInterface
{
	/**
	 * @var array An array of HTTP methods that would cause a change in the application state.
	 */
	protected const STATE_CHANGERS = [
		'POST',
		'PUT',
		'PATCH',
		'DELETE',
	];

	/**
	 * @var array An array of default options for the CSRF cookie.
	 */
	protected const COOKIE_OPTIONS = [
		'path' => '/',
		'domain' => '',
		'secure' => false,
		'httponly' => false,
		'samesite' => 'Lax',
	];

	public function __construct(
		protected Config $config,
		protected Session $session,
		protected URLBuilder $urlBuilder,
		protected ?bool $required = null,
	) {}

	public function execute(Request $request, Response $response, Closure $next): Response {
		// set new CSRF token
		$response->cookies->add(
			'XSRF-TOKEN',
			$this->session->generateOneTimeToken(),
			$this->config->get('inertia::csrf.cookie_ttl', 0),
			array_merge(self::COOKIE_OPTIONS, $this->config->get('inertia::csrf.cookie_options', [])),
		);

		// check if a token is required
		$required = $this->required === true
			|| (
				$this->required !== false
				&& in_array($request->getMethod(), self::STATE_CHANGERS, true)
			);

		// check if the token is required
		if ($required) {
			$req_token = $request->headers->get('X-XSRF-TOKEN');
			
			// check if the token is invalid
			if (empty($req_token) || !$this->session->validateOneTimeToken($req_token)) {
				// check if we should use a prop or throw an exception
				if ($prop = $this->config->get('inertia::csrf.use_prop', false)) {
					// handle nested props
					$parts = explode('.', $prop, 2);
					if (count($parts) > 1) {
						$flash = $this->session->getFlash($parts[0], []);
						Arr::set($flash, $parts[1], 'The page was expired. Please try again.');
					} else {
						$flash = 'The page was expired. Please try again.'; // no refresh needed when using a prop
					}

					// set the error message
					$this->session->putFlash($parts[0], $flash);

					// redirect back to the previous page with errors
					return $response->setBody(new Redirect($this->urlBuilder->current(), Redirect::SEE_OTHER));
				} else {
					// throw an exception
					throw new InvalidTokenException('The page was expired. Please refresh the page and try again.');
				}
			}
		}

		return $next($request, $response);
	}
}