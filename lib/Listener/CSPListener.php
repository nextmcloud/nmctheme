<?php

declare(strict_types=1);

namespace OCA\NMCTheme\Listener;

use OC\Security\CSP\ContentSecurityPolicy;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IRequest;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

class CSPListener implements IEventListener {

	private IRequest $request;
	private function isPageLoad(): bool {
		$scriptNameParts = explode('/', $this->request->getScriptName());
		return end($scriptNameParts) === 'index.php';
	}

	public function __construct(IRequest $request) {
		$this->request = $request;
	}

	public function handle(Event $event): void {
		if (!$event instanceof AddContentSecurityPolicyEvent) {
			return;
		}
		if (!$this->isPageLoad()) {
			return;
		}

		$csp = new ContentSecurityPolicy();
		$csp->addAllowedImageDomain('\'self\'');
		$csp->addAllowedMediaDomain('\'self\'');
		$csp->addAllowedConnectDomain('\'self\'');

		//Add the policy to the event
		$event->addPolicy($csp);
	}
}
