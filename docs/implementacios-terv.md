# Implementációs terv – Cellauto API (Laravel)

| Mező | Érték |
|------|--------|
| **Verzió** | 1.0 |
| **Dátum** | 2026-04-11 |
| **Cél** | A backend fejlesztés **ütemezhető lépései**, a meglévő követelmény- és API-dokumentumokkal összhangban |
| **Hivatkozások** | `kovetelmenyspecifikacio-tablamentesek.md`, `kovetelmenyspecifikacio-adatbazis.md`, `api-board-saves.md`, `backend.md`, `database-schema.md` |

---

## 1. Cél és keretek

### 1.1 Stratégiai cél

Egy **stabil, jól dokumentált, tesztelt** REST API biztosítása a sejtautomata kliens számára: auth (Sanctum), felhasználói erőforrások (listák, színek, táblaállapot-mentések), egységes hibakezelés és karbantartható validáció.

### 1.2 Megszorítások

- **Stack:** Laravel 12, PHP 8.2+, Sanctum, SQLite (dev) / MySQL (prod tipikus).
- **Auth:** Bearer token; védett végpontok `auth:sanctum`; admin műveletek `admin` middleware.
- **Kompatibilitás:** Meglévő kliensek és DB séma változásai **verziózott migrációkkal**, ahol lehet visszafelé kompatibilis API viselkedés.

---

## 2. Kiinduló állapot (rövid állomány)

| Terület | Állapot | Megjegyzés |
|---------|---------|------------|
| Ping, login, Sanctum | Kész | `AuthController`, felfüggesztés ellenőrzés |
| Users (lista, saját user) | Kész | |
| Users admin (create, suspend, update) | Kész | `admin` middleware |
| Lists / Words CRUD | Kész | `position`, egyediség DB-ben |
| Color lists / Colors CRUD | Kész | `position` ütközés: lásd `api-color-lists-colors.md` |
| Board save groups / saves | Kész | Controllerek, route-ok, migrációk |
| Követelmény-specifikációk | Kész | `kovetelmenyspecifikacio-*.md` |
| Egységes API hibák, Form Requestek | Részleges / hiányos | `backend.md` „ismert teendők” |
| Feature tesztek a board / teljes API-ra | Ellenőrizendő | `composer run test` |

---

## 3. Fázisok és feladatcsomagok

### Fázis A – Dokumentáció és szerződés szinkron (rövid táv, alacsony kockázat)

**Cél:** Egy forrás legyen igaz: a kód, az API leírás és a követelmények ne mondjanak ellent.

| # | Feladat | Kimenet |
|---|---------|---------|
| A1 | `api-board-saves.md` frissítése a **tényleges** viselkedésre (PUT mentés: `name` + `payload` kötelező; GET lista: teljes `payload`; PUT csoport: `name` kötelező) | Pontos integrációs leírás a frontendnek |
| A2 | `backend.md` szinkron: board-save végpontok **implementálva**; hivatkozás a `kovetelmenyspecifikacio-*.md` fájlokra | Kevesebb félreértés onboardingnál |
| A3 | Szükség szerint `database-schema.md` ellenőrzése migráció után (`php artisan migrate` + séma diff) | Prod/dev séma egyezés |

**Becsült erőfeszítés:** 0,5–1 nap (szövegszerkesztés + gyors kódellenőrzés).

---

### Fázis B – Backend minőség: validáció és válaszforma (közép táv)

**Cél:** Éles használatra alkalmas **központosított validáció** és előkészítés az egységes JSON hibákra.

| # | Feladat | Részletek |
|---|---------|-----------|
| B1 | **Form Request** osztályok bevezetése főbb `store` / `update` műveletekre | Csökkenti a controller zajt, egy helyen a szabályok |
| B2 | **Policy** vagy közös jogosultság-trait | `user_id` ellenőrzés ismétlődésének csökkentése (lists, colors, board) |
| B3 | Egységes **hiba payload** konvenció (pl. `message` + `errors` mező Laravel 422-hez, 403/404 szövegek) | Összhang a `backend.md` javaslattal |
| B4 | Opcionális: **API verzió** prefix (`/api/v1/...`) csak akkor, ha breaking változás várható | Döntés termék szinten |

