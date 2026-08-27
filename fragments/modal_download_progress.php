<!--  Download progress modal -->
<div class="modal fade" id="listDownloadModal" tabindex="-1" aria-labelledby="listDownloadModalLabel" aria-hidden="true">
<div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
        <h1 class="modal-title fs-5" id="listDownloadModalLabel">Download checklist</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body" id="listDownloadModalContent">
        Working ...
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-danger" id="listDownloadModalButton" data-bs-dismiss="modal">Stop</button>
    </div>
    </div>
</div>
</div>
<!--  End of Download progress modal -->

<script>

if(document.getElementById('listDownloadModal')){
    // When we show the modal we start the process that then
    // polls until it is finished
    document.getElementById('listDownloadModal').addEventListener('show.bs.modal', event => {
        generateListDownload(event.relatedTarget.dataset.wfoFormat);
    })

    // If they close the modal we stop everything by reloading the page.
    document.getElementById('listDownloadModal').addEventListener('hide.bs.modal', event => {
        window.location = "search";
    })
}

/*

    Generating the download list with a progress bar

*/
function generateListDownload(format){

    // get a handle on the div we will be updating
    const modalContent = document.getElementById('listDownloadModalContent');

    fetch('/list_download.php?format=' + format)
        .then(response => response.json())
        .then(json => {
            modalContent.innerHTML = json.message;
            if(json.finished){
                // change the button
                const button = document.getElementById('listDownloadModalButton');
                button.innerHTML = "Close";
                button.classList.remove('btn-danger');
                button.classList.add('btn-success');
            }else{ 
                setTimeout( () => { 
                    generateListDownload(format);
                }, 10);
            }
            return json;
        }).catch((error) => {
            console.log(error);
            modalContent.innerHTML = error;
        });
}

</script>