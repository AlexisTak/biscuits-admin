#!/bin/bash

# ============================================
# SETUP COMPLET PANEL ADMIN BISCUITS DEV
# ============================================


# 11. Clear cache
echo "🧹 Nettoyage du cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo "✓ Cache nettoyé"
echo ""
