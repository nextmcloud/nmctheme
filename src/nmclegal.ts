import { translate as t } from '@nextcloud/l10n'

const app = 'nmctheme'

const COPYRIGHT_CLASS = 'nmc-navigation-copyright'
const NAVIGATION_SELECTOR = '#app-navigation-vue, #app-navigation:not(.vue)'
const PUBLIC_LEGAL_LINK_SELECTOR = '#public-page-menu a[id^="nmc-"][id$="--link"]'

function placeNavigationCopyright(): void {
	document.querySelectorAll<HTMLElement>(NAVIGATION_SELECTOR).forEach(navigation => {
		if (navigation.lastElementChild?.classList.contains(COPYRIGHT_CLASS)) {
			return
		}

		let copyright = navigation.querySelector<HTMLElement>(`:scope > .${COPYRIGHT_CLASS}`)
		if (copyright === null) {
			copyright = document.createElement('p')
			copyright.className = COPYRIGHT_CLASS
			copyright.textContent = t(app, 'Copyright')
		}
		navigation.append(copyright)
	})
}

// core renders public menu entries without a target
function openPublicLegalLinksInNewTab(): void {
	document.querySelectorAll<HTMLAnchorElement>(PUBLIC_LEGAL_LINK_SELECTOR).forEach(link => {
		if (link.target === '_blank') {
			return
		}

		link.target = '_blank'
		link.rel = 'noopener noreferrer'
		link.setAttribute('aria-label', `${link.textContent?.trim()}, ${t(app, 'opens in new tab')}`)
	})
}

function setupLegalNotices(): void {
	const apply = (): void => {
		placeNavigationCopyright()
		openPublicLegalLinksInNewTab()
	}

	apply()

	const observer = new MutationObserver(apply)
	observer.observe(document.body, { childList: true, subtree: true })
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', setupLegalNotices)
} else {
	setupLegalNotices()
}
