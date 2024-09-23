<?php

namespace inventor96\Inertia;

use mako\http\Response;
use mako\http\response\Status;

class Inertia {
	protected Response $response;

	public function __construct(Response $response) {
		$this->response = $response;
	}

	/**
	 * Redirect the browser window to a new location.
	 *
	 * @param string $url The URL to redirect to.
	 * @return Response The response object.
	 */
	public function location(string $url): Response {
		$this->response->setStatus(Status::CONFLICT);
		$this->response->headers->add('X-Inertia-Location', $url);
		return $this->response;
	}
}