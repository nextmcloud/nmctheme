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

use OC\L10N\Factory;
use OC\L10N\LazyL10N;
use OCP\IConfig;
use OCP\IUser;
use OCP\L10N\IFactory;
use OCP\L10N\ILanguageIterator;

class LanguageIteratorDecorator implements ILanguageIterator {
    private ILanguageIterator $decorated;
    private array $supported_locales = [];

    public function __construct(ILanguageIterator $decorated,
                                array $supported_locales) {
        $this->decorated = $decorated;
        $this->supported_locales = $supported_locales;
    }

    public function rewind(): void {
		$this->decorated->rewind();
	}

    public function current(): string {
    	$locale = $this->decorated->rewind();
        
        if (empty($this->supported_locales) ||
            in_array($locale, $this->supported_locales)) {
            return $locale;
        } else {
            return 'en';
        }
    }

    public function next(): bool {
        return $this->decorated->next();
    }

    public function key(): int {
        return $this->decorated->key();
    }

    public function valid(): bool {
        return $this->decorated->valid();
    }

}


class FactoryDecorator implements IFactory {
	private Factory $decoratedFactory;

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
     * NMCTheme restricts the available locales and translations
     * to only a selectable set
     * @var string[]  
     * 
     */
    protected array $supported_locales = [];


	/**
	 * @param IConfig $config
	 */
	public function __construct(
		IConfig $config,
		Factory $decoratedFactory,
	) {
		$this->config = $config;
		$this->decoratedFactory = $decoratedFactory;

        // configuration for supported locales of nmctheme
        $supportedLocales = $this->config->getSystemValue('nmc_supported_locales', false);
        if (is_array($supportedLocales)) {
            // the default en must be always supported
            $this->supported_locales = array_unique(array_merge($supportedLocales, ['en', 'en_GB']));
        }
	}

    public function getDecorated() :Factory {
        return $this->decoratedFactory;
    }

    /**
     * Filter by supported locales (if set)
     */
    protected function filterLocale(string $locale) {
        if (empty($this->supported_locales) ||
            in_array($locale, $this->supported_locales)) {
            return $locale;
        } else {
            return 'en';
        }
    }

    /**
     * Filter a set of supported locales (if set)
     */
    protected function filterLocales(array $locales) {
        if (empty($this->supported_locales)) {
            return $locales;
        }


        $filteredLocales = array_filter($locales, function($locale) {
            return in_array($locale['code'], $this->supported_locales);
        });
        
        if (empty($this->supported_locales) ||
            empty($filteredLocales)) {
            return ['en'];
        } else {
            // make sure that indexed are corrected
            return array_values($filteredLocales);
        }
    }

    /**
     * Predicate to check filter
     */
    protected function isSupportedLocale(string $locale) {
        return (empty($this->supported_locales) || 
                in_array($locale, $this->supported_locales));
    }

	/**
	 * Read all the available translation jsons for app.
     * This is not part of the interface IFactory
	 *
	 * @param string $app
	 * @param string $lang
	 * @return string[]
	 */
	public function getTranslationsForApp($app, $lang) {

        // if language is not suppported - not translations
        if (!$this->isSupportedLocale($lang)) {
            return [];
        }

        $translations = [];
        $l10nFilenames = $this->decoratedFactory->getL10nFilesForApp($app, $lang);
		foreach ($l10nFilenames as $filename) {
			$json = json_decode(file_get_contents($filename), true);
			if (!\is_array($json)) {
				$jsonError = json_last_error();
				\OC::$server->getLogger()->warning("Failed to load $filename - json error code: $jsonError", ['app' => 'l10n']);
			} else {
				if (array_key_exists('translations', $json)) {
					$translations = array_merge($translations, $json['translations']);
				}
			}
		}

		// load the multi-app json for lang if not loaded yet
		// lazy load theme overrides map once. We need the modified $lang.
		if (!isset($this->overrides[$lang])) {
			// FIXME  this could break as  is marked obsolete
			$this->overrides[$lang] = $this->getOverrides($lang);
		}

        $overrides = $this->overrides[$lang][$app] ?? [];
        return array_merge($translations, $overrides);
	}

