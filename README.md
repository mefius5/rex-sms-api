## Start aplikacji 

# Aby uruchomić aplikację, należy wykonać następujące kroki (nie stawiałem dockera)
`php artisan serve`

# Start kolejek
`php artisan queue:work`

# Puszczenie testów
`php artisan test`

Wg zadania w aplikacji są dwa enpointy (jak w zadaniu zastosowałem spsapi.pl):
POST /api/sms - wysłanie smsa, przykładowy request (raw json):
```
{
    "recipient": "+48123456789",
    "message": "Hello world"
}
``
GET /api/sms - pobranie listy wysłanych smsów

Do skonfigurowania w .env

```
SMSAPI_API_TOKEN=your_api_token
SMSAPI_BASE_URL=https://api.smsapi.pl
SMSAPI_FROM=your_sender_name
```




