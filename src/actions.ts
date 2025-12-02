import { registerFileAction } from '@nextcloud/files'
import { action as versionsAction } from './actions/versionsAction'

registerFileAction(versionsAction)
