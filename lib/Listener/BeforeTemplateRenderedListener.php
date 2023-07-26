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

use OCP\AppFramework\Services\IInitialState;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCA\Theming\Service\ThemesService;
use OCA\Theming\ITheme;
use OCA\NMCTheme\Themes\Magenta;
use OCA\NMCTheme\Themes\MagentaDark;

class BeforeTemplateRenderedListener implements IEventListener {
	private IInitialState $initialState;
	private IUserSession $userSession;
	private IConfig $config;
	private IURLGenerator $urlGenerator;
    private ThemesService $themesService;

    // own theme descriptions
    private Magenta     $magentaDefault;
    private MagentaDark $magentaDark;


	public function __construct(
		IInitialState $initialState,
		IUserSession $userSession,
		IConfig $config,
		IURLGenerator $urlGenerator,
        ThemesService $themesService,
        Magenta     $magentaDefault,
        MagentaDark $magentaDark) {
		$this->initialState = $initialState;
		$this->container = $container;
		$this->userSession = $userSession;
		$this->config = $config;
		$this->urlGenerator = $urlGenerator;
        $this->themesService = $themesService;
        $this->magentaDefault = $magentaDefault;
        $this->magentaDark = $magentaDark;
	}

    /**
     * The registration of Magenta themes is only done
     * if rendering in browser takes place. (and not in
     * application bootstrapping).
     */
	public function handle(Event $event): void {
        // add Magenta theme modes
        // $this->themesService->registerThemes([$this->magentaDefault, $this->magentaDark]); 
	}
}
