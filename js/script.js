document.addEventListener("DOMContentLoaded", function () {

    const container = document.querySelector(".container");

    const registerBtn = document.querySelector(".register-btn");
    const loginBtn = document.querySelector(".login-btn");


    // =========================================
    // LOGIN / REGISTER SWITCH
    // =========================================

    if (registerBtn) {
        registerBtn.addEventListener("click", function () {

            container.classList.add("active");

        });
    }


    if (loginBtn) {
        loginBtn.addEventListener("click", function () {

            container.classList.remove("active");

        });
    }


    // =========================================
    // PROFILE PICTURE PREVIEW
    // =========================================

    const profilePictureInput =
        document.getElementById("profile_picture");

    const imagePreview =
        document.getElementById("imagePreview");


    if (profilePictureInput && imagePreview) {

        profilePictureInput.addEventListener(
            "change",
            function () {

                const file = this.files[0];

                if (!file) return;


                const reader = new FileReader();


                reader.onload = function (event) {

                    imagePreview.src =
                        event.target.result;

                };


                reader.readAsDataURL(file);

            }
        );

    }

});