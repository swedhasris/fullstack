# ConnectIT ITSM — Laravel + MySQL

Enterprise-grade IT Service Management system built with **Laravel 13** + **MySQL** + **Blade Templates**.

---

## 🚀 Quick Start

### Requirements
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js 18+ (for Vite assets)

### Installation

```bash
# 1. Clone and enter directory
cd connectit-laravel

# 2. Install PHP dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Configure MySQL in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=connectit_itsm
# DB_USERNAME=root
# DB_PASSWORD=your_password

# 6. Create MySQL database
mysql -u root -p -e "CREATE DATABASE connectit_itsm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 7. Run migrations
php artisan migrate

# 8. Seed default data (users, SLA policies, settings)
php artisan db:seed

# 9. Install Node dependencies and build assets
npm install
npm run build

# 10. Create storage symlink
php artisan storage:link

# 11. Start the server
php artisan serve
```

Visit: **http://localhost:8000**

---

## 🔑 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Ultra Super Admin | ultra@connectit.local | password |
| Super Admin | super@connectit.local | password |
| Admin | admin@connectit.local | password |
| Agent | agent1@connectit.local | password |
| User | user@connectit.local | password |

---

## ⚙️ Environment Configuration

### Required
```env
APP_NAME="ConnectIT ITSM"
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_DATABASE=connectit_itsm
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Optional Integrations
```env
# Google Gemini AI (for Kiru AI assistant)
GEMINI_API_KEY=your_gemini_api_key

# Twilio WhatsApp
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=whatsapp:+14155238886

# SMTP Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_app_password
MAIL_FROM_ADDRESS=noreply@yourcompany.com
```

---

## 🏗️ Architecture

```
app/
├── Http/
│   ├── Controllers/          # 12 controllers (Auth, Ticket, Dashboard, SLA, etc.)
│   └── Middleware/           # CheckRole RBAC middleware
├── Models/                   # 20 Eloquent models
├── Services/                 # TicketService, OmniChannelService, AiService
├── Enums/                    # 12 PHP 8.1 enums (TicketStatus, UserRole, etc.)
├── Events/                   # TicketCreated, TicketAssigned, etc.
├── Listeners/                # SendTicketNotificationListener
└── Jobs/                     # SendEmailNotification, SendWhatsAppNotification

resources/views/
├── layouts/                  # app.blade.php, sidebar, header, chatbot
├── auth/                     # login, register, profile
├── tickets/                  # index, show, create
├── dashboard.blade.php       # Main dashboard with KPIs, charts, heatmap
├── sla/                      # SLA management
├── reports/                  # Analytics & reports
├── users/                    # User management
├── assets/                   # CMDB
├── problems/                 # Problem management
├── changes/                  # Change management
├── knowledge/                # Knowledge base
├── timesheets/               # Time tracking
└── settings/                 # System settings
```

---

## 🎯 Features

### Ticket Management
- Full incident lifecycle (New → Assigned → In Progress → Resolved → Closed)
- Priority matrix (Impact × Urgency = Priority)
- SLA response & resolution timers with real-time countdown
- SLA pause/resume on hold statuses
- Activity timeline (comments, work notes, status changes, emails)
- AI-powered suggestions via Google Gemini

### ITSM Modules
- **Problem Management** — Root cause analysis, workarounds
- **Change Management** — CAB workflow, risk assessment, rollback plans
- **CMDB** — Asset inventory with warranty tracking
- **Knowledge Base** — Self-service articles with ratings

### Analytics & Reporting
- Live dashboard with KPI metrics, heatmaps, leaderboards
- SLA compliance tracking
- Agent performance reports
- Priority distribution charts

### Notifications
- Email via SMTP (queued)
- WhatsApp via Twilio (queued)
- In-app notifications

### Security
- Laravel session authentication
- CSRF protection on all forms
- Role-based access control (6 levels)
- Bcrypt password hashing

---

## 🚢 Production Deployment

### Apache (Shared Hosting)
The `public/.htaccess` is pre-configured. Point your document root to `public/`.

### Nginx (VPS)
Use the included `nginx.conf` as a template. Replace `your-domain.com` with your actual domain.

### Queue Worker (for notifications)
```bash
php artisan queue:work --tries=5 --timeout=60
```

### Supervisor (keep queue running)
```ini
[program:connectit-queue]
command=php /var/www/connectit-laravel/artisan queue:work --tries=5
autostart=true
autorestart=true
user=www-data
```

### Cron (for scheduled tasks)
```cron
* * * * * cd /var/www/connectit-laravel && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔧 Artisan Commands

```bash
# Check omnichannel health
php artisan omnichannel:health

# Poll incoming emails
php artisan omnichannel:poll

# Test omnichannel connectivity
php artisan omnichannel:test

# Clear all caches
php artisan optimize:clear

# Run migrations fresh with seed
php artisan migrate:fresh --seed
```

---

## 📝 License
MIT License — ConnectIT ITSM
