<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <!-- Leaflet Javascript - Make sure you put this AFTER Leaflet's CSS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Bootstrap CSS -->

    <!-- this is how we load it from a CDN if we aren't building our own Bootstrap version
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    -->
    
    <link href="theme/css/custom.css" rel="stylesheet" >

    <link href="style/main.css" rel="stylesheet">

    <!-- FIXME: This is whilst in dev to prevent Google or Bing indexing the wrong URL -->
    <meta name="robots" content="noindex">

    <title><?php echo @$page_title ? $page_title : 'World Flora Online'; ?></title>
</head>

<body class="bg-body-secondary" >

<div class="banner fixed-top">
    <div id="wfo-banner">
        <a href="/" style="text-decoration:none; color: white;"><h1>World Flora Online</h1></a>
    </div>

        <nav class="wfo-navbar navbar navbar-expand-md navbar-dark bg-primary">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
                    aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav me-auto mb-2 mb-md-0">
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="/">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="search">Search</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="news">News</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="about">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="contribute">Contributing</a>
                        </li>
                    </ul>
                </div>
                <a href="search" style="color: white; padding-right:1em; padding-bottom:0.5em;">
                <svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true"><path d="M14.386 14.386l4.0877 4.0877-4.0877-4.0877c-2.9418 2.9419-7.7115 2.9419-10.6533 0-2.9419-2.9418-2.9419-7.7115 0-10.6533 2.9418-2.9419 7.7115-2.9419 10.6533 0 2.9419 2.9418 2.9419 7.7115 0 10.6533z" stroke="currentColor" stroke-width="2"  fill="none" fill-rule="evenodd" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </a>
            </div>
        </nav>

    </div>
    <main class="container-flex" style="margin-top: 150px;">

    <?php
    if($system_message){
        echo '<div class="container-lg">';
        echo "<div class=\"alert alert-danger\" role=\"alert\"><strong>&nbsp;System Message:&nbsp;</strong>{$system_message}</div>";
        echo '</div>';
    }
?>

    <!-- end header.php -->