<template>
	<div>
		<li v-for="item in items"
			:id="item.id"
			:key="item.id"
			class="menu-entry">
			<a :href="item.url" :target="item.target">
				<img :src="item.icon">
				<span>{{ t('nmcsettings', item.name) }}</span>
			</a>
		</li>
	</div>
</template>

<script>
import { translate } from '@nextcloud/l10n'

export default {
	props: {
		menuItems: {
			type: Array,
			required: true,
		},
	},
	data() {
		return {
			items: this.menuItems,
		}
	},
	methods: {
		t: translate,
	},
}
</script>
