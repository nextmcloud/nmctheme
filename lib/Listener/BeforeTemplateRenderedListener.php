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

use OCP\IURLGenerator;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\AppFramework\Http\TemplateResponse;
use OCA\Theming\Service\ThemesService;
use OCA\NMCTheme\Themes\TeleNeoWebFont;

class BeforeTemplateRenderedListener implements IEventListener {
	private IURLGenerator $urlGenerator;
    private ThemesService $themesService;
    private ThemeInjectionService $themeInjectionService;
	private TeleNeoWebFont $teleNeoWebFont;

	public function __construct(
		IURLGenerator $urlGenerator,
        ThemesService $themesService,
		TeleNeoWebFont $teleNeoWebFont) {
        $this->urlGenerator = $urlGenerator;
		$this->themesService = $themesService;
		$this->teleNeoWebFont = $teleNeoWebFont;
	}

    /**
	 * Inject theme header into anonymous pages without user relation
	 *
	 * @param string $themeId the theme ID
	 * @param bool $plain request the :root syntax
	 * @param string $media media query to use in the <link> element
     */
    private function addPublicThemeHeader(string $themeId, bool $plain = true, string $media = null) {
		$linkToCSS = $this->urlGenerator->linkToRoute('theming.Theming.getThemeStylesheet', [
			'themeId' => $themeId,
			'plain' => $plain
		]);
		\OCP\Util::addHeader('link', [
			'rel' => 'stylesheet',
			'media' => $media,
			'href' => $linkToCSS
		]);
	}


	/**
	 * The registration of Magenta themes is only done
	 * if rendering in browser takes place. (and not in
	 * application bootstrapping).
	 */
	public function handle(Event $event): void {
		// always enable TeleNeoWebFonts if this theme is installed
        if ($event->getResponse()->getRenderAs() === TemplateResponse::RENDER_AS_USER) {
            $this->themesService->enableTheme($this->teleNeoWebFont);
        } else {
            // fix Telekom font theme always
            $this->addPublicThemeHeader($this->teleNeoWebFont->getId());

            // TODO: we can add dedicated theme css for anonymous renderings directly or
            // as theme injection
            \OCP\Util::addStyle("nmctheme", "nmcdefault");       
        }
	}
}
