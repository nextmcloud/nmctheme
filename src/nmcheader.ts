import Vue from 'vue'
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
}]

function updateNotificationsLabel() {
	const textEl = document
		.querySelector('#notifications button')
		?.querySelector('.button-vue__text')

		console.log(textEl)
	if (textEl) {
		textEl.textContent = t(app, 'Notifications')
		return true
	}
	return false
}

window.addEventListener('DOMContentLoaded', function() {
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
