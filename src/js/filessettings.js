import Vue from 'vue'
import StorageQuota from '../components/StorageQuota.vue'
import { createPinia, PiniaVuePlugin } from 'pinia'

Vue.use(PiniaVuePlugin)
const pinia = createPinia()

const StorageQuotaView = Vue.extend(StorageQuota)
const View = new StorageQuotaView({
	name: 'StorageQuotaRoot',
	pinia,
})

window.addEventListener('DOMContentLoaded', () => {
	// Select the <li> that should be moved
	const settingsButton = document.querySelector(
		'li[data-cy-files-navigation-settings-button]'
	)

	// Target Files navigation list
	const filesNavList = document.querySelector('.files-navigation__list')

	if (!settingsButton || !filesNavList) {
		return
	}

	// Move the settings button into the Files navigation list
	filesNavList.appendChild(settingsButton)

	// Create the mount anchor
	const entrySettings = document.querySelector('.app-navigation-entry__settings')
	if (!entrySettings) return

	let anchor = document.createElement('div')
	anchor.id = 'storage-quota-app'
	entrySettings.appendChild(anchor)

	// Mount the vue component
	View.$mount('#storage-quota-app')
})
