# SMS Toplu Gönderim Sistemi

Laravel 12 tabanlı, 200.000+ SMS gönderimi yapabilen production-ready bulk SMS sistemi.

## 🚀 Özellikler

- **Repository Pattern**: Veritabanı soyutlaması
- **Service Layer**: İş mantığı katmanı
- **Queue System**: Asenkron mesaj gönderimi
- **Redis Cache**: Idempotency ve performans
- **Rate Limiting**: Webhook provider koruması (50 req/min)
- **Interface + DTO**: Repository arayüzleri ve veri taşıma objeleri kullanıldı
- **REST API**: Gönderilen mesajlar endpoint'i
- **Swagger UI**: API dokümantasyonu
- **Unit/Feature Tests**: Temel senaryoları kapsayan testler

## 📋 Gereksinimler

- PHP 8.2+
- MySQL 8.0+
- Redis 6.0+
- Composer 2.x
- Laravel 12

**Not:** Şartname metninde Laravel 10.x ve 11+ birlikte geçiyor. Proje Laravel 12 kullanır (11+ şartını karşılar).

## 📦 Temel Paketler

- laravel/framework ^12.0
- darkaonline/l5-swagger ^10.1
- laravel/tinker ^2.10.1

## 🔧 Kurulum

### 1. Projeyi İndirin

```bash
git clone <repository-url>
cd insiderone_task
```

### 2. Bağımlılıkları Yükleyin

```bash
composer install
```

### 3. Environment Ayarları

`.env` dosyasını oluşturun:

```bash
cp .env.example .env
php artisan key:generate
```

`.env` içinde düzenleyin:

```env
DB_CONNECTION=mysql
DB_DATABASE=insiderone_task
DB_USERNAME=root
DB_PASSWORD=your_password

CACHE_STORE=redis
QUEUE_CONNECTION=redis

WEBHOOK_URL=https://webhook.site/d044750d-268e-4696-887c-89e911000053
WEBHOOK_AUTH_KEY=INS.me1x9uMcyYG1hkKQVPoc.bO3j9aZwRTOcA2Ywo
WEBHOOK_MOCK=false
```

Not: Rate limit testlerinde WEBHOOK_MOCK=true yapılabilir.

### 4. Database Oluşturun

```bash
mysql -u root -p
CREATE DATABASE insiderone_task CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### 5. Migration ve Seeder

```bash
php artisan migrate
php artisan db:seed
```

Seeder 140+ test data oluşturur:
- 107 müşteri (95 aktif, 12 pasif)
- 6 mesaj
- 35 mesaj gönderimi (28 pending, 5 sent, 2 failed)

## 🎯 Kullanım

### Queue Worker Başlatma

**Terminal 1:**
```bash
php artisan queue:work redis --tries=3
```

### Mesaj Gönderimi

**Terminal 2:**
```bash
# 2 mesaj gönder (default)
php artisan messages:send

# Custom limit
php artisan messages:send --limit=10

# Detaylı log
php artisan messages:send --limit=5 -v
```

### API Kullanımı

**Gönderilen Mesajları Listele:**

```bash
# Default (50 kayıt, sayfa 1)
curl http://127.0.0.1:8000/api/v1/messages/sent

# Pagination
curl "http://127.0.0.1:8000/api/v1/messages/sent?page=2&per_page=20"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "webhook_message_id": "msg_65a0b12f2c9d4",
      "phone_number": "905551234567",
      "message_content": "Test mesajı",
      "status": "sent",
      "sent_at": "2026-01-19T10:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "total": 100,
    "last_page": 2
  }
}
```

### Swagger UI

API dokümantasyonuna erişin:

```
http://127.0.0.1:8000/api/documentation
```

## 🏗️ Mimari

### Repository Pattern

```
app/Repositories/
├── CustomerRepository.php
├── MessageRepository.php
└── MessageSendRepository.php
```

```
app/Repositories/Contracts/
├── CustomerRepositoryInterface.php
├── MessageRepositoryInterface.php
└── MessageSendRepositoryInterface.php
```

### Service Layer

```
app/Services/
├── MessageService.php      # İş mantığı
└── WebhookService.php      # HTTP client + cache
```

```
app/DTOs/
└── SendWebhookMessageDto.php
```

**Not:** MessageService içinde `CHUNK_SIZE=200` ile `chunkById` kullanılır. Bu sayede büyük veri setlerinde bellek şişmez ve gönderim batch halinde ilerler.

### Jobs

```
app/Jobs/
└── SendMessageJob.php      # Asenkron gönderim
   - Idempotency (2 gün)
   - Retry: 3 deneme (60s, 180s backoff)
```

### Redis Cache

**Idempotency:**
```
Key: job:message_send:{id}
TTL: 2 gün
```
Not: Aynı `message_send` kaydı için cache kilidi tutulur; böylece job tekrar tetiklense bile ikinci kez gönderim yapılmaz.

**Message Info:**
```
Key: webhook:message_send:{id}
TTL: 2 gün
Data: {messageId, phone, sent_at}
```
Not: Webhook’tan dönen `messageId`, `message_sends.webhook_message_id` alanına yazılır; aynı anda `status=sent` ve `sent_at` güncellenir.

**API Response:**
```
Key: sent:{page}:{per_page}
TTL: 5 dakika
Auto-clear: Job success
```

**Rate Limit:**
```
Key: webhook_limit:{YmdHi}
TTL: 60 saniye
Limit: 50 request/min
```

## 🧪 Testler

### Tüm Testleri Çalıştır

```bash
php artisan test
```

### Test Kategorileri

```bash
# Unit Tests
php artisan test --testsuite=Unit

# Feature Tests
php artisan test --testsuite=Feature
```

**Test Coverage:**
- MessageServiceTest: 3 test
- WebhookServiceTest: 3 test
- SendMessageJobTest: 3 test
- MessageControllerTest: 3 test
- **Total: 14 test, 30 assertion**

## 📊 Database Schema

### customers
- id, name, phone_number, is_active, timestamps

### messages
- id, title, content, status (enum), sent_count, timestamps

### message_sends
- id, customer_id, message_id, phone_number, message_content
- status (pending/sent/failed), webhook_message_id, sent_at, timestamps

## 🔐 Güvenlik

- **Idempotency**: Duplicate mesaj engelleme
- **Rate Limiting**: Provider koruması
- **Validation**: 160 karakter SMS limiti
- **Retry Mechanism**: Hatalı gönderim yeniden deneme

## 🚀 Production Önerileri

1. **Supervisor** kullanın queue worker için:
```bash
sudo apt install supervisor
# /etc/supervisor/conf.d/laravel-worker.conf oluşturun
```

2. **Cronjob** ile job tetikleyin (örnek: her dakika):
```bash
* * * * * cd /var/www/insiderone_task && php artisan messages:send --limit=200 >> /var/log/insiderone_task/messages_send.log 2>&1
```

3. **Redis persistence** aktif edin
4. **Log rotation** yapılandırın
5. **Monitoring** ekleyin (Horizon, Pulse)
6. **Rate limit** değerlerini provider'a göre ayarlayın

## 📝 Notlar

- SMS gönderimi webhook.site üzerinden test edilmiştir
- 200K mesaj kapasitesi için Redis ve MySQL optimize edilmiştir
- Tüm cache key'leri namespace'li (collision önleme)
- API endpoint cache otomatik temizlenir (real-time data)

## 👤 Developer

Geliştirici: [BULUT KURU]
Tarih: Ocak 2026
Laravel Version: 12
PHP Version: 8.4.16
