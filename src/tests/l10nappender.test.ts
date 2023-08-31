import { MockXhrServer, newServer } from 'mock-xmlhttprequest' // eslint-disable-line n/no-unpublished-import
import { loadTranslations, translate } from '@nextcloud/l10n'
import { loadThemeTranslations, appendThemeTranslations } from '../l10nappender'

const setLocale = (locale) => document.documentElement.setAttribute('data-locale', locale)

describe('l10nappender', () => {
	let server: MockXhrServer

	beforeEach(async () => {
		setLocale('de_DE')
		server = newServer()
		// some typical translations
			.addHandler('GET', '/core/l10n/de_DE.json', {
				status: 200,
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					translations: {
						'File conflict': 'Konflikt',
						'More details': 'Details',
						'Replace file': 'Dateiersatz',
					},
					pluralForm: 'nplurals=2; plural=(n != 1);',
				}),
			})
			.addHandler('GET', '/files/l10n/de_DE.json', {
				status: 200,
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					translations: {
						'Crop image previews': 'Vorschaubilder  beschneiden',
						'My shares': 'Geteilten Inhalte',
						'Shared with me': 'Geteilt',
						All: 'Alles',
						'Toggle grid view': 'Ansicht ändern',
						'Display settings': 'Anzeigeeinstellungen',
						'Expand storage': 'Speicherplatz erweitern',
						'Memory used up to %s%%': 'Speicher zu %s%% belegt ',
						of: 'von',
					},
					pluralForm: 'nplurals=2; plural=(n != 1);',
				}),
			})
			.addHandler('GET', '/richdocuments/l10n/de_DE.json', {
				status: 200,
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					translations: {
						'Edit with {productName}': 'Mit Office bearbeiten',
						Archive: 'Archiv',
						'Open with {productName}': 'Mit Office öffnen',
						'Compress files': 'Archiv erstellen',
						'File name': 'Dateiname',
						Cancel: 'Abbrechen',
					},
				}),
			})
		// the appender data from nmctheme
			.addHandler('GET', '/nmctheme/l10n/de_DE.json', {
				status: 200,
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					core: {
						translations: {
							'File conflict': 'Dateikonflikt',
							'AddOn files': 'Zusatzfiles',
						},
						pluralForm: 'nplurals=2; plural=(n != 1);',
					},
					files: {
						translations: {
							'My shares': 'Meine geteilten Inhalte',
							'Shared with me': 'Mit mir geteilt',
							All: 'Alle',
						},
						pluralForm: 'nplurals=2; plural=(n != 1);',
					},
					richdocuments: {
						translations: {
							'Edit with {productName}': 'Mit Collabora bearbeiten',
							'Open with {productName}': 'Mit Collabora öffnen',
						},
					},
					nmctheme: {
						translations: {
							Magentacloud: '<em>Magenta</em>CLOUD',
						},
						pluralForm: 'nplurals=2; plural=(n != 1);',
					},
					text: {
						translations: {
							'Add Link': 'Link hinzufügen',
						},
						pluralForm: 'nplurals=2; plural=(n != 1);',
					},
				}),
			})
		// Response with empty body, but has translations in theme
			.addHandler('GET', '/noappend/l10n/de_DE.json', {
				status: 200,
				headers: { 'Content-Type': 'application/json' },
				body: '',
			})
		// Response contains JSON but no translations, and also none in theme
			.addHandler('GET', '/emptyappend/l10n/de_DE.json', {
				status: 200,
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({}),
			})
		// Response contains JSON but the translations are invalid
			.addHandler('GET', '/invalidappend/l10n/de_DE.json', {
				status: 200,
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					translations: 'invalid',
				}),
			})
			.addHandler('GET', '/404/l10n/de.json', {
				status: 404,
				statusText: 'Not Found',
			})
			.addHandler('GET', '/500/l10n/de_DE.json', {
				status: 500,
				statusText: 'Internal Server Error',
			})
			.addHandler('GET', '/networkissue/l10n/de_DE.json', (req) =>
				req.setNetworkError())
			.setDefault404()
		server
			.disableTimeout()
		server
			.install()

		// always load the l10n translations available until appender comes in
		const callback = jest.fn()
		await loadTranslations('core', callback)
		await loadTranslations('files', callback)
		await loadTranslations('richdocuments', callback)
	})

	afterEach(() => {
		server.remove()
		jest.clearAllMocks()
	})

	it('registers new translations', async () => {

		try {
			const trans = await loadThemeTranslations()
			appendThemeTranslations(trans)
		} catch (e) {
			expect(e).toBe('Unexpected error:' + e)
		}

		// Not all requests don (3 packages + appender)
		expect(server.getRequestLog().length).toBe(4)

		// New translations work
		expect(translate('core', 'File conflict')).toBe('Dateikonflikt')
		expect(translate('core', 'AddOn files')).toBe('Zusatzfiles')
		expect(translate('core', 'More details')).toBe('Details')
		expect(translate('files', 'Shared with me')).toBe('Mit mir geteilt')
		expect(translate('files', 'Toggle grid view')).toBe('Ansicht ändern')
		expect(translate('files', 'All')).toBe('Alle')
		expect(translate('richdocuments', 'Edit with {productName}')).toBe('Mit Collabora bearbeiten')
		expect(translate('richdocuments', 'Open with {productName}')).toBe('Mit Collabora öffnen')
		// nmctheme is always container only in appender
		expect(translate('nmctheme', 'Magentacloud')).toBe('<em>Magenta</em>CLOUD')
		// text is only contained in appender, but included though app is not used
		expect(translate('text', 'Add Link')).toBe('Link hinzufügen')
	})

	it('does reject on network error', async () => {
		try {
			await loadThemeTranslations('networkissue')
			expect('').toBe('Unexpected pass')
		} catch (e) {
			expect(e instanceof Error).toBe(true)
			expect((<Error>e).message).toMatch('network')
		}
	})

	it('does reject on server error', async () => {
		try {
			await loadThemeTranslations('500')
			expect('').toBe('Unexpected pass')
		} catch (e) {
			expect(e instanceof Error).toBe(true)
			expect((<Error>e).message).toMatch('500')
		}
	})

	it('does reject on unavailable bundle', async () => {
		try {
			await loadThemeTranslations('404')
			expect('').toBe('Unexpected pass')
		} catch (e) {
			expect(e instanceof Error).toBe(true)
			expect((<Error>e).message).toMatch('404')
		}
	})

	it('does reject on invalid bundle', async () => {
		try {
			await loadThemeTranslations('noappend')
			expect('').toBe('Unexpected end')
		} catch (e) {
			expect(e instanceof Error).toBe(true)
			expect((<Error>e).message).toMatch('Unexpected end')
		}
	})

	it('does reject on missing bundle', async () => {
		try {
			await loadThemeTranslations('invalidappend')
			expect('').toBe('Unexpected pass')
		} catch (e) {
			expect(e instanceof Error).toBe(true)
			expect((<Error>e).message).toMatch('file format problem')
		}
	})

	it('does reject on empty response', async () => {
		try {
			await loadThemeTranslations('emptyappend')
			expect('').toBe('Unexpected pass')
		} catch (e) {
			expect(e instanceof Error).toBe(true)
			expect((<Error>e).message).toMatch('file format problem')
		}
	})
})
