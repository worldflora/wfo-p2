<?php

    $request = @$_SESSION['search_request'];

    // value in the box comes from the current
    // request or the one we have saved.
    $q = @$_GET['q'];
    if(!$q && $request)$q = $request['q'];

    // set up the type based on the previously submitted values.
    if($request){
        $search_type = @$request["search_type"];
        $search_type_label = $search_type == 'name' ? "Name:" : 'Text:';
    }else{
        $search_type = 'name';
        $search_type_label = "Name:";
    }
    
?>

<div class="form-group">
    <div class="input-group">
        <button class="btn btn-outline-secondary" type="submit" id="search_box_switch_button"
            name="search_box_switch_button" data-bs-toggle="tooltip" data-bs-placement="right"
            title="Click to toggle between name and text searching."
            value="<?php echo $search_type; ?>"><?php echo $search_type_label; ?></button>
        <input id="search_box" autofocus class="form-control" type="text" name="q" placeholder="Search..."
            value="<?php echo $q ?>" />
        <button class="btn btn-success" type="submit">Search</button>
    </div>
    <input type="hidden" name="search_type" id="search_type_input" value="<?php echo $search_type; ?>" />
    <input type="hidden" name="timestamp" value="<?php echo time(); ?>" />
    <div id="search_suggest"></div>

</div>