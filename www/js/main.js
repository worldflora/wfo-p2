

// enable the bootstrap tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl, {
        trigger: 'hover'
    });
})

// search box suggestion behaviour
const searchBox = document.getElementById("search_box");
if (searchBox) {
    searchBox.addEventListener("keyup", (e) => {

        const val = e.target.value;
        const targetDiv = document.getElementById("search_suggest");

        // simply call for ajax to update the suggestions
        // the logic will be handled in the php script
        fetch('search_suggest.php?q=' + encodeURIComponent(val))
            .then(function (response) {
                return response.text();
            })
            .then(function (text) {
                targetDiv.innerHTML = text;
            })
            .catch(function (error) {
                // render nothing but report it in the console.
                targetDiv.innerHTML = null;
                console.log(error);
            });
    });
}
