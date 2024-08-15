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
                    <div class="form-group">
                        <div class="input-group">
                            <input id="1" class="form-control" type="text" name="q" placeholder="Search..." required />
                            &nbsp;
                            <span class="input-group-btn">
                                <button class="btn btn-success" type="submit">Search</button>
                            </span>
                        </div>
                    </div>
                </form>
                <p>This is an experimental mockup of a new portal for the World Flora Online. The current live version
                    is available <a href="https://www.worldfloraonline.org/" target="wfo">here</a>.</p>
            </div>
        </div>
    </div>
</div>

<?php
require_once('footer.php');
?>