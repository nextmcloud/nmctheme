<?php

declare(strict_types=1);

namespace OCA\NMCTheme\Tests\Service;

class NMCThemesServiceTest extends NMCThemesServiceTestCase {
	public function testClassMapping() {
		$themeClasses = $this->themesService->getThemes();
		$this->assertCount(8, $themeClasses);
		// the default theme is not registered by own name
		// check key->class mapping
		$this->assertArrayHasKey('default', $themeClasses);
		$this->assertEquals($this->defaultTheme, $themeClasses['default']);
		$this->assertArrayHasKey('magenta25', $themeClasses, "No original name registration for default");
		$this->assertEquals($this->defaultTheme, $themeClasses['magenta25']);
		$this->assertArrayHasKey('magenta25dark', $themeClasses);
		$this->assertEquals($this->selectableThemes[0], $themeClasses['magenta25dark']);
		$this->assertArrayHasKey('selectable1', $themeClasses);
		$this->assertEquals($this->selectableThemes[1], $themeClasses['selectable1']);
		$this->assertArrayHasKey('selectable2', $themeClasses);
		$this->assertEquals($this->selectableThemes[2], $themeClasses['selectable2']);
		$this->assertArrayHasKey('teleneoweb', $themeClasses);
		$this->assertEquals($this->staticThemes[0], $themeClasses['teleneoweb']);
		$this->assertArrayHasKey('static1', $themeClasses);
		$this->assertEquals($this->staticThemes[1], $themeClasses['static1']);
		$this->assertArrayHasKey('static2', $themeClasses);
		$this->assertEquals($this->staticThemes[2], $themeClasses['static2']);
	}

    /**
     * Test situation is
     * - anonymous page, no login
     * - no enforced theme in server config
     */
	public function testAnonymousDefault() {
		$this->config->expects($this->never())->method('getUserValue');
		$this->userSession->expects($this->once())->method('getUser')->willReturn(null);
		$this->config->expects($this->once())->method('getSystemValueString')
			->with($this->equalTo('enforce_theme'), $this->equalTo(''))->willReturn('');
	
		$enabledThemes = $this->themesService->getEnabledThemes();
		$this->assertCount(4, $enabledThemes);
		$this->assertContains('default', $enabledThemes);
		$this->assertContains('teleneoweb', $enabledThemes);
		$this->assertContains('static1', $enabledThemes);
		$this->assertContains('static2', $enabledThemes);
 
	}

    /**
     * Test situation is
     * - anonymous page, no login
     * - enforced theme in server config
     */
	public function testAnonymousDefaultEnforced() {
		$this->config->expects($this->never())->method('getUserValue');
		$this->userSession->expects($this->never())->method('getUser');
		$this->config->expects($this->once())->method('getSystemValueString')
			->with($this->equalTo('enforce_theme'), $this->equalTo(''))->willReturn('selectable1');
	
		$enabledThemes = $this->themesService->getEnabledThemes();
		$this->assertCount(4, $enabledThemes);
		$this->assertNotContains('default', $enabledThemes);
		$this->assertContains('selectable1', $enabledThemes);
		$this->assertContains('teleneoweb', $enabledThemes);
		$this->assertContains('static1', $enabledThemes);
		$this->assertContains('static2', $enabledThemes);
	}

    /**
     * Test situation is
     * - user logged in
     * - enforced theme in server config
     * - enforcement uses the real name of the default theme
     */
	public function testUserDefaultEnforced() {
		$this->config->expects($this->never())->method('getUserValue');
		$this->userSession->expects($this->never())->method('getUser');
		$this->config->expects($this->once())->method('getSystemValueString')
			->with($this->equalTo('enforce_theme'), $this->equalTo(''))->willReturn('magenta25');
	
		$enabledThemes = $this->themesService->getEnabledThemes();
		$this->assertCount(4, $enabledThemes);
		$this->assertNotContains('default', $enabledThemes);
		$this->assertContains('magenta25', $enabledThemes);
		$this->assertContains('teleneoweb', $enabledThemes);
		$this->assertContains('static1', $enabledThemes);
		$this->assertContains('static2', $enabledThemes);
	}

