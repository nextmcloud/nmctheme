<template>
	<div class="progress-bar" :style="cssStyles" />
</template>

<script>
export default {
	props: {
		percentage: 0,
	},
	computed: {
		cssStyles() {
			return {
				'--percentage': this.percentage + '%',
			}
		},
	},
}

</script>

<style>

.progress-bar {
	width: 100%;
	background-color: var(--nmc-ods-blue-primary);
	height: 8px;
	border-radius: 9999px;
	position: relative;
}

.progress-bar::before {
	content: "";
	position: absolute;
	height: 100%;
	max-width: 100%;
	width: var(--percentage);
	transition: width 100ms cubic-bezier(0.4,0,0.6,1);
	border-radius: 9999px;
	background: var(--nmc-ods-blue-contrast);
}

.progress-bar::after {
	content: "";
	position: absolute;
	right: 2px;
	top: 50%;
	transform: translateY(-50%);
	width: 4px;
	height: 4px;
	background: var(--nmc-ods-blue-contrast);
	border-radius: 50%;
	border: 1px solid var(--nmc-color-text-and-icon-white);
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

</style>
