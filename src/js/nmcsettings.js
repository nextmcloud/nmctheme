import Vue from 'vue'
import UserMenu from '../components/UserMenu.vue'

const menuItems = [{
	id: 'settings',
	name: 'Account Settings',
	icon: '/customapps/nmctheme/img/settings/img/admin.svg',
	url: '/index.php/settings/user/account',
	target: '_self',
}, {
	id: 'help',
	name: 'Help & FAQ',
	icon: '/customapps/nmctheme/img/settings/img/help.svg',
	url: 'https://cloud.telekom-dienste.de/hilfe',
	target: '_blank',
}, {
	id: 'customer-center',
	name: 'Customer Center',
	icon: '/customapps/nmctheme/img/settings/img/kundencenter.svg',
	url: 'https://www.telekom.de/mein-kundencenter',
	target: '_blank',
}]

const UserMenuView = Vue.extend(UserMenu)
const View = new UserMenuView({ propsData: { menuItems } })

window.addEventListener('DOMContentLoaded', function() {

	document.querySelectorAll('nav.user-menu__nav li').forEach(function(element) {
		const core = 'core_'
		const admin = 'admin_'

		if (element.id !== 'logout' && !element.id.includes(core) && !element.id.includes(admin)) {
			element.remove()
		}
	})

	const head = document.querySelector('head')
	const user = head.attributes['data-user'].value

	const menuButton = document.querySelector('#user-menu > a')
	menuButton.innerHTML = '<span>' + user + '</span>'

	const searchButton = document.querySelector('#unified-search > a')
	searchButton.innerHTML = '<span>Suche</span>'

	const adminIcon = document.querySelector('nav.user-menu__nav li#admin_settings img')
	if (adminIcon) {
		adminIcon.src = '/customapps/nmctheme/img/settings/img/apps.svg'
	}

	const appsIcon = document.querySelector('nav.user-menu__nav li#core_apps img')
	if (appsIcon) {
		appsIcon.src = '/customapps/nmctheme/img/actions/add.svg'
	}

	const usersIcon = document.querySelector('nav.user-menu__nav li#core_users img')
	if (usersIcon) {
		usersIcon.src = '/customapps/nmctheme/img/settings/img/users.svg'
	}

	const logoutIcon = document.querySelector('nav.user-menu__nav li#logout img')
	if (logoutIcon) {
		logoutIcon.src = '/customapps/nmctheme/img/actions/logout.svg'
	}

	const userMenu = document.querySelector('nav.user-menu__nav ul')
	const menuElements = document.createElement('div')
	menuElements.id = 'nmcsettings-menu'
	userMenu.prepend(menuElements)
	View.$mount('#nmcsettings-menu')

	const settingsBody = document.getElementById('body-settings')
	if (!settingsBody) return

	// removes app navigation elements
	document.querySelectorAll('[data-section-type]').forEach(function(element) {
		const type = element.attributes['data-section-type'].value
		if (type === 'personal') {
			const id = element.attributes['data-section-id'].value
			if (id !== 'account' && id !== 'session') {
				element.remove()
			}
		}
	})

	// removes app navigation elements in NC25
	document.querySelectorAll('#app-navigation li a').forEach(function(element) {
		const href = element.href
		const user = '/user'
		const account = '/account'
		const session = '/session'

		if (href.includes(user) && !href.includes(account) && !href.includes(session)) {
			element.parentElement.remove()
		}
	})
})
