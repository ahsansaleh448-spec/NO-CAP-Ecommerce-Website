#!/bin/bash
# Simple Linux Setup for NO CAP

echo "=== NO CAP Linux Setup ==="

# 1. Ensure uploads directory has write permissions
mkdir -p uploads
chmod 777 uploads
echo "[OK] uploads folder permissions set."

# 2. Import database.sql into MySQL
echo ""
read -p "Import database.sql into MySQL? (y/n): " ans
if [ "$ans" = "y" ] || [ "$ans" = "Y" ]; then
    read -p "Enter MySQL username [default: root]: " user
    user=${user:-root}
    mysql -u "$user" -p < database.sql
    echo "[OK] database.sql imported!"
fi

echo ""
echo "Setup complete! Run: ./run_dev.sh to start the website."
