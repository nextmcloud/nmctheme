import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export const IS_LEGACY_VERSION = window._oc_config.version.startsWith('25')

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
        // v25
        if (IS_LEGACY_VERSION) {
            const keyName = {
                'show_hidden': 'show',
                'crop_image_previews': 'crop'
            }
            await axios.post(generateUrl('/apps/files/api/v1/' + key.replaceAll('_', '')), {
                [keyName[key]]: value,
            })
            return
        }
        // v26, v27
        await axios.put(generateUrl('/apps/files/api/v1/config/' + key), {
            value,
        })
    } catch (error) { }
}


