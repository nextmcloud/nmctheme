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
use OCP\AppFramework\Http\TemplateResponse;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IL10N;
use OCP\INavigationManager;
use OCP\IURLGenerator;

class BeforeTemplateRenderedListener implements IEventListener {
	private IURLGenerator $urlGenerator;
	private ContentSecurityPolicyNonceManager $nonceManager;
	private Util $themingUtil;
	private INavigationManager $navManager;
	private IL10N $l;

	public function __construct(
		IURLGenerator $urlGenerator,
		ContentSecurityPolicyNonceManager $nonceManager,
		Util $themingUtil,
		INavigationManager $navManager,
		IL10N $l) {
		$this->urlGenerator = $urlGenerator;
		$this->nonceManager = $nonceManager;
		$this->themingUtil = $themingUtil;
		$this->navManager = $navManager;
		$this->l = $l;
	}

	/**
	 * FIXME The navigation bar entry for Photos app has label photos, but the app
	 * is also managing videos. Unfortunately, changing the translations for Photos to
	 * Media also changes
	 *
	 * The final fix in photos app should change the folder name with separate labels
	 * for appname and
	 *
	 * Meanwhile, we can "teach" the navigation manager to rename photos app in menu.
	 */
	protected function fixPhotosAppName() {
		if ($this->navManager !== null) {
			$entries = $this->navManager->getAll();
			if (array_key_exists('photos', $entries)) {
				$entry = $entries['photos'];
				$entry['name'] = $this->l->t('PhotosMedia');
				$this->navManager->add($entry); // this also replaces entries ;-)
			}
		}
	}

	/**
	 * The registration of Magenta themes is only done
	 * if rendering in browser takes place. (and not in
	 * application bootstrapping).
	 */
	public function handle(Event $event): void {
		$response = $event->getResponse();

		// FIXME as long as photos app has photos as internal label and appname label
		if ($response->getRenderAs() === TemplateResponse::RENDER_AS_USER ||
			 $response->getRenderAs() === TemplateResponse::RENDER_AS_PUBLIC) {
			$this->fixPhotosAppName();
		}

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
		\OCP\Util::addScript("nmctheme", "nmctheme-mimetypes", "core");
		\OCP\Util::addScript('nmctheme', 'nmctheme-nmcfooter', 'theming');
		\OCP\Util::addScript("nmctheme", "nmctheme-newfilemenuplugin", "files");
		\OCP\Util::addScript("nmctheme", "nmctheme-filelistplugin", "files");
		\OCP\Util::addScript("nmctheme", "nmctheme-filessettings", "files");
		\OCP\Util::addScript("nmctheme", "nmctheme-nmclogo");
		\OCP\Util::addScript("nmctheme", "nmctheme-conflictdialog");
	}
}
