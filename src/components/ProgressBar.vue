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
	background-color: var(--telekom-color-ui-faint);
	height: 6px;
	border-radius: var(--telekom-radius-small);
	position: relative;
}

.progress-bar::before {
	content: "";
	position: absolute;
	height: 100%;
	max-width: 100%;
	width: var(--percentage);
	/** Add new variables
		--telekom-motion-duration-immediate : 100ms
		--telekom-motion-easing-standard : cubic-bezier(0.4,0,0.6,1)
	*/
	transition: width 100ms cubic-bezier(0.4,0,0.6,1);
	border-radius: var(--telekom-radius-small);
	background: var(--telekom-color-primary-standard);
}

</style>
