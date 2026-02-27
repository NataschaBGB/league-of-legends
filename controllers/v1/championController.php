<?php

// ChampionController.php is responsible for handling HTTP requests related to champions. It receives the HTTP method and ID (if provided) from the router (routes/v1.php) and uses the ChampionService to perform database operations. It also adds HATEOAS links and pagination to the responses before sending them back as JSON.

    // get functions from utils.php to use here
    require_once __DIR__ . '/../../helpers/utils.php';
    // get ChampionService to perform database operations related to champions
    require_once __DIR__ . '/../../services/ChampionService.php';

    // Constructor - ChampionController class
    // The constructor is a special method in a class that is automatically called when an instance of the class is created.
    class ChampionController {

        // $service is an instance of ChampionService that will be used to call methods for getting, creating, updating, and deleting champions in the database.
        // It is initialized in the constructor so it can be reused across different HTTP method handlers (GET, POST, PUT, PATCH, DELETE).
        // This keeps the code organized and allows for separation of concerns between the controller (handling HTTP requests) and the service (handling business logic and database interactions).
        
        private $service;

        // the function __construct() is called when a new instance of ChampionController is created. It initializes the $service property by creating a new instance of ChampionService.
        // This allows the controller to use the service's methods to interact with the database when handling requests.
        // It allows us to keep the database logic separate from the controller logic, so we dont have to use the 'new ChampionService()' in every method that needs to interact with the database. We can just call $this->service->methodName() to use the service's methods.
        public function __construct() {
            $this->service = new ChampionService();
        }

        // handleRequest() is the main method that handles incoming HTTP requests for champion-related resources. It takes the HTTP method (GET, POST, PUT, PATCH, DELETE) and an optional ID (for specific champion operations) as parameters.
        public function handleRequest($method, $id) {

            try {

                switch ($method) {

                    // if the request is a GET
                    case 'GET':
                        // if id is provided (e.g. /champions/50)
                        if ($id) {
                            // getChampion() is called from the ChampionService to retrieve the champion with the specified ID from the database. The result is stored in $champion.
                            $champion = $this->service->getChampion($id);
                            // respond() is called to send the champion data back to the client as a JSON response. The addChampionLinks() function is used to add HATEOAS links to the champion data before sending it in the response.
                            respond(addChampionLinks($champion));
                        }
                        // if id is not provided (e.g. /champions?offset=0&limit=10)
                        else {
                            // set the offset and limit for pagination based on query parameters. If not provided, default to offset=0 and limit=10.
                            // The max(0, (int)$_GET['offset']) ensures that the offset cannot be negative.
                            $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
                            $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

                            // getAllChampions() is called from the ChampionService to retrieve a list of champions from the database based on the specified offset and limit for pagination. The result is stored in $champions.
                            $champions = $this->service->getAllChampions($offset, $limit);
                            // respond() is called to send the list of champions back to the client as a JSON response.
                            // The addPagePagination() function is used to add HATEOAS links and pagination information to the champion data before sending it in the response.
                            respond(addPagePagination($champions, $offset, $limit));
                        }
                        break;

                    // if the request is a POST (creating a new champion)
                    case 'POST':
                        // getJsonData() is called to read the JSON body from the request and decode it into an associative array. This allows us to access the data sent by the client in the request body.
                        // The $_POST superglobal is combined with the JSON data to allow for both form-data and JSON input. If there are overlapping keys, the form-data will overwrite the JSON data.
                        $data = $_POST + getJsonData();
                        // createChampion() is called from the ChampionService to create a new champion in the database using the data provided in the request body.
                        // The result (the created champion) is sent back to the client as a JSON response with a 201 Created status code.
                        respond($this->service->createChampion($data), 201);
                        break;

                    // if the request is a PUT (updating an existing champion) or PATCH (partially updating an existing champion)
                    case 'PUT':
                        // if no ID is provided in the URI (e.g. /champions without an ID), respond with a 400 Bad Request error since we need an ID to know which champion to update
                        if (!$id) respond(["error" => "Missing ID"], 400);
                        // getJsonData() is called to read the JSON body from the request and decode it into an associative array. This allows us to access the data sent by the client in the request body for updating the champion.
                        $data = getJsonData();
                        // updateChampion() is called from the ChampionService to update the champion with the specified ID in the database using the data provided in the request body.
                        // The third parameter (false for PUT, true for PATCH) indicates whether it is a partial update (PATCH) or a full update (PUT).
                        // The result (the updated champion) is sent back to the client as a JSON response
                        respond($this->service->updateChampion($id, $data, false));
                        break;

                    // if the request is a PATCH (partially updating an existing champion)
                    case 'PATCH':
                        // if no ID is provided in the URI (e.g. /champions without an ID), respond with a 400 Bad Request error since we need an ID to know which champion to update
                        if (!$id) respond(["error" => "Missing ID"], 400);
                        // getJsonData() is called to read the JSON body from the request and decode it into an associative array. This allows us to access the data sent by the client in the request body for updating the champion.
                        $data = getJsonData();
                        // updateChampion() is called from the ChampionService to update the champion with the specified ID in the database using the data provided in the request body.
                        // The third parameter (true for PATCH) indicates that it is a partial update, meaning that only the fields provided in the request body will be updated, and any missing fields will not be changed in the database.
                        // The result (the updated champion) is sent back to the client as a JSON response
                        respond($this->service->updateChampion($id, $data, true));
                        break;

                    // if the request is a DELETE (deleting an existing champion)
                    case 'DELETE':
                        // if no ID is provided in the URI (e.g. /champions without an ID), respond with a 400 Bad Request error since we need an ID to know which champion to delete
                        if (!$id) respond(["error" => "Missing ID"], 400);
                        // deleteChampion() is called from the ChampionService to delete the champion with the specified ID from the database.
                        $this->service->deleteChampion($id);
                        // After deletion, a response with a 204 No Content status code is sent back to the client to indicate that the deletion was successful and there is no content to return in the response body.
                        respond(["message" => "Champion deleted"], 204);
                        break;

                    // if the HTTP method is not one of the above (GET, POST, PUT, PATCH, DELETE), respond with a 405 Method Not Allowed error since the API does not support that method for this resource
                    default:
                        respond(["error" => "Method not allowed"], 405);
                }

            }
            // if any exceptions are thrown during the handling of the request (e.g. database errors, validation errors, etc.), they will be caught here.
            catch (Exception $e) {
                
                // The error message is logged for debugging purposes, and a generic 500 Internal Server Error response is sent back to the client to indicate that something went wrong on the server side.
                // This prevents sensitive error details from being exposed to the client while still allowing developers to see the error in the server logs.
                error_log($e->getMessage());
                respond(["error" => "Internal Server Error"], 500);
            
            }
        }
    }