<template>
	<div id="content-vue" :class="['content', `app-${appName.toLowerCase()}`]">
		<!-- TODO: with vue3 the `selector` attribute needs to be changed to `to="#skip-actions"` -->
		<Teleport selector="#skip-actions">
			<div class="vue-skip-actions__container">
				<div class="vue-skip-actions__headline">
					{{ t('core', 'Keyboard navigation help') }}
				</div>
				<div class="vue-skip-actions__buttons">
					<NcButton v-show="hasAppNavigation"
						type="tertiary"
						href="#app-navigation-vue"
						@click.prevent="openAppNavigation"
						@focusin="currentFocus = 'navigation'"
						@mouseover="currentFocus = 'navigation'">
						{{ t('core', 'Skip to app navigation') }}
					</NcButton>
					<NcButton type="tertiary"
						href="#app-content-vue"
						@focusin="currentFocus = 'content'"
						@mouseover="currentFocus = 'content'">
						{{ t('core', 'Skip to main content') }}
					</NcButton>
				</div>
				<NcIconSvgWrapper v-show="!isMobile"
					class="vue-skip-actions__image"
					:svg="currentImage"
					size="auto" />
			</div>
            &nbsp;<!-- TODO: Remove with vue3! This is a bug of vue-simple-portal that does not allow a single child, ref LinusBorg/vue-simple-portal#20 -->
		</Teleport>
		<slot />
	</div>
</template>

<script>
import { emit } from '@nextcloud/event-bus'
// TODO: This is built-in for vue3 just drop the import
import { Portal as Teleport } from '@linusborg/vue-simple-portal'
import { useIsMobile } from '../utils/ismobile.js'

import NcButton from '@nextcloud/vue/dist/Components/NcButton.js'
import { NcIconSvgWrapper } from '@nextcloud/vue'

/* eslint-disable import/no-unresolved */
import contentSvg from '../svg/svg-tastatur-right-highlighted.svg'
import navigationSvg from '../svg/svg-tastatur-left-highlighted.svg'
/* eslint-enable import/no-unresolved */

export default {
	name: 'SkipActions',
	components: {
		NcButton,
		NcIconSvgWrapper,
		Teleport,
	},
	props: {
		appName: {
			type: String,
			required: true,
		},
	},
	setup() {
		const isMobile = useIsMobile()
		return {
			isMobile,
		}
	},
	data() {
		return {
			hasAppNavigation: true,
			currentFocus: '', // unknown
		}
	},
	computed: {
		currentImage() {
			if (this.currentFocus === 'navigation') {
				return navigationSvg
			}
			return contentSvg
		},
	},
	beforeMount() {
		const container = document.getElementById('skip-actions')
		if (container) {
			// clear default buttons
			container.innerHTML = ''
			// add class for scoping styles
			container.classList.add('vue-skip-actions')
		}
	},
	methods: {
		t,
		openAppNavigation() {
			emit('toggle-navigation', { open: true })
			this.$nextTick(() => {
				window.location.hash = 'app-navigation-vue'
				// we need to manually focus if the window location is already set to the app-navigation then it will not focus again
				document.getElementById('app-navigation-vue').focus()
			})
		},
		setAppNavigation(value) {
			this.hasAppNavigation = value
			// If app navigation is available and no focus was set yet, set it to navigation as it is the first button
			if (this.currentFocus === '') {
				this.currentFocus = 'navigation'
			}
		},
	},
}
</script>

<style lang="scss">
// Remove server stylings and add a backdrop
#skip-actions.vue-skip-actions:focus-within {
    top: 0!important;
    left: 0!important;
    width: 100vw;
    height: 100vh;
    padding: var(--body-container-margin)!important;
    backdrop-filter: brightness(50%);
}
</style>

<style lang="scss" scoped>
.vue-skip-actions {
    &__container {
        background-color: var(--color-main-background);
        border-radius: var(--border-radius-large);
        padding: 22px;
    }

    &__headline {
        font-weight: bold;
        font-size: 20px;
        line-height: 30px;
        margin-bottom: 12px;
    }

    &__buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;

        > * {
            // Ensure buttons are same width on smaller screens (container wrapped)
            flex: 1 0 fit-content;
        }
    }

    &__image {
        margin-top: 12px;
    }
}

.content {
    box-sizing: border-box;
    margin: var(--body-container-margin);
    margin-top: var(--header-height);
    display: flex;
    width: calc(100% - var(--body-container-margin) * 2);
    border-radius: var(--body-container-radius);
    height: var(--body-height);
    overflow: hidden;
    padding: 0;

    &:not(.with-sidebar--full) {
        position: fixed;
    }

    :deep(*) {
        box-sizing: border-box;
    }
}
</style>
