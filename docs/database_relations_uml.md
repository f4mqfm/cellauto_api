# Database Relations UML

## 1. Cél

Ez a dokumentum kizárólag az adatbázis relációinak PlantUML leírását tartalmazza, részletes bontásban:

- teljes adatbázis-ER nézet (fő üzleti és rendszer táblákkal),
- külön fókusz az összekapcsoló táblákra,
- táblánkénti oszlop- és kulcsrészletek.

## 2. Jelölések

- `<<PK>>`: elsődleges kulcs
- `<<FK>>`: idegen kulcs
- `<<UQ>>`: egyedi kulcs
- `nullable`: a mező lehet `NULL`
- Kapcsolatok:
  - `||--o{` = egy-a-többhöz
  - `}|--||` = sok-az-egyhez (irányított értelmezés)

## 3. Teljes relációs modell (PlantUML)

```plantuml
@startuml
hide methods
hide stereotypes
skinparam linetype ortho
skinparam wrapWidth 240
skinparam maxMessageSize 240

' ===== Core auth/system =====
entity "users" as users {
  +id : BIGINT <<PK>>
  --
  username : VARCHAR(255) <<UQ>>
  name : VARCHAR(255)
  email : VARCHAR(255) <<UQ>>
  email_verified_at : TIMESTAMP nullable
  role : VARCHAR(255)
  active : TINYINT(1)
  suspended_at : TIMESTAMP nullable
  password : VARCHAR(255)
  remember_token : VARCHAR(100) nullable
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
}

entity "personal_access_tokens" as pat {
  +id : BIGINT <<PK>>
  --
  tokenable_type : VARCHAR(255)
  tokenable_id : BIGINT
  name : TEXT
  token : VARCHAR(64) <<UQ>>
  abilities : TEXT nullable
  last_used_at : TIMESTAMP nullable
  expires_at : TIMESTAMP nullable
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
}

entity "sessions" as sessions {
  +id : VARCHAR(255) <<PK>>
  --
  user_id : BIGINT nullable
  ip_address : VARCHAR(45) nullable
  user_agent : TEXT nullable
  payload : LONGTEXT
  last_activity : INT
}

entity "password_reset_tokens" as prt {
  +email : VARCHAR(255) <<PK>>
  --
  token : VARCHAR(255)
  created_at : TIMESTAMP nullable
}

' ===== Domain: word lists =====
entity "lists_word" as lists_word {
  +id : BIGINT <<PK>>
  --
  user_id : BIGINT <<FK>>
  name : VARCHAR(255)
  public : TINYINT(1)
  notes : TEXT nullable
  wordlist : MEDIUMTEXT nullable
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
}

entity "words" as words {
  +id : BIGINT <<PK>>
  --
  list_id : BIGINT <<FK>>
  generation : INT UNSIGNED
  word : VARCHAR(255)
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
  --
  UNIQUE(list_id, generation, word)
}

entity "word_gen_messages" as wgm {
  +id : BIGINT <<PK>>
  --
  list_id : BIGINT <<FK>>
  generation : INT UNSIGNED
  correct_answer_message : TEXT nullable
  incorrect_answer_message : TEXT nullable
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
  --
  UNIQUE(list_id, generation)
}

entity "word_relations" as wr {
  +id : BIGINT <<PK>>
  --
  list_id : BIGINT <<FK>>
  from_word_id : BIGINT <<FK>>
  to_word_id : BIGINT <<FK>>
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
  --
  UNIQUE(list_id, from_word_id, to_word_id)
}

' ===== Domain: color lists =====
entity "color_lists" as cl {
  +id : BIGINT <<PK>>
  --
  user_id : BIGINT <<FK>>
  name : VARCHAR(255)
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
}

entity "colors" as colors {
  +id : BIGINT <<PK>>
  --
  list_id : BIGINT <<FK>>
  color : VARCHAR(50)
  position : INT UNSIGNED
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
  --
  UNIQUE(list_id, position)
}

' ===== Domain: board saves =====
entity "board_save_groups" as bsg {
  +id : BIGINT <<PK>>
  --
  user_id : BIGINT <<FK>>
  name : VARCHAR(255)
  position : INT UNSIGNED nullable
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
}

entity "board_saves" as bs {
  +id : BIGINT <<PK>>
  --
  user_id : BIGINT <<FK>>
  board_save_group_id : BIGINT <<FK>>
  name : VARCHAR(255)
  payload : JSON
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
  --
  UNIQUE(board_save_group_id, name)
}

' ===== Domain: task saves/evaluations =====
entity "task_save_groups" as tsg {
  +id : BIGINT <<PK>>
  --
  user_id : BIGINT <<FK>>
  name : VARCHAR(255)
  position : INT UNSIGNED nullable
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
}

entity "task_saves" as ts {
  +id : BIGINT <<PK>>
  --
  user_id : BIGINT <<FK>>
  task_save_group_id : BIGINT <<FK>>
  word_list_id : BIGINT <<FK>> nullable
  name : VARCHAR(255)
  level : ENUM(Easy,Medium,Hard)
  generation_mode : VARCHAR(50)
  board_size : INT UNSIGNED
  generations_count : INT UNSIGNED
  time_limit : INT UNSIGNED
  payload : JSON
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
  --
  UNIQUE(task_save_group_id, name)
}

entity "task_evaluations" as te {
  +id : BIGINT <<PK>>
  --
  task_save_id : BIGINT <<FK>>
  user_id : BIGINT <<FK>>
  date : DATETIME
  note : TEXT nullable
  filled_board : JSON nullable
  total_good_cell : INT UNSIGNED
  good_cell : INT UNSIGNED
  bad_cell : INT UNSIGNED
  unfilled_cell : INT UNSIGNED
  possible_sentence : INT UNSIGNED
  good_sentence : INT UNSIGNED
  bad_sentence : INT UNSIGNED
  duplicate_sentence : INT UNSIGNED
  sentence_result : TEXT nullable
  completed_time : INT UNSIGNED
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
}

' ===== Domain: access =====
entity "access_logs" as al {
  +id : BIGINT <<PK>>
  --
  user_id : BIGINT <<FK>> nullable
  event_type : VARCHAR(20)
  entry_point : VARCHAR(20)
  ip_address : VARCHAR(45)
  user_agent : VARCHAR(1024) nullable
  occurred_at : TIMESTAMP
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
}

' ===== Infra tables =====
entity "cache" as cache {
  +key : VARCHAR(255) <<PK>>
  --
  value : MEDIUMTEXT
  expiration : INT
}

entity "cache_locks" as cache_locks {
  +key : VARCHAR(255) <<PK>>
  --
  owner : VARCHAR(255)
  expiration : INT
}

entity "jobs" as jobs {
  +id : BIGINT <<PK>>
  --
  queue : VARCHAR(255)
  payload : LONGTEXT
  attempts : TINYINT UNSIGNED
  reserved_at : INT UNSIGNED nullable
  available_at : INT UNSIGNED
  created_at : INT UNSIGNED
}

entity "job_batches" as job_batches {
  +id : VARCHAR(255) <<PK>>
  --
  name : VARCHAR(255)
  total_jobs : INT
  pending_jobs : INT
  failed_jobs : INT
  failed_job_ids : LONGTEXT
  options : MEDIUMTEXT nullable
  cancelled_at : INT nullable
  created_at : INT
  finished_at : INT nullable
}

entity "failed_jobs" as failed_jobs {
  +id : BIGINT <<PK>>
  --
  uuid : VARCHAR(255) <<UQ>>
  connection : TEXT
  queue : TEXT
  payload : LONGTEXT
  exception : LONGTEXT
  failed_at : TIMESTAMP
}

' ===== Relationships =====
users ||--o{ lists_word
users ||--o{ cl
users ||--o{ bsg
users ||--o{ tsg
users ||--o{ ts
users ||--o{ te
users ||--o{ al

lists_word ||--o{ words
lists_word ||--o{ wgm
lists_word ||--o{ wr
lists_word ||--o{ ts

words ||--o{ wr : from_word_id
words ||--o{ wr : to_word_id

cl ||--o{ colors
bsg ||--o{ bs
tsg ||--o{ ts
ts ||--o{ te

@enduml
```

