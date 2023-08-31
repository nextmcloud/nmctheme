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
		StorageQuota
	}
}

</script>

<style>
.files-settings {
	padding-right: 1rem;
}

@media only screen and (max-width: 1024px) {
	.files-settings {
		padding-bottom: 4rem;
	}
}
</style>