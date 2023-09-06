import Vue from 'vue'
import UserMenuItem from '../components/UserMenuItem.vue'
import { createPinia, PiniaVuePlugin } from 'pinia'

Vue.use(PiniaVuePlugin)
const pinia = createPinia()

const UserMenuItemView = Vue.extend(UserMenuItem)
const View = new UserMenuItemView({ pinia })

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

	// TODO: Find a way to remove app navigation elements in NC25
	document.querySelectorAll('[data-section-type]').forEach(function(element) {
		const type = element.attributes['data-section-type'].value
		if (type === 'personal') {
			const id = element.attributes['data-section-id'].value
			if (id !== 'account' && id !== 'session') {
				element.remove()
			}
		}
	})
})