**Becsült erőfeszítés:** 2–5 nap, modulonként iterálva.

---

### Fázis C – Board saves: üzleti szigor és teljesítmény (szükség szerint)

**Cél:** A `kovetelmenyspecifikacio-tablamentesek.md` és az `api-board-saves.md` közötti **szándékolt** viselkedés megvalósítása, ha a termék ezt kéri.

| # | Feladat | Hatás |
|---|---------|--------|
| C1 | `PUT` mentés: **részleges frissítés** – csak `name` vagy csak `payload` (validáció: `sometimes`) | Kényelmesebb kliens, kevesebb adatmozgatás |
| C2 | `GET .../saves` lista: opcionálisan **payload nélkül** (query: `?include=payload` vagy alapból meta only) | Kevesebb JSON nagy mentésnél |
| C3 | **Payload séma validáció** (pl. `schemaVersion`, kötelező meta kulcsok) külön osztály / rule | Adatminőség, kevesebb hibás mentés |
| C4 | Csoport `PUT`: **részleges** `name` / `position` | Összhang az eredeti API-javaslattal |

**Becsült erőfeszítés:** 3–7 nap (tervezés + tesztek + dokumentáció frissítés).

**Függőség:** Frontend egyeztetés (breaking változás lehet).

---

### Fázis D – Színek / listák ergonomia (opcionális)

| # | Feladat | Indok |
|---|---------|--------|
| D1 | **Reorder** endpoint színekre (vagy tranzakciós batch frissítés) | Elkerüli a `position` egyediség ütközést két lépéses cserénél (`api-color-lists-colors.md`) |
| D2 | Hasonló minta szavaknál, ha a UI ugyanígy szenved | Konzisztencia |

**Becsült erőfeszítés:** 1–3 nap / endpoint.

---

### Fázis E – Tesztelés és üzemeltetés

| # | Feladat | Cél |
|---|---------|-----|
| E1 | **Feature tesztek** kritikus útvonalakra: login, egy board CRUD flow, 403/404 | Regresszió védelem |
| E2 | CI-ben `composer run test` zöld ág | Merge gate |
| E3 | Prod checklist: `.env`, `APP_KEY`, DB, `php artisan migrate --force`, opcionálisan queue worker | Üzembe állítás |

---

## 4. Kockázatok és enyhítés

| Kockázat | Enyhítés |
|----------|----------|
| API viselkedés változik (C fázis) | Verziózás vagy feature flag; A fázis dokumentáció előbb |
| Payload validáció túl szigorú | Fokozatos bevezetés: előbb warning log, később 422 |
| Nagy `payload` lista végponton | C2 + lapozás (`per_page`) később |

---

## 5. Döntési pontok (stakeholder)

1. **Kell-e** a board mentésnél szerver oldali sémaellenőrzés (C3), vagy marad a kliens felelőssége?
2. **Breaking** API módosítás megengedett-e a következő release-ben (C1–C2)?
3. **Színek reorder** prioritás a következő sprintben?

---

## 6. Ajánlott sorrend (összefoglaló)

1. **A fázis** – dokumentáció szinkron (azonnal, alacsony költség).
2. **E1 részben** – legalább auth + board smoke tesztek (paralelben B1-gyel kezdhető).
3. **B fázis** – Form Request + hibák (stabil alap).
4. **C / D** – termékprioritás szerint.
5. **E2–E3** – élesítés előtt teljes körben.

---

## 7. Verziótörténet

| Verzió | Dátum | Változás |
|--------|--------|----------|
| 1.0 | 2026-04-11 | Első implementációs terv |
