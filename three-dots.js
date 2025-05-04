document.addEventListener("click", function (event) {
    let menu = document.getElementById("userMenuOptions");
    let button = document.querySelector(".user-menu-btn");

    // Toggle menu when button is clicked
    if (event.target === button) {
        menu.classList.toggle("show");
    } else {
        // Hide menu when clicking outside
        menu.classList.remove("show");
    }
});

function copyProfileUrl() {
    let url = window.location.href;
    navigator.clipboard.writeText(url).then(() => alert("Profile URL copied!"));
}

function shareProfile() {
    alert("Sharing options will be implemented soon!");
}
