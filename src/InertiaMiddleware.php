<?php

namespace inventor96\Inertia;

use Closure;
use mako\config\Config;
use mako\http\Request;
use mako\http\Response;
use mako\http\routing\middleware\MiddlewareInterface;

class InertiaMiddleware implements MiddlewareInterface
{
	protected Inertia $inertia;
	protected Config $config;

	public function __construct(Inertia $inertia, Config $config) {
		$this->inertia = $inertia;
		$this->config = $config;
	}

	public function execute(Request $request, Response $response, Closure $next): Response {
		// validate inertia version
		if (
			$request->isAjax()
			&& $request->getMethod() === 'GET'
			&& $request->headers->get('X-Inertia')
			&& $request->headers->get('X-Inertia-Version') !== $this->config->get('inertia::version', '1.0'))
		{
			return $this->inertia->location($request->getPath());
		}

		return $next($request, $response);
	}
}