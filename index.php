<?php

    // show errors for debugging
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // get functions from utils.php to use here
    require_once __DIR__ . '/helpers/utils.php';

    // get the url and parse it into an array
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // remove the base path ("/league-of-legends") from the URI, so we can work with "/api/v1/champions/50" in the rest of the code.
    $uri = str_replace('/league-of-legends', '', $uri);

    // explode the URI array into segments, so we can easily access the different parts (version, resource, id)
    $segments = explode('/', trim($uri, '/'));

    // get the 'api'(0), 'v1'(1), 'champions'(2), 'id'(3) from the segments array
    $api = $segments[0] ?? null;
    $version = $segments[1] ?? null;
    $resource = $segments[2] ?? null;
    $id = $segments[3] ?? null;

    // get the HTTP method (GET, POST, PUT, PATCH, DELETE) from the request
    $method = $_SERVER['REQUEST_METHOD'];

    // if the first segment is not 'api', return 404 Not Found
    if ($api !== 'api') {
        respond(["error" => "Not Found"], 404);
    }

    // route the request to the correct version handler (v1.php, v2.php, etc.) based on the second segment of the URI
    switch ($version) {
        case 'v1':
            require_once __DIR__ . '/routes/v1.php';
            break;

        // case 'v2':
        //     require_once __DIR__ . '/routes/v2.php';
        //     break;

        // default case if version is not supported, return 400 Bad Request
        default:
            respond(["error" => "API version not supported"], 400);
    }




// [Client / Browser / Postman]
//          |
//          | HTTP Request (GET, POST, PUT, PATCH, DELETE)
//          | fx /league-of-legends/api/v1/champions/50
//          v
//     ┌──────────────────────┐
//     │    index.php         │
//     │ - Error reporting    │
//     │ - Includes utils     │
//     │ - Parses URI         │
//     │ - Splits base path   │
//     │ - Checks HTTP method │
//     └──────────────────────┘
//          |
//          | version = 'v1'
//          v
//     ┌─────────────────────┐
//     │    routes/v1.php    │
//     │ - Matches resource  │
//     │ - Includes          │
//     │  ChampionController │
//     │ - Calls             │
//     │   handleRequest()   │
//     └─────────────────────┘
//          |
//          | calls
//          v
// ┌───────────────────────────┐
// │   ChampionController.php  │
// │ - Handles HTTP methods    │
// │ - Reads ID from $id       │
// │ - Reads JSON / form-data  │
// │ - Calls ChampionService   │
// │ - Adds HATEOAS links      │
// │ - Adds pagination         │
// └───────────────────────────┘
//          |
//          | calls
//          v
// ┌───────────────────────────┐
// │    ChampionService.php    │
// │ - Database logic (SELECT, │
// │   INSERT, UPDATE, DELETE) │
// │ - Transactions            │
// │ - Returns data as array   │
// └───────────────────────────┘
//          |
//          | uses
//          v
// ┌──────────────────────────┐
// │     connect.php (PDO)    │
// │ - Creates DB connection  │
// │ - Prepared statements    │
// └──────────────────────────┘
//          |
//          | and sends data back as array to
//          v
// ┌──────────────────────────┐
// │  ChampionController.php  │
// │ - Wraps array in JSON    │
// │ - Adds HATEOAS           |
// |     + pagination links   │
// │ - Sends HTTP status      │
// └──────────────────────────┘
//          |
//          | HTTP Response (JSON)
//          v
//       [Client / Postman]
// JSON response with data requested in the beginning
//      - (e.g. champion with id 50, or list of champions with pagination)