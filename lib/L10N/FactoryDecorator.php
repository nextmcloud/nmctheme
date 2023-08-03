<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author B. Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\NMCTheme\L10N;


use OCP\IConfig;
use OCA\NMCTheme\IL10N;
use OCP\IUser;
use OCP\L10N\IFactory;
use OCP\L10N\ILanguageIterator;
use OC\L10N\LazyL10N;

use OCA\NMCTheme\L10N\L10NDecorator;
use OCA\NMCTheme\L10N\L10N;

class FactoryDecorator implements IFactory {
    private IFactory $decoratedFactory;

    /**
	 * cached instances
     * 
     * It invalidates the instances from the decorated factory,
     * but the instances are only referenced in get() anyway.
     * 
	 * @var string[][][]: Translation matrix in the form
     *                    translations[$lang][$app][$msg] => $translation
	 */
	protected array $instances = [];

    /**
     * The translation overrides from the combined language map in
     * nmctheme. The overrides are read only once. This is not made for
     * complete translations (then you better replace language files in server core),
     * but for spurious adoptions of wordings.
     * 
	 * @var string[][][]: Translation matrix in the form
     *                    overrides[$lang][$app][$msg] => $translation
     */
    protected array $overrides = [];

	/**
	 * @param IConfig $config
	 */
	public function __construct(
		IConfig $config,
        IFactory $decoratedFactory
	) {
		$this->config = $config;
        $this->decoratedFactory = $decoratedFactory;
	}

    /**
	 * Collect relevant translations from core or app files
	 *
     * NOTE: the old themeing support is removed as it is not used anymore
     * in out theming approach
     * 
	 * @param string $app
	 * @param string $lang
	 * @return string[]
	 */
	protected function readL10NJson($app, $lang) {
		$languageFiles = [];

		$i18nDir = $this->findL10nDir($app);
		$transFile = strip_tags($i18nDir) . strip_tags($lang) . '.json';

		if (($this->isSubDirectory($transFile, $this->serverRoot . '/core/l10n/')
				|| $this->isSubDirectory($transFile, $this->serverRoot . '/lib/l10n/')
				|| $this->isSubDirectory($transFile, \OC_App::getAppPath($app) . '/l10n/'))
			&& file_exists($transFile)
		) {
            $json = json_decode(file_get_contents($translationFile), true);
            if (!\is_array($json)) {
                $jsonError = json_last_error();
                \OC::$server->getLogger()->warning("Failed to load $translationFile - json error code: $jsonError", ['app' => 'l10n']);
                return [];
            }
            return json;
		}
		return [];
	}

	/**
	 * Get a language instance
	 *
	 * @param string $app
	 * @param string|null $lang
	 * @param string|null $locale
	 * @return \OCP\IL10N
	 */
	public function get($app, $lang = null, $locale = null) {
        return new LazyL10N(function () use ($app, $lang, $locale) {
            if (!isset($this->instances[$lang])) {
                $app = \OC_App::cleanAppId($app);
                if ($lang !== null) {
                    $lang = str_replace(['\0', '/', '\\', '..'], '', $lang);
                }

                $forceLang = $this->config->getSystemValue('force_language', false);
                if (is_string($forceLang)) {
                    $lang = $forceLang;
                }

                $forceLocale = $this->config->getSystemValue('force_locale', false);
                if (is_string($forceLocale)) {
                    $locale = $forceLocale;
                }

                if ($lang === null || !$this->languageExists($app, $lang)) {
                    $lang = $this->findLanguage($app);
                }

                if ($locale === null || !$this->localeExists($locale)) {
                    $locale = $this->findLocale($lang);
                }

                // load the multi-app json for lang if not loaded yet
                // lazy load theme overrides map once. We need the modified $lang.
                if (!isset($this->overrides[$lang])) {
                    // read the combined JSON from
                    $allAppOverrides = $this->readL10NJson("nmctheme", $lang);
                    // FIXME need to map values of "translations" key for each app
                    foreach ($allAppOverrides as $app => $appOverride) {
                        $allAppOverrides[$app] = $appOverride['translations'];
                    }
                    
                    $this->overrides[$lang] = $allAppOverrides;
                }

                if (!isset($this->instances[$lang][$app])) {
                    $translations = $this->readL10NJson($app, $lang)["translations"];
                    $overrides = $this->overrides[$lang][$app] ?? [];

                    $this->instances[$lang][$app] = new L10N(
                        $this,
                        $app,
                        $lang,
                        $locale,
                        array_merge($translations, $overrides)
                    );
                }
            }

            return $this->instances[$lang][$app];
        });
    }

	/**
	 * No decoration, just pass on to original IFactory
     * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function findLanguage(?string $appId = null): string {
        return $this->decoratedFactory->findLanguage($appId);
    }

	/**
	 * No decoration, just pass on to original IFactory
     * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function findGenericLanguage(string $appId = null): string {
        return $this->decoratedFactory->findGenericLanguage($appId);
    }
    
	/**
	 * No decoration, just pass on to original IFactory
     * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function findLocale($lang = null) {
        return $this->decoratedFactory->findLocale($lang);
    }

	/**
	 * No decoration, just pass on to original IFactory
     * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function findLanguageFromLocale(string $app = 'core', string $locale = null) {
        return $this->decoratedFactory->findLanguageFromLocale($app, $locale);
    }

	/**
	 * No decoration, just pass on to original IFactory
     * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function findAvailableLanguages($app = null): array  {
        return $this->decoratedFactory->findAvailableLanguages($app);
    }

	/**
	 * No decoration, just pass on to original IFactory
     * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function findAvailableLocales() {
        return $this->decoratedFactory->findAvailableLocales();
    }

	/**
	 * No decoration, just pass on to original IFactory
     * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function languageExists($app, $lang) {
        return $this->decoratedFactory->languageExists($app, $lang);
    }

	/**
	 * No decoration, just pass on to original IFactory
     * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function localeExists($locale) {
        return $this->decoratedFactory->localeExists($locale);
    }

	/**
	 * No decoration, just pass on to original IFactory
     * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function getLanguageIterator(IUser $user = null): ILanguageIterator {
        return $this->decoratedFactory->getLanguageIterator($user);
    }

	/**
	 * No decoration, just pass on to original IFactory
     * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function getLanguages(): array {
        return $this->decoratedFactory->getLanguages();
    }

	/**
	 * No decoration, just pass on to original IFactory
     * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function getUserLanguage(IUser $user = null): string {
        return $this->decoratedFactory->getUserLanguage($user);
    }
}