# Comprehensive Documentation - RDE Websites Platform

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Setup and Installation](#setup-and-installation)
3. [WordPress Platform](#wordpress-platform)
4. [Content Management Tools](#content-management-tools)
5. [Payment Integration](#payment-integration)
6. [Subscription Management](#subscription-management)
7. [Analytics Dashboard](#analytics-dashboard)
8. [Security Best Practices](#security-best-practices)
9. [Troubleshooting](#troubleshooting)
10. [API Reference](#api-reference)

## System Architecture

### Overview

The RDE Websites platform consists of three main components:

1. **WordPress Platform** - Core website with WooCommerce and Elementor
2. **Content Management Tools** - Standalone utilities for content management
3. **App + Marketing Integration** - Payment and subscription systems

### Technology Stack

```
Frontend:
- WordPress + Elementor
- JavaScript (ES6+)
- CSS3
- HTML5

Backend:
- PHP 8.0+
- Python 3.8+
- MySQL 8.0+

APIs & Integration:
- WooCommerce REST API
- WordPress REST API
- CCBill Payment Gateway
- Google Analytics 4
```

## Setup and Installation

### Prerequisites

Ensure you have the following installed:

```bash
# Check PHP version
php -v  # Should be 8.0 or higher

# Check MySQL version
mysql --version  # Should be 8.0 or higher

# Check Node.js version
node -v  # Should be 16 or higher

# Check Python version
python --version  # Should be 3.8 or higher

# Check Composer
composer --version
```

### Step-by-Step Installation

#### 1. Clone Repository

```bash
git clone https://github.com/rdhall1963/RDE-Websites.git
cd RDE-Websites
```

#### 2. Configure Environment

```bash
# Copy environment template
cp wordpress-platform/config/.env.template wordpress-platform/config/.env

# Edit environment variables
nano wordpress-platform/config/.env
```

#### 3. Install Dependencies

```bash
# Install all dependencies
npm run install:all

# Or install individually
cd wordpress-platform && npm install && composer install
cd ../content-management-tools && pip install -r requirements.txt
```

#### 4. Setup WordPress

```bash
cd wordpress-platform
chmod +x scripts/setup.sh
./scripts/setup.sh
```

#### 5. Configure Payment Gateway

```bash
# Edit CCBill configuration
nano app-marketing-integration/ccbill-integration/ccbill-config.js

# Update with your CCBill credentials:
# - Account Number
# - Sub-Account
# - Form Name
# - Salt Key
```

#### 6. Initialize Database

```bash
# Import subscription tables
mysql -u root -p your_database < app-marketing-integration/subscription-manager/install.sql
```

## WordPress Platform

### Theme Development

The custom theme is located in `wordpress-platform/themes/custom-theme/`.

#### Theme Structure

```
custom-theme/
├── style.css              # Main stylesheet
├── functions.php          # Theme functions
├── index.php              # Main template
├── header.php             # Header template
├── footer.php             # Footer template
├── inc/                   # Include files
│   ├── customizer.php     # Customizer settings
│   ├── template-tags.php  # Template functions
│   ├── woocommerce.php    # WooCommerce customizations
│   └── elementor.php      # Elementor customizations
└── assets/                # Theme assets
    ├── css/               # Stylesheets
    ├── js/                # JavaScript files
    └── images/            # Images
```

#### Adding Custom Widgets

1. Create widget file in `inc/widgets/`
2. Register widget in `functions.php`:

```php
require_once get_template_directory() . '/inc/widgets/custom-widget.php';

add_action('widgets_init', function() {
    register_widget('Custom_Widget');
});
```

### Plugin Development

Custom plugins are located in `wordpress-platform/plugins/custom-plugins/`.

#### Plugin Structure

```
rde-custom-integrations/
├── rde-custom-integrations.php  # Main plugin file
├── includes/                     # Core functionality
│   ├── class-analytics.php
│   ├── class-seo.php
│   ├── woocommerce/
│   └── elementor/
├── admin/                        # Admin interface
│   ├── css/
│   ├── js/
│   └── views/
└── assets/                       # Frontend assets
    ├── css/
    └── js/
```

### WooCommerce Configuration

#### Payment Gateway Setup

1. Navigate to WooCommerce > Settings > Payments
2. Enable CCBill gateway
3. Configure settings:
   - Account Number
   - Sub-Account
   - Form Name
   - Currency Code
   - Salt Key
   - Enable Test Mode (for testing)

#### Product Configuration for Subscriptions

```php
// Add subscription meta to products
update_post_meta($product_id, '_subscription_price', '29.99');
update_post_meta($product_id, '_subscription_period', 'month');
update_post_meta($product_id, '_subscription_period_interval', '1');
```

## Content Management Tools

### Gallery Manager

#### Basic Usage

```bash
cd content-management-tools/gallery

# Process images
python gallery_manager.py /path/to/source /path/to/output

# This will:
# - Create thumbnails (300x300)
# - Create medium size (800x800)
# - Create large size (1920x1920)
# - Optimize all images
# - Generate gallery.html
# - Create gallery-metadata.json
```

#### Advanced Options

```python
from gallery_manager import GalleryManager

# Create manager instance
manager = GalleryManager('source_dir', 'output_dir')

# Process images with custom quality
manager.optimize_image(
    'image.jpg',
    'output.jpg',
    max_size=(1200, 1200),
    quality=90
)

# Generate HTML gallery
manager.generate_html()
```

### File Organizer

#### Basic Usage

```bash
cd content-management-tools/file-organizer

# Find duplicates
python file_organizer.py /path/to/files --find-duplicates

# Organize by type
python file_organizer.py /path/to/files --organize /path/to/output

# Add date prefixes
python file_organizer.py /path/to/files --add-dates

# Generate report
python file_organizer.py /path/to/files --report
```

#### File Organization Categories

- **images**: .jpg, .jpeg, .png, .gif, .bmp, .svg, .webp
- **documents**: .pdf, .doc, .docx, .txt, .rtf, .odt
- **videos**: .mp4, .avi, .mov, .wmv, .flv, .mkv
- **audio**: .mp3, .wav, .flac, .aac, .ogg, .m4a
- **archives**: .zip, .rar, .7z, .tar, .gz
- **code**: .php, .js, .css, .html, .py, .java, .cpp

### Email Automation

#### Setup SMTP

```bash
cd content-management-tools/email-automation

# Configure SMTP
python email_automation.py --setup \
  --host smtp.gmail.com \
  --port 587 \
  --username your-email@gmail.com \
  --password your-password

# Test connection
python email_automation.py --test

# Generate WordPress config
python email_automation.py --generate-wp-config
```

#### Using Email Templates

```html
<!-- templates/welcome-email.html -->
<!-- Variables: {{name}}, {{verification_link}}, {{unsubscribe_link}} -->
```

Replace variables in your code:

```php
$template = file_get_contents('templates/welcome-email.html');
$html = str_replace(
    array('{{name}}', '{{verification_link}}'),
    array($user_name, $verification_url),
    $template
);
```

## Payment Integration

### CCBill Setup

#### 1. Create CCBill Account

1. Sign up at [CCBill](https://www.ccbill.com)
2. Complete merchant verification
3. Get your account credentials

#### 2. Configure Integration

```javascript
// ccbill-config.js
const CCBILL_CONFIG = {
    account: {
        accountNumber: 'YOUR_ACCOUNT_NUMBER',
        subAccount: 'YOUR_SUBACCOUNT',
        formName: 'YOUR_FORM_NAME',
        salt: 'YOUR_SALT_KEY',
        currencyCode: '840', // USD
    }
};
```

#### 3. Setup Webhooks

Configure webhook URL in CCBill admin:
```
Production: https://your-domain.com/wp-json/rde/v1/webhook/ccbill
Sandbox: https://your-domain.com/wp-json/rde/v1/webhook/ccbill
```

#### 4. Test Integration

1. Enable test mode in WooCommerce settings
2. Create a test product
3. Process a test order
4. Verify webhook receives data

### Payment Flow

```
1. Customer adds product to cart
2. Customer proceeds to checkout
3. CCBill gateway generates payment URL
4. Customer redirected to CCBill
5. Customer completes payment
6. CCBill sends webhook to site
7. Order marked as complete
8. Subscription created (if applicable)
```

## Subscription Management

### Creating Subscriptions

```php
$subscription_manager = RDE_Subscription_Manager::get_instance();

$subscription_id = $subscription_manager->create_subscription(array(
    'user_id'           => 123,
    'product_id'        => 456,
    'order_id'          => 789,
    'amount'            => 29.99,
    'billing_period'    => 'month',
    'billing_interval'  => 1,
));
```

### Managing Subscriptions

```php
// Cancel subscription
$subscription_manager->cancel_subscription($subscription_id);

// Pause subscription
$subscription_manager->pause_subscription($subscription_id);

// Resume subscription
$subscription_manager->resume_subscription($subscription_id);

// Get user subscriptions
$subscriptions = $subscription_manager->get_user_subscriptions($user_id, 'active');
```

### Subscription Lifecycle

1. **Active** - Subscription is active and renewing
2. **Paused** - Temporarily suspended by user
3. **Cancelled** - Cancelled by user or admin
4. **Expired** - Reached end date without renewal
5. **Pending** - Awaiting first payment

### Automatic Renewals

Renewals are checked daily via WordPress cron:

```php
// Force manual check
do_action('rde_check_subscription_renewals');
do_action('rde_check_subscription_expirations');
```

## Analytics Dashboard

### Accessing Dashboard

Open `app-marketing-integration/analytics-dashboard/dashboard.html` in a browser.

### Key Metrics

- **Total Revenue** - All-time revenue
- **Active Subscriptions** - Current active subscribers
- **Churn Rate** - Percentage of cancelled subscriptions
- **ARPU** - Average Revenue Per User

### Integrating with Your Site

```javascript
// Fetch data from WordPress REST API
fetch('/wp-json/rde/v1/analytics')
    .then(response => response.json())
    .then(data => updateDashboard(data));
```

## Security Best Practices

### Environment Variables

Never commit sensitive data. Use environment variables:

```bash
# .env
DB_PASSWORD=secure_password
CCBILL_SALT=secret_salt_key
SMTP_PASSWORD=email_password
```

### WordPress Security

1. **Use strong passwords**
2. **Keep WordPress updated**
3. **Install security plugins** (Wordfence, iThemes Security)
4. **Enable SSL/HTTPS**
5. **Disable file editing**: `define('DISALLOW_FILE_EDIT', true);`
6. **Regular backups**

### Payment Security

1. **Never store credit card data**
2. **Use CCBill tokenization**
3. **Verify webhook signatures**
4. **Use HTTPS for all transactions**
5. **PCI compliance**

## Troubleshooting

### Common Issues

#### WordPress Installation Fails

```bash
# Check permissions
chmod -R 755 wordpress/
chown -R www-data:www-data wordpress/

# Check database connection
mysql -u username -p database_name
```

#### CCBill Webhook Not Receiving

1. Check webhook URL is correct
2. Verify SSL certificate is valid
3. Check server logs for errors
4. Test webhook manually with curl:

```bash
curl -X POST https://your-domain.com/wp-json/rde/v1/webhook/ccbill \
  -H "Content-Type: application/json" \
  -d '{"eventType":"NewSaleSuccess","order_id":123}'
```

#### Subscription Not Renewing

```bash
# Check WordPress cron
wp cron event list

# Run cron manually
wp cron event run rde_check_subscription_renewals
```

## API Reference

### REST API Endpoints

#### Subscriptions

```
GET    /wp-json/rde/v1/subscriptions
GET    /wp-json/rde/v1/subscriptions/{id}
POST   /wp-json/rde/v1/subscriptions
PUT    /wp-json/rde/v1/subscriptions/{id}
DELETE /wp-json/rde/v1/subscriptions/{id}
```

#### Webhooks

```
POST /wp-json/rde/v1/webhook/ccbill
POST /wp-json/rde/v1/webhook/subscription
POST /wp-json/rde/v1/webhook/analytics
```

#### Analytics

```
GET /wp-json/rde/v1/analytics
GET /wp-json/rde/v1/analytics/revenue
GET /wp-json/rde/v1/analytics/subscriptions
GET /wp-json/rde/v1/analytics/churn
```

### WordPress Actions and Filters

#### Actions

```php
// Subscription lifecycle
do_action('rde_subscription_created', $subscription_id, $args);
do_action('rde_subscription_status_changed', $subscription_id, $old_status, $new_status);
do_action('rde_subscription_cancelled', $subscription_id);
do_action('rde_subscription_renewed', $subscription_id, $user_id, $amount);

// Webhook events
do_action('rde_webhook_subscription_created', $subscription_id, $params);
do_action('rde_webhook_subscription_updated', $subscription_id, $params);
```

#### Filters

```php
// Modify subscription data
$subscription_data = apply_filters('rde_subscription_data', $data, $subscription_id);

// Modify payment URL
$payment_url = apply_filters('rde_ccbill_payment_url', $url, $order);
```

---

For more information, visit the [GitHub repository](https://github.com/rdhall1963/RDE-Websites) or create an issue.
