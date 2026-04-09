# API – Users

Minden user végpont JSON-t ad vissza. A védett végpontokhoz **Laravel Sanctum Bearer token** kell.

## Auth

### Bearer token header

```
Authorization: Bearer <TOKEN>
```

## Public

### POST `/api/login`

Bejelentkezés email *vagy* username alapján.

- **Body**
  - `login` (string, kötelező): email vagy username
  - `password` (string, kötelező)

- **200 OK válasz**
  - `token` (string): plain text Sanctum token
  - `user` (object): user adatok

- **Hibák**
  - **401** `{"error":"Hibás adatok"}`
  - **403** `{"error":"A felhasználó fel van függesztve"}`

Példa:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"login":"admin@example.com","password":"secret"}'
```

## Auth (Sanctum)

### GET `/api/user`

Visszaadja az aktuális bejelentkezett usert.

```bash
curl http://localhost:8000/api/user \
  -H "Authorization: Bearer <TOKEN>"
```

### GET `/api/users`

User lista (jelenlegi implementáció: **minden user**).

```bash
curl http://localhost:8000/api/users \
  -H "Authorization: Bearer <TOKEN>"
```

## Auth + Admin

Ezekhez `auth:sanctum` + `admin` middleware kell.

### POST `/api/users`

Új user létrehozása.

- **Body (jelenlegi implementáció szerint)**
  - `name` (string, kötelező)
  - `email` (string, kötelező)
  - `username` (string, kötelező)
  - `password` (string, kötelező)
  - `role` (string, opcionális; default: `vendeg`)

### PUT `/api/users/{id}`

User frissítése.

- **Body**
  - `name`, `email`, `username`, `role` (opcionális)
  - `password` (opcionális; ha megadod, újrahash-eli)

### POST `/api/users/{id}/suspend`

Felfüggesztés: `active=false`, `suspended_at=now()`

### POST `/api/users/{id}/unsuspend`

Visszaaktiválás: `active=true`, `suspended_at=null`

