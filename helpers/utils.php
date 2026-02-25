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
    function addChampionLinks(array $champion): array {
        $id = $champion['id'];

        $champion['links'] = [
            "self" => "/league-of-legends/api/v1/champions/$id",
            "all_champions" => "/league-of-legends/api/v1/champions?offset=0&limit=10"
        ];

        return $champion;
    }

    // Tilføj pagination + HATEOAS-links til et array af champions
    function addPagePagination($data, $offset, $limit) {
        $champions = array_map('addChampionLinks', $data['champions']);
        $total = $data['total'];

        return [
            "count" => $total,
            "previous" => $offset > 0 ? "/league-of-legends/api/v1/champions?offset=" . max(0, $offset - $limit) . "&limit=$limit" : null,
            "next" => ($offset + $limit < $total) ? "/league-of-legends/api/v1/champions?offset=" . ($offset + $limit) . "&limit=$limit" : null,
            "champions" => $champions
        ];
    }