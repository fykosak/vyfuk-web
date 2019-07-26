/**
 * Implementation of DokuWiki's media manager for media selection
 * @see https://github.com/splitbrain/dokuwiki/blob/65a6bb072adbc4fdbfd45391b93c089e18c997ac/lib/scripts/media.js#L8
 * @author Štěpán Stenchlák <s.stenchlak@gmail.com> <stenchlak@fykos.cz>
 * @version 2017-10 schválně, kdy se to pokazí...
 */
DWMediaSelector = new (function () {
    /**
     * URL to media manager
     * @type {string|null}
     */
    const MEDIA_MANAGER_URL = "lib/exe/mediamanager.php";

    /**
     * Opens media manager window
     * @param {Function|null} callback (mediaID) called after the media is selected
     * @param path preferred path id
     * @param {Boolean} preferBlankWindow
     */
    this.execute = (callback, path = '', preferBlankWindow = false) => {
        const url = DOKU_BASE + MEDIA_MANAGER_URL + '?ns=' + path; // DOKU_BASE is a global variable
        let mediaManagerWindow;

        // Decide on screen resolution
        if (window.innerWidth <= 640 || preferBlankWindow) {
            mediaManagerWindow = window.open(url, '_blank');
        } else {
            let width = window.innerWidth * 0.66;
            // define the height in
            let height = width * window.innerHeight / window.innerWidth;
            // Ratio the height to the width as the user screen ratio

            mediaManagerWindow = window.open(url, 'newwindow', 'width=' + width + ', height=' + height + ', top=' + ((window.innerHeight - height) / 2) + ', left=' + ((window.innerWidth - width) / 2));
        }

        // Modify the script
        if (callback !== null) {
            mediaManagerWindow.addEventListener("load", () => {
                const mediaContent = mediaManagerWindow.document.getElementById('media__content');
                if (mediaContent) {
                    jQuery(mediaContent).on('click', 'a.select', function () { // Add new function to scope for bind select media
                        callback(this.getAttribute('id').substr(2)); // Extract data (official method)
                        mediaManagerWindow.close(); // Close window
                    });
                }

                mediaManagerWindow.opener = null; // You have no parents - some options will be disabled
                mediaManagerWindow.document.getElementById('media__opts').innerHTML = ''; // Remove options
                mediaManagerWindow.dw_mediamanager.attachoptions(); // Update options
            });
        }

        return true;
    }
})();
