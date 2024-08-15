<?php

$page_title = "WFO: Search";

require_once('header.php');
?>

<div class="container-lg">

    <div class="row">
        <div class="col">
            <div data-bs-toggle="offcanvas" style="float: right;">
                <button class="btn btn-secondary d-lg-none" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasResponsive" aria-controls="offcanvasResponsive">Filter</button>
            </div>
            <h1>Search</h1>
            <p>This is where we search around</p>

            <?php print_r($_GET) ?>
        </div>
        <div class="col-4 bg-light offcanvas-lg offcanvas-end" tabindex="-1" id="offcanvasResponsive"
            aria-labelledby="offcanvasResponsiveLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasResponsiveLabel">Filter Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                    data-bs-target="#offcanvasResponsive" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <div class="container" style="padding-top: 1em;">
                    <h4 class="d-none d-lg-block">Filter Settings</h4>
                    <p>This is content within an <code>.offcanvas-lg</code>.</p>
                    <p>Here is a load of stuff ...</p>
                </div>
            </div>
        </div>
    </div>





</div>

<?php
require_once('footer.php');
?>