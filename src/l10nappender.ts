
import { generateFilePath } from '@nextcloud/router';
import { register, getLocale } from '@nextcloud/l10n';

/** @notExported */
type ThemeTranslations = Record<string, Record<string, Record<string, string | string[] | undefined>>>

/** the function is exported for tests and could be reused */
export function loadThemeTranslations(appname: string = 'nmctheme'): Promise<ThemeTranslations> {

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
                    if (keys.length>0 && 
                        overrides[keys[0]].hasOwnProperty('translations') &&
                        typeof overrides[keys[0]].translations == 'object')
                        resolve(overrides)
                    else
                        reject(new Error('Theme translations: file format problem!'))
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

/** the function is exported for tests and could be reused */
export function appendThemeTranslations(overrides: ThemeTranslations | undefined) {
    let appsTranslations = overrides || {}
    Object.keys(appsTranslations).forEach(appname => {
        register(appname, appsTranslations[appname].translations)
    })
    // we do not override plural function but assume that the overrides
    // conform to the original plural form
}

/** the rest is the browser based part (not unit tested) */

/**
 * during script loading phase, the translation json
 * for the selected language is already loaded 
 */
declare global {
    interface Window {
        _oc_theme_l10n_overrides?: ThemeTranslations
    }
}

loadThemeTranslations('nmctheme')        
.then((result) => {
    window._oc_theme_l10n_overrides = result;
}).catch(error => {
    console.log(error);
});


/**
 * Add the translation appending event listener.
 * As soon as all scripts are loaded, the listener registers the translations
 * from nmctheme that override default translations 
 */
window.addEventListener('load', (event) => {
    appendThemeTranslations(window._oc_theme_l10n_overrides);
})