# Dynamic Tables API

Laravel API для динамического создания таблиц в PostgreSQL и CRUD-операций с данными.

Проект запускается через Docker Compose. Nginx не используется, Laravel работает через `php artisan serve`.

## Что внутри Docker

- PHP 8.2
- Composer
- PostgreSQL 16
- Laravel
- Автоматический запуск миграций

## Быстрый запуск

Скопируйте env для Docker:

```bash
cp .env.docker.example .env
```

Запустите проект:

```bash
docker compose up --build
```

При первом запуске контейнер сам выполнит:

```bash
composer install
php artisan key:generate
php artisan migrate
```

API будет доступен по адресу:

```text
http://127.0.0.1:8000
```

## Остановка проекта

Остановить контейнеры:

```bash
docker compose down
```

Остановить контейнеры и удалить базу данных:

```bash
docker compose down -v
```

## Полезные команды

Зайти внутрь PHP-контейнера:

```bash
docker compose exec app bash
```

Запустить миграции:

```bash
docker compose exec app php artisan migrate
```

Запустить тесты:

```bash
docker compose exec app php artisan test
```

В проекте уже написаны Feature-тесты для основных сценариев:

- создание схемы динамической таблицы;
- автоматическое добавление `id`;
- полный CRUD;
- пагинация;
- защита от неправильных имен таблиц;
- проверка неизвестных полей;
- проверка обязательных полей;
- проверка дублирующей таблицы;
- проверка полного `PUT`;
- проверка `columnCount`.

Очистить кеш:

```bash
docker compose exec app php artisan optimize:clear
```

## Настройки базы данных в Docker

В Docker используется PostgreSQL со следующими настройками:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=dynamic_tables
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

Эти настройки уже есть в `.env.docker.example`.

## Роуты API

Базовый URL:

```text
http://127.0.0.1:8000
```

### Схемы таблиц

```http
POST /api/v1/dynamic-tables/schemas
GET /api/v1/dynamic-tables/schemas
GET /api/v1/dynamic-tables/schemas/{tableName}
```

### Данные таблиц

```http
POST /api/v1/dynamic-tables/data/{tableName}
GET /api/v1/dynamic-tables/data/{tableName}
GET /api/v1/dynamic-tables/data/{tableName}/{id}
PUT /api/v1/dynamic-tables/data/{tableName}/{id}
DELETE /api/v1/dynamic-tables/data/{tableName}/{id}
```

## Тела запросов для проверки

Для запросов с JSON укажите header:

```http
Content-Type: application/json
```

### 1. Создать динамическую таблицу

```http
POST /api/v1/dynamic-tables/schemas
```

```json
{
  "tableName": "contacts_alpha",
  "userFriendlyName": "Контакты проекта Alpha",
  "columns": [
    {
      "name": "full_name",
      "type": "TEXT",
      "isNullable": false
    },
    {
      "name": "email",
      "type": "TEXT",
      "isNullable": true
    },
    {
      "name": "age",
      "type": "INTEGER",
      "isNullable": true
    },
    {
      "name": "balance",
      "type": "DECIMAL",
      "isNullable": true
    },
    {
      "name": "is_active",
      "type": "BOOLEAN",
      "isNullable": false
    }
  ]
}
```

### 2. Получить список таблиц

```http
GET /api/v1/dynamic-tables/schemas
```

Body не нужен.

### 3. Получить схему одной таблицы

```http
GET /api/v1/dynamic-tables/schemas/contacts_alpha
```

Body не нужен.

### 4. Создать запись

```http
POST /api/v1/dynamic-tables/data/contacts_alpha
```

```json
{
  "full_name": "Арсен Керезбеков",
  "email": "kerezbekov.dev@gmail.com",
  "age": 20,
  "balance": 1500.75,
  "is_active": true
}
```

### 5. Получить список записей

```http
GET /api/v1/dynamic-tables/data/contacts_alpha?page=0&size=20
```

Body не нужен.

### 6. Получить одну запись

```http
GET /api/v1/dynamic-tables/data/contacts_alpha/1
```

Body не нужен.

### 7. Полностью обновить запись

```http
PUT /api/v1/dynamic-tables/data/contacts_alpha/1
```

```json
{
  "full_name": "Арсен Керезбеков",
  "email": "kerezbekov.dev@gmail.com",
  "age": 21,
  "balance": 2500.50,
  "is_active": true
}
```

Важно: `PUT` требует полный набор полей таблицы, кроме `id`.

### 8. Удалить запись

```http
DELETE /api/v1/dynamic-tables/data/contacts_alpha/1
```

Body не нужен.

## Ошибки API

Все ошибки возвращаются в одном JSON-формате:

```json
{
  "timestamp": "2026-09-24T10:00:00.000000Z",
  "status": 400,
  "error": "Bad Request",
  "message": "Invalid table name",
  "path": "/api/v1/dynamic-tables/schemas"
}
```

### 400 Bad Request

Возвращается, когда запрос неправильный.

Примеры:

- неправильное имя таблицы;
- неправильное имя колонки;
- колонка называется `id`;
- неизвестный тип колонки;
- пустой массив `columns`;
- неизвестное поле при создании записи;
- не передано обязательное поле;
- неправильный тип значения, например строка вместо `INTEGER`;
- в `PUT` передан не полный набор полей.

Пример неправильного body:

```json
{
  "tableName": "bad-table-name",
  "columns": []
}
```

### 404 Not Found

Возвращается, когда таблица или запись не найдена.

Примеры:

```http
GET /api/v1/dynamic-tables/schemas/unknown_table
GET /api/v1/dynamic-tables/data/contacts_alpha/999
DELETE /api/v1/dynamic-tables/data/contacts_alpha/999
```

### 409 Conflict

Возвращается, когда таблица с таким именем уже существует.

Пример:

```http
POST /api/v1/dynamic-tables/schemas
```

Если `contacts_alpha` уже создана, повторное создание вернет `409 Conflict`.

### 500 Internal Server Error

Возвращается при неожиданной ошибке сервера или базы данных.

Примеры:

- PostgreSQL недоступен;
- ошибка подключения к базе;
- внутренняя ошибка приложения.

## Поддерживаемые типы колонок

```text
TEXT
INTEGER
BIGINT
DECIMAL
BOOLEAN
DATE
TIMESTAMP
```

## Почему не используется Eloquent для динамических таблиц

Eloquent удобно использовать, когда таблицы известны заранее.

В этом проекте таблицы создаются пользователем во время работы приложения. У каждой таблицы могут быть разные колонки, поэтому заранее создать отдельную Eloquent-модель невозможно.

Поэтому:

- Eloquent используется только для служебных таблиц с метаданными;
- динамические таблицы обрабатываются через `DB`;
- значения передаются через безопасные параметры запроса;
- имена таблиц и колонок проходят строгую валидацию.

Такой подход проще, безопаснее и лучше подходит для этого задания.

## Если нужно запустить без Docker

Установите зависимости:

```bash
composer install
```

Создайте `.env`:

```bash
cp .env.example .env
```

Сгенерируйте ключ:

```bash
php artisan key:generate
```

Настройте PostgreSQL в `.env`, затем выполните:

```bash
php artisan migrate
php artisan serve
```
