<?php

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    require_once __DIR__ . '/helpers/utils.php';

    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Fjern base path
    $uri = str_replace('/league-of-legends', '', $uri);

    $segments = explode('/', trim($uri, '/'));

    $api = $segments[0] ?? null;
    $version = $segments[1] ?? null;
    $resource = $segments[2] ?? null;
    $id = $segments[3] ?? null;

    $method = $_SERVER['REQUEST_METHOD'];

    if ($api !== 'api') {
        respond(["error" => "Not Found"], 404);
    }

    switch ($version) {
        case 'v1':
            require_once __DIR__ . '/routes/v1.php';
            break;

        // case 'v2':
        //     require_once __DIR__ . '/routes/v2.php';
        //     break;

        default:
            respond(["error" => "API version not supported"], 400);
    }


// [Client / Browser / Postman]
//          |
//          | HTTP Request (GET, POST, PUT, PATCH, DELETE)
//          v
//     ┌─────────────┐
//     │  index.php  │
//     │  (entry)    │
//     └─────────────┘
//          |
//          | inkluderer
//          v
//     ┌─────────────┐
//     │ router.php  │
//     │ (URL → ctrl)│
//     └─────────────┘
//          |
//          | kalder
//          v
// ┌─────────────────────────┐
// │ ChampionController.php  │
// │  - Håndterer HTTP       │
// │    metoder              │
// │  - Læser ID fra URL     │
// │  - Læser JSON / POST    │
// └─────────────────────────┘
//          |
//          | kalder service
//          v
// ┌─────────────────────────┐
// │ ChampionService.php     │
// │  - Database logik       │
// │  - SELECT, INSERT,      │
// │    UPDATE, DELETE       │
// │  - Transactions         │
// └─────────────────────────┘
//          |
//          | bruger
//          v
// ┌─────────────────────────┐
// │ connect.php (PDO)       │
// │  - Opretter DB forbindelse│
// │  - Prepared statements   │
// └─────────────────────────┘
//          |
//          | ↩ data / succes / fejl
//          v
// ┌─────────────────────────┐
// │ ChampionController.php  │
// │  - formaterer JSON      │
// │  - sender HTTP status   │
// └─────────────────────────┘
//          |
//          | HTTP Response (JSON)
//          v
//       [Client / Postman]