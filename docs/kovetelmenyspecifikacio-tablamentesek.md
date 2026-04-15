# Követelményspecifikáció – Táblaállapot mentések (Board Save)

| Mező | Érték |
|------|--------|
| **Verzió** | 1.0 |
| **Dátum** | 2026-04-11 |
| **Hatókör** | REST API modul: felhasználónkénti **mentési csoportok** és azokon belüli **név szerinti táblaállapot-mentések** (JSON payload) |
| **Forrás** | Implementáció: `BoardSaveGroupController`, `BoardSaveController`, modellek, migrációk, `routes/api.php` |
| **Kapcsolódó dokumentumok** | `docs/api-board-saves.md` (API részletek, payload séma), `docs/database-schema.md` |

---

## 1. Bevezetés

### 1.1 Cél

A sejtautomata alkalmazásban a bejelentkezett felhasználó a **tábla pillanatképét** (cellaértékek, táblatípus, szomszédság, mód, opcionális UI-kontextus) **strukturáltan elmentheti**, **csoportokba szervezve**, **név szerinti slotokban**. A követelmények biztosítják az adatok **szétválasztását felhasználók szerint**, a **név egyediségét csoportszinten**, és a **REST API-n keresztüli kezelhetőséget**.

### 1.2 Hatókör (scope)

**Benne van:**

- Csoportok CRUD műveletei (saját rekordokra).
- Mentések CRUD műveletei egy adott csoporton belül (saját rekordokra).
- `payload` tárolása JSON-ként, a kliens által küldött tömb/objektum szerkezet validálása (Laravel: kötelező nem üres asszociatív struktúra `array` szabályként).

**Kívül esik (nem része ennek az SRS-nek):**

- A payload **tartalmi** (üzleti) részletes sémaellenőrzése a szerveren (pl. `schemaVersion`, `board` enum) – a jelen backend a `payload`-ot **strukturált JSON tömbként** fogadja, nem validálja belső mezőket.
- Admin felület, export/import, megosztás más felhasználókkal.

### 1.3 Fogalmak

| Fogalom | Meghatározás |
|---------|----------------|
| **Mentési csoport** | Felhasználói logikai mappa; rekord a `board_save_groups` táblában. |
| **Mentés** | Egy elnevezett táblaállapot; rekord a `board_saves` táblában, pontosan egy csoporthoz tartozik. |
| **Payload** | A táblaállapot és metaadatok JSON reprezentációja; a `board_saves.payload` oszlopban tárolva. |

---

## 2. Érintettek és előfeltételek

| Érintett | Szerep |
|----------|--------|
| Végfelhasználó | Csoportokat és mentéseket kezel saját fiókjában. |
| Frontend / kliens | Bearer tokenes hívások, payload összeállítása az `api-board-saves.md` séma szerint (ajánlott). |
| Backend | Laravel Sanctum auth, adatperzisztencia, jogosultság-ellenőrzés. |

**Előfeltétel:** érvényes felhasználói munkamenet / token (`auth:sanctum`), az API többi védett végpontjával konzisztensen.

---

## 3. Funkcionális követelmények

### 3.1 Hitelesítés és jogosultság

| Azonosító | Követelmény |
|-----------|-------------|
| **FR-AUTH-01** | Minden board-save végpont csak `auth:sanctum` middleware mellett érhető el. |
| **FR-AUTH-02** | A listázott és módosítható `board_save_groups` rekordok `user_id` mezője **egyezzen** a kérésben szereplő felhasználó azonosítójával. |
| **FR-AUTH-03** | Mentés (`board_saves`) esetén a rekord `user_id` és a csoport `user_id` is egyezzen a bejelentkezett felhasználóval; ellenkező esetben **403** vagy **404** az alábbi szabály szerint. |

### 3.2 Mentési csoportok

| Azonosító | Követelmény |
|-----------|-------------|
| **FR-GRP-01** | `GET /api/board-save-groups`: a válasz a bejelentkezett felhasználó összes csoportja; rendezés **`position` növekvő**, majd **`id` növekvő**. |
| **FR-GRP-02** | `POST /api/board-save-groups`: kötelező `name` (string, max 255); opcionális `position` (egész, ≥ 0); a rekord `user_id`-je automatikusan a hívó user; válasz **201** + létrejött objektum. |
| **FR-GRP-03** | `GET /api/board-save-groups/{board_save_group}`: csak saját csoport olvasható; idegen esetén **403**, JSON `{ "error": "Nincs jogosultság" }`. |
| **FR-GRP-04** | `PUT /api/board-save-groups/{board_save_group}`: csak saját csoport; **kötelező** a `name` (max 255); `position` opcionális (nullable, egész ≥ 0); idegen csoport **403**. |
| **FR-GRP-05** | `DELETE /api/board-save-groups/{board_save_group}`: csak saját csoport; törlés után válasz `{ "ok": true }`; az adatbázis **CASCADE** szabályai szerint a kapcsolódó mentések is törlődnek. |

### 3.3 Mentések (egy csoporton belül)

