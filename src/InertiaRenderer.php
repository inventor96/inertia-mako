<?php
namespace inventor96\Inertia;

use mako\config\Config;
use mako\http\Request;
use mako\http\Response;
use mako\view\renderers\RendererInterface;
use mako\view\ViewFactory;

class InertiaRenderer implements RendererInterface {
	protected Request $request;
	protected Response $response;
	protected Config $config;
	protected ViewFactory $view_factory;

	public function __construct(Request $request, Response $response, Config $config, ViewFactory $view_factory) {
		$this->request = $request;
		$this->response = $response;
		$this->config = $config;
		$this->view_factory = $view_factory;
	}

	public function render(string $view, array $data = []): string {
		// build the inertia page
		$page = json_encode([
			'component' => $view,
			'props' => $data,
			'url' => $this->request->getPath(),
			'version' => $this->config->get('inertia::version', '1.0'),
		]);

		// render json response if the request is an inertia request
		if ($this->request->isAjax() && $this->request->headers->get('X-Inertia')) {
			$this->response->headers->add('X-Inertia', 'true');
			$this->response->setType('application/json');
			return $page;
		}

		// render the base view
		$this->response->setType('text/html');
		return $this->view_factory->render(
			$this->config->get('inertia::inertia.html_template', 'inertia::default'),
			[
				'page' => $page,
				'title' => $this->config->get('inertia::inertia.title', 'Loading...'),
				'css_path' => $this->config->get('inertia::inertia.css_path', '/assets/css/app.css'),
				'js_path' => $this->config->get('inertia::inertia.js_path', '/assets/js/app.js'),
			]
		);
	}
}