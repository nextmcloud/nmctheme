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
	background-color: #C8D9EF;
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
	background: #5B7290;
}

.progress-bar::after {
	content: "";
	position: absolute;
	right: 2px;
	top: 50%;
	transform: translateY(-50%);
	width: 4px;
	height: 4px;
	background: #5B7290;
	border-radius: 50%;
	border: 1px solid #FFFFFF;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

</style>
