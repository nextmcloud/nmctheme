<template>
	<div class="files-settings">
		<StorageQuota />
		<DisplaySettings />
	</div>
</template>

<script>
import DisplaySettings from './DisplaySettings.vue'
import StorageQuota from './StorageQuota.vue'
export default {
	components: {
		DisplaySettings,
		StorageQuota,
	},
}

</script>

<style>
.files-settings {
	padding: 0 1.5rem;
}
</style>
