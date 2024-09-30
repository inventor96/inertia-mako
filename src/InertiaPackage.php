<?php
namespace inventor96\Inertia;

use mako\application\Package;
use mako\view\ViewFactory;

class InertiaPackage extends Package {
	protected string $packageName = 'inventor96/inertia-mako';
	protected string $fileNamespace = 'inertia';

	/**
	 * @inheritDoc
	 */
	function bootstrap(): void {
		// register the inertia class as a singleton
		$this->container->registerSingleton([Inertia::class, 'inertia'], Inertia::class);

		// register the inertia renderer
		$this->container->get(ViewFactory::class)->extend('.vue', InertiaRenderer::class);
	}
}