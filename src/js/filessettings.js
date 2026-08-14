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

let isMounted = false

const setupStorageQuota = () => {
	if (isMounted) {
		return true
	}

	// Rendered by Vue after DOMContentLoaded; retry via the observer until present.
	const entrySettings = document.querySelector('.app-navigation-entry__settings')
	if (!entrySettings) {
		return false
	}

	// Best-effort: lift the "Files settings" button into the main nav list.
	const settingsButton = document.querySelector('li[data-cy-files-navigation-settings-button]')
	const filesNavList = document.querySelector('.files-navigation__list, .app-navigation__list')
	if (settingsButton && filesNavList) {
		filesNavList.appendChild(settingsButton)
	}

	const anchor = document.createElement('div')
	anchor.id = 'storage-quota-app'
	entrySettings.appendChild(anchor)
	View.$mount('#storage-quota-app')

	isMounted = true
	return true
}

const waitForNavigation = () => {
	if (setupStorageQuota()) {
		return
	}

	const observer = new MutationObserver(() => {
		if (setupStorageQuota()) {
			observer.disconnect()
		}
	})
	observer.observe(document.body, { childList: true, subtree: true })
}

// Handle the bundle loading after DOMContentLoaded has already fired.
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', waitForNavigation)
} else {
	waitForNavigation()
}
