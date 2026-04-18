# Adatbázis UML – entitások és kapcsolatok

Ez a dokumentum a **cellauto** adatbázis **logikai modelljét** UML-szerű **osztálydiagram** formában foglalja össze (Mermaid). A részletes oszlopleírások és SQL a [`database-schema.md`](database-schema.md)-ben találhatók.

**Verzió:** 1.2 · **Dátum:** 2026-04-16

---

## 1. Üzleti / domain modell

A felhasználó (`User`) központi entitás: szólisták, színpaletták és táblaállapot-mentések csoportjai mind hozzá kötődnek. A `board_saves` tábla mind a felhasználóra, mind a csoportra mutat (szűrés és integritás miatt); törléskor a migrációk **CASCADE** szabályt használnak a kapcsolódó sorokra.

```mermaid
classDiagram
    direction TB

    class User {
        +id: bigint
        +username: string
        +name: string
        +email: string
        +role: string
        +active: bool
        +suspended_at: datetime
        +password: string
    }

    class WordList {
        +id: bigint
        +user_id: FK
        +name: string
        +public: bool
        +notes: string
        +wordlist: string
    }

    class Word {
        +id: bigint
        +list_id: FK
        +generation: int
        +word: string
    }

    class WordRelation {
        +id: bigint
        +list_id: FK
        +from_word_id: FK
        +to_word_id: FK
    }

    class WordGenMessage {
        +id: bigint
        +list_id: FK
        +generation: int
        +correct_answer_message: string
        +incorrect_answer_message: string
    }

    class ColorList {
        +id: bigint
        +user_id: FK
        +name: string
    }

    class Color {
        +id: bigint
        +list_id: FK
        +color: string
        +position: int
    }

    class BoardSaveGroup {
        +id: bigint
        +user_id: FK
        +name: string
        +position: int
    }

    class BoardSave {
        +id: bigint
        +user_id: FK
        +board_save_group_id: FK
        +name: string
        +payload: json
    }

    User "1" --> "*" WordList : lists
    WordList "1" --> "*" Word : words
    WordList "1" --> "*" WordGenMessage : wordGenMessages
    WordList "1" --> "*" WordRelation : wordRelations
    Word "1" --> "*" WordRelation : fromRelations
    Word "1" --> "*" WordRelation : toRelations

    User "1" --> "*" ColorList : colorLists
    ColorList "1" --> "*" Color : colors

    User "1" --> "*" BoardSaveGroup : boardSaveGroups
    BoardSaveGroup "1" --> "*" BoardSave : boardSaves
    User "1" --> "*" BoardSave : boardSaves
```

**Megjegyzések:**

- `Word`: generáció-alapú; egyedi a `(list_id, generation, word)` (lásd séma).
- `WordGenMessage`: generációnként opcionális helyes/helytelen válasz szöveg a listához (`UNIQUE(list_id, generation)`).
- `WordRelation`: csak szomszédos generációk között értelmezett (GENn -> GENn+1), listán belül.
- `Color`: egy színes listán belül egyedi a `(list_id, position)`.
- `BoardSave`: egy csoporton belül egyedi a `name` (`board_save_group_id` + `name`).

---

## 2. ER nézet (összefoglaló)

Az alábbi **entity–relationship** diagram ugyanezt a modellt mutatja klasszikus ER jelöléssel (összhangban a [`database-schema.md`](database-schema.md) „Áttekintő ER” blokkjával).

```mermaid
erDiagram
    users ||--o{ lists_word : "user_id"
    lists_word ||--o{ words : "list_id"
    lists_word ||--o{ word_gen_messages : "list_id"
    lists_word ||--o{ word_relations : "word_relations.list_id"
    words ||--o{ word_relations : "word_relations.from_word_id"
    words ||--o{ word_relations : "word_relations.to_word_id"
    users ||--o{ color_lists : "user_id"
    color_lists ||--o{ colors : "list_id"
    users ||--o{ board_save_groups : "user_id"
    board_save_groups ||--o{ board_saves : "board_save_group_id"
    users ||--o{ board_saves : "user_id"
```

---


## 3. Auth (Sanctum) – asszociáció

A `personal_access_tokens` tábla **polimorf** kapcsolattal hivatkozik a token tulajdonosára (`tokenable_type`, `tokenable_id`). Tipikus esetben a típus a `User` modell, azaz egy felhasználónak több API tokenje lehet.

