<template>
	<div class="display-settings">
		<button id="display-settings-btn"
			class="display-btn"
			type="button"
			:class="{open: isOpened}"
			:aria-expanded="isOpened ? 'true' : 'false'"
			aria-controls="display-settings-list"
			@click.stop="toggleOpen">
			{{ t('nmctheme', 'Display settings') }}
		</button>
		<div id="display-settings-list" class="display-settings__list" :class="{open: isOpened}" aria-labelledby="display-settings-btn">
			<NcCheckboxRadioSwitch v-if="textAvailable"
				:checked="userConfig.show_folder_info"
				:disabled="isOpened ? false : true"
				@update:checked="setConfig('show_folder_info', $event)">
				{{ t('nmctheme', 'Show folder info text') }}
			</NcCheckboxRadioSwitch>
			<NcCheckboxRadioSwitch 
				:checked="userConfig.show_hidden"
				:disabled="isOpened ? false : true"
				@update:checked="setConfig('show_hidden', $event)">
				{{ t('files', 'Show hidden files') }}
			</NcCheckboxRadioSwitch>
			<NcCheckboxRadioSwitch 
				:checked="userConfig.crop_image_previews"
				:disabled="isOpened ? false : true"
				@update:checked="setConfig('crop_image_previews', $event)">
				{{ t('files', 'Crop image previews') }}
			</NcCheckboxRadioSwitch>
		</div>
	</div>
</template>

<script>
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import { useUserConfigStore } from '../store/userconfig.ts'
import { loadState } from '@nextcloud/initial-state'
import { translate } from '@nextcloud/l10n'

export default {
	components: {
		NcCheckboxRadioSwitch,
	},
	setup() {
		const userConfigStore = useUserConfigStore()
		return {
			userConfigStore,
		}
	},
	data() {
		return {
			isOpened: false,
		}
	},
	computed: {
		userConfig() {
			return this.userConfigStore
		},
		textAvailable() {
			return loadState('text', 'workspace_available', false)
		},
	},
	methods: {
		setConfig(key, value) {
			this.userConfigStore.update(key, value)
		},
		toggleOpen() {
			this.isOpened = !this.isOpened
		},
		t: translate,
	},
}

</script>

<style lang="scss">

.display-settings {
	display: flex;
	flex-direction: column;
	padding: 1rem 0;

	#display-settings-btn {
		all: unset;
		border-radius: 0.25rem;
		color: var(--color-primary);
		width: auto;
		position: relative;
		padding: 0.25rem 0.5rem 0.25rem 1.75rem;
		margin-bottom: 0.5rem;

		&:hover {
			color: var(--color-primary-hover);
    		cursor: pointer;
		}

		// arrow
		&::before {
			content: '';
			border-left: 6px solid transparent;
			border-right: 6px solid transparent;
			border-bottom: 6px solid var(--color-primary);
			position: absolute;
			top: calc(50% - 3px);
			left: 0.5rem;
		}
		// rotate arrow when opened
		&.open::before {
			transform: rotate(0.5turn);
		}
	}
	// checkbox container styling
	&__list {
		display: flex;
		flex-direction: column;
		max-height: 0;
		overflow: hidden;
		padding: 2px;
		transition: max-height 0.8s;
		gap: 0.25rem;

		&.open {
			max-height: 280px;
		}

		.checkbox-content {
			border-radius: 0.25rem !important;
			gap: 0.5rem !important;
			max-width: 100% !important;
			padding: 0 0.5rem !important;

			&:hover {
				background-color: var(--nmc-color-background-hover) !important;
			}
		}
	}
}
</style>