| Azonosító | Követelmény |
|-----------|-------------|
| **FR-SAV-01** | `GET .../board-save-groups/{group}/saves`: csak akkor, ha a csoport a useré; különben **403**. Válasz: a csoport összes mentése; a kapcsolat modell szerinti sorrend (**`id` csökkenő**). A válasz **teljes rekordokat** ad vissza, **beleértve a `payload` mezőt** is. |
| **FR-SAV-02** | `POST .../saves`: `name` kötelező, max 255, **egyedi** a `(board_save_group_id, name)` páron belül (adatbázis UNIQUE + Laravel validáció); `payload` kötelező, **tömb típusú** (üres tömb is elfogadott struktúrában); válasz **201** + rekord. |
| **FR-SAV-03** | `GET .../saves/{save}`: csoport és mentés összetartozásának és user egyezésnek ellenőrzése; ha a mentés nem a megadott csoporthoz tartozik vagy nem a useré, **404**, `{ "error": "Nincs találat" }`. |
| **FR-SAV-04** | `PUT .../saves/{save}`: ugyanaz az összetartozás-ellenőrzés mint a GET-nél; validáció: **`name` és `payload` egyaránt kötelező** (a jelen implementáció **nem** támogatja a részleges PATCH-et, ahol csak az egyik frissülne). `name` egyediség a csoporton belül, önmaga kizárásával. |
| **FR-SAV-05** | `DELETE .../saves/{save}`: törlés után `{ "ok": true }`; hibás összerendelés esetén **404** (`Nincs találat`). |

### 3.4 Adatintegritás

| Azonosító | Követelmény |
|-----------|-------------|
| **FR-DB-01** | `board_save_groups.user_id` → `users.id`, törléskor **CASCADE** a felhasználóra. |
| **FR-DB-02** | `board_saves.user_id` → `users.id`, **CASCADE**; `board_saves.board_save_group_id` → `board_save_groups.id`, **CASCADE**. |
| **FR-DB-03** | `board_saves` táblán **UNIQUE** kényszer: `(board_save_group_id, name)`. |

---

## 4. Nem funkcionális követelmények

| Azonosító | Követelmény |
|-----------|-------------|
| **NFR-01 – Biztonság** | Csak a tulajdonos éri el a saját csoportjait és mentéseit; token nélkül **401** (Sanctum). |
| **NFR-02 – Konzisztencia** | HTTP státuszok és hibaüzenetek illeszkedjenek a többi API modulhoz ahol lehetséges (403 szöveg: „Nincs jogosultság”; 404 mentéseknél: „Nincs találat”). |
| **NFR-03 – Teljesítmény (jövőbeli)** | Nagy számú / méretű mentés esetén érdemes lehet a lista végponton a `payload` kihagyása; **jelen implementáció ezt nem alkalmazza** (lásd FR-SAV-01). |

---

## 5. Payload (információs követelmény)

A szerver a `payload` mezőt **JSON tömbként** tárolja. Az **üzleti** tartalom (pl. `schemaVersion`, `board`, `cells` vs `matrix`) a **`docs/api-board-saves.md`** dokumentumban van részletezve; a klienseknek **ajánlott** ezt a sémát követni a kompatibilitás érdekében.

**Megjegyzés a követelményekhez:** a backend **nem kötelezővé teszi** a dokumentumban felsorolt belső kulcsokat – csak azt, hogy a `payload` Laravel szerint **tömb** legyen.

---

## 6. API végpontok összefoglalója (implementált)

| Módszer | Útvonal | Funkció |
|---------|---------|---------|
| GET | `/api/board-save-groups` | Saját csoportok listája |
| POST | `/api/board-save-groups` | Új csoport |
| GET | `/api/board-save-groups/{board_save_group}` | Egy csoport |
| PUT | `/api/board-save-groups/{board_save_group}` | Csoport frissítése |
| DELETE | `/api/board-save-groups/{board_save_group}` | Csoport törlése |
| GET | `/api/board-save-groups/{board_save_group}/saves` | Csoport mentései (teljes payload-dal) |
| POST | `/api/board-save-groups/{board_save_group}/saves` | Új mentés |
| GET | `/api/board-save-groups/{board_save_group}/saves/{save}` | Egy mentés |
| PUT | `/api/board-save-groups/{board_save_group}/saves/{save}` | Mentés frissítése (név + payload kötelező) |
| DELETE | `/api/board-save-groups/{board_save_group}/saves/{save}` | Mentés törlése |

A Laravel `apiResource` az `api` prefixet és az `Accept: application/json` elvárást a projekt konvenciói szerint kezeli.

---

## 7. Traceability: dokumentum ↔ kód

| Követelmény | Fő forrás a kódban |
|-------------|---------------------|
| Csoportok rendezése indexnél | `BoardSaveGroupController::index` |
| Mentések sorrendje | `BoardSaveGroup::saves()` `orderByDesc('id')` |
| Egyediség és payload validáció | `BoardSaveController::store`, `update` |
| Jogosultság | Mindkét controller `user_id` összehasonlítások |
| Útvonalak | `routes/api.php` (54–56. sor környéke) |
| Séma | `database/migrations/2026_04_10_12000*.php` |

---

## 8. Ismert eltérés az `api-board-saves.md` javaslataitól

| Téma | api-board-saves.md | Jelen implementáció |
|------|---------------------|---------------------|
| PUT mentés | „Legalább az egyik kötelező” (név vagy payload) | **Mindkettő kötelező** |
| GET mentések listája | Javaslat: lista nélkül payload | **Teljes rekord + payload** minden elemnél |
| PUT csoport | Részleges frissítés jellegű szöveg | **`name` mindig kötelező** az update-ben |

Ezek az eltérések **követelmény-szinten** ebben a dokumentumban a **kód** szerint tekintendők hitelesnek; az API leírás frissítése szükség szerint külön feladat.

---

## 9. Verziótörténet

| Verzió | Dátum | Változás |
|--------|--------|----------|
| 1.0 | 2026-04-11 | Első követelményspecifikáció a táblaállapot-mentés modulhoz |
