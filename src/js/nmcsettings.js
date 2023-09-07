import Vue from 'vue'
import UserMenuItem from '../components/UserMenuItem.vue'

const UserMenuItemView = Vue.extend(UserMenuItem)
const View = new UserMenuItemView()

window.addEventListener('DOMContentLoaded', function() {

	document.querySelectorAll('nav.user-menu__nav li').forEach(function(element) {
		if (element.id !== 'logout') {
			element.remove()
		}
	})

	const userMenu = document.querySelector('nav.user-menu__nav ul')
	const listItemAccount = document.createElement('div')
	listItemAccount.id = 'account-settings'
	userMenu.prepend(listItemAccount)
	View.$mount('#account-settings')

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
		const href = element.attributes['href'].value
		const user = "/user";
		const account = "/account";
		const session = "/session";

		if (href.includes(user) && !href.includes(account) && !href.includes(session)) {
			element.parentElement.remove()
		}
	})
})
