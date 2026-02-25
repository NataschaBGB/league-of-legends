<?php
// services/ChampionService.php

require_once __DIR__ . '/../connect.php';

class ChampionService {
    public function getAllChampions($offset = 0, $limit = 10) {

        global $dbh;

        $sql = "SELECT 
        champions.id,
        champions.name,
        champions.title,
        JSON_ARRAYAGG(roles.role) AS roles,
        champions.description,
        difficulties.difficulty AS difficulty
        FROM champions
        LEFT JOIN champs_roles ON champions.id = champs_roles.champion_id
        LEFT JOIN roles ON champs_roles.role_id = roles.id
        LEFT JOIN difficulties ON champions.difficulty = difficulties.id
        GROUP BY champions.id
        ORDER BY champions.id
        LIMIT :limit OFFSET :offset";

        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        $champions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($champions as &$champion) {
            $champion['roles'] = json_decode($champion['roles'], true);
        }

        return $champions;
    }


    public function getChampion($id) {

        global $dbh;
        
        $sql = "SELECT 
        champions.id,
        champions.name,
        champions.title,
        JSON_ARRAYAGG(roles.role) AS roles,
        champions.description,
        difficulties.difficulty AS difficulty
        FROM champions
        LEFT JOIN champs_roles ON champions.id = champs_roles.champion_id
        LEFT JOIN roles ON champs_roles.role_id = roles.id
        LEFT JOIN difficulties ON champions.difficulty = difficulties.id
        WHERE champions.id = :id 
        GROUP BY champions.id";
        
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $champion = $stmt->fetch();
        
        if ($champion) $champion['roles'] = json_decode($champion['roles'], true);
        
        return $champion;
    }


    public function createChampion($data) {
        
        global $dbh;
        
        $roles = array_unique((array)($data['roles'] ?? []));
        
        $dbh->beginTransaction();
        
        $sql = "INSERT INTO champions (name, title, description, difficulty) 
        VALUES (:name,:title,:description,:difficulty)";

        $stmt = $dbh->prepare($sql);
        
        $stmt->execute([
            ":name"=>$data['name'],
            ":title"=>$data['title'],
            ":description"=>$data['description'],
            ":difficulty"=>$data['difficulty']
        ]);

        $champion_id = $dbh->lastInsertId();
        
        if (!empty($roles)) {
            $stmtRole = $dbh->prepare("INSERT INTO champs_roles (champion_id, role_id) VALUES (:champion_id, :role_id)");
            $stmtRole->bindParam(":champion_id", $champion_id, PDO::PARAM_INT);
        
            foreach ($roles as $role_id) {
                $stmtRole->bindParam(":role_id", $role_id, PDO::PARAM_INT);
                $stmtRole->execute();
            }
        }
        
        $dbh->commit();
        
        return $this->getChampion($champion_id);
    }

    public function updateChampion($id, $data, $partial = false) {
        
        global $dbh;
        
        $fields = [];
        $params = [":id" => $id];

        // Loop gennem alle felter, der kan opdateres
        foreach (["name","title","description","difficulty"] as $field) {
            // PATCH: hvis felt ikke er sat, spring over
            if ($partial && !isset($data[$field])) continue;

            // PUT: hvis felt mangler, smid fejl
            if (!$partial && !isset($data[$field])) {
                throw new Exception("Missing field: $field");
            }

            $fields[] = "$field = :$field";
            $params[":$field"] = $field === "difficulty" ? (int)$data[$field] : $data[$field];
        }

        // Håndter roller
        $roles = $data['roles'] ?? null;

        $dbh->beginTransaction();

        // Opdater champions kun hvis der er felter
        if (!empty($fields)) {
            $stmt = $dbh->prepare("UPDATE champions SET ".implode(", ", $fields)." WHERE id = :id");
            $stmt->execute($params);
        }

        // Håndter roller kun hvis roller er sat (PATCH) eller alle roller skal overskrives (PUT)
        if ($roles !== null) {
            // Slet alle eksisterende roller
            $stmtDelete = $dbh->prepare("DELETE FROM champs_roles WHERE champion_id = :id");
            $stmtDelete->execute([":id" => $id]);

            // Indsæt nye roller, hvis der er nogen
            if (!empty($roles)) {
                $stmtRole = $dbh->prepare("INSERT INTO champs_roles (champion_id, role_id) VALUES (:champion_id, :role_id)");
                foreach ($roles as $role_id) {
                    $stmtRole->execute([
                        ":champion_id" => $id,
                        ":role_id" => (int)$role_id
                    ]);
                }
            }
        }

        $dbh->commit();

        // Returner opdateret champion
        return $this->getChampion($id);
    }

    public function deleteChampion($id) {
        
        global $dbh;
        
        $sql = "DELETE FROM champions WHERE id = :id";
        $stmt = $dbh->prepare($sql);
        
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return true;
    }
}