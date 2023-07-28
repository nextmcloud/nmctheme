<?php

declare(strict_types=1);

namespace OCA\NMCTheme\Tests\Service;

use OCP\IConfig;
use OCP\IUser;
use OCP\IUserSession;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\App\IAppManager;

use PHPUnit\Framework\TestCase;

use OCA\NMCTheme\AppInfo\Application;
use OCA\NMCTheme\Service\NMCThemesService;
use OCA\NMCTheme\Themes\TeleNeoWebFont;
use OCA\NMCTheme\Themes\Magenta;
use OCA\NMCTheme\Themes\MagentaDark;

use OCA\Theming\Service\ThemesService;
use OCA\Theming\ThemingDefaults;
use OCA\Theming\ITheme;

use OCA\Theming\Themes\DarkHighContrastTheme;
use OCA\Theming\Themes\DarkTheme;
use OCA\Theming\Themes\DefaultTheme;
use OCA\Theming\Themes\DyslexiaFont;
use OCA\Theming\Themes\HighContrastTheme;
use OCA\Theming\Themes\LightTheme;

class NMCThemesServiceTest extends TestCase
{
    /** @var ThemesService */
    private $themesService;

    /** @var IUserSession */
    private $userSession;
    /** @var IConfig */
    private $config;
    /** @var ThemingDefaults */
    private $themingDefaults;
    /** @var AppManager */
    private $appManager;

    /** @var ITheme */
    private $defaultTheme;
    /** @var ITheme[] */
    private $selectableThemes;
    /** @var ITheme[] */
    private $staticThemes;

    protected function setUp(): void
    {
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

    function testClassMapping() {
        $themeClasses = $this->themesService->getThemes();
        $this->assertCount(7,$themeClasses);
        $this->assertArrayHasKey('magenta25', $themeClasses);
        $this->assertArrayHasKey('magenta25dark', $themeClasses);
        $this->assertArrayHasKey('selectable1', $themeClasses);
        $this->assertArrayHasKey('selectable2', $themeClasses);
        $this->assertArrayHasKey('teleneoweb', $themeClasses);
        $this->assertArrayHasKey('static1', $themeClasses);
        $this->assertArrayHasKey('static2', $themeClasses);
    }

    function testAnonymousDefault() {
        $this->config->expects($this->never())->method('getUserValue');
        $this->config->expects($this->once())->method('getSystemValueString')->willReturn('');
        $this->userSession->expects($this->once())->method('getUser')->willReturn(null);        
    
        $enabledThemes = $this->themesService->getEnabledThemes();
    }

    function testUnthemedUser() {
        $this->config->expects($this->any())->method('getUserValue')->willReturn('[]');
        $this->config->expects($this->once())->method('getSystemValueString')->willReturn('');
        $this->userSession->expects($this->any())->method('getUser')->willReturn($this->user);        
        $this->user->expects($this->any())->method('getUID')->willReturn('0815user');

        $enabledThemes = $this->themesService->getEnabledThemes();
    }


}