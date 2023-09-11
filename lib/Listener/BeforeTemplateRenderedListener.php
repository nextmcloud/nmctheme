<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author B. Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\NMCTheme\Listener;

use OC\Security\CSP\ContentSecurityPolicyNonceManager;
use OCA\Theming\Util;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

use OCP\IURLGenerator;

class BeforeTemplateRenderedListener implements IEventListener {
	private IURLGenerator $urlGenerator;
	private ContentSecurityPolicyNonceManager $nonceManager;
	private Util $themingUtil;

	public function __construct(
		IURLGenerator $urlGenerator,
		ContentSecurityPolicyNonceManager $nonceManager,
		Util $themingUtil
	) {
		$this->urlGenerator = $urlGenerator;
		$this->nonceManager = $nonceManager;
		$this->themingUtil = $themingUtil;
	}

	/**
	 * The registration of Magenta themes is only done
	 * if rendering in browser takes place. (and not in
	 * application bootstrapping).
	 */
	public function handle(Event $event): void {
		$response = $event->getResponse();

		if (($response->getStatus() >= 400) && ($response->getStatus() < 600)) {
			// render client error states with own layout => own #body-status id
			$tmplparams = $response->getParams();
			$tmplparams['bodyid'] = "body-status";
			$response->setParams($tmplparams);
		}

		// add own mimetypelist from the dynamic service endpoint
		$mimetypelist = $this->urlGenerator->linkToRoute('nmctheme.MimeIcon.getMimeTypeList');
		\OCP\Util::addHeader("script",
			[ 'nonce' => $this->nonceManager->getNonce(),
				'src' => $mimetypelist . '?nmcv=' . $this->themingUtil->getCacheBuster() ],
			''); // the empty text is needed to generate HTML5 valid tags


		// you can add additional styles, links and scripts before rendering
		// keep src for future use:   \OCP\Util::addScript("nmctheme", "../dist/l10nappender");
		\OCP\Util::addScript("nmctheme", "../dist/mimetypes", "core");
		\OCP\Util::addScript('nmctheme', '../dist/nmcfooter', 'theming');
		\OCP\Util::addScript("nmctheme", "../dist/filessettings");
		\OCP\Util::addScript("nmctheme", "../dist/nmcsettings");
		\OCP\Util::addScript("nmctheme", "../dist/nmclogo");
		\OCP\Util::addScript("nmctheme", "../dist/conflictdialog");
	}
}
