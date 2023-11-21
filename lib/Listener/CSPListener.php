<?php

declare(strict_types=1);

namespace OCA\NMCTheme\Listener;

use OC\Security\CSP\ContentSecurityPolicy;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\IRequest;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

class CSPListener implements IEventListener {

	private IRequest $request;
	private IConfig $iConfig;
	private function isPageLoad(): bool {
		$scriptNameParts = explode('/', $this->request->getScriptName());
		return end($scriptNameParts) === 'index.php';
	}

	public function __construct(IRequest $request, IConfig $iConfig) {
		$this->request = $request;
		$this->iConfig = $iConfig;
	}

	public function handle(Event $event): void {
		if (!$event instanceof AddContentSecurityPolicyEvent) {
			return;
		}
		if (!$this->isPageLoad()) {
			return;
		}
		$response = $event->getResponse();

		$csp = new ContentSecurityPolicy();
		$csp->addAllowedImageDomain('\'self\'');
		$csp->addAllowedMediaDomain('\'self\'');
		$csp->addAllowedConnectDomain('\'self\'');
		$response->setContentSecurityPolicy($csp);
	}
}
