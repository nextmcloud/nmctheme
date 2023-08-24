import { defineStore } from 'pinia'
import { emit, subscribe } from '@nextcloud/event-bus'
import { loadState } from '@nextcloud/initial-state'
import { updateDisplaySettings } from '../components/filesSettings.utils'
import Vue from 'vue'

export interface UserConfig {
	[key: string]: boolean
}
export interface UserConfigStore {
	userConfig: UserConfig
}

const userConfig = loadState('files', 'config', {
	show_hidden: false,
	crop_image_previews: false,
	_initialized: false,
}) as UserConfig

export const useUserConfigStore = function(...args) {
	const store = defineStore('userconfig', {
		state: () => ({
			userConfig,
		} as UserConfigStore),

		actions: {
			/**
			 * Update the user config local store
			 */
			onUpdate(key: string, value: boolean) {
				Vue.set(this.userConfig, key, value)
			},

			/**
			 * Update the user config local store AND on server side
			 */
			async update(key: string, value: boolean) {
				await updateDisplaySettings(key, value)

				emit('files:config:updated', { key, value })
			},
		},
	})

	const userConfigStore = store(...args)

	// Make sure we only register the listeners once
	if (!userConfigStore.userConfig._initialized) {
		subscribe('files:config:updated', function({ key, value }: { key: string, value: boolean }) {
			userConfigStore.onUpdate(key, value)
		})
		userConfigStore.onUpdate('_initialized', true)
	}

	return userConfigStore
}
