<?php
/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author B. Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\NMCTheme\AppInfo;

use OCA\NMCTheme\Listener\BeforeTemplateRenderedListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;

use OCA\Theming\Service\ThemesService;
use OCA\Theming\ITheme;
use OCA\NMCTheme\Themes\Magenta;
use OCA\NMCTheme\Themes\MagentaDark;

class Application extends App implements IBootstrap {
	public const APP_ID = 'nmctheme';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
        // the listener could be helpful to inject additional scripts
        //$context->registerEventListener(BeforeTemplateRenderedEvent::class, BeforeTemplateRenderedListener::class);
    }

	public function boot(IBootContext $context): void {
        /** @var ThemesService $themesService */
        $themesService = $this->getContainer()->get(ThemesService::class);

        /** @var Magenta $magentaDefault */
        $magentaDefault = $this->getContainer()->get(Magenta::class);

        /** @var MagentaDark $magentaDark */
        $magentaDark = $this->getContainer()->get(MagentaDark::class);
        
        $themesService->registerThemes([$magentaDefault, $magentaDark]);
    }
}
