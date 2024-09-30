<?php

namespace inventor96\Inertia;

use Closure;
use mako\http\Request;
use mako\http\Response;
use mako\http\routing\middleware\MiddlewareInterface;

class InertiaMiddleware implements MiddlewareInterface
{
	public function __construct(
		protected Inertia $inertia,
	) {}

	public function execute(Request $request, Response $response, Closure $next): Response {
		// validate inertia version
		if (
			$request->isAjax()
			&& $request->getMethod() === 'GET'
			&& $request->headers->get('X-Inertia')
			&& $request->headers->get('X-Inertia-Version') !== $this->inertia->getVersion())
		{
			// TODO: handle re-flashing
			return $this->inertia->location($request->getPath());
		}

		return $next($request, $response);
	}
}