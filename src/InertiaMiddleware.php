<?php

namespace inventor96\Inertia;

use Closure;
use mako\http\Request;
use mako\http\Response;
use mako\http\routing\middleware\MiddlewareInterface;
use mako\session\Session;

class InertiaMiddleware implements MiddlewareInterface
{
	public function __construct(
		protected Inertia $inertia,
		protected ?Session $session = null,
	) {}

	public function execute(Request $request, Response $response, Closure $next): Response {
		// validate inertia version
		if (
			$request->isAjax()
			&& $request->getMethod() === 'GET'
			&& $request->headers->get('X-Inertia')
			&& $request->headers->get('X-Inertia-Version') !== $this->inertia->getVersion())
		{
			$this->session?->reflash();
			$q = http_build_query($request->getQuery()->all());
			return $this->inertia->location($request->getPath() . ($q ? '?' . $q : ''));
		}

		return $next($request, $response);
	}
}