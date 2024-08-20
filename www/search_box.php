<?php
    // value in the box comes from the current
    // request or the one we have saved.
    $q = @$_GET['q'];
    if(!$q){
        $request = @$_SESSION['search_request'];
        if($request) $q = $request['q'];
    }
    
?>

<div class="form-group">
    <div class="input-group">
        <input id="search_box" autofocus class="form-control" type="text" name="q" placeholder="Search..."
            value="<?php echo $q ?>" required />
        &nbsp;
        <span class="input-group-btn">
            <button class="btn btn-success" type="submit">Search</button>
        </span>
        <input type="hidden" name="timestamp" value="<?php echo time(); ?>" />
    </div>
    <div id="search_suggest"></div>

</div>