## 4. Összekapcsoló táblák - külön bontásban (PlantUML)

### 4.1. word_relations (önkapcsoló + listakapcsoló)

```plantuml
@startuml
hide methods
hide stereotypes
skinparam linetype ortho

entity "lists_word" as lists_word {
  *id : BIGINT <<PK>>
}

entity "words" as words {
  *id : BIGINT <<PK>>
  --
  list_id : BIGINT <<FK>>
  generation : INT UNSIGNED
  word : VARCHAR(255)
  --
  UNIQUE(list_id, generation, word)
}

entity "word_relations" as wr {
  *id : BIGINT <<PK>>
  --
  list_id : BIGINT <<FK>>
  from_word_id : BIGINT <<FK>>
  to_word_id : BIGINT <<FK>>
  created_at : TIMESTAMP nullable
  updated_at : TIMESTAMP nullable
  --
  UNIQUE(list_id, from_word_id, to_word_id)
}

lists_word ||--o{ words
lists_word ||--o{ wr
words ||--o{ wr : from_word_id
words ||--o{ wr : to_word_id

@enduml
```

Részletek:
- `word_relations` egyszerre kapcsolódik a listához és két külön szó rekordhoz.
- A `UNIQUE(list_id, from_word_id, to_word_id)` megakadályozza a duplikált él létrehozását.
- Alkalmazási szabály szerint a kapcsolat tipikusan `GENn -> GENn+1` között értelmezett.

