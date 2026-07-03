# visitador-api — Backend Reference

> Laravel 13 · PHP 8.4 · Sanctum · SQLite/MySQL/PostgreSQL · PHPUnit

---

## Table of Contents

1. [Project Structure](#1-project-structure)
2. [Models](#2-models)
3. [Migrations](#3-migrations)
4. [Controllers](#4-controllers)
5. [Routes](#5-routes)
6. [Form Requests & Validation](#6-form-requests--validation)
7. [Resources](#7-resources)
8. [Database Seeding](#8-database-seeding)
9. [Tests](#9-tests)
10. [Configuration](#10-configuration)
11. [Dependencies](#11-dependencies)

---

## 1. Project Structure

```
visitador-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # 9 controllers
│   │   ├── Requests/             # Form request validation classes
│   │   │   └── Concerns/         # Shared traits (UppercasesInput)
│   │   └── Resources/            # JSON API resource transformers
│   ├── Models/                   # 11 Eloquent models
│   └── Providers/                # Service providers
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   └── web.php
├── config/
├── tests/
│   └── Feature/
└── composer.json
```

---

## 2. Models

### Client
**File:** `app/Models/Client.php`

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | PK |
| `razon_social` | string | |
| `nit` | string | unique |
| `email` | string | |
| `phone_number` | string | |
| `created_at` / `updated_at` | timestamp | |

**Relationships:**
- `hasMany(Farm::class)`

---

### Farm
**File:** `app/Models/Farm.php`

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | PK |
| `client_id` | bigint | FK → clients (cascade delete) |
| `nombre` | string | |
| `transformator_capacity_kva` | unsigned int | nullable |
| `access_ways` | string | nullable |
| `observations` | text | nullable |
| `farm_voltage` | enum | `110V`, `220V` |
| `farm_electric_current` | enum | `monophase`, `biphase`, `triphase` |
| `have_own_transformator` | boolean | default false |
| `is_transformator_feeds_other_installations` | boolean | default false |
| `transformator_are_feeding_installations` | string | nullable |
| `distance_to_neighbor_boundary_m` | decimal(10,2) | nullable |
| `neighboring_properties_notes` | string | nullable |
| `have_easy_access_for_trailer` | boolean | default false |
| `staff_availability` | boolean | default false |
| `has_storage_warehouse` | boolean | default false |
| `how_many_warehouses` | unsigned int | default 0 |
| `total_galpones` | unsigned int | nullable — auto-sync via model events |

**Relationships:**
- `belongsTo(Client::class)`
- `hasOne(FarmGeorreference::class)`
- `hasMany(FarmContact::class)`
- `hasMany(Galpon::class)` — ordered by `sort_order`, `name`

**Custom methods:**
- `refreshTotalGalpones()` — recounts and saves `total_galpones` quietly

**Model events:**
- `Galpon::created` / `Galpon::deleted` → trigger `Farm::refreshTotalGalpones()`

---

### FarmContact
**File:** `app/Models/FarmContact.php`

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | PK |
| `farm_id` | bigint | FK → farms (cascade delete) |
| `type` | enum | `administrador`, `veterinario`, `encargado`, `otro` |
| `name` | string | |
| `email` | string | nullable |
| `phone` | string | nullable |

**Relationships:**
- `belongsTo(Farm::class)`

---

### FarmGeorreference
**File:** `app/Models/FarmGeorreference.php`

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | PK |
| `farm_id` | bigint | FK → farms (cascade delete) |
| `address` | string | nullable |
| `town` | string | nullable |
| `department` | string | nullable |
| `map_url_reference` | string | nullable |

**Relationships:**
- `belongsTo(Farm::class)`

---

### Galpon
**File:** `app/Models/Galpon.php`  
**Table:** `galpones`

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | PK |
| `farm_id` | bigint | FK → farms (cascade delete) |
| `name` | string | |
| `code` | string | nullable |
| `status` | string | default `active` |
| `description` | text | nullable |
| `dimensions_json` | json | nullable — cast to array |
| `technical_attributes_json` | json | nullable — cast to array |
| `observations` | text | nullable |
| `sort_order` | unsigned int | default 0 |

**`dimensions_json` shape:**
```json
{
  "largo_m": 0.0,
  "ancho_m": 0.0,
  "altura_canal_m": 0.0,
  "altura_cumbrera_m": 0.0
}
```

**Relationships:**
- `belongsTo(Farm::class)`
- `hasMany(GalponSystem::class)` — ordered by `id`

**Model events:**
- `created` / `deleted` → `$this->farm->refreshTotalGalpones()`

---

### GalponSystem
**File:** `app/Models/GalponSystem.php`  
**Table:** `galpon_systems`

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | PK |
| `galpon_id` | bigint | FK → galpones (cascade delete) |
| `system_id` | bigint | FK → systems_catalog (cascade delete) |
| `quantity` | unsigned int | nullable |
| `notes` | text | nullable |
| `technical_attributes_json` | json | nullable — cast to array |

**Unique constraint:** `[galpon_id, system_id]`

**Relationships:**
- `belongsTo(Galpon::class)`
- `belongsTo(SystemsCatalog::class, 'system_id')`

---

### SystemsCatalog
**File:** `app/Models/SystemsCatalog.php`  
**Table:** `systems_catalog`

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | PK |
| `code` | string | unique |
| `name` | string | |
| `category` | string | nullable |
| `is_active` | boolean | default true — cast to boolean |

**Seeded systems (20):**

| Code | Name |
|---|---|
| `comedero_automatico` | Comedero Automático |
| `bebedero_niple` | Bebedero Niple |
| `falso_techo` | Falso Techo |
| `cortina_lateral` | Cortina Lateral |
| `calefaccion` | Calefacción |
| `silos` | Silos |
| `alimentacion` | Alimentación |
| `ventiladores` | Ventiladores |
| `nebulizadores` | Nebulizadores |
| `iluminacion` | Iluminación |
| `extractores` | Extractores |
| `panel_humedo` | Panel Húmedo |
| `inlet` | Inlet |
| `tunel_door` | Tunel Door |
| `red_electrica` | Red Eléctrica |
| `tablero_control_potencia` | Tablero de Control y Potencia |
| `controlador` | Controlador |
| `sistema_pesaje` | Sistema de Pesaje |
| `sistema_comunicacion` | Sistema de Comunicación |
| `aislamiento` | Aislamiento |

---

### Project
**File:** `app/Models/Project.php`

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | PK |
| `client_id` | bigint | FK → clients (cascade delete) |
| `farm_id` | bigint | FK → farms (cascade delete) |
| `name` | string | |
| `code` | string | nullable |
| `tipo` | string | nullable — see constants |
| `linea` | string | nullable — see constants |
| `status` | enum | `draft`, `active`, `paused`, `completed`, `cancelled` — default `draft` |
| `start_date` | date | nullable — cast to date |
| `end_date` | date | nullable — cast to date |
| `description` | text | nullable |

**Constants (defined in ProjectController):**
```php
TIPOS  = ['SOLUCION TOTAL', 'AMBIENTE CONTROLADO', 'AMBIENTE ABIERTO']
LINEAS = ['AVICULTURA: LEVANTE Y PRODUCCION', 'AVICULTURA: ENGORDE DE POLLO', 'PORCICULTURA', 'BOVINO']
```

**Relationships:**
- `belongsTo(Client::class)`
- `belongsTo(Farm::class)`
- `hasMany(ProgressReport::class)`

---

### ProgressReport
**File:** `app/Models/ProgressReport.php`

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | PK |
| `project_id` | bigint | FK → projects (cascade delete) |
| `report_number` | unsigned int | |
| `cutoff_date` | date | cast to date |
| `start_date` | date | cast to date |
| `end_date` | date | cast to date |
| `weighted_progress_percent` | decimal(5,2) | default 0 |
| `scheduled_progress_percent` | decimal(5,2) | default 0 |
| `difference_percent` | decimal(5,2) | default 0 |
| `contract_days` | unsigned int | nullable |
| `elapsed_days` | unsigned int | nullable |
| `remaining_days` | unsigned int | nullable |
| `notes` | text | nullable |

**Relationships:**
- `belongsTo(Project::class)`
- `hasMany(ProgressReportItem::class)`
- `hasMany(ProgressCurvePoint::class)`

---

### ProgressReportItem
**File:** `app/Models/ProgressReportItem.php`

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | PK |
| `progress_report_id` | bigint | FK → progress_reports (cascade delete) |
| `section_name` | string | nullable |
| `activity_code` | string | nullable |
| `activity_name` | string | |
| `status` | enum | `pending`, `in_progress`, `completed` — default `pending` |
| `advance_percent` | decimal(5,2) | default 0 |
| `pending_percent` | decimal(5,2) | default 0 |
| `notes` | text | nullable |

**Relationships:**
- `belongsTo(ProgressReport::class, 'progress_report_id')`

---

### ProgressCurvePoint
**File:** `app/Models/ProgressCurvePoint.php`

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | PK |
| `progress_report_id` | bigint | FK → progress_reports (cascade delete) |
| `date` | date | cast to date |
| `projected_percent` | decimal(5,2) | default 0 |
| `actual_percent` | decimal(5,2) | default 0 |

**Relationships:**
- `belongsTo(ProgressReport::class, 'progress_report_id')`

---

### Role
**File:** `app/Models/Role.php`

| Field | Type | Notes |
|---|---|---|
| `id` | bigint | PK |
| `name` | string | |
| `slug` | string | |

**Relationships:**
- `hasMany(User::class)`

**Seeded values:** `admin` / `visitador`

---

## 3. Migrations

| Migration file | Tabla/Acción |
|---|---|
| `2026_04_14_230000_create_clients_table` | Crea `clients` |
| `2026_04_14_230001_create_farms_table` | Crea `farms` |
| `2026_04_14_230002_create_farm_georreferences_table` | Crea `farm_georreferences` |
| `2026_04_14_230003_create_farm_contacts_table` | Crea `farm_contacts` |
| `2026_04_20_000015_create_systems_catalog_table` | Crea `systems_catalog` + seed 20 sistemas |
| `2026_04_20_000019_create_projects_table` | Crea `projects` |
| `2026_04_20_000021_create_progress_reports_table` | Crea `progress_reports` |
| `2026_04_20_000022_create_progress_report_items_table` | Crea `progress_report_items` |
| `2026_04_20_000023_create_progress_curve_points_table` | Crea `progress_curve_points` |
| `2026_04_20_000026_add_galpon_count_fields_to_farms` | Agrega `total_galpones`, `galpones_a_cotizar` a `farms` |
| `2026_07_03_000001_drop_galpones_a_cotizar_from_farms` | Elimina `galpones_a_cotizar` de `farms` |
| `2026_05_15_000001_refactor_client_data_module` | Refactor de estructura de datos de clientes |
| `2026_05_15_000002_drop_unused_visit_and_structure_tables` | Elimina tablas obsoletas de visitas/estructuras |
| `2026_05_15_000003_ensure_farm_columns_exist_in_legacy_databases` | Compatibilidad de esquema con DBs legacy |
| `2026_05_16_000001_add_project_classification_and_sync_systems_catalog` | Clasificación de proyectos + sync catálogo |
| `2026_05_16_000002_create_galpones_table` | Crea `galpones` |
| `2026_05_16_000003_create_galpon_systems_table` | Crea `galpon_systems` |

---

## 4. Controllers

### ClientController
**File:** `app/Http/Controllers/ClientController.php`  
**Paginación:** 15/página con `farms`

| Método | Acción |
|---|---|
| `index()` | Lista paginada de clientes con farms |
| `store(StoreClientRequest)` | Crea cliente → 201 |
| `show(Client)` | Cliente con farms, georeferences, contacts |
| `update(UpdateClientRequest, Client)` | Actualiza cliente |
| `destroy(Client)` | Elimina cliente → 204 |

---

### FarmController
**File:** `app/Http/Controllers/FarmController.php`  
**Paginación:** 15/página con client, georreference, contacts

| Método | Acción |
|---|---|
| `index()` | Lista paginada de farms con relaciones |
| `store(StoreFarmRequest)` | Crea farm con datos relacionados → 201 |
| `show(Farm)` | Farm completo con galpones y sistemas |
| `update(UpdateFarmRequest, Farm)` | Actualiza farm y relaciones anidadas |
| `destroy(Farm)` | Elimina farm → 204 |

---

### FarmContactController
**File:** `app/Http/Controllers/FarmContactController.php`  
**Paginación:** 15/página | **Filtro:** `?farm_id=`

| Método | Acción |
|---|---|
| `index(Request)` | Lista paginada, filtrable por `farm_id` |
| `store(StoreFarmContactRequest)` | Crea contacto → 201 |
| `show(FarmContact)` | Contacto con farm |
| `update(UpdateFarmContactRequest, FarmContact)` | Actualiza contacto |
| `destroy(FarmContact)` | Elimina contacto → 204 |

---

### FarmGeorreferenceController
**File:** `app/Http/Controllers/FarmGeorreferenceController.php`  
**Paginación:** 15/página

| Método | Acción |
|---|---|
| `index()` | Lista paginada con farm |
| `store(StoreFarmGeorreferenceRequest)` | Crea georreferencia → 201 |
| `show(FarmGeorreference)` | Georreferencia con farm |
| `update(UpdateFarmGeorreferenceRequest, FarmGeorreference)` | Actualiza georreferencia |
| `destroy(FarmGeorreference)` | Elimina georreferencia → 204 |

---

### GalponController
**File:** `app/Http/Controllers/GalponController.php`

| Método | Acción |
|---|---|
| `index(Farm)` | Lista galpones de un farm con sistemas |
| `store(StoreGalponRequest, Farm)` | Crea galpon bajo farm → 201 |
| `show(Galpon)` | Galpon con sistemas |
| `update(UpdateGalponRequest, Galpon)` | Actualiza galpon |
| `destroy(Galpon)` | Elimina galpon → 204 |

---

### GalponSystemController
**File:** `app/Http/Controllers/GalponSystemController.php`

| Método | Acción |
|---|---|
| `index(Galpon)` | Lista sistemas de un galpon |
| `store(StoreGalponSystemRequest, Galpon)` | Crea sistema bajo galpon → 201 |
| `show(GalponSystem)` | Sistema con detalles del catálogo |
| `update(UpdateGalponSystemRequest, GalponSystem)` | Actualiza sistema |
| `destroy(GalponSystem)` | Elimina sistema → 204 |

---

### ProjectController
**File:** `app/Http/Controllers/ProjectController.php`  
**Paginación:** 20/página | **Filtros:** `client_id`, `farm_id`, `tipo`, `linea`, `status`

| Método | Acción |
|---|---|
| `index(Request)` | Lista paginada con filtros opcionales |
| `store(Request)` | Crea proyecto (normaliza a mayúsculas) → 201 |
| `show(Project)` | Proyecto con client, farm, progress reports |
| `update(Request, Project)` | Actualiza proyecto (validación parcial) |
| `destroy(Project)` | Elimina proyecto → 204 |
| `validatedPayload(Request, bool)` *(private)* | Valida y normaliza a mayúsculas |

---

### ProgressReportController
**File:** `app/Http/Controllers/ProgressReportController.php`

| Método | Acción |
|---|---|
| `index(Project)` | Lista reportes del proyecto ordenados por `report_number` |
| `store(Request, Project)` | Crea reporte con validación inline → 201 |
| `show(ProgressReport)` | Reporte con project, items, curve points |
| `update(Request, ProgressReport)` | Actualiza reporte (validación parcial) |
| `destroy(ProgressReport)` | Elimina reporte → 204 |

---

### SystemsCatalogController
**File:** `app/Http/Controllers/SystemsCatalogController.php`

| Método | Acción |
|---|---|
| `index()` | Lista sistemas activos del catálogo |
| `store(Request)` | Crea sistema → 201 |
| `show(SystemsCatalog)` | Devuelve sistema individual |
| `update(Request, SystemsCatalog)` | Actualiza sistema |
| `destroy(SystemsCatalog)` | Elimina sistema → 204 |
| `validatedPayload(Request, bool)` *(private)* | Valida y normaliza a mayúsculas |

---

## 5. Routes

**File:** `routes/api.php`

### Pública (throttle: 5/min)
```
POST   /login
```

### Protegidas (`auth:sanctum`)

#### Auth
```
POST   /logout
GET    /me
```

#### Clientes
```
GET    /clients
POST   /clients
GET    /clients/{client}
PUT    /clients/{client}
PATCH  /clients/{client}
DELETE /clients/{client}
```

#### Farms
```
GET    /farms
POST   /farms
GET    /farms/{farm}
PUT    /farms/{farm}
PATCH  /farms/{farm}
DELETE /farms/{farm}
```

#### Georreferencias
```
GET    /farm-georreferences
POST   /farm-georreferences
GET    /farm-georreferences/{farmGeorreference}
PUT    /farm-georreferences/{farmGeorreference}
PATCH  /farm-georreferences/{farmGeorreference}
DELETE /farm-georreferences/{farmGeorreference}
```

#### Contactos
```
GET    /farm-contacts             ?farm_id=
POST   /farm-contacts
GET    /farm-contacts/{farmContact}
PUT    /farm-contacts/{farmContact}
PATCH  /farm-contacts/{farmContact}
DELETE /farm-contacts/{farmContact}
```

#### Galpones
```
GET    /farms/{farm}/galpones
POST   /farms/{farm}/galpones
GET    /galpones/{galpon}
PUT    /galpones/{galpon}
PATCH  /galpones/{galpon}
DELETE /galpones/{galpon}
```

#### Sistemas de Galpón
```
GET    /galpones/{galpon}/systems
POST   /galpones/{galpon}/systems
GET    /galpon-systems/{galponSystem}
PUT    /galpon-systems/{galponSystem}
PATCH  /galpon-systems/{galponSystem}
DELETE /galpon-systems/{galponSystem}
```

#### Catálogo de Sistemas
```
GET    /systems-catalog
POST   /systems-catalog
GET    /systems-catalog/{systemsCatalog}
PUT    /systems-catalog/{systemsCatalog}
PATCH  /systems-catalog/{systemsCatalog}
DELETE /systems-catalog/{systemsCatalog}
```

#### Proyectos
```
GET    /projects                  ?client_id= &farm_id= &tipo= &linea= &status=
POST   /projects
GET    /projects/{project}
PUT    /projects/{project}
PATCH  /projects/{project}
DELETE /projects/{project}
```

#### Reportes de Avance
```
GET    /projects/{project}/progress-reports
POST   /projects/{project}/progress-reports
GET    /projects/{project}/progress-reports/{progressReport}
PUT    /projects/{project}/progress-reports/{progressReport}
PATCH  /projects/{project}/progress-reports/{progressReport}
DELETE /projects/{project}/progress-reports/{progressReport}
```

---

## 6. Form Requests & Validation

### Trait transversal: `UppercasesInput`
**File:** `app/Http/Requests/Concerns/UppercasesInput.php`

Todos los Form Requests del proyecto usan este trait. Ejecuta `prepareForValidation()` antes de la validación para convertir a mayúsculas los campos declarados en `uppercaseFields()`. Soporta arrays anidados de forma recursiva.

---

### StoreClientRequest / UpdateClientRequest
**Uppercase fields:** `razon_social`, `nit`

| Campo | Reglas (Store) | Reglas (Update) |
|---|---|---|
| `razon_social` | `required\|string\|max:255` | `sometimes\|string\|max:255` |
| `nit` | `required\|string\|max:50\|unique:clients` | `sometimes\|...\|unique:clients,nit,{id}` |
| `email` | `required\|email\|max:255` | `sometimes\|email\|max:255` |
| `phone_number` | `required\|string\|max:50` | `sometimes\|string\|max:50` |

---

### StoreFarmRequest / UpdateFarmRequest
**Uppercase fields:** `nombre`, `access_ways`, `observations`, `transformator_are_feeding_installations`

| Campo | Tipo | Notas |
|---|---|---|
| `client_id` | required | exists:clients |
| `nombre` | required string max:255 | |
| `transformator_capacity_kva` | nullable integer min:0 | |
| `access_ways` | nullable string max:500 | |
| `observations` | nullable string max:5000 | |
| `farm_voltage` | nullable | in: `110V`, `220V`, `440V` |
| `farm_electric_current` | nullable | in: `monophase`, `biphase`, `triphase` |
| `have_own_transformator` | nullable boolean | |
| `is_transformator_feeds_other_installations` | nullable boolean | |
| `have_easy_access_for_trailer` | nullable boolean | |
| `staff_availability` | nullable boolean | |
| `has_storage_warehouse` | nullable boolean | |
| `how_many_warehouses` | nullable integer min:0 | |
| `total_galpones` | nullable integer min:0 | |

---

### StoreFarmContactRequest / UpdateFarmContactRequest
**Uppercase fields:** `name`

| Campo | Reglas |
|---|---|
| `farm_id` | `required\|exists:farms` |
| `type` | `required\|in:administrador,veterinario,encargado,otro` |
| `name` | `required\|string\|max:255` |
| `email` | `nullable\|email\|max:255` |
| `phone` | `nullable\|string\|max:50` |

---

### StoreFarmGeorreferenceRequest / UpdateFarmGeorreferenceRequest
**Uppercase fields:** `address`, `town`, `department`

| Campo | Reglas |
|---|---|
| `farm_id` | `required\|exists:farms` |
| `address` | `nullable\|string\|max:500` |
| `town` | `nullable\|string\|max:255` |
| `department` | `nullable\|string\|max:255` |
| `map_url_reference` | `nullable\|string\|max:1000` |

---

### StoreGalponRequest / UpdateGalponRequest
**Uppercase fields:** `name`, `code`, `description`, `observations`, `technical_attributes_json`

| Campo | Reglas |
|---|---|
| `name` | `required\|string\|max:255` |
| `code` | `nullable\|string\|max:100` |
| `status` | `nullable\|in:active,inactive,under_construction,retired` |
| `description` | `nullable\|string\|max:5000` |
| `dimensions_json.largo_m` | `nullable\|numeric\|min:0` |
| `dimensions_json.ancho_m` | `nullable\|numeric\|min:0` |
| `dimensions_json.altura_canal_m` | `nullable\|numeric\|min:0` |
| `dimensions_json.altura_cumbrera_m` | `nullable\|numeric\|min:0` |
| `technical_attributes_json` | `nullable\|array` |
| `observations` | `nullable\|string\|max:5000` |
| `sort_order` | `nullable\|integer\|min:0` |

---

### StoreGalponSystemRequest / UpdateGalponSystemRequest
**Uppercase fields:** `notes`, `technical_attributes_json`

| Campo | Reglas |
|---|---|
| `system_id` | `required\|exists:systems_catalog\|unique to galpon` |
| `quantity` | `nullable\|integer\|min:1` |
| `notes` | `nullable\|string\|max:5000` |
| `technical_attributes_json` | `nullable\|array` |

---

## 7. Resources

### ClientResource
**File:** `app/Http/Resources/ClientResource.php`

```
id, razon_social, nit, email, phone_number,
farms (FarmResource collection — whenLoaded),
created_at, updated_at
```

### FarmResource
**File:** `app/Http/Resources/FarmResource.php`

```
id, client_id, nombre, transformator_capacity_kva,
access_ways, observations, farm_voltage, farm_electric_current,
have_own_transformator, is_transformator_feeds_other_installations,
transformator_are_feeding_installations, have_easy_access_for_trailer,
staff_availability, has_storage_warehouse, how_many_warehouses,
total_galpones,
client     (ClientResource — whenLoaded),
georreference (FarmGeorreferenceResource — whenLoaded),
contacts   (FarmContactResource collection — whenLoaded),
galpones   (GalponResource collection — whenLoaded),
created_at, updated_at
```

### FarmContactResource
**File:** `app/Http/Resources/FarmContactResource.php`

```
id, farm_id, type, name, email, phone,
created_at, updated_at
```

### FarmGeorreferenceResource
**File:** `app/Http/Resources/FarmGeorreferenceResource.php`

```
id, farm_id, address, town, department, map_url_reference,
created_at, updated_at
```

### GalponResource
**File:** `app/Http/Resources/GalponResource.php`

```
id, farm_id, name, code, status, description,
dimensions_json, technical_attributes_json, observations, sort_order,
systems (GalponSystemResource collection — whenLoaded),
created_at, updated_at
```

### GalponSystemResource
**File:** `app/Http/Resources/GalponSystemResource.php`

```
id, galpon_id, system_id, quantity, notes, technical_attributes_json,
system (catalog data — whenLoaded),
created_at, updated_at
```

---

## 8. Database Seeding

**File:** `database/seeders/DatabaseSeeder.php`

```
Role::create(['name' => 'Administrador', 'slug' => 'admin'])
Role::create(['name' => 'Visitador',     'slug' => 'visitador'])

User::create([
    'email'    => 'admin@visitador.com',
    'password' => 'password',
    'role_id'  => Role(admin)->id,
])
```

**UserFactory** (`database/factories/UserFactory.php`):

| Campo | Valor |
|---|---|
| `name` | `fake()->name()` |
| `email` | `fake()->unique()->safeEmail()` |
| `password` | `bcrypt('password')` |
| `phone` | `fake()->phoneNumber()` |
| `role_id` | First role or creates one |
| `remember_token` | `Str::random(10)` |

---

## 9. Tests

**File:** `tests/Feature/ClientDataModuleTest.php`

| Test | Qué verifica |
|---|---|
| `test_active_text_fields_are_normalized_to_uppercase_and_farm_accepts_440v` | Normalización a mayúsculas en client, farm (440V), georreferencia y contacto |
| `test_farm_exposes_galpones_and_their_systems_with_dimensions` | Relaciones anidadas farm→galpon→systems, conteo automático, actualización |
| `test_project_accepts_tipo_and_linea_with_uppercase_normalization` | Creación de proyecto con enums tipo/linea, validación de mayúsculas |
| `test_systems_catalog_exposes_the_updated_20_active_systems` | Verifica que existan los 20 sistemas activos del catálogo |

---

## 10. Configuration

### `config/sanctum.php`
| Clave | Valor |
|---|---|
| `expiration` | `480` (minutos — 8 horas) |
| `stateful_domains` | `localhost:3000`, `127.0.0.1:8000`, `::1` |
| `guard` | `web` |

### `config/database.php`
| Clave | Valor |
|---|---|
| `default` | `sqlite` (env override) |
| `foreign_key_constraints` | `true` |
| Conexiones disponibles | `sqlite`, `mysql`, `mariadb`, `pgsql`, `sqlsrv` |

### `config/queue.php`
| Clave | Valor |
|---|---|
| `default` | `database` |
| `retry_after` | `90` segundos |
| `failed driver` | `database-uuids` → tabla `failed_jobs` |

### `.env defaults`
| Clave | Valor |
|---|---|
| `DB_CONNECTION` | `sqlite` |
| `SESSION_DRIVER` | `database` (120 min) |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `MAIL_MAILER` | `log` |
| `LOG_CHANNEL` | `single` (debug) |

---

## 11. Dependencies

### Production (`composer.json`)

| Paquete | Versión | Uso |
|---|---|---|
| `laravel/framework` | `^13.0` | Framework base |
| `laravel/sanctum` | `^4.0` | Autenticación API por tokens |
| `laravel/tinker` | `^3.0` | REPL de Artisan |

### Development

| Paquete | Versión | Uso |
|---|---|---|
| `fakerphp/faker` | `^1.23` | Generación de datos falsos |
| `laravel/pail` | `^1.2.5` | Tail de logs en tiempo real |
| `laravel/pint` | `^1.27` | Code style fixer |
| `mockery/mockery` | `^1.6` | Mocking en tests |
| `nunomaduro/collision` | `^8.6` | Error handling mejorado |
| `phpunit/phpunit` | `^12.5` | Framework de testing |

### Scripts npm/composer

```bash
composer setup   # install + key:generate + migrate + build
composer dev     # server + queue:listen + vite (paralelo)
composer test    # config:clear + phpunit
```
