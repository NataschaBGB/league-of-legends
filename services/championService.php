<?php

// ChampionService.php is responsible for handling all database interactions related to champions. It contains methods for retrieving, creating, updating, and deleting champions from the database.
// The ChampionController calls these methods to perform the necessary operations based on the incoming HTTP requests.

    // include the database connection to use in this service
    require_once __DIR__ . '/../connect.php';

    // the ChampionService class contains methods for interacting with the champions data in the database
    class ChampionService {
        
        // getAllChampions() retrieves a list of champions from the database with pagination support. It takes an offset and limit as parameters to determine which champions to retrieve.
        // It returns an array containing the total count of champions and the list of champions based on the specified offset and limit.
        public function getAllChampions($offset = 0, $limit = 10) {
            
            // the global $dbh variable is used to access the database connection created in connect.php. This allows us to execute SQL queries against the database to retrieve champion data.
            global $dbh;

            // get the total count of champions in the database by executing a COUNT(*) query on the champions table. This is useful for pagination to know how many total champions there are.
            $stmtCount = $dbh->query("SELECT COUNT(*) FROM champions");
            // fetchColumn() is used to retrieve the count value from the result of the COUNT(*) query. The count is cast to an integer to ensure it is returned as a number.
            $count = (int) $stmtCount->fetchColumn();

            // sql query to select champion data from the database
            // roles is retrieved using a LEFT JOIN with the champs_roles and roles tables, and aggregated into a JSON array using JSON_ARRAYAGG() to return all roles for each champion in a single field.
            // difficulty is retrieved using a LEFT JOIN with the difficulties table to get the difficulty level for each champion.
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
            LIMIT :limit 
            OFFSET :offset";

            // prepare the SQL statement to prevent SQL injection and allow for parameter binding. The :limit and :offset placeholders will be replaced with the actual values for pagination.
            // the actual values for limit and offset is returned from the ChampionController based on the query parameters in the request (e.g. /champions?offset=0&limit=10).
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();

            $champions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Since the roles are returned as a JSON array in the 'roles' field for each champion, we need to decode it back into a PHP array for easier handling in the application. This is done using json_decode() for each champion in the list.
            foreach ($champions as &$champion) {
                $champion['roles'] = json_decode($champion['roles'], true);
            }

            // return an array containing the total count of champions and the list of champions based on the specified offset and limit. This allows the ChampionController to send this data back to the client in the response, including pagination information.
            return [
                "count" => $count,
                "champions" => $champions
            ];
        }


        // getChampion() retrieves a single champion from the database based on the provided ID.
        // It returns an associative array containing the champion's data, including its roles and difficulty level.
        public function getChampion($id) {

            global $dbh;
            
            // SQL query to select a single champion from the database based on the provided ID.
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
            
            // if a champion is found with the specified ID, the roles field (which is returned as a JSON array) is decoded back into a PHP array for easier handling in the application.
            // If no champion is found, $champion will be false and we simply return that without trying to decode roles.
            if ($champion) $champion['roles'] = json_decode($champion['roles'], true);
            
            // return an associative array containing the champion's data, including its roles and difficulty level.
            // If no champion is found, it will return false.
            return $champion;
        }


        // createChampion() creates a new champion in the database using the data provided in the $data array.
        // It returns the created champion's data as an associative array.
        public function createChampion($data) {
            
            global $dbh;
            
            // extract the roles from the provided data. The roles are expected to be an array of role IDs that should be associated with the champion. We use array_unique() to ensure that there are no duplicate role IDs. If the roles field is not provided in the data, we default to an empty array.
            $roles = array_unique((array)($data['roles'] ?? []));
            
            // begin a database transaction to ensure that all operations (inserting the champion and associating roles) are treated as a single unit of work. If any part of the transaction fails, we can roll back to maintain data integrity.
            $dbh->beginTransaction();
            
            // SQL query to insert a new champion into the champions table. The name, title, description, and difficulty fields are inserted based on the data provided in the $data array.
            $sql = "INSERT INTO champions (name, title, description, difficulty) 
            VALUES (:name,:title,:description,:difficulty)";

            $stmt = $dbh->prepare($sql);
            
            // execute the prepared statement with the actual values for name, title, description, and difficulty from the $data array. The difficulty is cast to an integer to ensure it is stored as a number in the database.
            // the values is expected to be provided in the request body when creating a new champion (e.g. via a POST request to /champions with a JSON body containing the champion data).
            $stmt->execute([
                ":name"=>$data['name'],
                ":title"=>$data['title'],
                ":description"=>$data['description'],
                ":difficulty"=>$data['difficulty']
            ]);

            // after inserting the champion, we get the ID of the newly created champion using lastInsertId().
            // This ID is needed to associate the champion with its roles in the champs_roles table.
            $champion_id = $dbh->lastInsertId();
            
            // if roles has been provided with the champion created
            if (!empty($roles)) {
                // sql query to insert associations between the champion and its roles into the champs_roles table.
                $stmtRole = $dbh->prepare("INSERT INTO champs_roles (champion_id, role_id) VALUES (:champion_id, :role_id)");
                // bind champion_id parameter to the last inserted id
                $stmtRole->bindParam(":champion_id", $champion_id, PDO::PARAM_INT);
            
                // loop through each role ID in the roles array and execute the prepared statement to insert the association between the champion and each role into the champs_roles table.
                foreach ($roles as $role_id) {
                    $stmtRole->bindParam(":role_id", $role_id, PDO::PARAM_INT);
                    $stmtRole->execute();
                }
                // by looping through the roles and inserting them into the champs_roles table, we get a new row for each role associated with the champion, but with the same champion_id.
                // This allows us to have a many-to-many relationship between champions and roles, where each champion can have multiple roles and each role can be associated with multiple champions.
            }
            
            // commit the transaction to save all changes to the database. If any part of the transaction had failed (e.g. an error inserting the champion or associating roles), we could have rolled back to maintain data integrity.
            $dbh->commit();
            
            // return the created champion's data as an associative array by calling getChampion() with the ID of the newly created champion.
            // This allows us to retrieve the full champion data, including its roles and difficulty level, to send back in the response to the client after creating a new champion.
            return $this->getChampion($champion_id);
        }

        // updateChampion() updates an existing champion in the database based on the provided ID and data.
        // It can handle both full updates (PUT) and partial updates (PATCH) depending on the $partial parameter.
        public function updateChampion($id, $data, $partial = false) {
            
            global $dbh;
            
            // fields array to hold the values to be updated, if the update is a PATCH (partial update)
            $fields = [];
            // params array to hold the parameters for the SQL query, starting with the ID of the champion to be updated, so no id is needed in the $data array. The ID is taken from the URI (e.g. /champions/50) and passed as a parameter to this method from the ChampionController.
            $params = [":id" => $id];

            // loop through all fields that can be updated (name, title, description, difficulty) and check if they are provided in the $data array.
            foreach (["name","title","description","difficulty"] as $field) {
                // if $partial is true (indicating a PATCH request) and the field is not provided in the $data array, we simply skip that field and do not include it in the update.
                // This allows for partial updates where only the provided fields are updated, and any missing fields will remain unchanged in the database.
                if ($partial && !isset($data[$field])) continue;

                // if $partial is false (indicating a PUT request) and the field is not provided in the $data array, we throw an exception because for a full update (PUT), all fields are required and must be provided in the request body.
                // This ensures that when doing a full update, we have all the necessary data to update the champion completely.
                if (!$partial && !isset($data[$field])) {
                    throw new Exception("Missing field: $field");
                }

                // if the field is provided in the $data array, we add it to the $fields array for building the SQL query and add the corresponding parameter to the $params array for binding in the prepared statement.
                // this makes sure that only the fields provided in the request body are included in the update, and we can safely handle both full updates (PUT) and partial updates (PATCH) with the same method.
                // the difficulty field is cast to an integer to ensure it is stored as a number in the database, while the other fields are treated as strings.
                $fields[] = "$field = :$field";
                $params[":$field"] = $field === "difficulty" ? (int)$data[$field] : $data[$field];
            }

            // get the roles from the provided data.
            $roles = $data['roles'] ?? null;

            $dbh->beginTransaction();

            // if fields are not empty
            if (!empty($fields)) {
                // build the SQL query to update the champion in the database based on the provided fields and parameters.
                // use the implode() function to join the fields with commas for the SET clause of the UPDATE statement. -
                // - so if we have name and title provided in the $data array, the $fields array will contain ["name = :name", "title = :title"], and implode(", ", $fields) will produce the string "name = :name, title = :title" for the SQL query.
                $stmt = $dbh->prepare("UPDATE champions SET ".implode(", ", $fields)." WHERE id = :id");
                $stmt->execute($params);
            }

            // if roles is provided in the $data array (either for a PATCH or a PUT request), we need to update the champion's roles in the database. If roles is not provided, we will leave the existing roles unchanged.
            if ($roles !== null) {
                // To update the roles for a champion, we first delete all existing role associations for that champion from the champs_roles table using a DELETE statement with the champion_id. This ensures that we remove any old roles that are no longer associated with the champion.
                $stmtDelete = $dbh->prepare("DELETE FROM champs_roles WHERE champion_id = :id");
                $stmtDelete->execute([":id" => $id]);

                // if roles is not empty, we then insert the new role associations for the champion into the champs_roles table using an INSERT statement.
                if (!empty($roles)) {
                    $stmtRole = $dbh->prepare("INSERT INTO champs_roles (champion_id, role_id) VALUES (:champion_id, :role_id)");
                    // bind champion_id parameter to the ID of the champion being updated
                    $stmtRole->bindParam(":champion_id", $id, PDO::PARAM_INT);

                    // loop through each role ID in the roles array and execute the prepared statement to insert the association between the champion and each role into the champs_roles table.
                    foreach ($roles as $role_id) {
                        $stmtRole->bindParam(":role_id", $role_id, PDO::PARAM_INT);
                        $stmtRole->execute();
                    }
                    // by looping through the roles and inserting them into the champs_roles table, we get a new row for each role associated with the champion, but with the same champion_id.
                    // This allows us to update the champion's roles to match exactly what is provided in the request body, whether it's a full update (PUT) or a partial update (PATCH).
                    // If roles is an empty array, it will effectively remove all roles from the champion.
                }
            }

            $dbh->commit();

            // return the updated champion's data as an associative array by calling getChampion() with the ID of the updated champion.
            // This allows us to retrieve the full champion data, including its roles and difficulty level, to send back in the response to the client after updating the champion.
            return $this->getChampion($id);
        }

        // deleteChampion() deletes an existing champion from the database based on the provided ID. It returns true if the deletion was successful.
        public function deleteChampion($id) {
            
            global $dbh;
            
            // SQL query to delete the champion from the champions table based on the provided ID.
            $sql = "DELETE FROM champions WHERE id = :id";
            $stmt = $dbh->prepare($sql);
            
            // bind the ID parameter to the ID of the champion being deleted, which is taken from the URI (e.g. /champions/50) and passed as a parameter to this method from the ChampionController.
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            $stmt->execute();
            
            // return true to indicate that the deletion was successful.
            // The ChampionController will send an appropriate response back to the client based on this result (e.g. a 204 No Content status code if the deletion was successful).
            return true;
        }
    }