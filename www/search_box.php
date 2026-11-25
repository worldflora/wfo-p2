<?php

    $request = @$_SESSION['search_request'];

    // value in the box comes from the current
    // request or the one we have saved.
    $q = @$_GET['q'];
    if(!$q && $request)$q = $request['q'];

    // set up the type based on the previously submitted values.
    if($request){
        $search_type = @$request["search_type"];
        $search_type_placeholder = $search_type == 'name' ? 'Start typing a name ...' : 'Search all text ...';
        $search_type_label = $search_type == 'name' ? "Names:" : 'Text:';
    }else{
        $search_type = 'name';
        $search_type_placeholder = 'Start typing a name ...';
        $search_type_label = "Names:";
    }


    
?>

<div class="form-group">
    <div class="input-group">

 <button 
    id="search_box_switch_button"
    class="btn btn-outline-secondary dropdown-toggle"
    type="button"
    data-bs-toggle="dropdown"
    aria-expanded="false"
    data-bs-toggle="tooltip" 
    data-bs-placement="right" 
    title="Click to switch between name and text searching."
    ><?php echo $search_type_label; ?></button>
  <ul class="dropdown-menu" > 
    <li><a class="dropdown-item search_box_switch" href="#" data-search-kind="name" >Search names alphabetically</a></li>
    <li><a class="dropdown-item search_box_switch" href="#" data-search-kind="text" >Search all text</a></li>
  </ul>
     
        <input id="search_box" 
            autofocus 
            class="form-control"
            type="text"
            name="q"
            placeholder="<?php echo $search_type_placeholder; ?>"
            value="<?php echo $q ?>" />

        <button class="btn btn-success" type="submit">Search</button>

        <button 
            class="btn btn-outline-secondary"
            id="search_box_cancel_button"
            name="search_box_switch_button"
            data-bs-toggle="tooltip" 
            data-bs-placement="left" 
            title="Click to clear all search parameters."
            value="clear_search">X</button>
    
    </div>
    <input type="hidden" name="search_type" id="search_type_input" value="<?php echo $search_type; ?>" />
    <input type="hidden" name="timestamp" value="<?php echo time(); ?>" />
    <div id="search_suggest"></div>

</div>