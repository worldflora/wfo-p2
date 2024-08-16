<?php

?>

<div class="form-group">
    <div class="input-group">
        <input id="search_box" autofocus class="form-control" type="text" name="q" placeholder="Search..."
            value="<?php echo @$_GET['q'] ?>" required />
        &nbsp;
        <span class="input-group-btn">
            <button class="btn btn-success" type="submit">Search</button>
        </span>
    </div>
    <div id="search_suggest"></div>

</div>