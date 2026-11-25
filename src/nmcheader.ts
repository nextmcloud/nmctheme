import Vue from 'vue'
import NmcAppMenu from './components/NmcAppMenu.vue'
import UserMenu from './components/UserMenu.vue'
import { translate as t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'

const app = 'nmctheme'

const menuItems = [{
	id: 'settings',
	name: t(app, 'Account settings'),
	url: generateUrl('/settings/user/account'),
	target: '_self',
}, {
	id: 'help',
	name: t(app, 'Faq'),
	url: 'https://cloud.telekom-dienste.de/hilfe',
	target: '_blank',
}, {
	id: 'customer_center',
	name: t(app, 'Customer center'),
	url: 'https://www.telekom.de/mein-kundencenter',
	target: '_blank',
}, {
	id: 'open_source',
	name: t(app, 'Open Source information'),
	url: 'https://static.magentacloud.de/licences/webui.htm',
	target: '_blank',
}, {
	id: 'data_privacy',
	name: t(app, 'Data privacy'),
	url: 'https://static.magentacloud.de/Datenschutz',
	target: '_blank',
}, {
	id: 'imprint',
	name: t(app, 'Imprint'),
	url: 'https://www.telekom.de/impressum',
	target: '_blank',
}]

function updateNotificationsLabel() {
	const textEl = document
		.querySelector('#notifications button')
		?.querySelector('.button-vue__text')

	if (textEl) {
		textEl.textContent = t(app, 'Notifications')
		return true
	}
	return false
}

function updateSearchLabel() {
	const labelEl = document.querySelector('#header .header-center .unified-search-input__label')

	if (labelEl) {
		labelEl.textContent = t(app, 'Search')
		return true
	}
	return false
}

// NC 34+ layout: the search moved into .header-center and the app list became a waffle popover
function adaptHeaderSince34(headerCenter: Element) {
	const headerStart = document.querySelector('#header .header-start')
	if (headerStart !== null) {
		const appMenu = document.createElement('div')
		headerStart.appendChild(appMenu)
		new (Vue.extend(NmcAppMenu))().$mount(appMenu)
	}

	if (!updateSearchLabel()) {
		const observer = new MutationObserver(() => {
			if (updateSearchLabel()) {
				observer.disconnect()
			}
		})
		observer.observe(headerCenter, { childList: true, subtree: true })
	}
}

window.addEventListener('DOMContentLoaded', function() {
	const headerCenter = document.querySelector('#header .header-center')
	if (headerCenter !== null) {
		adaptHeaderSince34(headerCenter)
	}

	const head = document.querySelector('head')
	if (head !== null) {
		const user = head.attributes['data-user-displayname'].value
		const menuButton = document.querySelector('#user-menu > button')
		if (menuButton !== null) {
			const username = document.createElement('span')
			username.className = 'button-vue__label'
			username.innerText = user
			menuButton.appendChild(username)
		}

		const searchButton = document.querySelector('#unified-search > button')
		if (searchButton !== null) {
			const searchlabel = document.createElement('span')
			searchlabel.className = 'button-vue__label'
			searchlabel.innerText = t(app, 'Search')
			searchButton.appendChild(searchlabel)
		}

		const target = document.getElementById('notifications')

		if (target) {
			const observer = new MutationObserver(() => {
				if (updateNotificationsLabel()) {
					observer.disconnect()
				}
			})

			observer.observe(target, {
				childList: true,
				subtree: true,
			})
		}

		const menuElements = document.createElement('div')
		menuElements.id = 'nmcsettings-menu'

		const userMenu = document.querySelector('#header-menu-user-menu .header-menu__content ul')
		if (userMenu !== null) {
			userMenu.prepend(menuElements)
		}

		const View = Vue.extend(UserMenu)
		new View({ propsData: { menuItems } }).$mount('#nmcsettings-menu')
	}
})
