<?php

    // get functions from utils.php to use here
    require_once __DIR__ . '/../helpers/utils.php';

    // route the request to the correct resource handler (ChampionController, RoleController, etc.) based on the third segment of the URI (from index.php)
    switch ($resource) {

        // if the resource is 'champions', include the ChampionController and call its handleRequest() method, passing the HTTP method and ID (if provided)
        case 'champions':
            require_once __DIR__ . '/../controllers/v1/ChampionController.php';
            // we create a new ChampionController to get access to its methods for handling champion-related requests
            $controller = new ChampionController();
            // handleRequest() is called so it knows which HTTP method is being used (GET, POST, PUT, PATCH, DELETE) and can read the ID from the URI if it is provided (e.g. /champions/50)
            $controller->handleRequest($method, $id);
            // break is used to prevent the switch statement from continuing to check other cases after a match is found
            break;

        // case 'roles':
        //     require_once __DIR__ . '/../controllers/v1/RoleController.php';
        //     $controller = new RoleController();
        //     $controller->handleRequest($method, $id);
        //     break;

        // default case if resource is not supported / does not exist, return 404 Not Found
        default:
            respond(["error" => "Resource not found"], 404);
    }