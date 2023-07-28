<?php
/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author Bernd Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\NMCTheme\Service;

use OCP\IConfig;
use OCP\IUser;
use OCP\IUserSession;

use OCA\NMCTheme\AppInfo\Application;

use OCA\Theming\ITheme;
use OCA\Theming\Service\ThemesService;

use OCA\Theming\Themes\DarkHighContrastTheme;
use OCA\Theming\Themes\DarkTheme;
use OCA\Theming\Themes\DefaultTheme;
use OCA\Theming\Themes\DyslexiaFont;
use OCA\Theming\Themes\HighContrastTheme;
use OCA\Theming\Themes\LightTheme;


/**
 * An alternative ThemesService with the following properties:
 * - you can define the list of themes on boot in service registration
 * - you can set default theme to use
 * - you can define a set of "static" themes to always to include
 * - if no user theme is set, the default (either configured or from registration) is used
 */
class NMCThemesService extends ThemesService {
	private IUserSession $userSession;
	private IConfig $config;

	/** @var ITheme */
	private array $defaultTheme;

    /** @var ITheme[] */
	private array $themeClasses;

    /** @var string[] */
	private array $staticThemeIds;

    /** @var string[] */
	private array $selectableThemeIds;

    /** @var string[] */
	private array $selectableFontIds;

    /**
     * Register the different types of themes on service creation
     * 
     * @param ITheme   $default          The registered default theme that is
     *                                   used as fallback. It is also selectable.
     * @param ITheme[] $selectableThemes a list of themes to select one from
     *                                   as the user theme
     * @param ITheme[] $staticThemes     a list of themes to always include
     */
	public function __construct(IUserSession $userSession,
								IConfig $config,
                                ITheme $default,
                                array $selectableThemes,
                                array $staticThemes,
                                DefaultTheme $defaultTheme,
								LightTheme $lightTheme,
								DarkTheme $darkTheme,
								HighContrastTheme $highContrastTheme,
								DarkHighContrastTheme $darkHighContrastTheme,
								DyslexiaFont $dyslexiaFont) {
        parent::__construct($userSession, $config, $defaultTheme, $lightTheme, 
                            $darkTheme, $highContrastTheme, $darkHighContrastTheme, $dyslexiaFont);
		$this->userSession = $userSession;
		$this->config = $config;

        $mapThemeIds = function (ITheme $theme) {
            return $theme->getId();
        };
        
		// Register themes
        $this->default = $default;
        $this->staticThemeIds = array_map($mapThemeIds, $staticThemes);
        $nonStaticThemes = array_merge([$default], $selectableThemes);
        $nonStaticThemeIds = array_merge(['default'], array_map($mapThemeIds, $selectableThemes));
		$this->selectableThemeIds = array_map($mapThemeIds,
                                              array_filter($nonStaticThemes, function(ITheme $theme) { 
                                                return $theme->getType() == ITheme::TYPE_THEME; 
                                              }));
        $this->selectableFontIds = array_map($mapThemeIds,
                                              array_filter($nonStaticThemes, function(ITheme $theme) { 
                                                return $theme->getType() == ITheme::TYPE_FONT; 
                                              }));
        $this->themeClasses = array_merge( array_combine($nonStaticThemeIds, $nonStaticThemes),
                                           array_combine($this->staticThemeIds, $staticThemes) );
    }

	/**
	 * Get the list of all registered themes
	 * 
	 * @return ITheme[] the (id, $theme) map of registered themes
	 */
	public function getThemes(): array {
		return $this->themeClasses;
	}

	/**
	 * Get the list of all enabled themes IDs
	 * for the logged-in user
	 * 
	 * @return string[]
	 */
	public function getEnabledThemes(): array {
        $enforcedThemeId = $this->config->getSystemValueString('enforce_theme', '');
        if ($enforcedThemeId !== '') {
            // a theme enforce page setup, make sure that the theme is in selectable list
            return array_unique(array_merge([$enforcedThemeId], $this->staticThemeIds));
		}

        $user = $this->userSession->getUser();
		if ($user === null) {
            // a non-enforce, anonymous page setup
			return array_unique(array_merge( [$this->default->getId()], $this->staticThemeIds));
		}

        // a user selected page setup
        $enabledThemeIds = json_decode($this->config->getUserValue($user->getUID(), Application::APP_ID,
                                     'enabled-themes', '[]'));
        $selectedThemeIds = array_intersect($this->selectableThemeIds, $enabledThemeIds);
        if ( empty($selectedThemeIds) ) {
            // add default to the enabled fonts adn statics (as there is no selected theme)
            return array_unique(array_merge([$this->default->getId()], $enabledThemeIds, $this->staticThemeIds));
        }

		return array_unique(array_merge($selectedThemes, $this->staticThemeIds));
    }

	/**
	 * Enable a theme for the logged-in user
	 * 
	 * @param ITheme $theme the theme to enable
	 * @return string[] the enabled themes
	 */
	public function enableTheme(ITheme $theme): array {
		$themeIds = $this->getEnabledThemes();

		// If already enabled, ignore
		if (in_array($theme->getId(), $themeIds)) {
			return $themeIds;
		}

        // we are not in user context
        $user = $this->userSession->getUser();
        if ($user === null) {
            return $themeIds;            
        }
        
        if (in_array($theme->getId(), $this->selectableThemeIds)) {
            // change selection and sort it
            $themeIds = array_unique(
                            array_merge([$theme->getId()], 
                                        array_diff($themeIds, $this->selectableThemeIds)));
        } else if (in_array($theme->getId(), $this->selectableFontIds)) {
            $themeIds = array_unique(array_merge([$theme->getId()], $themeIds));
        } // statics are already always included

        $this->config->setUserValue($user->getUID(), Application::APP_ID, 
                                    'enabled-themes', json_encode(array_values($themeIds)));
		return $themeIds;
	}

	/**
	 * Disable a theme for the logged-in user
	 * 
	 * @param ITheme $theme the theme to disable
	 * @return string[] the enabled themes
	 */
	public function disableTheme(ITheme $theme): array {
        $themeIds = $this->getEnabledThemes();

        $foundId = array_search($theme->getId(), $themeIds);

		// If already disabled, ignore
		if (!$foundId) {
			return $themeIds;
		}

		if (in_array($theme->getId(), $this->staticThemeIds)) {
            return $themeIds;
        }        

        // we are not in user context
        $user = $this->userSession->getUser();
        if ($user === null) {
            return $themeIds;            
        }
        
        // we are not testing for not disabling a selectable theme as this
        // may produces trouble with Nextcloud settings page (TODO)

        array_splice($themeIds, $foundId, $foundId);
        $this->config->setUserValue($user->getUID(), Application::APP_ID, 
                                    'enabled-themes', json_encode(array_values($themeIds)));
		return $themeIds;
	}

	/**
	 * Check whether a theme is enabled or not
	 * 
	 * @return bool
	 */
	public function isEnabled(ITheme $theme): bool {
		$themeIds = $this->getEnabledThemes();
		return in_array($theme->getId(), $themeIds);
	}

}
