# Projektarchitektúra áttekintése

## 1. Bevezetés

A projekt egy többrétegű, kliens-szerver architektúrára épülő alkalmazás, amelynek backend komponense REST alapú API-t biztosít. Az architektúra célja a moduláris felépítés, a skálázhatóság támogatása és a jogosultságvezérelt, biztonságos működés megvalósítása.

## 2. Magas szintű architekturális kép

A rendszer fő komponensei:

- **Frontend kliensek**: felhasználói (www) és admin felület.
- **Backend API**: üzleti logika, adatkezelés, hitelesítés, jogosultságkezelés.
- **Adatbázisréteg**: relációs tárolás, migrációval kezelt sémák.

A komponensek közötti kommunikáció HTTP protokollon, JSON adatformátumban történik.

## 3. Backend belső rétegződése

A backend Laravel alapú rétegezett mintát követ:

- **Routing réteg** (`routes/api.php`): végpontok és middleware-csoportok definiálása.
- **Middleware réteg**: keresztmetszeti szabályok érvényesítése (pl. hitelesítés, aktív session, admin/staff szerepkör).
- **Controller réteg**: kérésfeldolgozás, validáció, üzleti műveletek koordinálása.
- **Model réteg (Eloquent ORM)**: táblák és relációk leképezése, adatbázis-hozzáférés.
- **Migrációs réteg** (`database/migrations`): adatbázisséma verziózott kezelése.

Ez a felosztás biztosítja, hogy a rendszer felelősségi körei jól elkülönüljenek, ami javítja a karbantarthatóságot.

## 4. Funkcionális modulok

A projekt domainje több, egymáshoz kapcsolódó modulból áll:

- **Hitelesítés és felhasználókezelés**: login/logout, user adatok, adminisztratív műveletek.
- **Szólista-kezelés**: listák, szavak, generációs struktúra.
- **Kapcsolati logika**: szókapcsolatok és generációs üzenetek.
- **Színlista-kezelés**: színlisták és elemeik.
- **Mentési alrendszerek**:
  - táblaállapot mentések (`board_save_groups`, `board_saves`),
  - feladatmentések és feladatértékelések (`task_save_groups`, `task_saves`, `task_evaluations`).
- **Naplózási modul**: hozzáférési események tárolása és visszakereshetősége.

A moduláris felépítés lehetővé teszi, hogy új funkciók minimális mellékhatással kerüljenek be a rendszerbe.

## 5. Adatarchitektúra és relációk

A perzisztencia relációs adatmodellre épül. A központi entitás a `users` tábla, amelyhez több 1-N kapcsolat kapcsolódik (pl. listák, mentéscsoportok, értékelések, naplók). A domain-specifikus táblák egymásra épülő relációkat alkotnak (pl. lista -> szavak -> szókapcsolatok, illetve feladatcsoport -> feladat -> értékelés).

Az adatbázis evolúciója migrációkon keresztül történik, így a sémamódosítások verziókövetetten, reprodukálható módon kezelhetők.

## 6. Biztonsági architektúra

A biztonsági modell többrétegű:

- **Sanctum tokenalapú hitelesítés** a védett végpontokhoz.
- **Aktív session ellenőrzés** (`active-session` middleware), amely vizsgálja a token állapotát, az inaktivitási időt és a felhasználó aktív státuszát.
- **Szerepkör alapú hozzáférés** (`staff`, `admin` middleware).
- **Erőforrás-szintű jogosultság-ellenőrzés** a controllerekben (saját/nem saját adatok kezelése).

Ennek eredményeként az architektúra egyszerre támogatja a funkcionalitást és a biztonságos üzemet.

## 7. Kommunikációs és integrációs elvek

A komponensek közti integráció egységes API-szerződésen alapul:

- JSON kérés-válasz modell,
- konzisztens HTTP metódushasználat,
- beágyazott erőforrás-útvonalak a relációk kifejezésére,
- strukturált hibaválaszok.

Ez a megközelítés csökkenti a frontend-backend csatoltságát, és támogatja a párhuzamos fejlesztést.

## 8. Üzemeltetési és bővíthetőségi szempontok

Az architektúra támogatja a hosszú távú fejleszthetőséget:

- jól elkülönített felelősségi rétegek,
- migrációalapú adatbázis-karbantartás,
- szabályozott middleware-lánc,
- könnyen bővíthető endpointstruktúra.

Az üzleti logika központosítása miatt a rendszer viselkedése következetes marad akkor is, ha több kliensfelület használja ugyanazt az API-t.

## 9. Összegzés

A projekt architektúrája modern, rétegezett backend szemléletet valósít meg, amely stabil alapot ad egy többmodulos oktatási/feladatkezelési rendszer működéséhez. A technológiai választások (Laravel, Sanctum, Eloquent, migrációk) együtt olyan keretrendszert biztosítanak, amely megfelel a szakdolgozati szempontból releváns követelményeknek: átláthatóság, biztonság, karbantarthatóság és bővíthetőség.
