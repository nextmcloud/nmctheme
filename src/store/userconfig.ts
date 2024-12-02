import { defineStore } from 'pinia'
import { emit, subscribe } from '@nextcloud/event-bus'
import { loadState } from '@nextcloud/initial-state'
import { updateDisplaySettings } from '../components/filesSettings.utils'
import Vue from 'vue'

export interface UserConfig {
	[key: string]: boolean
}

const userConfig = loadState('files', 'config', {
	show_hidden: false,
	crop_image_previews: false,
	_initialized: false,
}) as UserConfig

export const useUserConfigStore = function(...args) {
	const store = defineStore('userconfig', {
		state: (): UserConfig => {
			const textState = {
				show_folder_info: loadState('text', 'workspace_enabled', false),
			}
			return { ...userConfig, ...textState }
		},

		actions: {
			/**
			 * Update the user config local store
			 * @param key
			 * @param value
			 */
			onUpdate(key: string, value: boolean) {
				Vue.set(this, key, value)
			},

			/**
			 * Update the user config local store AND on server side
			 * @param key
			 * @param value
			 */
			async update(key: string, value: boolean) {
				await updateDisplaySettings(key, value)

				emit('files:config:updated', { key, value })
			},
		},
	})

	const userConfigStore = store(...args)

	// Make sure we only register the listeners once
	if (!userConfigStore._initialized) {
		subscribe('files:config:updated', (params) => {
			const userParams = params as { key: string; value: boolean }
			userConfigStore.onUpdate(userParams.key, userParams.value)
		})
		userConfigStore.onUpdate('_initialized', true)
	}

	return userConfigStore
}
