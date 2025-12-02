import { FileAction, Permission } from '@nextcloud/files'
import { translate as t } from '@nextcloud/l10n'

export const action = new FileAction({
	id: 'versions',
	displayName() {
		return t('nmctheme', 'Versions')
	},

	title() {
		return t('nmctheme', 'Versions')
	},

	iconSvgInline() {
		return ''
	},

	enabled(nodes) {
		if (nodes.length !== 1) {
			return false
		}

		if (window.OCP.Files.Router.params.view === 'trashbin') {
			return false
		}

		const node = nodes[0]

		console.log(node)

		if (node.attributes?.['is-encrypted'] === 1) {
			return false
		}

		// enable versions button in any case
		return true
	},

	async exec(node, view, dir) {
		// You need read permissions to see the sidebar
		if ((node.permissions & Permission.READ) !== 0) {

			window.OCA.Files.Sidebar.close()
			window.OCA.Files.Sidebar.setActiveTab('version_vue')

			try {
				// Silently update current fileid
				window.OCP.Files.Router.goToRoute(
					null,
					{ view: view.id, fileid: node.fileid },
					{ dir },
					true,
				)

				// TODO: migrate Sidebar to use a Node instead
				await window.OCA.Files.Sidebar.open(node.path)

				return null
			} catch (error) {
				return false
			}
		}
	},

	order: -60,
})
