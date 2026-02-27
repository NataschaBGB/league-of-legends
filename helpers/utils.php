<?php
// utils.php contains helper functions that are used across the application, such as sending JSON responses, reading JSON data from requests, and adding HATEOAS links to champion data.
// These functions help keep the code organized and reusable, allowing us to easily send consistent responses and handle request data in a standardized way throughout our API.

    // send JSON response with status code
    function respond($data, $status = 200) {

        // check Accept header for application/json or */*
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '*/*';

        // if client does not accept JSON, return 406 Not Acceptable
        if (strpos($accept, 'application/json') === false && strpos($accept, '*/*') === false) {
            http_response_code(406);
            header("Content-Type: application/json");
            echo json_encode([
                "error" => "Not Acceptable",
                "message" => "This API only supports 'application/json' responses."
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }

        // set content type to application/json
        header("Content-Type: application/json");

        // set HTTP status code
        http_response_code($status);

        // send JSON response with pretty print and unescaped slashes
        // unescaped slashes makes URLs in the response more readable (e.g. "/league-of-legends/api/v1/champions/50" instead of "\/league-of-legends\/api\/v1\/champions\/50")
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        // stop further execution after sending response
        exit;
    }


    // read JSON body from request
    function getJsonData() {
        // get raw input data and decode JSON into an associative array
        $data = json_decode(file_get_contents("php://input"), true);
        // if data is not an array (null or another type), set data to an empty array
        // this ensures that we can safely combine it with $_POST without errors
        if (!is_array($data)) $data = [];

        // combine with $_POST – form-data will overwrite JSON data if there are overlapping keys
        return $_POST + $data;
    }

    // add HATEOAS links to a champion array
    function addChampionLinks(array $champion): array {

        // extract champion ID for building links
        $id = $champion['id'];

        // add self link and link to list of champions
        $champion['links'] = [
            "self" => "/league-of-legends/api/v1/champions/$id",
            "all_champions" => "/league-of-legends/api/v1/champions?offset=0&limit=10"
        ];

        // return all champion data including links
        return $champion;
    }

    // add pagination + HATEOAS-links to an array of champions
    function addPagePagination($data, $offset, $limit) {
        // add HATEOAS links to each champion in the list
        // list of champions is taken from $data['champions'] which is the array of champions returned from the championService->getAllChampions()
        // addChampionLinks() is called for each champion to add self and all_champions links to each champion in the list
        $champions = array_map('addChampionLinks', $data['champions']);
        // count is taken from $data['count'] which is the total number of champions in the database, returned from championService->getAllChampions()
        $count = $data['count'];

        // build pagination links based on current offset, limit, and total count
        // previous link is only included if offset is greater than 0 (i.e. there is a previous page)
        // next link is only included if offset + limit is less than total count (i.e. there is a next page)
        return [
            "count" => $count,
            "previous" => $offset > 0 ? "/league-of-legends/api/v1/champions?offset=" . max(0, $offset - $limit) . "&limit=$limit" : null,
            "next" => ($offset + $limit < $count) ? "/league-of-legends/api/v1/champions?offset=" . ($offset + $limit) . "&limit=$limit" : null,
            "champions" => $champions
        ];
    }