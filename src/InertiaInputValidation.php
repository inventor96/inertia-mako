<?php

namespace inventor96\Inertia;

use Closure;
use mako\http\Request;
use mako\http\Response;
use mako\http\response\senders\Redirect;
use mako\http\response\Status;
use mako\http\routing\middleware\MiddlewareInterface;
use mako\http\routing\URLBuilder;
use mako\session\Session;
use mako\validator\exceptions\ValidationException;
use mako\view\ViewFactory;

class InertiaInputValidation implements MiddlewareInterface
{
	public function __construct(
		protected URLBuilder $urlBuilder,
		protected Session $session,
		protected ViewFactory $viewFactory,
		protected Inertia $inertia,
	) {
	}
	
	public function execute(Request $request, Response $response, Closure $next): Response
	{
		// check for error bag request
		$bag = $request->headers->get('X-Inertia-Error-Bag');

		// assign validation errors to the view (e.g. for the request after a redirect)
		$this->inertia->share([
			'errors' => $bag
				? [$bag => $this->session->getFlash('inertia_errors', [])]
				: $this->session->getFlash('inertia_errors', []),
		]);

		try {
			return $next($request, $response);
		} catch (ValidationException $e) {
			// flash the validation errors to the session
			$this->session->putFlash('inertia_errors', $e->getErrors());

			// redirect back to the previous page with errors, fallback to the current page
			return $response->setBody(new Redirect(
				$request->getReferrer($this->urlBuilder->current()),
				defined(Status::class . '::SeeOther') ? Status::SeeOther : Status::SEE_OTHER,
			));
		}
	}
}