<template>
	<div class="storage-quota">
		<div class="storage-quota__title" @click.stop.prevent="debounceUpdateStorageStats">
			<img class="" src="../../img/app-logo.svg" alt="" />
			<p v-html="storageStatsTitle"></p>
		</div>
		<ProgressBar :percentage="memoryUsage" />
		<p v-if="memoryUsage > 0">Memory used up to {{ memoryUsage }}%</p>
		<a class="storage-quota__link"
			target="_blank"
			rel="noopener"
			href="https://cloud.telekom-dienste.de/tarife">
			Expand storage
		</a>
	</div>
</template>

<script>
import { loadStats, IS_LEGACY_VERSION } from './filesSettings.utils'
import { formatFileSize } from '@nextcloud/files'
import { subscribe } from '@nextcloud/event-bus'
import { throttle, debounce } from 'throttle-debounce'
import ProgressBar from './ProgressBar.vue'
import axios from '@nextcloud/axios'

export default {
	components: {
		ProgressBar
	},
	data() {
		return {
			loadingStorageStats: false,
			storageStats: {},
		}
	},
	computed: {
		formattedStats() {
			const usedQuotaByte = formatFileSize(this.storageStats?.used)
			const quotaByte = formatFileSize(this.storageStats?.quota)
			return { usedQuotaByte, quotaByte }
		},
		storageStatsTitle() {
			const { usedQuotaByte, quotaByte } = this.formattedStats

			if (this.storageStats?.quota < 0) {
				return `<b>${usedQuotaByte}</b> used`
			}

			return `<b>${usedQuotaByte}</b> of ${quotaByte}`
		},
		memoryUsage() {
			return parseFloat((this.storageStats?.used / this.storageStats?.quota) * 100).toFixed(2)
		}
	},
	beforeMount() {
		this.loadStorageStats()
		/**
		 * Update storage stats every minute
		 * TODO: remove when all views are migrated to Vue
		 */
		setInterval(this.throttleUpdateStorageStats, 60 * 1000)

		subscribe('files:node:created', this.throttleUpdateStorageStats)
		subscribe('files:node:deleted', this.throttleUpdateStorageStats)
		subscribe('files:node:moved', this.throttleUpdateStorageStats)
		subscribe('files:node:updated', this.throttleUpdateStorageStats)
	},
	methods: {
		debounceUpdateStorageStats: debounce(200, function(event) {
			this.updateStorageStats(event)
		}),
		throttleUpdateStorageStats: throttle(1000, function(event) {
			this.updateStorageStats(event)
		}),
		async loadStorageStats() {
			this.storageStats = await loadStats()
		},
		async updateStorageStats(event = null) {
			if (this.loadingStorageStats) {
				return
			}

			this.loadingStorageStats = true
			try {
				const response = await axios.get(
					IS_LEGACY_VERSION ? 
						generateUrl('/apps/files/ajax/getstoragestats') : 
						generateUrl('/apps/files/api/v1/stats')
				)
				if (!response?.data?.data) {
					throw new Error('Invalid storage stats')
				}
				this.storageStats = response.data.data
			} catch (error) {
				// log
			} finally {
				this.loadingStorageStats = false
			}
		},
	}
}
</script>

<style lang="scss">
.storage-quota {
	display: flex;
	flex-direction: column;
	gap: 1rem;

	&__title {
		display: flex;
		flex-wrap: nowrap;
		gap: var(--telekom-spacing-composition-space-04);
		align-items: end;
		img {
			width: 30px;
		}
		p {
			font: var(--telekom-text-style-lead-text);
		}
	}

	&__link {
		width: fit-content;
		padding: 0.5rem 1.5rem;
		border-radius: var(--telekom-radius-small);
		font: var(--telekom-text-style-body);
		border: 1px solid #191919;
		&:hover {
			border-color: var(--telekom-color-primary-standard);
			color: var(--telekom-color-primary-standard);
		}
	}
}
</style>
