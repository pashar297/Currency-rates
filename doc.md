# Currency Converter API

REST API для конвертации валют с кешированием курсов.

## 1 Требования

- PHP 8.3+
- MySQL 8.0+
- Nginx
- Composer

# Установка

## 1. Клонирование проекта
```bash
git clone <repository-url>
cd currency-converter
```

## 2 Установка зависимостей

### Через Docker
```bash
docker-compose up --build -d
```
### Локально
```bash
composer install
```

## 3 Настройка окружения
Добавление параметроов в .env.dev:
```
# Отредактировать DATABASE_URL под ваши настройки
DATABASE_URL="mysql://user:password@host:3306/db_name?serverVersion=8.0&charset=utf8mb4"

BINANCE_API_URL=https://api.binance.com
BINANCE_API_TIMEOUT=10
```

## 4 Подготовка БД
### Создание БД
bin/console doctrine:database:create --if-not-exists

### Создание таблиц
bin/console doctrine:schema:update --force

### Выполнение миграций (добавление записей)
bin/console doctrine:migrations:migrate

### 5 Настройка crontab
cp config/crontab/crontab /etc/cron.d/app-cron

Все зависит от настроек среды, если локально и через докер, то в докере уже настроено и проблем быть не дожно.


# Примеры запросов
### Получение курсов за последние 24 часа
GET http://localhost:8080/api/rates/last-24h?pair=EUR/BTC

Parameters:
- pair: Валютная пара (например, EUR/BTC) (Required)

Response
```json
{
"pair": "EUR/BTC",
"period": "last-24h",
"data": 
[
        {
            "timestamp": "2025-09-13T19:56:21+00:00",
            "rate": 0.00001016
        },
        {
            "timestamp": "2025-09-13T20:30:52+00:00",
            "rate": 0.00001015
        },
        {
            "timestamp": "2025-09-14T11:00:18+00:00",
            "rate": 0.00001014
        },
        {
            "timestamp": "2025-09-14T11:00:31+00:00",
            "rate": 0.00001014
        },
        {
            "timestamp": "2025-09-14T11:32:26+00:00",
            "rate": 0.00001015
        }
    ]
}
```

### Получение курсов за определенный день
GET http://localhost:8080/api/rates/day?pair=EUR/BTC&date=2025-09-13

Parameters:
- pair: Валютная пара (например, EUR/BTC) (Required)
- date: Дата в формате YYYY-MM-DD (например, 2025-09-13) (Required)

Response
```json
{
"pair": "EUR/BTC",
"period": "2025-09-13",
"data": 
[
        {
            "timestamp": "2025-09-13T19:56:21+00:00",
            "rate": 0.00001016
        },
        {
            "timestamp": "2025-09-13T20:30:52+00:00",
            "rate": 0.00001015
        }
    ]
}
```