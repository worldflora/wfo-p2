

// enable the bootstrap tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl, {
    });
})

// search box suggestion behaviour
const searchBox = document.getElementById("search_box");
if (searchBox) {

    // on keydown we just look for the arrow to see
    // if they want to move to the selection box 
    searchBox.addEventListener("keydown", (e) => {

        if (e.code === "ArrowDown") {
            const s = document.querySelector("#search_suggest select");
            s.selectedIndex = 0;
            s.focus();
            e.preventDefault();
        }
    });

    // key up we use to start a suggestion
    searchBox.addEventListener("keyup", (e) => {

        const val = e.target.value;
        const targetDiv = document.getElementById("search_suggest");

        // we only do a suggestion if we are in name mode
        const searchBoxSwitch = document.getElementById("search_box_switch_button");
        if (searchBoxSwitch.value != 'name') return null;

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
                //console.log(error);
            });
    });
}

const searchBoxSwitch = document.getElementById("search_box_switch_button");
const searchTypeInput = document.getElementById("search_type_input");
if (searchBoxSwitch) {
    searchBoxSwitch.addEventListener("click", (e) => {
        console.log(e);

        // for some reason this event capture enter in the
        // input text box so we need a hack to ignore it.
        if (searchBox === document.activeElement) return;

        if (e.target.value == 'name') {
            e.target.value = 'text';
            e.target.innerHTML = 'Text:';
            searchTypeInput.value = 'text';  // submitted with the form

            // hide any suggested names
            const targetDiv = document.getElementById("search_suggest");
            targetDiv.innerHTML = null;

        } else {
            e.target.value = 'name';
            e.target.innerHTML = 'Name:';
            searchTypeInput.value = 'name'; // submitted with the form
        }
        document.getElementById("search_box").focus(); // hides the help buble
        e.preventDefault();
    });

}


// the search box is loaded dynamically so needs to
// have events hard coded
function searchSuggestKeyDown(e) {

    // up arrow at the top moves us back to the
    // search box
    if (e.code === "ArrowUp" && e.target.selectedIndex == 0) {
        const searchBox = document.getElementById("search_box");
        searchBox.focus();
        searchBox.setSelectionRange(-1, -1);
        e.preventDefault();
    }

    // enter loads the page of the chosen name
    if (e.code === "Enter") {
        window.location = e.target.value;
        e.preventDefault();
    }
}

// Map functions


