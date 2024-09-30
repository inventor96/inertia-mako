<?php
namespace inventor96\Inertia;

use mako\application\Application;
use mako\config\Config;
use mako\file\FileSystem;
use mako\http\Request;
use mako\http\Response;
use mako\view\renderers\RendererInterface;
use mako\view\ViewFactory;
use mindplay\vite\Manifest;

/**
 * @property string $path This is only here to make the IDE happy; the actual property is protected in the `ViewFactory` class.
 */
class InertiaRenderer implements RendererInterface {
	protected string $hot_file;

	public function __construct(
		protected Request $request,
		protected Response $response,
		protected Config $config,
		protected ViewFactory $view_factory,
		protected FileSystem $file_system,
		protected Application $app,
		protected Inertia $inertia,
	) {}

	public function render(string $view, array $data = []): string {
		// build the page object
		$page = $this->getInertiaObject($view, $data);

		// render json response if the request is an inertia request
		if ($this->request->isAjax() && $this->request->headers->get('X-Inertia')) {
			$this->response->headers->add('X-Inertia', 'true');
			$this->response->setType('application/json');
			return $page;
		}

		// build vite stuff for a full page render
		$vite_manifest = $this->getViteManifest();

		// render the full page
		$this->response->setType('text/html');
		return $this->view_factory->render(
			$this->config->get('inertia::inertia.html_template', 'inertia::default'),
			[
				'page' => $page,
				'title' => $this->config->get('inertia::inertia.title', 'Loading...'),
				'tags' => $vite_manifest->createTags('app/resources/js/app.js'),
			]
		);
	}

	protected function getInertiaObject(string $view, array $data): string {
		return json_encode([
			'component' => $this->getVueView($view),
			'props' => $data,
			'url' => $this->request->getPath(),
			'version' => $this->inertia->getVersion(),
		]);
	}

	protected function getVueView(string $view): string {
		return str_replace([
			(fn() => $this->path)->call($this->view_factory) . '/', // remove the view factory's base path
			'Pages/', // remove the pages folder
			'.vue', // remove the file extension
		], '', $view);
	}

	protected function getViteManifest(): Manifest {
		$hot = $this->isHot();

		return new Manifest(
			$hot,
			$this->config->get('inertia::vite.manifest', $this->app->getPath() . '/../public/manifest.json'),
			$hot
				? $this->file_system->get($this->getHotFile()) . '/'
				: $this->config->get('inertia::vite.base_path', '/'),
		);
	}

	protected function isHot(): bool {
		return $this->file_system->isFile($this->getHotFile());
	}

	protected function getHotFile(): string {
		// populate the hot file path if it's not already populated
		if (!isset($this->hot_file)) {
			$this->hot_file = $this->config->get('inertia::vite.hot_file')
				?? $this->app->getPath() . '/../public/hot';
		}

		return $this->hot_file;
	}
}