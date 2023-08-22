import Vue from 'vue'
import FilesSettings from '../components/FilesSettings.vue'
import { createPinia, PiniaVuePlugin } from 'pinia'

Vue.use(PiniaVuePlugin)
const pinia = createPinia()

const FilesSettingsView = Vue.extend(FilesSettings)
const View = new FilesSettingsView({
	name: 'FilesSettingsRoot',
	pinia,
})

window.addEventListener('DOMContentLoaded', function() {
	const entrySettings = document.querySelector('.app-navigation-entry__settings')
	if (!entrySettings) return
	const anchor = document.createElement('div')
	anchor.id = 'files-settings-app'
	entrySettings.appendChild(anchor)
	View.$mount('#files-settings-app')
})
