document.addEventListener("DOMContentLoaded", function () {

    /*
    |--------------------------------------------------------------------------
    | KONFIRMASI LINK LOGOUT
    |--------------------------------------------------------------------------
    */

    const logoutLinks = document.querySelectorAll(
        'a[href*="logout.php"]'
    );

    logoutLinks.forEach(function (link) {

        link.addEventListener("click", function (event) {

            const confirmLogout = confirm(
                "Apakah kamu yakin ingin logout?"
            );

            if (!confirmLogout) {
                event.preventDefault();
            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | AUTO DISMISS ALERT
    |--------------------------------------------------------------------------
    */

    const alerts = document.querySelectorAll(
        ".alert[data-auto-dismiss='true']"
    );

    alerts.forEach(function (alert) {

        setTimeout(function () {

            const closeButton = alert.querySelector(
                ".btn-close"
            );

            if (closeButton) {
                closeButton.click();
            }

        }, 4000);

    });


    /*
    |--------------------------------------------------------------------------
    | SEARCH INPUT
    |--------------------------------------------------------------------------
    */

    const searchInput = document.querySelector(
        'input[name="q"]'
    );

    if (searchInput) {

        searchInput.addEventListener("keydown", function (event) {

            if (event.key === "Escape") {
                searchInput.value = "";
            }

        });

    }

});