```mermaid
classDiagram
    direction LR
    class User {
        +id: bigint
    }
    class PersonalAccessToken {
        <<Sanctum>>
        +tokenable_type: string
        +tokenable_id: bigint
        +name: string
        +token: string
        +abilities: string
        +expires_at: datetime
    }
    User "1" --> "*" PersonalAccessToken : morphMany
```

---

## 4. Laravel infrastruktúra (nem üzleti modell)

Ezek a táblák framework funkciókhoz kellenek (session, cache, queue, migrációk, jelszó reset). Önálló üzleti entitásokként általában nem modellezzük őket; a [`database-schema.md`](database-schema.md) táblázatában vannak felsorolva.

| Tábla | Szerep |
|-------|--------|
| `migrations` | futtatott migrációk |
| `sessions` | DB session (`SESSION_DRIVER=database`) |
| `cache`, `cache_locks` | cache backend |
| `jobs`, `job_batches`, `failed_jobs` | queue |
| `password_reset_tokens` | jelszó visszaállítás |

---

## 5. Gráf / grafikon formátumok (exportálható kép)

Az alábbi formátumokból **valódi grafikon** (PNG, SVG, PDF) készíthető külső eszközökkel – ez nem a Mermaid beépített nézete, hanem iparági szabványos gráf-leírás.

### 5.1 Graphviz (DOT)

A fájlt mentheted pl. `database-domain.dot` néven, majd:

`dot -Tpng database-domain.dot -o database-domain.png` vagy `-Tsvg` SVG-hez.

```dot
digraph cellauto_domain {
  graph [rankdir=TB, fontname="Helvetica"];
  node [shape=box, style=rounded, fontname="Helvetica"];
  edge [fontname="Helvetica", fontsize=10];

  users;
  lists;
  words;
  color_lists;
  colors;
  board_save_groups;
  board_saves;

  users -> lists [label="1:N  user_id", arrowhead=vee];
  lists -> words [label="1:N  list_id", arrowhead=vee];
  users -> color_lists [label="1:N  user_id", arrowhead=vee];
  color_lists -> colors [label="1:N  list_id", arrowhead=vee];
  users -> board_save_groups [label="1:N  user_id", arrowhead=vee];
  board_save_groups -> board_saves [label="1:N  board_save_group_id", arrowhead=vee];
  users -> board_saves [label="1:N  user_id", arrowhead=vee];
}
```

### 5.2 PlantUML (osztálydiagram → PNG/SVG)

PlantUML [online](https://www.plantuml.com/plantuml/) vagy CLI (`plantuml` jar / extension) segítségével renderelhető.

```plantuml
@startuml cellauto_domain
skinparam classAttributeIconSize 0
skinparam linetype ortho
hide circle

class User {
  +id: bigint
  +username: string
  +name: string
  +email: string
  +role: string
  +active: bool
  +suspended_at: datetime
  +password: string
}

class WordList {
  +id: bigint
  +user_id: FK
  +name: string
}

class Word {
  +id: bigint
  +list_id: FK
  +generation: int
  +word: string
}

class ColorList {
  +id: bigint
  +user_id: FK
  +name: string
}

class Color {
  +id: bigint
  +list_id: FK
  +color: string
  +position: int
}

class BoardSaveGroup {
  +id: bigint
  +user_id: FK
  +name: string
  +position: int
}

class BoardSave {
  +id: bigint
  +user_id: FK
  +board_save_group_id: FK
  +name: string
  +payload: json
}

User "1" --> "*" WordList : lists
WordList "1" --> "*" Word : words
User "1" --> "*" ColorList : colorLists
ColorList "1" --> "*" Color : colors
User "1" --> "*" BoardSaveGroup : boardSaveGroups
BoardSaveGroup "1" --> "*" BoardSave : boardSaves
User "1" --> "*" BoardSave : boardSaves

@enduml
```

### 5.3 PlantUML – Sanctum token (külön gráf)

```plantuml
@startuml cellauto_sanctum
skinparam classAttributeIconSize 0

class User {
  +id: bigint
}

class PersonalAccessToken <<Sanctum>> {
  +tokenable_type: string
  +tokenable_id: bigint
  +name: string
  +token: string
  +abilities: string
  +expires_at: datetime
}

User "1" --> "*" PersonalAccessToken : morphMany

@enduml
```
