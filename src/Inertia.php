<?php

namespace inventor96\Inertia;

use mako\config\Config;
use mako\http\Response;
use mako\http\response\Status;
use mako\view\ViewFactory;

class Inertia {
	/**
	 * The shared data that will be available on all Inertia pages, including
	 * any closures that will be executed when the data is needed.
	 */
	protected array $share_data = [];

	/**
	 * The keys of the shared data. This gets populated when the shared data is
	 * retrieved, and is used to populate the `sharedProps` property that
	 * InertiaRenderer provides to the client.
	 */
	protected array $share_keys = [];

	public function __construct(
		protected Response $response,
		protected Config $config,
		protected ViewFactory $view_factory,
	) {
		$this->view_factory->autoAssign('*', fn() => $this->getShareData());
	}

	/**
	 * Redirect the browser window to a new location.
	 *
	 * @param string $url The URL to redirect to.
	 * @return Response The response object.
	 */
	public function location(string $url): Response {
		$this->response->setStatus(defined(Status::class . '::Conflict') ? Status::Conflict : Status::CONFLICT);
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

	/**
	 * Share data with all Inertia pages. This can be used to share data that
	 * is needed on every page, such as the authenticated user or flash
	 * messages.
	 *
	 * @param mixed $data The data to share. This can be an array of key-value
	 *     pairs, or a closure that returns such an array. The closure will be
	 *     executed when the data is actually needed, allowing for lazy
	 *     evaluation of shared data.
	 * @return void
	 */
	public function share(mixed $data): void {
		// if the data is a closure, store the closure for later
		if ($data instanceof \Closure) {
			$this->share_data[] = $data;
			return;
		}

		// if the data is an array, merge it with the existing shared data.
		$this->share_data = array_merge($this->share_data, $data);
	}

	/**
	 * Get the shared data, including executing any closures and merging their results.
	 *
	 * @return array
	 */
	protected function getShareData(): array {
		// collect shared data, including executing any closures
		$data = [];
		foreach ($this->share_data as $key => $item) {
			if ($item instanceof \Closure) {
				$data = array_merge($data, $item());
			} else {
				$data = array_merge($data, [$key => $item]);
			}
		}

		// include the keys as sharedProps
		$this->share_keys = [
			...array_keys($data),

			// include Mako View Factory values
			'__charset__',
			'__viewfactory__',
		];

		// return the final set
		return $data;
	}

	/**
	 * Get the keys of the shared data. This is used to populate the
	 * `sharedProps` property that InertiaRenderer provides to the client. This
	 * needs to be called after the shared data has been retrieved at least 
	 * once, so that the keys can be populated. This should happen by default
	 * because the view factory auto-assignment happens before the renderer is
	 * called. The auto-assignment calls `getShareData()` (which populates the
	 * keys), and then the renderer calls `getInertiaObject()`, which calls
	 * `getShareKeys()` to populate the `sharedProps` property.
	 *
	 * @return array The keys of the shared data.
	 */
	public function getShareKeys(): array {
		return $this->share_keys;
	}
}