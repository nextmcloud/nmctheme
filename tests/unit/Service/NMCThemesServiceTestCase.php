<?php

declare(strict_types=1);

namespace OCA\NMCTheme\Tests\Service;

use OCA\NMCTheme\AppInfo\Application;
use OCA\NMCTheme\Service\NMCThemesService;
use OCA\NMCTheme\Themes\Magenta;
use OCA\NMCTheme\Themes\MagentaDark;

use OCA\NMCTheme\Themes\TeleNeoWebFont;

use OCA\Theming\ITheme;
use OCA\Theming\Service\ThemesService;
use OCA\Theming\Themes\DarkHighContrastTheme;
use OCA\Theming\Themes\DarkTheme;
use OCA\Theming\Themes\DefaultTheme;

use OCA\Theming\Themes\DyslexiaFont;
use OCA\Theming\Themes\HighContrastTheme;
use OCA\Theming\Themes\LightTheme;

use OCA\Theming\ThemingDefaults;
use OCP\App\IAppManager;
use OCP\IConfig;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

/**
 * Base test fixture for ThemesService unittests.
 *
 * Hint: Make sure that your bootstrap.php contains a PSR4
 * registration for the unittest root dir
 * \OC::$composerAutoloader->addPsr4('OCA\\NMCTheme\\Tests\\', dirname(__FILE__) . '/unit/', true);
 *
 */

class NMCThemesServiceTestCase extends TestCase {
	/** @var ThemesService */
	protected $themesService;

	/** @var IUserSession */
	protected $userSession;
	/** @var IConfig */
	protected $config;
	/** @var ThemingDefaults */
	protected $themingDefaults;
	/** @var AppManager */
	protected $appManager;

	/** @var ITheme */
	protected $defaultTheme;
	/** @var ITheme[] */
	protected $selectableThemes;
	/** @var ITheme[] */
	protected $staticThemes;

	protected function setUp(): void {
		parent::setUp();

		$this->userSession = $this->createMock(IUserSession::class);
		$this->user = $this->createMock(IUser::class);
		$this->config = $this->createMock(IConfig::class);
		$this->themingDefaults = $this->createMock(ThemingDefaults::class);

		$this->themingDefaults->expects($this->any())
			->method('getColorPrimary')
			->willReturn('#0082c9');

		$this->themingDefaults->expects($this->any())
			->method('getDefaultColorPrimary')
			->willReturn('#0082c9');

		$this->appManager = $this->createMock(IAppManager::class);
		//$this->appManager->expects($this->once())
		//    ->method('getAppWebPath')
		//    ->willReturn('/strangeapps/nmctheme/');

		// prpeare a complex theme setup
		$app = new \OCP\AppFramework\App(Application::APP_ID);
		$this->defaultTheme = $app->getContainer()->get(Magenta::class);
		$selectable1 = $this->createMock(ITheme::class);
		$selectable1->expects($this->any())->method('getId')->willReturn("selectable1");
		$selectable1->expects($this->any())->method('getType')->willReturn(ITheme::TYPE_FONT);
		$selectable2 = $this->createMock(ITheme::class);
		$selectable2->expects($this->any())->method('getId')->willReturn("selectable2");
		$selectable2->expects($this->any())->method('getType')->willReturn(ITheme::TYPE_THEME);
		$this->selectableThemes = [ $app->getContainer()->get(MagentaDark::class),
			$selectable1, $selectable2 ];

		$static1 = $this->createMock(ITheme::class);
		$static1->expects($this->any())->method('getId')->willReturn("static1");
		$static1->expects($this->any())->method('getType')->willReturn(ITheme::TYPE_FONT);
		$static2 = $this->createMock(ITheme::class);
		$static2->expects($this->any())->method('getId')->willReturn("static2");
		$static2->expects($this->any())->method('getType')->willReturn(ITheme::TYPE_THEME);
		$this->staticThemes = [ $app->getContainer()->get(TeleNeoWebFont::class),
			$static1, $static2 ];

		$this->themesService = new NMCThemesService(
			$this->userSession,
			$this->config,
			$this->defaultTheme,
			$this->selectableThemes,
			$this->staticThemes,
			$app->getContainer()->get(DefaultTheme::class),
			$app->getContainer()->get(LightTheme::class),
			$app->getContainer()->get(DarkTheme::class),
			$app->getContainer()->get(HighContrastTheme::class),
			$app->getContainer()->get(DarkHighContrastTheme::class),
			$app->getContainer()->get(DyslexiaFont::class)
		);
	}
}