### 4.2. task_evaluations (felhasználó + feladat összerendelés)

```plantuml
@startuml
hide methods
hide stereotypes
skinparam linetype ortho

entity "users" as users {
  *id : BIGINT <<PK>>
}

entity "task_saves" as ts {
  *id : BIGINT <<PK>>
}

entity "task_evaluations" as te {
  *id : BIGINT <<PK>>
  --
  task_save_id : BIGINT <<FK>>
  user_id : BIGINT <<FK>>
  date : DATETIME
  note : TEXT nullable
  filled_board : JSON nullable
  total_good_cell : INT UNSIGNED
  good_cell : INT UNSIGNED
  bad_cell : INT UNSIGNED
  unfilled_cell : INT UNSIGNED
  possible_sentence : INT UNSIGNED
  good_sentence : INT UNSIGNED
  bad_sentence : INT UNSIGNED
  duplicate_sentence : INT UNSIGNED
  sentence_result : TEXT nullable
  completed_time : INT UNSIGNED
}

users ||--o{ te
ts ||--o{ te

@enduml
```

Részletek:
- Egy felhasználó több értékelést is adhat több külön feladatra.
- Egy feladathoz több értékelés tartozhat (több felhasználó vagy több próbálkozás).
- A kapcsolat nem klasszikus N-N pivot, de funkcionálisan összerendelő tábla (`users` + `task_saves`).

### 4.3. word_gen_messages (lista + generáció összerendelés)

```plantuml
@startuml
hide methods
hide stereotypes
skinparam linetype ortho

entity "lists_word" as lists_word {
  *id : BIGINT <<PK>>
}

entity "word_gen_messages" as wgm {
  *id : BIGINT <<PK>>
  --
  list_id : BIGINT <<FK>>
  generation : INT UNSIGNED
  correct_answer_message : TEXT nullable
  incorrect_answer_message : TEXT nullable
  --
  UNIQUE(list_id, generation)
}

lists_word ||--o{ wgm

@enduml
```

Részletek:
- Generációnként legfeljebb egy üzenetpár tárolható listán belül.
- A `UNIQUE(list_id, generation)` biztosítja az egyértelműséget.

## 5. Kulcs- és integritási megjegyzések

