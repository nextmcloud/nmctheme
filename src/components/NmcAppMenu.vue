<template>
	<nav class="app-menu nmc-app-menu" :aria-label="t('core', 'Applications')">
		<ul class="app-menu__list">
			<li v-for="app in apps"
				:key="app.id"
				class="app-menu-entry"
				:class="{ 'app-menu-entry--active': app.active }">
				<a class="app-menu-entry__link"
					:href="app.href"
					:aria-current="app.active ? 'page' : null">
					<span class="app-menu-entry__label">{{ app.name }}</span>
				</a>
			</li>
		</ul>
	</nav>
</template>

<script>
import { subscribe, unsubscribe } from '@nextcloud/event-bus'
import { loadState } from '@nextcloud/initial-state'
import { translate } from '@nextcloud/l10n'

/**
 * Horizontal app tabs, as core rendered them up to NC 33.
 * From NC 34 core only offers a waffle popover, so nmctheme renders the list itself
 * from the same initial state and keeps the NC 33 markup for the existing styles.
 */
export default {
	data() {
		return {
			apps: loadState('core', 'apps', []),
		}
	},
	mounted() {
		subscribe('nextcloud:app-menu.refresh', this.setApps)
	},
	beforeDestroy() {
		unsubscribe('nextcloud:app-menu.refresh', this.setApps)
	},
	methods: {
		t: translate,
		setApps({ apps }) {
			this.apps = apps
		},
	},
}
</script>
