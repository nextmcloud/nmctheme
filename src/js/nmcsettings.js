import Vue from 'vue'
import UserMenuItem from '../components/UserMenuItem.vue'

const menuItems = [{
	itemId: 'customer-center',
	itemName: 'Customer Center',
	itemIcon: '/customapps/nmctheme/img/settings/img/kundencenter.svg',
	itemUrl: 'https://www.telekom.de/mein-kundencenter',
	itemTarget: '_blank',
}, {
	itemId: 'help',
	itemName: 'Help & FAQ',
	itemIcon: '/customapps/nmctheme/img/settings/img/help.svg',
	itemUrl: 'https://cloud.telekom-dienste.de/hilfe',
	itemTarget: '_blank',
}, {
	itemId: 'settings',
	itemName: 'Account Settings',
	itemIcon: '/customapps/nmctheme/img/settings/img/admin.svg',
	itemUrl: '/index.php/settings/user/account',
	itemTarget: '_self',
}]

const UserMenuItemView = Vue.extend(UserMenuItem)

window.addEventListener('DOMContentLoaded', function() {

	document.querySelectorAll('nav.user-menu__nav li').forEach(function(element) {
		if (element.id !== 'logout') {
			element.remove()
		}
	})

	const logoutIcon = document.querySelector('nav.user-menu__nav img')
	logoutIcon.src = '/customapps/nmctheme/img/actions/logout.svg'

	const userMenu = document.querySelector('nav.user-menu__nav ul')

	menuItems.forEach(function(menuItem) {
		const menuItemElement = document.createElement('div')
		menuItemElement.id = 'nmcsettings-menu-' + menuItem.itemId
		userMenu.prepend(menuItemElement)

		new UserMenuItemView({
			propsData: menuItem,
		}).$mount('#nmcsettings-menu-' + menuItem.itemId)
	})

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
