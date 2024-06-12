export const sidebarAction = async (node, view, dir) => {
	try {
		// TODO: migrate Sidebar to use a Node instead
		await window.OCA.Files.Sidebar.open(node.path)

		// Silently update current fileid
		window.OCP.Files.Router.goToRoute(
			null,
			{ view: view.id, fileid: node.fileid },
			{ dir },
			true,
		)

		return null
	} catch (error) {
		return false
	}
}
