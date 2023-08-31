import { generateFilePath } from '@nextcloud/router'
import { register, getLocale } from '@nextcloud/l10n'

/** @notExported */
type ThemeTranslations = Record<string, Record<string, Record<string, string | string[] | undefined>>>

/**
 * the function is exported for tests and could be reused
 * @param appname  name of the theme app that overrides translations
 */
export function loadThemeTranslations(appname = 'nmctheme'): Promise<ThemeTranslations> {

	const url = generateFilePath(appname, 'l10n', getLocale() + '.json')

	return new Promise<ThemeTranslations>((resolve, reject) => {
		const request = new XMLHttpRequest()
		request.open('GET', url, true)
		request.onerror = () => {
			reject(new Error(request.statusText || 'Theme translations: network problem'))
		}
		request.onload = () => {
			if (request.status >= 200 && request.status < 300) {
				try {
					const overrides = JSON.parse(request.responseText)
					const keys = Object.keys(overrides)
					if (keys.length > 0 && Object.keys(overrides[keys[0]]).includes('translations')) {
						resolve(overrides)
					} else {
						reject(new Error('Theme translations: file format problem!'))
					}
				} catch (error) {
					reject(new Error('Theme translations: ' + error))
				}
			} else {
				reject(new Error('Theme translations: ' + request.status + ' ' + request.statusText))
			}
		}
		request.send()
	})
}

/**
 * the function is exported for tests and could be reused
 *
 * Note: The function is already compatible with V25 as it internally
 * uses addAppTranslations from nextcloud-l10n. The same dose the register
 * function from the newser package, so no extra backporting required!
 * (what a luck!)
 *
 * @param overrides  the set of override translations to register/append onload
 */
export function appendThemeTranslations(overrides: ThemeTranslations | undefined) {
	const appsTranslations = overrides || {}
	Object.keys(appsTranslations).forEach(appname => {
		register(appname, appsTranslations[appname].translations)
	})

}

/** the rest is the browser based part (not unit tested) */

loadThemeTranslations('nmctheme')
	.then((result) => {
		window._oc_theme_l10n_overrides = result
	}).catch(error => {
		// FIXME Is there a logger way to show the problem in browser console?
		console.log(error) // eslint-disable-line no-console
	})

/**
 * Add the translation appending event listener.
 * As soon as all scripts are loaded, the listener registers the translations
 * from nmctheme that override default translations
 */
window.addEventListener('load', () => {
	appendThemeTranslations(window._oc_theme_l10n_overrides)
})
