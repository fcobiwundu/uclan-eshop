
function toggleMenu() {
    const nav = document.getElementById("nav-links");
    const burger = document.getElementById("burger-menu");

    if (!nav || !burger) return;

    const isOpen = nav.classList.toggle("nav-active");

    burger.classList.toggle("open", isOpen);

    burger.setAttribute("aria-expanded", String(isOpen));

    burger.setAttribute(
        "aria-label",
        isOpen ? "Close navigation" : "Open navigation"
    );
}

document.addEventListener("DOMContentLoaded", function () {

    const burger = document.getElementById("burger-menu");
    const nav = document.getElementById("nav-links");

    if (!burger || !nav) return;

    // Handle burger button clicks.
    burger.addEventListener("click", function () {
        toggleMenu();
    });

    // Close menu when a navigation link is selected.
    nav.addEventListener("click", function (event) {

        if (event.target.closest("a")) {

            nav.classList.remove("nav-active");
            burger.classList.remove("open");

            burger.setAttribute("aria-expanded", "false");
            burger.setAttribute("aria-label", "Open navigation");
        }
    });

    // Close menu when Escape is pressed.
    document.addEventListener("keydown", function (event) {

        if (event.key === "Escape") {

            nav.classList.remove("nav-active");
            burger.classList.remove("open");

            burger.setAttribute("aria-expanded", "false");
            burger.setAttribute("aria-label", "Open navigation");
        }
    });

    // Reset mobile navigation when returning to desktop width.
    window.addEventListener("resize", function () {

        if (window.innerWidth > 760) {

            nav.classList.remove("nav-active");
            burger.classList.remove("open");

            burger.setAttribute("aria-expanded", "false");
            burger.setAttribute("aria-label", "Open navigation");
        }
    });

});
