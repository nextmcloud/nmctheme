import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import { emit } from '@nextcloud/event-bus'
import axios from '@nextcloud/axios'

export const IS_LEGACY_VERSION = window._oc_config.version.startsWith('25')

const SETTINGS_MAPPING = {
	show_hidden: 'show',
	crop_image_previews: 'crop',
}

const CONFIG_SETTINGS_MAPPING = {
	show_hidden: 'showhidden',
	crop_image_previews: 'cropimagepreviews',
}

export const loadStats = async () => {
	let browserState = loadState('files', 'storageStats', null)
	// v25
	if (!browserState) {
		try {
			const response = await axios.get(generateUrl('/apps/files/ajax/getstoragestats'))
			if (!response?.data?.data) {
				throw new Error('Invalid storage stats')
			}
			browserState = response.data.data
		} catch (error) { }
	}
	return browserState
}

export const updateDisplaySettings = async (key: string, value: boolean) => {
	try {
		if (key === 'show_folder_info') {
			await axios.post(generateUrl('/apps/text/settings'), {
				key: 'workspace_enabled', value: value ? '1' : '0',
			}).then(() => emit(value ? 'Text::showRichWorkspace' : 'Text::hideRichWorkspace', { autofocus: true }))
			return
		}
		// v25
		if (IS_LEGACY_VERSION) {
			await axios.post(generateUrl('/apps/files/api/v1/' + key.replaceAll('_', '')), {
				[SETTINGS_MAPPING[key]]: value,
			})
			window.OCA.Files.App._filesConfig.set(CONFIG_SETTINGS_MAPPING[key], value)
		} else {
			// v26, v27
			await axios.put(generateUrl('/apps/files/api/v1/config/' + key), {
				value,
			})
		}
	} catch (error) { }
}
