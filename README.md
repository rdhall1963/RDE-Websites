# RDE-Websites

**Build websites, games & technical services**

A comprehensive full-stack development platform for building modern websites with WordPress/WooCommerce, custom content management tools, and integrated payment & subscription systems.

## 🚀 Overview

RDE-Websites is a complete solution for building and managing professional websites with e-commerce capabilities, multilingual support, advanced SEO, and subscription management.

### Key Features

- **WordPress/WooCommerce Platform** - Complete e-commerce solution with Elementor customization
- **Content Management Tools** - Code snippets, gallery management, file organization, and email automation
- **App + Marketing Integration** - CCBill payments, subscription management, and analytics dashboards

## 📁 Project Structure

```
RDE-Websites/
├── wordpress-platform/          # WordPress/WooCommerce with Elementor
│   ├── themes/                  # Custom themes
│   ├── plugins/                 # Custom plugins
│   ├── config/                  # Configuration files
│   └── scripts/                 # Setup and deployment scripts
│
├── content-management-tools/    # Content management utilities
│   ├── snippets/                # Reusable code snippets (PHP, JS, CSS)
│   ├── gallery/                 # Gallery management tools
│   ├── file-organizer/          # File organization automation
│   └── email-automation/        # Email setup and templates
│
├── app-marketing-integration/   # Payment and subscription integration
│   ├── ccbill-integration/      # CCBill payment gateway
│   ├── subscription-manager/    # Automated subscription management
│   ├── analytics-dashboard/     # Analytics and reporting
│   └── webhooks/                # Webhook handlers
│
├── docs/                        # Documentation
└── config/                      # Global configuration
```

## 🛠️ Technology Stack

### WordPress Platform
- **WordPress** - Latest version with security best practices
- **WooCommerce** - E-commerce functionality
- **Elementor Pro** - Page builder for custom designs
- **WPML/Polylang** - Multilingual support
- **Yoast SEO Premium** - Advanced SEO optimization
- **Google Analytics 4** - Analytics integration
- **PHP 8.0+** - Backend language
- **MySQL 8.0+** - Database

### Content Management
- **Python 3** - Automation scripts
- **PIL/Pillow** - Image processing
- **JavaScript** - Frontend utilities
- **HTML/CSS** - Email templates

### Payment & Subscriptions
- **CCBill** - Payment gateway integration
- **Custom Subscription System** - Automated lifecycle management
- **REST API** - Webhook integrations
- **Chart.js** - Analytics visualization

## 🚦 Quick Start

### Prerequisites

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Node.js 16+ and npm
- Python 3.8+
- Composer

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/rdhall1963/RDE-Websites.git
cd RDE-Websites
```

2. **Setup WordPress Platform**
```bash
cd wordpress-platform
cp config/.env.template config/.env
# Edit config/.env with your settings
chmod +x scripts/setup.sh
./scripts/setup.sh
```

3. **Install Dependencies**
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Install Python dependencies (for tools)
cd ../content-management-tools
pip install -r requirements.txt
```

4. **Configure Payment Integration**
```bash
cd ../app-marketing-integration/ccbill-integration
# Edit ccbill-config.js with your CCBill credentials
```

## 📖 Documentation

Detailed documentation is available in each module:

- [WordPress Platform Guide](./wordpress-platform/README.md)
- [Content Management Tools](./content-management-tools/README.md)
- [App + Marketing Integration](./app-marketing-integration/README.md)

## 🎯 Features

### WordPress Development Services Platform

✅ **WooCommerce Integration**
- Full e-commerce capabilities
- Product management
- Order processing
- Payment gateways

✅ **Elementor Customization**
- Custom widgets
- Page templates
- Responsive designs

✅ **Multilingual Support**
- WPML/Polylang integration
- Translation management
- RTL support

✅ **SEO & Analytics**
- Yoast SEO Premium
- Google Analytics 4
- Google Tag Manager
- Facebook Pixel

### Content Management Tools

✅ **Code Snippets Library**
- PHP snippets for WordPress
- JavaScript utilities
- CSS utility classes
- Version controlled

✅ **Gallery Management**
- Automated image optimization
- Multiple size generation
- Gallery template creation
- Responsive layouts

✅ **File Organization**
- Automated file naming
- Duplicate detection
- Category-based organization
- Batch processing

✅ **Email Automation**
- SMTP configuration
- Professional email templates
- WordPress integration
- Transactional emails

### App + Marketing Project

✅ **CCBill Payment Integration**
- Secure payment processing
- Recurring billing support
- Webhook notifications
- PCI compliance

✅ **Subscription Management**
- Automated lifecycle management
- Renewal notifications
- Cancellation handling
- Grace periods
- Upgrade/downgrade workflows

✅ **Analytics Dashboard**
- Revenue tracking
- Subscriber metrics
- Churn analysis
- Custom reporting
- Real-time updates

## 🔧 Configuration

### Environment Variables

Create a `.env` file in each module with the following structure:

```bash
# Database
DB_NAME=your_database
DB_USER=your_user
DB_PASSWORD=your_password

# WordPress
SITE_URL=https://your-domain.com
ADMIN_EMAIL=admin@your-domain.com

# CCBill
CCBILL_ACCOUNT=your_account_number
CCBILL_SUBACCOUNT=your_subaccount
CCBILL_SALT=your_salt_key

# Email
SMTP_HOST=smtp.your-provider.com
SMTP_PORT=587
SMTP_USER=your_email@domain.com
SMTP_PASS=your_password
```

## 🧪 Testing

```bash
# Run PHP tests
cd wordpress-platform
composer test

# Run JavaScript tests
npm test

# Run linters
composer lint
npm run lint
```

## 📦 Deployment

1. **Build assets**
```bash
npm run build
```

2. **Deploy to production**
```bash
./scripts/deploy.sh production
```

3. **Configure webhooks**
- Set up CCBill webhook URL: `https://your-domain.com/wp-json/rde/v1/webhook/ccbill`
- Configure webhook signatures

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the GPL-3.0 License - see the LICENSE file for details.

## 👤 Author

**RDE Websites**
- GitHub: [@rdhall1963](https://github.com/rdhall1963)

## 🙏 Acknowledgments

- WordPress Community
- WooCommerce
- Elementor
- CCBill
- All contributors

## 📞 Support

For questions and support:
- Create an issue in the GitHub repository
- Check the documentation in each module
- Contact the development team

---

**Built with ❤️ by RDE Websites**