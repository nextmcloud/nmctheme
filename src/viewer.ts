
/**
 * Decorate the viewer open function to never 
 * enable sidebar
 */
function noSidebarOpen(params: object) {
    return window.OCA.Viewer.open({
            ...params,
            sidebar: false
        })
}


//window.addEventListener('DOMContentLoaded', function() {
//    if (window.OCA.Viewer) {
        // completely disable sidebar
        // by overriding the sidebar parameter
//        window.OCA.Viewer.open = noSidebarOpen
//    }
//});
