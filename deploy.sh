#!/bin/bash

# URL Shortener Docker Deployment Script
# Usage: ./deploy.sh

set -e

echo "=== URL Shortener Docker Deployment ==="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/var/www/html/onion"
DOMAIN="chiba.tw"

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root${NC}"
    exit 1
fi

# Step 1: Install Docker if not present
if ! command -v docker &> /dev/null; then
    echo "Installing Docker..."
    curl -fsSL https://get.docker.com | sh
    systemctl enable docker
    systemctl start docker
fi

# Step 2: Navigate to project directory
cd "$PROJECT_DIR"

# Step 3: Pull latest code
echo "Pulling latest code from git..."
git pull origin master

# Step 4: Check for SSL certificates
SSL_CERT="/etc/letsencrypt/live/${DOMAIN}/fullchain.pem"
SSL_KEY="/etc/letsencrypt/live/${DOMAIN}/privkey.pem"

if [ ! -f "$SSL_CERT" ] || [ ! -f "$SSL_KEY" ]; then
    echo -e "${YELLOW}SSL certificates not found. Installing certbot and obtaining certificates...${NC}"

    # Install certbot if not present
    if ! command -v certbot &> /dev/null; then
        apt-get update
        apt-get install -y certbot
    fi

    # Stop any service using port 80
    systemctl stop nginx 2>/dev/null || true
    docker compose -f docker-compose.prod.yml down 2>/dev/null || true

    # Obtain certificate
    certbot certonly --standalone -d "$DOMAIN" --non-interactive --agree-tos --email admin@${DOMAIN}

    echo -e "${GREEN}SSL certificates obtained successfully${NC}"
fi

# Step 5: Check .env.prod exists
if [ ! -f ".env.prod" ]; then
    echo -e "${RED}Error: .env.prod not found!${NC}"
    echo "Please create .env.prod with the following required settings:"
    echo "  APP_URL=http://${DOMAIN}"
    echo "  GOOGLE_CLIENT_ID=your-client-id"
    echo "  GOOGLE_CLIENT_SECRET=your-client-secret"
    echo "  GOOGLE_REDIRECT=http://${DOMAIN}/login/oauth_back/google"
    exit 1
fi

# Check for required Google OAuth settings in .env.prod
if ! grep -q "GOOGLE_CLIENT_ID=." .env.prod; then
    echo -e "${RED}Error: GOOGLE_CLIENT_ID not set in .env.prod${NC}"
    exit 1
fi

# Step 6: Stop existing web services
echo "Stopping existing web services..."
systemctl stop nginx 2>/dev/null || true
systemctl stop php8.2-fpm 2>/dev/null || true
systemctl stop php-fpm 2>/dev/null || true

# Step 7: Build and start containers
echo "Building and starting Docker containers..."
docker compose -f docker-compose.prod.yml down 2>/dev/null || true
docker compose -f docker-compose.prod.yml build --no-cache
docker compose -f docker-compose.prod.yml up -d

# Step 8: Wait for container to be ready
echo "Waiting for container to start..."
sleep 5

# Step 9: Clear caches (don't use config:cache with env_file)
echo "Clearing application caches..."
docker exec url-shortener-app php artisan config:clear
docker exec url-shortener-app php artisan cache:clear
docker exec url-shortener-app php artisan route:clear
docker exec url-shortener-app php artisan view:clear

# Step 10: Run migrations
echo "Running database migrations..."
docker exec url-shortener-app php artisan migrate --force

# Step 11: Verify Google OAuth config
echo "Verifying Google OAuth configuration..."
GOOGLE_ID=$(docker exec url-shortener-app php artisan tinker --execute="echo config('services.google.client_id');" 2>/dev/null | tail -1)
if [ -z "$GOOGLE_ID" ] || [ "$GOOGLE_ID" == "" ]; then
    echo -e "${YELLOW}Warning: Google OAuth client_id may not be configured correctly${NC}"
else
    echo -e "${GREEN}Google OAuth configured: ${GOOGLE_ID:0:20}...${NC}"
fi

echo ""
echo -e "${GREEN}=== Deployment Complete ===${NC}"
echo "Application is running at: https://${DOMAIN}"
echo ""
docker compose -f docker-compose.prod.yml ps
