<?php


/**
 * Send JSON response med statuskode
 * Tjekker også Accept-header og returnerer 406 hvis klienten ikke accepterer JSON
 */
function respond($data, $status = 200) {
    // Tjek Accept-header
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '*/*';
    if (strpos($accept, 'application/json') === false && strpos($accept, '*/*') === false) {
        http_response_code(406);
        header("Content-Type: application/json");
        echo json_encode([
            "error" => "Not Acceptable",
            "message" => "This API only supports 'application/json' responses."
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Sæt Content-Type
    header("Content-Type: application/json");

    // Sæt statuskode
    http_response_code($status);

    // Send data som JSON
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Hent ID fra URL /champions/ID
 * SegmentIndex default 2: /league-of-legends/api/v1/champions/50
 */
function getIdFromUrl($segmentIndex = 2) {
    $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $parts = explode('/', $uri);
    return isset($parts[$segmentIndex]) ? (int)$parts[$segmentIndex] : null;
}


/**
 * Læs JSON body fra request
 */
function getJsonData() {
    // Hent JSON først
    $data = json_decode(file_get_contents("php://input"), true);
    // if data is not an array (null or another type), set data to an empty array
    // this ensures that we can safely combine it with $_POST without errors
    if (!is_array($data)) $data = [];

    // Kombiner med $_POST – form-data vil overskrive data hvis der er overlap
    return $_POST + $data;
}

// Tilføj HATEOAS links til champion data
// function addChampionLinks($champion) {

//     $id = $champion['id'];

//     $champion['links'] = [
//         "self" => "/league-of-legends/api/v1/champions/$id",
//         "champions" => "/league-of-legends/api/v1/champions"
//     ];

//     return $champion;
// }



/**
 * Læs JSON body eller form-data fra request
 * Returnerer altid en assoc. array
 */

/**
 * Tilføj HATEOAS links til champion data
 */
function addChampionLinks($champion) {
    $id = $champion['id'] ?? null;

    if ($id) {
        $champion['links'] = [
            "self" => "/league-of-legends/api/v1/champions/$id",
            "champions" => "/league-of-legends/api/v1/champions"
        ];
    }

    return $champion;
}

/**
 * Tilføj HATEOAS links til en liste af champions
 */
function addChampionsLinks(array $champions) {
    return array_map('addChampionLinks', $champions);
}