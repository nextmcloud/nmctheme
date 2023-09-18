/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author Bernd Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * This webpack main is needed to allow cross-loading of
 * vue components
 *
 */

import Vue from 'vue'
import { generateFilePath } from '@nextcloud/router'

Vue.prototype.OC = OC
Vue.prototype.OCA = OCA

// CSP config for webpack dynamic chunk loading
// eslint-disable-next-line
__webpack_nonce__ = btoa(OC.requestToken)

// Correct the root of the app for chunk loading
// eslint-disable-next-line
__webpack_public_path__ = generateFilePath('nmctheme', '', 'js/')