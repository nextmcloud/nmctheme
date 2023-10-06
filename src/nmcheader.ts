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

window.addEventListener('DOMContentLoaded', function() {
	const head = document.querySelector('head')
	if (head !== null) {
		const user = head.attributes['data-user-displayname'].value
	
		const menuButton = document.querySelector('#user-menu > a')
		if (menuButton !== null) {
			menuButton.innerHTML = '<span>' + user + '</span>'
		}
	}

	const searchButton = document.querySelector('#unified-search > a')
	if (searchButton !== null) {
		searchButton.innerHTML = '<span>' + t(app, 'Search') + '</span>'
	}

	const menuElements = document.createElement('div')
	menuElements.id = 'nmcsettings-menu'

	const userMenu = document.querySelector('nav.user-menu__nav ul')
	if (userMenu !== null) {
		userMenu.prepend(menuElements)
	}

	const View = Vue.extend(UserMenu)
	new View({ propsData: { menuItems } }).$mount('#nmcsettings-menu')
})
