#!/bin/bash

# Exit on error
set -e

# Check if we're in the right directory
if [ ! -d "client" ] || [ ! -d "server" ]; then
    echo "Error: Must be run from project root directory"
    exit 1
fi

# Build React app
echo "Building React application..."
cd client
npm install
npm run build
cd ..

# Stage changes
echo "Staging changes..."
git add client/dist
git add .cpanel.yml

# Commit and push changes
echo "Committing and pushing changes..."
git commit -m "Build for deployment $(date '+%Y-%m-%d %H:%M:%S')"
git push origin main

# Output success message
echo "✅ Changes pushed to repository."
echo "📝 Check cPanel Git Version Control for deployment status:"
echo "   https://mpbusinesshub.co.za/cpsess*/gitrepo/MPBusinessHub"