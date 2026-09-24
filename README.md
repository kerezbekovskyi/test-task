# Dynamic Tables API

Простой Laravel API для создания динамических таблиц в PostgreSQL и работы с данными через CRUD.

## Требования

- PHP 8.2 или выше
- Composer
- PostgreSQL

## Установка проекта

Сначала установите зависимости:

```bash
composer install
```

Создайте `.env` файл:

```bash
cp .env.example .env
```

Сгенерируйте ключ приложения:

```bash
php artisan key:generate
```

## Настройка базы данных

Создайте базу данных в PostgreSQL, например:

```sql
CREATE DATABASE dynamic_tables;
```

Потом откройте файл `.env` и настройте подключение:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=dynamic_tables
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

В `DB_USERNAME` и `DB_PASSWORD` укажите свои данные от PostgreSQL.

## Миграции

После настройки базы данных запустите миграции:

```bash
php artisan migrate
```

Эта команда создаст служебные таблицы:

```text
app_dynamic_table_definitions
app_dynamic_column_definitions
```

## Запуск проекта

Запустите локальный сервер:

```bash
php artisan serve
```

После запуска API будет доступен по адресу:

```text
http://127.0.0.1:8000
```

## Запуск тестов

```bash
php artisan test
```

## Роуты API

### Схемы таблиц

Создать динамическую таблицу:

```http
POST /api/v1/dynamic-tables/schemas
```

Получить список всех динамических таблиц:

```http
GET /api/v1/dynamic-tables/schemas
```

Получить схему одной таблицы:

```http
GET /api/v1/dynamic-tables/schemas/{tableName}
```

### Данные динамических таблиц

Создать запись:

```http
POST /api/v1/dynamic-tables/data/{tableName}
```

Получить список записей:

```http
GET /api/v1/dynamic-tables/data/{tableName}
```

Получить одну запись по ID:

```http
GET /api/v1/dynamic-tables/data/{tableName}/{id}
```

Обновить запись:

```http
PUT /api/v1/dynamic-tables/data/{tableName}/{id}
```

Удалить запись:

```http
DELETE /api/v1/dynamic-tables/data/{tableName}/{id}
```

## Пример создания таблицы

```bash
curl -X POST http://127.0.0.1:8000/api/v1/dynamic-tables/schemas \
  -H "Content-Type: application/json" \
  -d '{
    "tableName": "contacts_alpha",
    "userFriendlyName": "Контакты проекта Alpha",
    "columns": [
      { "name": "full_name", "type": "TEXT", "isNullable": false },
      { "name": "email", "type": "TEXT", "isNullable": true },
      { "name": "age", "type": "INTEGER", "isNullable": true },
      { "name": "is_active", "type": "BOOLEAN", "isNullable": false }
    ]
  }'
```

## Пример создания записи

```bash
curl -X POST http://127.0.0.1:8000/api/v1/dynamic-tables/data/contacts_alpha \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Арсен Керезбеков",
    "email": "kerezbekov.dev@gmail.com",
    "age": 20,
    "is_active": true
  }'
```

## Пример получения списка записей

```bash
curl "http://127.0.0.1:8000/api/v1/dynamic-tables/data/contacts_alpha?page=0&size=20"
```

## Тела запросов для проверки

Эти JSON можно использовать в Postman, Insomnia или curl.

### 1. Создание схемы таблицы

Метод:

```http
POST /api/v1/dynamic-tables/schemas
```

Body:

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

### 2. Создание записи

Метод:

```http
POST /api/v1/dynamic-tables/data/contacts_alpha
```

Body:

```json
{
  "full_name": "Арсен Керезбеков",
  "email": "kerezbekov.dev@gmail.com",
  "age": 20,
  "balance": 1500.75,
  "is_active": true
}
```

### 3. Полное обновление записи

Метод:

```http
PUT /api/v1/dynamic-tables/data/contacts_alpha/1
```

Body:

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

### 4. Запросы без body

Эти запросы выполняются без тела:

```http
GET /api/v1/dynamic-tables/schemas
GET /api/v1/dynamic-tables/schemas/contacts_alpha
GET /api/v1/dynamic-tables/data/contacts_alpha?page=0&size=20
GET /api/v1/dynamic-tables/data/contacts_alpha/1
DELETE /api/v1/dynamic-tables/data/contacts_alpha/1
```

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

Такой подход проще, безопаснее и лучше подходит для этого тестового задания.
