# League of Legends REST API

A versioned REST API built in PHP with a focus on clean architecture, separation of concerns, pagination, and HATEOAS.

The project was developed as a school project, as a backend API and can be used by frontend applications, Postman, or other clients.

---

## 🚀 Features

✅ RESTful endpoints (GET, POST, PUT, PATCH, DELETE)
✅ API versioning (/api/v1/)
✅ Clean architecture-inspired structure
✅ Separation of controller and service layer
✅ PDO with prepared statements
✅ Offset/Limit pagination
✅ Total count (similar to the Pokémon API)
✅ HATEOAS links
✅ JSON responses
✅ Proper HTTP status codes
✅ 406 Not Acceptable if the Accept header is not application/json

---

## 📂 Project Structure
league-of-legends/
│
├── index.php - Entry point
├── connect.php - Database connection (PDO)
│
├── routes/
│ └── v1.php - Version 1 routing
│
├── controllers/
│ └── ChampionController.php
│
├── services/
│ └── ChampionService.php
│
└── helpers/
└── utils.php - respond(), retrieve JSON data, HATEOAS, pagination

---

## 🔄 Request Flow

Client
↓
index.php
↓
routes/v1.php
↓
ChampionController
↓
ChampionService
↓
Database (PDO)
↓
JSON Response + HATEOAS + Pagination

---

## 🌐 Base URL

http://localhost/league-of-legends/api/v1/

---

## 📌 Endpoints

- Get all champions (paginated)

    GET http://localhost/league-of-legends/api/v1/champions?offset=0&limit=10

- Get single champion

    GET http://localhost/league-of-legends/api/v1/champions/{id}

- Create champion

    POST http://localhost/league-of-legends/api/v1/champions

- Update champion

    PUT http://localhost/league-of-legends/api/v1/champions/{id}

- Delete champion

    DELETE http://localhost/league-of-legends/api/v1/champions/{id}

---

## 📄 Example Response (Paginated)

JSON
{
    "count": 7,
    "previous": null,
    "next": "/league-of-legends/api/v1/champions?offset=5&limit=5",
    "champions": [
        {
            "id": 50,
            "name": "Ahri",
            "title": "the Nine-Tailed Fox",
            "roles": [
                "mage",
                "assasin"
            ],
            "description": "Innately connected to the magic of the spirit realm, Ahri is a fox-like vastaya who can manipulate her prey's emotions and consume their essence\u2014receiving flashes of their memory and insight from each soul she consumes. Once a powerful yet wayward predator, Ahri is now traveling the world in search of remnants of her ancestors while also trying to replace her stolen memories with ones of her own making.",
            "difficulty": "medium",
            "links": {
                "self": "/league-of-legends/api/v1/champions/50",
                "all_champions": "/league-of-legends/api/v1/champions?offset=0&limit=10"
            }
        },
        {
            "id": 51,
            "name": "Milio",
            "title": "The Gentle Flame",
            "roles": [
                "mage",
                "support"
            ],
            "description": "Milio is a warmhearted boy from Ixtal who has, despite his young age, mastered the fire axiom and discovered something new: soothing fire. With this newfound power, Milio plans to help his family escape their exile by joining the Yun Tal - just like his grandmother once did. Having traveled through the Ixtal jungles to the capital of Ixaocan, Milio now prepares to face the Vidalion and join the Yun Tal, unaware of the trials and dangers that await him.",
            "difficulty": "medium",
            "links": {
                "self": "/league-of-legends/api/v1/champions/51",
                "all_champions": "/league-of-legends/api/v1/champions?offset=0&limit=10"
            }
        }
    ]
}