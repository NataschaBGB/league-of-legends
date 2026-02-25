<?php

    require_once __DIR__ . '/../helpers/utils.php';

    switch ($resource) {

        case 'champions':
            require_once __DIR__ . '/../controllers/v1/ChampionController.php';
            $controller = new ChampionController();
            $controller->handleRequest($method, $id);
            break;

        // case 'roles':
        //     require_once __DIR__ . '/../controllers/v1/RoleController.php';
        //     $controller = new RoleController();
        //     $controller->handleRequest($method, $id);
        //     break;

        default:
            respond(["error" => "Resource not found"], 404);
    }