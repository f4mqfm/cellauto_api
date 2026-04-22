# A backend működése

## 1. Bevezetés

Jelen fejezet a rendszer backend oldalának működését mutatja be szakdolgozati formában. A backend feladata a kliensalkalmazások (web és admin felület) kiszolgálása, az üzleti logika végrehajtása, a hitelesítés és jogosultságkezelés biztosítása, valamint az adatok perzisztens tárolása.

A rendszer REST szemléletű, JSON alapú API-t biztosít, közös `/api` útvonal-előtaggal.

## 2. Felhasznált technológiák

### 2.1. Programozási környezet

- **Nyelv:** PHP 8.2
- **Framework:** Laravel 12
- **API hitelesítés:** Laravel Sanctum
- **Adatbázis-kezelés:** relációs adatbázis (a projekt dokumentációja MySQL DDL formát használ)
- **Fejlesztői eszközök:** Laravel Pint, PHPUnit, Tinker

### 2.2. Keretrendszer-szintű működés

A backend a Laravel standard rétegzett mintáját követi:

- **Routing réteg:** útvonalak és middleware-csoportok (`routes/api.php`)
- **Controller réteg:** kérésfeldolgozás és válaszépítés
- **Model réteg (Eloquent ORM):** adatbázis-műveletek és relációkezelés
- **Middleware réteg:** keresztmetszeti szabályok (hitelesítés, jogosultság, session-ellenőrzés)

## 3. Architektúra és rétegek

## 3.1. API-réteg

Az API végpontjai funkcionális modulokra bontva érhetők el, például:

- felhasználó- és hozzáféréskezelés,
- szólisták és szavak,
- szókapcsolatok és generációs üzenetek,
- színlisták,
- táblaállapot-mentések,
- feladatmentések és feladatértékelések.

A végpontok egységesen JSON választ adnak, hibás kérés esetén megfelelő HTTP státuszkóddal (pl. `401`, `403`, `404`, `422`).

## 3.2. Üzleti logika

Az üzleti szabályok döntően a controllerekben és részben middleware-ekben jelennek meg. Tipikus példák:

- felhasználó csak saját erőforrást módosíthat/törölhet,
- publikus listák olvashatók más felhasználók számára,
- szókapcsolat csak érvényes listaelemek között rögzíthető,
- feladatértékelés a megfelelő feladathoz és felhasználóhoz kapcsoltan kezelhető.

## 3.3. Adatelérési réteg

Az alkalmazás Eloquent modelleket használ, amelyek:

- definiálják a tömegesen írható mezőket (`fillable`),
- típuskonverziót adnak (`casts`, pl. `array`, `integer`, `datetime`),
- relációkat írnak le (`belongsTo`, `hasMany`).

Ez a megközelítés biztosítja az egységes adatelérést, valamint a relációk egyszerű bejárását.

## 4. Hitelesítés és jogosultságkezelés

## 4.1. Bejelentkezés és tokenkezelés

A rendszer tokenalapú hitelesítést alkalmaz Laravel Sanctum segítségével.

Bejelentkezés folyamata:

1. A kliens a `/api/login` végponton elküldi a felhasználói azonosítót (email vagy username), jelszót és belépési pontot (`www` vagy `admin`).
2. A backend ellenőrzi a jelszót hash alapon.
3. Sikeres hitelesítés esetén a rendszer hozzáférési tokent generál.
4. A token a további védett API-hívásoknál azonosítja a munkamenetet.

Kijelentkezéskor (`/api/logout`) az aktuális token törlésre kerül.

## 4.2. Aktív session ellenőrzés

A védett végpontokon az `auth:sanctum` mellett egy egyedi middleware (`active-session`) is fut:

- ellenőrzi, hogy van-e ténylegesen hitelesített felhasználó és token,
- tiltja az inaktív vagy felfüggesztett felhasználót,
- inaktivitási időkorlátot (idle timeout) kezel; lejáratkor a tokent érvényteleníti.

Ennek eredményeként a hitelesítés nem csak token meglétére, hanem az aktuális felhasználói állapotra is épül.

## 4.3. Jogosultsági szintek

A backend több jogosultsági szintet kezel middleware-aliasokkal:

- **felhasználói szint:** `auth:sanctum` + `active-session`
- **staff szint:** felhasználói szint + `staff`
- **admin szint:** felhasználói szint + `admin`

Ez role-based hozzáférésvezérlést valósít meg, elkülönítve az általános, oktatói/staff és adminisztrátori műveleteket.

## 5. Middleware és kérésfeldolgozás

## 5.1. Proxykezelés

A rendszer megbízható proxy beállítást használ (`trustProxies`), ezért a kliens IP-cím meghatározása reverse proxy környezetben is konzisztens.

## 5.2. Kérések validációja

A controllerek a Laravel validációs mechanizmusát használják. A validáció:

- ellenőrzi a kötelező mezőket,
- típus- és tartománykorlátokat alkalmaz,
- egyedi/összetett üzleti feltételeket is érvényesít.

Hibás bemenet esetén a backend standard, géppel feldolgozható hibaformát ad vissza.

## 6. Adatbázis és perzisztencia

## 6.1. Séma-kezelés

A táblaszerkezetet Laravel migrációk írják le. A migrációs megközelítés előnyei:

- verziózott adatbázisstruktúra,
- reprodukálható telepítés és frissítés,
- kontrollált sémaváltoztatás.

## 6.2. Kapcsolati modell

A rendszerben több, egymásra épülő domain található:

- felhasználó és jogosultság,
- szólista és szóháló relációk,
- mentések és értékelések,
- naplózás.

A főbb kapcsolatok 1-N jellegűek, kiegészítve összerendelő jellegű táblákkal (például szókapcsolatok vagy feladatértékelések).

## 7. Naplózás és üzemeltetési támogatás

## 7.1. Hozzáférési események naplózása

Az alkalmazás külön `access_logs` táblában rögzíti a fontos eseményeket:

- látogatás (`visit`),
- bejelentkezés (`login`),
- kijelentkezés (`logout`).

Az eseményekhez eltárolásra kerül többek között az időbélyeg, a belépési felület, az IP-cím és a user-agent.

## 7.2. Online állapot közelítő meghatározása

Admin oldalon a rendszer a felhasználói tokenek és a naplózott aktivitás alapján képez online státusz információt, amely támogatja az operatív felügyeletet.

## 8. Biztonsági megfontolások

A backend működésében az alábbi biztonsági szempontok hangsúlyosak:

- jelszavak hash-elt tárolása,
- tokenalapú API-hitelesítés,
- middleware-alapú jogosultsági szeparáció,
- inaktív/felfüggesztett fiókok automatikus blokkolása,
- inaktív sessionök lejáratkezelése.

## 9. Összegzés

A backend modern Laravel alapokra épülő, rétegezett architektúrát valósít meg. A megoldás fő erőssége az egységes API-szerkezet, a többszintű hitelesítési és jogosultsági modell, valamint a migráció-alapú adatbázis-karbantartás. Ez a felépítés stabil alapot ad a rendszer továbbfejlesztéséhez és a későbbi funkcionális bővítésekhez.
