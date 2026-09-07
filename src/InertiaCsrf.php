<?php

namespace inventor96\Inertia;

use Closure;
use mako\config\Config;
use mako\http\exceptions\InvalidTokenException;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\senders\Redirect;
use mako\http\response\Status;
use mako\http\routing\middleware\MiddlewareInterface;
use mako\http\routing\URLBuilder;
use mako\security\Key;
use mako\security\Signer;
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
	 * A fake session token using characters that shouldn't appear in a real
	 * hash. Mako's signer uses `hash_hmac('sha256', ...)`, so we use 64
	 * characters.
	 */
	protected const FAKE_TOKEN = '&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&:0';

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

	protected int $tokenTtl;
	protected int $cookieTtl;
	protected Signer $derivedSigner;

	public function __construct(
		protected Config $config,
		protected Session $session,
		protected URLBuilder $urlBuilder,
		protected ?bool $required = null,
	) {
		// validate token_ttl
		$this->tokenTtl = $this->config->get('inertia::csrf.token_ttl', 3600);
		if ($this->tokenTtl <= 0) {
			throw new \InvalidArgumentException('CSRF token TTL must be greater than 0.');
		}

		// validate cookie_ttl
		$this->cookieTtl = $this->config->get('inertia::csrf.cookie_ttl', 0);
		if ($this->cookieTtl < 0 || ($this->cookieTtl > 0 && $this->cookieTtl < $this->tokenTtl)) {
			throw new \InvalidArgumentException('CSRF cookie TTL must be 0 or at least as long as the token TTL.');
		}

		$this->derivedSigner = new Signer(Key::decode($this->config->get('application.secret')) . 'inertia-csrf-session-binding-v1');
	}

	public function execute(Request $request, Response $response, Closure $next): Response {
		// check if a token is required
		$required = $this->required === true
			|| (
				$this->required !== false
				&& in_array($request->getMethod(), self::STATE_CHANGERS, true)
			);

		// check if the token is required
		if ($required) {
			$raw_cookie_token = $request->cookies->get('XSRF-TOKEN', '');
			$cookie_token = $request->cookies->getSigned('XSRF-TOKEN', self::FAKE_TOKEN);
			$req_token = $request->headers->get('X-XSRF-TOKEN', '');

			// do the checks independently so processing is constant-time and prevents timing attacks
			$is_valid = hash_equals($raw_cookie_token, $req_token);
			$is_valid = !hash_equals(self::FAKE_TOKEN, $cookie_token) && $is_valid;
			$is_valid = $this->validateToken($cookie_token) && $is_valid;

			// check if the token is invalid
			if (!$is_valid) {
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
					return $response->setBody(new Redirect(
						$this->getRedirectUrl($request),
						defined(Status::class . '::SeeOther') ? Status::SeeOther : Status::SEE_OTHER,
					));
				} else {
					// throw an exception
					throw new InvalidTokenException('The page was expired. Please refresh the page and try again.');
				}
			}
		}

		// call the next middleware/handler
		$response = $next($request, $response);

		// set new CSRF token
		/** @var Response $response */
		$response->cookies->addSigned(
			'XSRF-TOKEN',
			$this->getDerivedSessionId() . ':' . time(),
			$this->cookieTtl,
			array_merge(self::COOKIE_OPTIONS, $this->config->get('inertia::csrf.cookie_options', [])),
		);

		return $response;
	}

	/**
	 * Returns the derived session ID used for CSRF token validation.
	 *
	 * This enables the session ID to remain protected, and separate from usual
	 * application signing.
	 *
	 * @return string
	 */
	protected function getDerivedSessionId(): string
	{
		return $this->derivedSigner->sign($this->session->getId());
	}

	/**
	 * Validates the CSRF token.
	 *
	 * The token is considered valid if it matches the current session ID and
	 * has not expired based on the configured cookie TTL.
	 *
	 * @param string $token
	 * @return boolean
	 */
	protected function validateToken(string $token): bool
	{
		$parts = explode(':', $token, 2);
		if (count($parts) !== 2 || $parts[0] === '' || !preg_match('/^\d+$/D', $parts[1])) {
			return false;
		}

		$timestamp = (int) $parts[1];
		$now = time();

		$is_valid = hash_equals($this->getDerivedSessionId(), $parts[0]); // session hashes are the same
		$is_valid = $timestamp <= $now && $is_valid; // token is not from the future
		$is_valid = ($now - $timestamp) <= $this->tokenTtl && $is_valid; // token has not expired

		return $is_valid;
	}

	/**
	 * Returns the URL to redirect to after a CSRF validation failure.
	 *
	 * This ensures that the user is redirected back to the same origin
	 * to prevent open redirect vulnerabilities.
	 *
	 * @param Request $request
	 * @return string
	 */
	protected function getRedirectUrl(Request $request): string
	{
		$current = $this->urlBuilder->current();
		$referrer = $request->getReferrer();
		$referrerParts = is_string($referrer) ? parse_url($referrer) : false;
		$currentParts = parse_url($current);

		if (
			is_array($referrerParts)
			&& is_array($currentParts)
			&& isset($referrerParts['scheme'], $referrerParts['host'])
			&& ($referrerParts['scheme'] === $currentParts['scheme'])
			&& ($referrerParts['host'] === $currentParts['host'])
			&& (($referrerParts['port'] ?? null) === ($currentParts['port'] ?? null))
		) {
			return $referrer;
		}

		return $current;
	}
}