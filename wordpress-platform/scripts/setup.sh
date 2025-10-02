#!/bin/bash

# WordPress Platform Setup Script
# This script sets up the WordPress platform with all required components

set -e

echo "========================================="
echo "RDE WordPress Platform Setup"
echo "========================================="

# Check for required tools
command -v php >/dev/null 2>&1 || { echo "PHP is required but not installed. Aborting." >&2; exit 1; }
command -v mysql >/dev/null 2>&1 || { echo "MySQL is required but not installed. Aborting." >&2; exit 1; }

# Load environment variables
if [ -f .env ]; then
    source .env
else
    echo "Warning: .env file not found. Using defaults."
fi

# Create database if it doesn't exist
echo "Setting up database..."
mysql -u root -p"${DB_ROOT_PASSWORD:-root}" -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME:-wordpress_db} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p"${DB_ROOT_PASSWORD:-root}" -e "CREATE USER IF NOT EXISTS '${DB_USER:-wordpress_user}'@'localhost' IDENTIFIED BY '${DB_PASSWORD:-wordpress}';"
mysql -u root -p"${DB_ROOT_PASSWORD:-root}" -e "GRANT ALL PRIVILEGES ON ${DB_NAME:-wordpress_db}.* TO '${DB_USER:-wordpress_user}'@'localhost';"
mysql -u root -p"${DB_ROOT_PASSWORD:-root}" -e "FLUSH PRIVILEGES;"

# Download WordPress core
if [ ! -d "wordpress" ]; then
    echo "Downloading WordPress..."
    wget https://wordpress.org/latest.tar.gz
    tar -xzf latest.tar.gz
    rm latest.tar.gz
fi

# Copy configuration
echo "Configuring WordPress..."
if [ ! -f "wordpress/wp-config.php" ]; then
    cp config/wp-config-template.php wordpress/wp-config.php
    # Update database credentials in wp-config.php
    sed -i "s/wordpress_db/${DB_NAME:-wordpress_db}/g" wordpress/wp-config.php
    sed -i "s/wordpress_user/${DB_USER:-wordpress_user}/g" wordpress/wp-config.php
    sed -i "s/your_password_here/${DB_PASSWORD:-wordpress}/g" wordpress/wp-config.php
fi

# Install Composer dependencies
echo "Installing Composer dependencies..."
composer install

# Install npm dependencies
echo "Installing npm dependencies..."
npm install

# Install WP-CLI if not present
if ! command -v wp &> /dev/null; then
    echo "Installing WP-CLI..."
    curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
    chmod +x wp-cli.phar
    sudo mv wp-cli.phar /usr/local/bin/wp
fi

# Install WordPress
echo "Installing WordPress core..."
cd wordpress
wp core install --url="${SITE_URL:-http://localhost}" \
                --title="${SITE_TITLE:-RDE Website}" \
                --admin_user="${ADMIN_USER:-admin}" \
                --admin_password="${ADMIN_PASSWORD:-admin}" \
                --admin_email="${ADMIN_EMAIL:-admin@example.com}" \
                --skip-email

# Install WooCommerce
echo "Installing WooCommerce..."
wp plugin install woocommerce --activate

# Install Elementor
echo "Installing Elementor..."
wp plugin install elementor --activate

# Install WPML or Polylang for multilingual
echo "Installing multilingual support..."
wp plugin install polylang --activate

# Install Yoast SEO
echo "Installing Yoast SEO..."
wp plugin install wordpress-seo --activate

# Install analytics plugins
echo "Installing analytics plugins..."
wp plugin install google-site-kit --activate
wp plugin install pixelyoursite --activate

cd ..

echo "========================================="
echo "Setup complete!"
echo "========================================="
echo "Site URL: ${SITE_URL:-http://localhost}"
echo "Admin User: ${ADMIN_USER:-admin}"
echo "Admin Password: ${ADMIN_PASSWORD:-admin}"
echo ""
echo "Next steps:"
echo "1. Configure WooCommerce settings"
echo "2. Set up Elementor templates"
echo "3. Configure multilingual settings"
echo "4. Connect Google Analytics and other tracking"
echo "========================================="
