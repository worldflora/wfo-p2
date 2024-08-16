<?php

$page_title = "WFO: Home Page";

require_once('header.php');
?>

<div class="container" style="margin-top: 4%;">
    <div>
        <div class="row">
            <div id="logo" class="text-center">
                <h1>World Flora Online</h1>
                <form role="form" method="GET" action="search">
                    <?php require_once('search_box.php') ?>
                </form>
                <p>This is an mockup of a new portal for the World Flora Online. The current live version
                    is available <a href="https://www.worldfloraonline.org/" target="wfo">here</a>.</p>
            </div>
        </div>
    </div>
</div>

<?php
require_once('footer.php');
?>