<template>
	<li :id="id" class="menu-entry">
		<a :href="url" :target="target">
			<img :src="icon">
			<span>{{ t('nmcsettings', name) }}</span>
		</a>
	</li>
</template>

<script>
import { translate } from '@nextcloud/l10n'

export default {
	props: {
		itemId: String,
		itemName: String,
		itemIcon: String,
		itemUrl: String,
		itemTarget: String,
	},
	data() {
		return {
			id: this.itemId,
			name: this.itemName,
			icon: this.itemIcon,
			url: this.itemUrl,
			target: this.itemTarget,
		}
	},
	methods: {
		t: translate,
	},
}
</script>
