<template>
	<div class="storage-quota">
		<div class="storage-quota__header">
			<div class="storage-quota__title" @click.stop.prevent="debounceUpdateStorageStats">
				<!-- eslint-disable-next-line vue/no-v-html -->
				<p v-html="storageStatsTitle" />
			</div>
			<div v-if="storageStats?.quota >= 0" class="storage-quota__total">
				{{ formattedStats.quotaByte }}
			</div>
		</div>
		<ProgressBar :percentage="memoryUsed" />
		<a class="storage-quota__link"
			target="_blank"
			rel="noopener"
			href="https://cloud.telekom-dienste.de/tarife">
			<NcIconSvgWrapper :svg="cloudIconSvg" class="storage-quota__link-icon" />
			{{ t('nmctheme', 'Expand storage') }}
		</a>
	</div>
</template>

<script>
import { loadStats, IS_LEGACY_VERSION } from './filesSettings.utils.ts'
import { formatFileSize } from '@nextcloud/files'
import { subscribe } from '@nextcloud/event-bus'
import { throttle, debounce } from 'throttle-debounce'
import { generateUrl } from '@nextcloud/router'
import ProgressBar from './ProgressBar.vue'
import axios from '@nextcloud/axios'
import { translate, getCanonicalLocale } from '@nextcloud/l10n'
import { NcIconSvgWrapper } from '@nextcloud/vue'
import cloudSvg from '../../img/app-logo.svg'
import cloudIconSvg from '../../img/actions/cloud.svg'

export default {
	components: {
		ProgressBar,
		NcIconSvgWrapper,
	},
	data() {
		return {
			loadingStorageStats: false,
			storageStats: {},
		}
	},
	computed: {
		formattedStats() {
			const usedQuotaByte = formatFileSize(this.storageStats?.used, false, true).replace(/iB/g, 'B')
			const quotaByte = formatFileSize(this.storageStats?.quota, false, true).replace(/iB/g, 'B')
			return { usedQuotaByte, quotaByte }
		},
		storageStatsTitle() {
			const { usedQuotaByte } = this.formattedStats

			if (this.storageStats?.quota < 0) {
				return `<b>${usedQuotaByte}</b> ` + t('nmctheme', 'used')
			}

			return `${usedQuotaByte} <span class="storage-percentage">(${t('nmctheme', 'Storage at {percentage}% used', { percentage: Math.round(this.memoryUsed) })})</span>`
		},
		memoryUsed() {
			return parseFloat((this.storageStats?.used / this.storageStats?.quota) * 100).toFixed(2)
		},
		memoryUsage() {
			return parseFloat((this.storageStats?.used / this.storageStats?.quota) * 100).toFixed(2).toLocaleString(getCanonicalLocale())
		},
		currentImage() {
			return cloudSvg
		},
		cloudIconSvg() {
			return cloudIconSvg
		},
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
					IS_LEGACY_VERSION
						? generateUrl('/apps/files/ajax/getstoragestats')
						: generateUrl('/apps/files/api/v1/stats'),
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
		t: translate,
	},
}
</script>

<style lang="scss">
.storage-quota {
	display: flex;
	flex-direction: column;
	padding: 0 1rem;

	&__header {
		display: flex;
		justify-content: space-between;
		align-items: baseline;
	}

	&__title {
		p {
			font-size: var(--telekom-typography-font-size-small);
			color: #6C7C8C;
			margin: 0;
		}

		.storage-percentage {
			font-weight: normal;
		}
	}

	&__total {
		font-size: var(--telekom-typography-font-size-small);
		color: #6C7C8C;
		font-weight: 500;
	}

	&__link {
		color: var(--telekom-color-text-and-icon-black-standard);
		width: fit-content;
		padding: 0.625rem 1.2rem;
		display: flex;
		align-items: center;
		gap: 0.5rem;
		font: var(--telekom-text-style-body);
		font-weight: bold;
		background-color: #D2E2FC;
		border-radius: 9999px;
		margin-top: 0.5rem;

		&-icon {
			width: 20px;
			height: 20px;
			min-width: 20px !important;
			min-height: 20px !important;

			:deep(svg) {
				fill: currentColor;
				color: #000000;
			}
		}
	}
}
</style>
