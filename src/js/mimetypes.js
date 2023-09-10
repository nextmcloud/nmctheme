
function _iconName(mimetype) {
    const dirMappings = {
        "dir": "folder",
        "dir-encrypted": "folder-encrypted",
        "dir-shared": "folder-shared",
        "dir-public": "folder-public",
        "dir-external": "folder-external",
        "dir-external-root": "folder-external",
    };

    return (mimetype in dirMappings) ? dirMappings[mimetype] : mimetype.replace('/', '-');
}

function cacheBusterUrl(path) {
    url = OC.generateUrl(path);
    if(OCA.Theming) {
        // as we depend on theming, this should usually be added
        url += "?v=" + OCA.Theming.cacheBuster;
    }
    return url;
}

/**
 * Find a matching icon in theme
 * @param {string} mimetype
 * @returns {string} icon path or null if not found
 */
function getThemeIconUrl(mimetype) {
    // nmctheme deliveres a resolved alias mapping
    // computed when delivering the mimetypelist.
    // Thus, only a simple mapping step is needed here.
    //

    
    if ((OC.MimeTypeList !== undefined) &&
        (OC.MimeTypeList.aliases !== undefined) &&
        (mimetype in OC.MimeTypeList.aliases)) {
        mimetype = OC.MimeTypeList.aliases[mimetype];    
    }

    // we direct all icon calls to the theme filetype icon service
    // which will do send either the nmctheme or the server icon
    // or the unknown icon
    path = '/apps/nmctheme/mime/img/';
    path += _iconName(mimetype);
    path += '.svg';

    url = cacheBusterUrl(path);
    return url;
}

window.addEventListener('DOMContentLoaded', function() {
    OC.MimeType.getIconUrl = getThemeIconUrl;
});

