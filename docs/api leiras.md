# API általános leírás

## Bevezetés

Ez a fejezet a rendszer backend API rétegének általános bemutatását tartalmazza, szakdolgozati célú összefoglaló formában. Az API feladata, hogy egységes és biztonságos kommunikációs felületet biztosítson a kliensalkalmazások (webes felület, admin felület) és az adatbázis között.

## Felhasznált technológiák

A backend modern PHP alapokon készült, az alábbi fő technológiai elemekkel:

- **PHP 8.2**: a szerveroldali üzleti logika megvalósításához.
- **Laravel 12**: keretrendszer az API-réteg, routing, middleware-kezelés és adatbázis-elérés támogatására.
- **Laravel Sanctum**: tokenalapú hitelesítési mechanizmus REST API környezetben.
- **Eloquent ORM**: objektum-relációs leképezés az adatbázis-műveletekhez.
- **Migrációk**: verziózott adatbázisséma-kezelés és reprodukálható telepítés.

## Az API szerepe a rendszerben

Az API központi komponensként működik, és az alábbi feladatokat látja el:

- felhasználói hitelesítés és munkamenet-kezelés,
- jogosultság alapú hozzáférés-ellenőrzés (felhasználó, staff, admin),
- üzleti entitások CRUD műveleteinek kiszolgálása,
- domain-specifikus logika végrehajtása (pl. listakezelés, feladatmentések, értékelések),
- naplózási események rögzítése.

## Funkcionális cél

Az API célja, hogy a kliensoldali komponensek számára stabil, skálázható és jól strukturált interfészt adjon. Ennek előnyei:

- a frontend és backend fejlesztés elkülöníthető,
- a rendszer modulárisan bővíthető új végpontokkal,
- az üzleti szabályok központilag, konzisztensen érvényesíthetők,
- egységes JSON-alapú adatcsere biztosítható.

## Biztonsági és üzemeltetési szempontok

A megoldás a szakdolgozati követelmények szempontjából is releváns biztonsági elemeket tartalmaz:

- tokenalapú API-hozzáférés,
- middleware-alapú endpoint-védelem,
- inaktív vagy felfüggesztett felhasználók tiltása,
- session-időtúllépés kezelése,
- hozzáférési események naplózása (bejelentkezés, kijelentkezés, látogatás).

## Összegzés

A backend API technológiai és architekturális szempontból korszerű, rétegezett felépítésű megoldás. A Laravel ökoszisztémára építve biztosítja a fejleszthetőséget, a megbízható hitelesítést, valamint az üzleti logika központi és ellenőrizhető végrehajtását, ami jól illeszkedik egy szakdolgozatban bemutatandó szoftverrendszer követelményeihez.