- `ON DELETE CASCADE` dominánsan a tulajdonosi relációknál jelenik meg (pl. lista -> szavak, csoport -> mentések).
- `ON DELETE SET NULL` ott van, ahol historikus rekord megtartható referencia nélkül is (pl. `access_logs.user_id`, `task_saves.word_list_id`).
- A JSON típusú mezők (`payload`, `filled_board`) strukturált kliensadatok tárolására szolgálnak.
- Több üzleti szabály nem csak adatbázisban, hanem alkalmazáslogikában érvényesül (például szórelációk generációs szomszédsága).

## 6. User-központú fa szerkezet (külön)

Az alábbi rész kifejezetten a `users` táblából indul ki, és fa jelleggel mutatja a kapcsolódó táblákat.

### 6.1. Teljes user-fa (PlantUML mindmap)

```plantuml
@startmindmap
* users
** lists_word
*** words
**** word_relations (from_word_id, to_word_id)
*** word_gen_messages
*** task_saves (word_list_id nullable)
**** task_evaluations
** color_lists
*** colors
** board_save_groups
*** board_saves
** task_save_groups
*** task_saves
**** task_evaluations
** task_saves (direct user_id)
*** task_evaluations
** task_evaluations (direct user_id)
** access_logs (user_id nullable)
@endmindmap
```

### 6.2. User ágak külön-külön (PlantUML)

#### 6.2.1. User -> Szólista ág

```plantuml
@startuml
left to right direction
hide methods
hide stereotypes

entity "users" as u
entity "lists_word" as lw
entity "words" as w
entity "word_relations" as wr
entity "word_gen_messages" as wgm

u ||--o{ lw : user_id
lw ||--o{ w : list_id
lw ||--o{ wgm : list_id
lw ||--o{ wr : list_id
w ||--o{ wr : from_word_id
w ||--o{ wr : to_word_id
@enduml
```

#### 6.2.2. User -> Színek ág

```plantuml
@startuml
left to right direction
hide methods
hide stereotypes

entity "users" as u
entity "color_lists" as cl
entity "colors" as c

u ||--o{ cl : user_id
cl ||--o{ c : list_id
@enduml
```

#### 6.2.3. User -> Tábla mentések ág

```plantuml
@startuml
left to right direction
hide methods
hide stereotypes

entity "users" as u
entity "board_save_groups" as bsg
entity "board_saves" as bs

u ||--o{ bsg : user_id
bsg ||--o{ bs : board_save_group_id
u ||--o{ bs : user_id
@enduml
```

#### 6.2.4. User -> Feladat mentések és értékelések ág

```plantuml
@startuml
left to right direction
hide methods
hide stereotypes

entity "users" as u
entity "task_save_groups" as tsg
entity "task_saves" as ts
entity "task_evaluations" as te
entity "lists_word" as lw

u ||--o{ tsg : user_id
tsg ||--o{ ts : task_save_group_id
u ||--o{ ts : user_id
lw ||--o{ ts : word_list_id (nullable)
ts ||--o{ te : task_save_id
u ||--o{ te : user_id
@enduml
```

#### 6.2.5. User -> Hozzáférési napló ág

```plantuml
@startuml
left to right direction
hide methods
hide stereotypes

entity "users" as u
entity "access_logs" as al

u ||--o{ al : user_id (nullable)
@enduml
```

### 6.3. Több apró részlet (user nézőpont)

- A `task_saves` kétszeresen kötődik a userhez: közvetlenül (`task_saves.user_id`) és közvetetten (`task_save_groups` ágon).
- A `task_evaluations` is kétszeresen kapcsolódik: a kitöltő userhez (`user_id`) és az értékelt feladathoz (`task_save_id`).
- A `word_relations` valójában irányított gráfél: egy listán belül `from_word_id -> to_word_id`.
- Az `access_logs.user_id` nullable, így naplóbejegyzés felhasználó törlése után is megmaradhat.
- A `task_saves.word_list_id` nullable kapcsolat, ezért a feladatmentés akkor is létezhet, ha a hivatkozott szólista megszűnik.
