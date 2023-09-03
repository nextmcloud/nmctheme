import { generateFilePath } from '@nextcloud/router'
import { register, getLocale } from '@nextcloud/l10n'

type AppTranslation = {
	translations: Record<string, string | string[] | undefined>,
	pluralForm?: string
}
type ThemeTranslations = Record<string, AppTranslation>

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
					const overrides: ThemeTranslations = JSON.parse(request.responseText)
					if (Object.values(overrides)[0]?.translations) {
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
export function appendThemeTranslations(appname: string) {
	if ( window._oc_theme_l10n_overrides === 'undefined' ) {
        window._oc_theme_l10n_overrides = {}
        loadThemeTranslations('nmctheme')
        .then((result) => {
            window._oc_theme_l10n_overrides = result
        }).catch(error => {
            // FIXME Is there a logger way to show the problem in browser console?
            console.log(error) // eslint-disable-line no-console
        })        
    }
	register({appname: window._oc_theme_l10n_overrides[appname]})
}