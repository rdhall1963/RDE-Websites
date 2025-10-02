# Quick Start Guide - RDE Websites

Get up and running with RDE-Websites in under 30 minutes!

## Prerequisites Check

Before starting, ensure you have:

```bash
# Check all prerequisites
php -v        # Should be 8.0+
mysql --version   # Should be 8.0+
node -v       # Should be 16+
npm -v
python --version  # Should be 3.8+
composer --version
```

## 5-Minute Setup

### Step 1: Clone and Navigate
```bash
git clone https://github.com/rdhall1963/RDE-Websites.git
cd RDE-Websites
```

### Step 2: Quick Install
```bash
# Install all dependencies at once
npm run install:all
```

### Step 3: Configure Environment
```bash
# Copy and edit environment file
cp wordpress-platform/config/.env.template wordpress-platform/config/.env
nano wordpress-platform/config/.env
```

**Minimum required settings:**
```env
DB_NAME=wordpress_db
DB_USER=wordpress_user
DB_PASSWORD=your_secure_password
SITE_URL=http://localhost:8000
ADMIN_EMAIL=admin@example.com
```

### Step 4: Run Setup
```bash
cd wordpress-platform
chmod +x scripts/setup.sh
./scripts/setup.sh
```

The setup script will:
- Create database
- Download WordPress
- Install core plugins
- Configure settings
- Set up WooCommerce
- Install Elementor

## What You Get

After setup, you'll have:

### 1. WordPress Admin
Access at: `http://localhost:8000/wp-admin`
- Username: admin (or what you set in .env)
- Password: Set during setup

### 2. Pre-installed Plugins
- ✅ WooCommerce (e-commerce)
- ✅ Elementor (page builder)
- ✅ Polylang (multilingual)
- ✅ Yoast SEO (SEO optimization)
- ✅ Google Site Kit (analytics)
- ✅ RDE Custom Integrations (custom features)

### 3. Content Management Tools
Located in `content-management-tools/`:

**Gallery Manager:**
```bash
cd content-management-tools/gallery
python gallery_manager.py /path/to/images /path/to/output
```

**File Organizer:**
```bash
cd content-management-tools/file-organizer
python file_organizer.py /path/to/files --organize /output
```

**Email Setup:**
```bash
cd content-management-tools/email-automation
python email_automation.py --setup \
  --host smtp.gmail.com \
  --port 587 \
  --username your@email.com \
  --password your_password
```

### 4. Payment Integration (Optional)

If you have CCBill credentials:

```bash
# Edit configuration
nano app-marketing-integration/ccbill-integration/ccbill-config.js
```

Update with your credentials:
- Account Number
- Sub-Account
- Form Name
- Salt Key

Then in WordPress Admin:
1. Go to WooCommerce > Settings > Payments
2. Enable CCBill
3. Enter your credentials
4. Save changes

### 5. Subscription System

Install subscription tables:
```bash
mysql -u root -p wordpress_db < app-marketing-integration/subscription-manager/install.sql
```

### 6. Analytics Dashboard

Open in browser:
```
app-marketing-integration/analytics-dashboard/dashboard.html
```

## Common First Tasks

### Create Your First Product

1. Go to WooCommerce > Products > Add New
2. Add product details
3. Set price
4. For subscriptions, check "Subscription" option
5. Publish

### Customize with Elementor

1. Pages > Add New
2. Click "Edit with Elementor"
3. Drag and drop widgets
4. Customize design
5. Publish

### Add Multilingual Support

1. Go to Languages > Settings
2. Add your languages
3. Translate content in Polylang interface

### Set Up Analytics

1. Go to Site Kit > Settings
2. Connect Google Account
3. Enable Analytics
4. Configure tracking

## Testing Your Setup

### Test WordPress
```bash
# Visit your site
open http://localhost:8000
```

### Test Admin Panel
```bash
# Login to admin
open http://localhost:8000/wp-admin
```

### Test WooCommerce
1. Add a product
2. View shop page
3. Add to cart
4. Test checkout process

### Test Content Tools
```bash
# Gallery - use sample images
cd content-management-tools/gallery
mkdir test-images test-output
# Add some images to test-images/
python gallery_manager.py test-images test-output

# File Organizer
cd ../file-organizer
python file_organizer.py /path/to/test/files --report
```

## Development Workflow

### Making Changes

```bash
# Edit theme
cd wordpress-platform/themes/custom-theme
# Make changes to style.css or functions.php

# Build assets (if using build tools)
npm run build

# Clear cache
wp cache flush
```

### Adding Features

1. Edit plugin in `wordpress-platform/plugins/custom-plugins/`
2. Add functionality
3. Test in WordPress admin
4. Commit changes

### Testing

```bash
# Run tests
npm test

# Lint code
npm run lint
```

## Troubleshooting

### Port Already in Use
```bash
# Change SITE_URL in .env
SITE_URL=http://localhost:8080
```

### Permission Errors
```bash
# Fix WordPress permissions
sudo chown -R www-data:www-data wordpress/
sudo chmod -R 755 wordpress/
```

### Database Connection Failed
```bash
# Test database connection
mysql -u wordpress_user -p wordpress_db
# If fails, check credentials in .env
```

### Plugins Not Installing
```bash
# Install manually with WP-CLI
wp plugin install woocommerce --activate
```

## Next Steps

Now that you're set up:

1. **Read Full Documentation**: [docs/DOCUMENTATION.md](docs/DOCUMENTATION.md)
2. **Customize Theme**: Edit `wordpress-platform/themes/custom-theme/`
3. **Add Products**: Set up your product catalog
4. **Configure Payments**: Set up CCBill or other gateways
5. **Set Up Subscriptions**: Configure subscription products
6. **Launch Analytics**: Set up tracking and dashboards
7. **Go Live**: Deploy to production server

## Getting Help

- 📖 Read the [Full Documentation](docs/DOCUMENTATION.md)
- 🐛 Report issues on [GitHub](https://github.com/rdhall1963/RDE-Websites/issues)
- 💬 Check existing issues for solutions
- 📧 Contact support

## Useful Commands

```bash
# WordPress
wp --info                 # Check WP-CLI
wp cache flush           # Clear cache
wp plugin list           # List plugins
wp theme list            # List themes

# Development
npm run dev              # Start dev server
npm run build            # Build for production
npm run lint             # Check code style

# Database
wp db export backup.sql  # Backup database
wp db import backup.sql  # Restore database

# Content Tools
python gallery_manager.py --help
python file_organizer.py --help
python email_automation.py --help
```

## Resources

- [WordPress Codex](https://codex.wordpress.org/)
- [WooCommerce Docs](https://woocommerce.com/documentation/)
- [Elementor Docs](https://elementor.com/help/)
- [CCBill Integration](https://www.ccbill.com/doc/)

---

**Ready to build something amazing!** 🚀

For detailed information, see the [complete documentation](docs/DOCUMENTATION.md).
