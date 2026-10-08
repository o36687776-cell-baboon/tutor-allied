#!/bin/bash

# PI Bootstrap Environment Setup
# This script sets up the complete development environment for Linux/Ubuntu servers

echo "🚀 Starting PI Bootstrap Environment Setup..."

# Update system packages
echo "📦 Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install required packages
echo "📦 Installing required packages..."
sudo apt install -y apache2 mysql-server php libapache2-mod-php php-mysql nodejs npm certbot python3-certbot-apache ufw

# Configure Apache
echo "🌐 Configuring Apache..."
sudo a2enmod rewrite
sudo systemctl restart apache2

# Set up MySQL database
echo "🗄️ Setting up MySQL database..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS pi_bootstrap;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'pi_boot_user'@'localhost' IDENTIFIED BY 'secure_password_here';"
sudo mysql -e "GRANT ALL PRIVILEGES ON pi_bootstrap.* TO 'pi_boot_user'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Install Docker
echo "🐳 Installing Docker..."
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# Configure firewall
echo "🔥 Configuring firewall..."
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 3307/tcp
sudo ufw allow 8081/tcp
sudo ufw allow 4001/tcp
echo "y" | sudo ufw --force enable

# Set up SSL certificate (optional - requires domain)
echo "🔒 Setting up SSL certificate..."
# Uncomment the line below and replace with your domain
# sudo certbot --apache -d pi.bootstrap.homework-help.online --email admin@homework-help.online --agree-tos --non-interactive

# Copy files to web directory (assuming files are in current directory)
echo "📁 Setting up web files..."
sudo mkdir -p /var/www/html/pi-bootstrap
sudo cp -r * /var/www/html/pi-bootstrap/
sudo chown -R www-data:www-data /var/www/html/pi-bootstrap
sudo chmod -R 755 /var/www/html/pi-bootstrap

# Start Docker services
echo "🐳 Starting Docker services..."
cd /var/www/html/pi-bootstrap
sudo docker-compose up -d

# Create Apache virtual host
echo "🌐 Creating Apache virtual host..."
sudo tee /etc/apache2/sites-available/pi-bootstrap.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerName pi.bootstrap.homework-help.online
    DocumentRoot /var/www/html/pi-bootstrap

    <Directory /var/www/html/pi-bootstrap>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/pi-bootstrap_error.log
    CustomLog \${APACHE_LOG_DIR}/pi-bootstrap_access.log combined
</VirtualHost>
EOF

sudo a2ensite pi-bootstrap.conf
sudo systemctl reload apache2

echo "✅ Setup complete!"
echo "🌐 Access your site at: http://pi.bootstrap.homework-help.online"
echo "🔒 For SSL, run: sudo certbot --apache -d pi.bootstrap.homework-help.online"
echo "🐳 Docker services running on ports: MySQL(3307), phpMyAdmin(8081), Webhook(4001)"
echo "🗄️ Database: pi_bootstrap, User: pi_boot_user, Password: secure_password_here"