	/**
	 * Read all the available theme translation overrides.
	 *
	 * @param string $app
	 * @param string $lang
	 * @return string[]
	 */
	public function getOverrides($lang) {
		$overrides = [];

		$l10nFilenames = $this->decoratedFactory->getL10nFilesForApp("nmctheme", $lang);
		if (!empty($l10nFilenames)) {
			$filename = $l10nFilenames[0];
			$json = json_decode(file_get_contents($filename), true);
			if (!\is_array($json)) {
				$jsonError = json_last_error() . '-' . json_last_error_msg();
				\OC::$server->getLogger()->warning("Failed to load $filename - json error: $jsonError", ['app' => 'l10n']);
			} else {
				$overrides = $json;
			}
		}

		foreach ($overrides as $app => $appOverride) {
			$overrides[$app] = $overrides[$app]['translations'];
		}
		return $overrides;
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

			if (!isset($this->instances[$lang][$app])) {
				$translations = $this->getTranslationsForApp($app, $lang);

				$this->instances[$lang][$app] = new L10N(
					$this,
					$app,
					$lang,
					$locale,
					$translations
				);
			}

			return $this->instances[$lang][$app];
		});
	}

	/**
	 * decorate standard IFactory with supported locale filter
	 * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function findLanguage(?string $appId = null): string {
		return $this->filterLocale($this->decoratedFactory->findLanguage($appId));
	}

	/**
	 * decorate standard IFactory with supported locale filter
	 * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function findGenericLanguage(string $appId = null): string {
		return $this->filterLocale($this->decoratedFactory->findGenericLanguage($appId));
	}
	
	/**
	 * decorate standard IFactory with supported locale filter
	 * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function findLocale($lang = null) {
		return $this->filterLocale($this->decoratedFactory->findLocale($lang));
	}

	/**
	 * decorate standard IFactory with supported locale filter
	 * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function findLanguageFromLocale(string $app = 'core', string $locale = null) {
		return $this->filterLocale($this->decoratedFactory->findLanguageFromLocale($app, $locale));
	}

	/**
	 * decorate standard IFactory with supported locale filter
	 * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function findAvailableLanguages($app = null): array {
		return $this->filterLocales($this->decoratedFactory->findAvailableLanguages($app));
	}

	/**
	 * decorate standard IFactory with supported locale filter
	 * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function findAvailableLocales() {
		return $this->filterLocales($this->decoratedFactory->findAvailableLocales());
	}

	/**
	 * decorate standard IFactory with supported locale filter
	 * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function languageExists($app, $lang) {
        if (!$this->isSupportedLocale($lang)) {
            return false;
        }

        return $this->decoratedFactory->languageExists($app, $lang);
	}

	/**
	 * decorate standard IFactory with supported locale filter
	 * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function localeExists($locale) {
        if (!$this->isSupportedLocale($locale)) {
            return false;
        }

		return $this->decoratedFactory->localeExists($locale);
	}

	/**
	 * decorate standard IFactory with supported locale filter
	 * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
    public function getLanguageIterator(IUser $user = null): ILanguageIterator {
        return new LanguageIteratorDecorator(
            $this->decoratedFactory->getLanguageIterator($user),
            $this->supported_locales);
	}

	/**
	 * decorate standard IFactory with supported locale filter
	 * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function getLanguages(): array {
		$languages = $this->decoratedFactory->getLanguages();

        if (empty($this->supported_locales)) {
            return $languages;
        }

        $commonLanguages = array_filter($languages['commonLanguages'], function($lang) { 
                return in_array($lang['code'], $this->supported_locales);
            });
        $otherLanguages = array_filter($languages['otherLanguages'], function($lang) { 
                return in_array($lang['code'], $this->supported_locales);
            });
        return [
            // re-index filtered arrays
			'commonLanguages' => array_values($commonLanguages),
			'otherLanguages' => array_values($otherLanguages)
		];
	}

	/**
	 * decorate standard IFactory with supported locale filter
	 * @see public\L10N\IFactory
	 * @see private\L10N\Factory
	 */
	public function getUserLanguage(IUser $user = null): string {
		return $this->filterLocale($this->decoratedFactory->getUserLanguage($user));
	}

}
