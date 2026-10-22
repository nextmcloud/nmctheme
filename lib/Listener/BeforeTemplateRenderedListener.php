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
use OCP\AppFramework\Http\Template\PublicTemplateResponse;
use OCP\AppFramework\Http\Template\SimpleMenuAction;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IL10N;
use OCP\INavigationManager;
use OCP\IURLGenerator;

class BeforeTemplateRenderedListener implements IEventListener {
	private const OPEN_SOURCE_URL = 'https://static.magentacloud.de/licences/webui.htm';
	private const DATA_PRIVACY_URL = 'https://static.magentacloud.de/Datenschutz';
	private const IMPRINT_URL = 'https://www.telekom.de/impressum';

	// keeps Download as the primary button
	private const LEGAL_ACTION_PRIORITY = 1000;

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

	protected function addLegalLinksToPublicMenu(PublicTemplateResponse $response): void {
		$response->setHeaderActions([
			new SimpleMenuAction('nmc-open-source', $this->l->t('Open Source information'), 'icon-nmc-open-source', self::OPEN_SOURCE_URL, self::LEGAL_ACTION_PRIORITY),
			new SimpleMenuAction('nmc-data-privacy', $this->l->t('Data privacy'), 'icon-nmc-data-privacy', self::DATA_PRIVACY_URL, self::LEGAL_ACTION_PRIORITY + 1),
			new SimpleMenuAction('nmc-imprint', $this->l->t('Imprint'), 'icon-nmc-imprint', self::IMPRINT_URL, self::LEGAL_ACTION_PRIORITY + 2),
		]);
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

		if ($response instanceof PublicTemplateResponse) {
			$this->addLegalLinksToPublicMenu($response);
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
		\OCP\Util::addScript("nmctheme", "nmctheme-nmclogo", "core");
		\OCP\Util::addScript('nmctheme', 'nmctheme-nmcheader', "core");
		\OCP\Util::addScript('nmctheme', 'nmctheme-nmclegal', "core");
		\OCP\Util::addScript('nmctheme', 'nmctheme-nmcfiles', "core");
		\OCP\Util::addScript("nmctheme", "nmctheme-mimetypes", "core");
		\OCP\Util::addScript("nmctheme", "nmctheme-skipactions", "core");
		\OCP\Util::addScript("nmctheme", "nmctheme-searchfavorites", "core");
		\OCP\Util::addScript("nmctheme", "nmctheme-viewportmetrics", "core");
		\OCP\Util::addScript("nmctheme", "nmctheme-filessettings", "files");
		\OCP\Util::addScript("nmctheme", "nmctheme-filelistplugin", "files");
		\OCP\Util::addScript("nmctheme", "nmctheme-trashbinfix", "files");
	}
}
