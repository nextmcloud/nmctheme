<template>
	<div class="files-settings">
		<StorageQuota />
	</div>
</template>

<script>
import StorageQuota from './StorageQuota.vue'
export default {
	components: {
		StorageQuota,
	},
}

</script>

<style>
.files-settings {
	padding: 0 1.5rem;
}
</style>