    /**
     * Test situation is
     * - user logged in
     * - user has no perferences set yet for themes
     */
	public function testUnthemedUser() {
		$this->config->expects($this->any())->method('getUserValue')->willReturn('[]');
		$this->config->expects($this->once())->method('getSystemValueString')->willReturn('');
		$this->userSession->expects($this->any())->method('getUser')->willReturn($this->user);
		$this->user->expects($this->any())->method('getUID')->willReturn('0815user');

		$enabledThemes = $this->themesService->getEnabledThemes();
        $this->assertCount(4, $enabledThemes);
		$this->assertContains('default', $enabledThemes);
		$this->assertContains('teleneoweb', $enabledThemes);
		$this->assertContains('static1', $enabledThemes);
		$this->assertContains('static2', $enabledThemes);
	}

    /**
     * Test situation is
     * - user logged in
     * - user has only non-existing preferences set
     */
    public function testUserObsoleteOnly() {
		$this->config->expects($this->any())->method('getUserValue')->willReturn('["blackbeard","sparrow"]');
		$this->config->expects($this->once())->method('getSystemValueString')->willReturn('');
		$this->userSession->expects($this->any())->method('getUser')->willReturn($this->user);
		$this->user->expects($this->any())->method('getUID')->willReturn('0815user');

		$enabledThemes = $this->themesService->getEnabledThemes();
        $this->assertCount(4, $enabledThemes);
		$this->assertContains('default', $enabledThemes);
		$this->assertContains('teleneoweb', $enabledThemes);
		$this->assertContains('static1', $enabledThemes);
		$this->assertContains('static2', $enabledThemes);
	}


    /**
     * Test situation is
     * - user logged in
     * - user has a mix of existing and non-existing set, but none is a selectable theme
     *   (default is then the main theme to use)
     */
	public function testObsoleteAndActualNoUserThemeSelected() {
		$this->config->expects($this->once())->method('getSystemValueString')->willReturn('');
		$this->userSession->expects($this->any())->method('getUser')->willReturn($this->user);
		$this->user->expects($this->any())->method('getUID')->willReturn('0815user');
		$this->config->expects($this->any())->method('getUserValue')
            ->with($this->equalTo('0815user'), $this->equalTo('nmctheme'),
                    $this->equalTo('enabled-themes'), $this->equalTo('[]'))
            ->willReturn('["darkforce","selectable1","teleneoweb","bluebay","static1"]');

		$enabledThemes = $this->themesService->getEnabledThemes();
        $this->assertCount(5, $enabledThemes);
        $this->assertContains('default', $enabledThemes);
        $this->assertContains('selectable1', $enabledThemes);
		$this->assertContains('teleneoweb', $enabledThemes);
		$this->assertContains('static1', $enabledThemes);
		$this->assertContains('static2', $enabledThemes);
	}

    /**
     * Test situation is
     * - user logged in
     * - user has a mix of existing and non-existing set
     * - selection contains a main theme
     */
	public function testObsoleteAndActualUserThemeSelected() {
		$this->config->expects($this->once())->method('getSystemValueString')->willReturn('');
		$this->userSession->expects($this->any())->method('getUser')->willReturn($this->user);
		$this->user->expects($this->any())->method('getUID')->willReturn('0815user');
		$this->config->expects($this->any())->method('getUserValue')
            ->with($this->equalTo('0815user'), $this->equalTo('nmctheme'),
                    $this->equalTo('enabled-themes'), $this->equalTo('[]'))
            ->willReturn('["selectable2","selectable1","teleneoweb","bluebay","static1"]');

		$enabledThemes = $this->themesService->getEnabledThemes();
        $this->assertCount(5, $enabledThemes);
        $this->assertContains('selectable1', $enabledThemes);
        $this->assertContains('selectable2', $enabledThemes);
		$this->assertContains('teleneoweb', $enabledThemes);
		$this->assertContains('static1', $enabledThemes);
		$this->assertContains('static2', $enabledThemes);
	}

}
