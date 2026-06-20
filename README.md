# Tour De Roar - Updated Platform

A modernized payment and event management system for Tour de Roar cycling organization.

## Features

- **Event Registration** - Users can register and pay for cycling events
- **Online Store** - Merchandise sales with integrated payment processing
- **Donation System** - One-time and recurring donations with custom amounts
- **Sponsorship Packages** - Corporate sponsorship with tiered packages
- **User Accounts** - Registration, login, password reset, transaction history
- **Admin Dashboard** - Manage events, products, donations, sponsorships, orders, and users
- **Email Notifications** - Automated emails for registrations, receipts, admin notifications
- **Payment Integration** - Stripe payment processing with webhook confirmations

## Tech Stack

- **Backend**: PHP 8.1+, MySQL
- **Frontend**: HTML, CSS, JavaScript
- **Payment**: Stripe Payment Intent API
- **Email**: ZeptoMail (production), local logging (development)
- **Dependencies**: Stripe PHP SDK (via Composer)

## Setup Instructions

### Prerequisites

- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB
- Composer
- Apache/Nginx web server
- Stripe account (get test keys from [dashboard.stripe.com](https://dashboard.stripe.com/apikeys))

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/tourderoar/Tour-De-Roar-Update1.git
   cd Tour-De-Roar-Update1
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure the application**
   ```bash
   cp config.sample.php config.php
   ```
   
   Edit `config.php` and add your credentials:
   - Database credentials (DB_HOST, DB_NAME, DB_USER, DB_PASS)
   - Stripe API keys (get from [dashboard.stripe.com/apikeys](https://dashboard.stripe.com/apikeys))
   - Stripe webhook secret (create webhook endpoint first - see below)
   - ZeptoMail credentials (production only)

6. **Configure Stripe webhook** (Required for payment confirmations)
   - Go to [dashboard.stripe.com/webhooks](https://dashboard.stripe.com/webhooks)
   - Add endpoint: `https://yourdomain.com/api/webhook/stripe`
   - Select events: `payment_intent.succeeded`, `payment_intent.payment_failed`
   - Copy the webhook signing secret to `config.php` as `STRIPE_WEBHOOK_SECRET`

7. **Set permissions**
   ```bash
   chmod 755 logs/
   chmod 755 images/events/
   chmod 755 images/products/
   ```

### Local Development

For local development (XAMPP, MAMP, etc.):

1. Place the project in your web server directory (e.g., `htdocs/tour_update`)
2. Access via `http://localhost/tour_update` (or your configured path)
3. Emails will be logged to `logs/mail.log` instead of being sent
4. Use Stripe test keys for all transactions

### Production Deployment

1. Set environment variables in Apache/Nginx config:
   ```apache
   SetEnv APP_ENV "production"
   SetEnv APP_URL "https://tourderoar.org"
   SetEnv DB_HOST "your_db_host"
   SetEnv DB_NAME "your_db_name"
   SetEnv DB_USER "your_db_user"
   SetEnv DB_PASS "your_db_password"
   SetEnv STRIPE_PUBLIC_KEY "pk_live_YOUR_KEY"
   SetEnv STRIPE_SECRET_KEY "sk_live_YOUR_KEY"
   SetEnv STRIPE_WEBHOOK_SECRET "whsec_YOUR_SECRET"
   SetEnv ZEPTOMAIL_TOKEN "your_zeptomail_token"
   ```

2. Use Stripe **live** keys (not test keys)
3. Ensure webhook endpoint is accessible and properly configured
4. Test email delivery via ZeptoMail

## Project Structure

```
tour_update/
├── account/           # User account pages (login, register, dashboard)
├── admin/             # Admin dashboard pages
├── api/               # API endpoints
│   ├── auth/          # Authentication endpoints
│   ├── payments/      # Payment processing (events, donations, sponsorships, store)
│   ├── webhook/       # Stripe webhook handler
│   ├── user/          # User account management
│   └── admin/         # Admin data management
├── css/               # Stylesheets
├── images/            # Image uploads and static assets
│   ├── events/        # Event images and gallery (uploaded by admins)
│   ├── products/      # Product images (uploaded by admins)
│   └── logos/         # Static logos
├── includes/          # Core PHP utilities
│   ├── auth.php       # Authentication helpers
│   ├── db.php         # Database connection
│   ├── mail.php       # Email sending wrapper
│   ├── stripe.php     # Stripe client initialization
│   └── response.php   # JSON response helpers
├── js/                # JavaScript files
├── logs/              # Error and mail logs (gitignored)
├── vendor/            # Composer dependencies (gitignored)
├── config.php         # Configuration (gitignored - copy from config.sample.php)
├── config.sample.php  # Configuration template
└── composer.json      # PHP dependencies
```

## Database Schema

Key tables:
- `users` - User accounts
- `admins` - Admin accounts
- `events` - Cycling events
- `event_registrations` - Event sign-ups and payments
- `donation_types` - Predefined donation amounts
- `donation_payments` - Donation transactions
- `sponsorship_packages` - Sponsorship tiers
- `sponsorship_payments` - Sponsorship transactions
- `products` - Store merchandise
- `orders` - Store orders
- `order_items` - Individual items in orders
- `gallery_images` - Gallery image metadata

## Payment Flow

1. **Frontend**: User fills form, validates card with Stripe.js
2. **API**: Create PaymentIntent and database record with `stripe_payment_intent_id`
3. **Stripe**: User completes payment
4. **Webhook**: Stripe sends `payment_intent.succeeded` event
5. **Backend**: Update database status to 'completed', send confirmation emails
6. **Emails**: Receipt to user, notification to all admins


## Security Notes

- `config.php` is gitignored - never commit real credentials
- Stripe webhook uses signature verification for authenticity
- All payment endpoints require valid user sessions
- Admin routes protected with separate authentication
- Passwords hashed with `password_hash()`
- SQL queries use prepared statements

## Support

For issues or questions, contact the development team or open an issue on GitHub.

## License

Proprietary - Tour de Roar Organization
