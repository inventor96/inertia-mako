<?php

namespace inventor96\Inertia;

use mako\http\Request;

class RedirectDestination
{
	/**
	 * Returns the URL to redirect to after a failed request.
	 *
	 * This ensures that the user is redirected back to the same origin
	 * to prevent open redirect vulnerabilities.
	 *
	 * @param Request $request
	 * @param string $current The current URL, used as the fallback.
	 * @return string
	 */
	public static function safe(Request $request, string $current): string
	{
		$referrer = $request->getReferrer();
		$referrerParts = is_string($referrer) ? parse_url($referrer) : false;
		$currentParts = parse_url($current);

		if (
			is_array($referrerParts)
			&& is_array($currentParts)
			&& isset($referrerParts['scheme'], $referrerParts['host'])
			&& ($referrerParts['scheme'] === $currentParts['scheme'])
			&& ($referrerParts['host'] === $currentParts['host'])
			&& (($referrerParts['port'] ?? null) === ($currentParts['port'] ?? null))
		) {
			return $referrer;
		}

		return $current;
	}
}