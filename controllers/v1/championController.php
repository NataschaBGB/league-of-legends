<?php

    require_once __DIR__ . '/../../helpers/utils.php';
    require_once __DIR__ . '/../../services/ChampionService.php';

    class ChampionController {

        private $service;

        public function __construct() {
            $this->service = new ChampionService();
        }

        public function handleRequest($method, $id) {

            try {

                switch ($method) {

                    case 'GET':
                        if ($id) {
                            $champion = $this->service->getChampion($id);
                            respond(addChampionLinks($champion));
                        }
                        else {
                            $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
                            $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

                            $champions = $this->service->getAllChampions($offset, $limit);
                            respond(addPagePagination($champions, $offset, $limit));
                        }
                        break;

                    case 'POST':
                        $data = $_POST + getJsonData();
                        respond($this->service->createChampion($data), 201);
                        break;

                    case 'PUT':
                        if (!$id) respond(["error" => "Missing ID"], 400);
                        $data = getJsonData();
                        respond($this->service->updateChampion($id, $data, false));
                        break;

                    case 'PATCH':
                        if (!$id) respond(["error" => "Missing ID"], 400);
                        $data = getJsonData();
                        respond($this->service->updateChampion($id, $data, true));
                        break;

                    case 'DELETE':
                        if (!$id) respond(["error" => "Missing ID"], 400);
                        $this->service->deleteChampion($id);
                        respond(["message" => "Champion deleted"], 204);
                        break;

                    default:
                        respond(["error" => "Method not allowed"], 405);
                }

            }
            catch (Exception $e) {
                
                error_log($e->getMessage());
                respond(["error" => "Internal Server Error"], 500);
            
            }
        }
    }