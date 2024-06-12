import { getFileActions, registerFileAction, FileAction, Node, Permission, View, getNavigation } from '@nextcloud/files'
import { translate as t } from '@nextcloud/l10n'
import { sidebarAction } from './utils/sidebar.js'

const Navigation = getNavigation()
const Views = Navigation.views

const sharinginView = Navigation.views.find(view => view.id === 'sharingin')

if(sharinginView) {
	sharinginView.order = 3
	Navigation.remove('sharingin')
	Navigation.register(sharinginView)
}

const filesView = Navigation.views.find(view => view.id === 'files')

if(filesView) {
	filesView.order = 10
	Navigation.remove('files')
	Navigation.register(filesView)
}

const handleCancel = async (dir: string, nodes: Node[]) => {
	return true
}

const fileAction = new FileAction({
	id: 'cancel_select',
	order: -100,
	iconSvgInline() {
		return ''
	},
	displayName() {
		return t('files', 'Cancel')
	},
	enabled(nodes: Node[]) {
		return true
	},
	async execBatch(nodes: Node[], view: View, dir: string) {
		const result = handleCancel(dir, nodes)
		return Promise.all(nodes.map(() => result))
	},
	async exec(node: Node, view: View, dir: string): Promise<boolean|null> {
		const result = handleCancel(dir, [node])
		return result
	},
})

registerFileAction(fileAction)

const FileActions = getFileActions()

const sharingStatusAction = FileActions.find(action => action.id === 'sharing-status')

if(sharingStatusAction) {

	const sharingStatusActionExec = sharingStatusAction.exec
	
	const sharingStatusMenuAction = new FileAction({
		id: 'sharing-status-menu',
		order: -100,
		iconSvgInline() {
			return ''
		},
		displayName() {
			return t('files_sharing', 'Show sharing options')
		},
		enabled() {
			return true
		},
		async exec(node: Node, view: View, dir: string) {
			if ((node.permissions & Permission.READ) !== 0) {
				window.OCA?.Files?.Sidebar?.setActiveTab?.('sharing')
				return sidebarAction(node, view, dir)
			}
			return null
		},
	})
	
	registerFileAction(sharingStatusMenuAction)

}
