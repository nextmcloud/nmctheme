<template>
	<div class="display-settings">
		<button id="display-settings-btn"
			class="display-btn"
			type="button"
			:class="{open: isOpened}"
			@click.stop="toggleOpen">
			{{ t('nmctheme', 'Display settings') }}
		</button>
		<div class="display-settings__list" :class="{open: isOpened}">
			<NcCheckboxRadioSwitch v-if="textAvailable"
				:checked="userConfig.show_folder_info"
				@update:checked="setConfig('show_folder_info', $event)">
				{{ t('nmctheme', 'Show folder info text') }}
			</NcCheckboxRadioSwitch>
			<NcCheckboxRadioSwitch :checked="userConfig.show_hidden"
				@update:checked="setConfig('show_hidden', $event)">
				{{ t('files', 'Show hidden files') }}
			</NcCheckboxRadioSwitch>
			<NcCheckboxRadioSwitch :checked="userConfig.crop_image_previews"
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

	// checkbox container styling
	&__list {
		display: flex;
		flex-direction: column;
		max-height: 0;
		overflow: hidden;
		transition: max-height 0.8s;
		gap: 0.5rem;
		&.open {
			max-height: 280px;
		}
	}

	#display-settings-btn {
		all: unset;
		color: var(--color-primary);
		width: fit-content;
		position: relative;
		padding-left: 1.75rem;
		margin-bottom: 6px;
		&:hover {
			color: var(--color-primary-hover);
			cursor: pointer;
		}
		// arrow
		&::before {
			content: '';
			border-left: 5px solid transparent;
			border-right: 5px solid transparent;
			border-bottom: 5px solid var(--color-primary);
			position: absolute;
			top: calc(50% - 3px);
			left: 5px;
		}
		// rotate arrow when opened
		&.open::before {
			transform: rotate(0.5turn);
		}
	}

	.display-settings__list .checkbox-radio-switch__label {
		gap: 0.5rem;
	}
}
</style>
