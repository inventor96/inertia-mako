<?php

namespace inventor96\Inertia;

use mako\config\Config;
use mako\http\Response;
use mako\http\response\Status;
use mako\view\ViewFactory;

class Inertia {
	public function __construct(
		protected Response $response,
		protected Config $config,
		protected ViewFactory $view_factory,
	) {}

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

	/**
	 * Render an Inertia page.
	 *
	 * @param string $page The page to render.
	 * @param array $props The props to pass to the page.
	 * @return string The rendered page.
	 */
	public function render(string $page, array $props = []): string {
		return $this->view_factory->render('Pages/' . $page, $props);
	}

	/**
	 * Get the Inertia asset version.
	 *
	 * @return string The Inertia asset version.
	 */
	public function getVersion(): string {
		return $this->config->get('inertia::version.0', 'undefined');
	}
}