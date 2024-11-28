import Vue from 'vue'
import { loadState } from '@nextcloud/initial-state'
import SkipActions from '../components/SkipActions.vue'

const appName = loadState('core', 'active-app', false)

window.addEventListener('DOMContentLoaded', function() {
	const skipActions = document.getElementById('skip-actions')
	if (!skipActions) return
	const anchor = document.createElement('div')


	anchor.id = 'skip-actions-app'
	skipActions.appendChild(anchor)

	const View = Vue.extend(SkipActions)
	new View({ propsData: { appName } }).$mount('#skip-actions-app')
})
