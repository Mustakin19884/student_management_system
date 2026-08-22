function closeSuccessPopup() {

    const popup = document.getElementById("successPopup");

    if (popup) {

        popup.classList.add("hide");

        setTimeout(() => {

            popup.remove();

        }, 300);

    }

}


document.addEventListener("DOMContentLoaded", function () {

    const popup = document.getElementById("successPopup");


    if (popup) {

        setTimeout(() => {

            closeSuccessPopup();

        }, 1800);


        const url = new URL(window.location.href);

        if (url.searchParams.has("success")) {

            url.searchParams.delete("success");

            window.history.replaceState(
                {},
                document.title,
                url.pathname
            );

        }

    }

});


document.addEventListener("DOMContentLoaded", function () {

    const popup = document.getElementById("successPopup");

    if (popup) {

        setTimeout(() => {

            closeSuccessPopup();

        }, 1500);

    }

});


function closeErrorPopup() {

    const popup = document.getElementById("errorPopup");

    if (popup) {

        popup.classList.add("hide");

        setTimeout(() => {

            popup.remove();

        }, 300);

    }
}


document.addEventListener("DOMContentLoaded", function () {

    const popup =
        document.getElementById("errorPopup");

    if (popup) {

        setTimeout(() => {

            closeErrorPopup();

        }, 3000);

    }

});