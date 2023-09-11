import Vue from 'vue'
import { generateFilePath } from '@nextcloud/router'

const viewerModules = await import(generateFilePath('viewer', 'js', 'viewer-main.js'));


//const DecoratedViewer = Vue.extend(Viewer)
//const View = new FilesSettingsView({
//	name: 'FilesSettingsRoot',
//	pinia,
//})