#!/bin/bash

# Exit on error
set -e

# Check if we're in the right directory
if [ ! -d "client" ] || [ ! -d "server" ]; then
    echo "Error: Must be run from project root directory"
    exit 1
fi

# Build React app
echo "🏗️  Building React application..."
cd client
npm install
npm run build
cd ..

# Check if build directory exists
if [ ! -d "client/dist" ]; then
    echo "❌ Build failed - dist directory not found"
    exit 1
fi

# Stage changes
echo "📦 Staging changes..."
git add client/dist
git add .cpanel.yml
git add server/

# Commit and push changes
echo "🚀 Committing and pushing changes..."
git commit -m "Build for deployment: $(date '+%Y-%m-%d %H:%M:%S')"
git push origin main

# Output success message with clear next steps
echo ""
echo "✅ Changes pushed to GitHub successfully!"
echo ""
echo "Next steps:"
echo "1. Go to cPanel → Git Version Control"
echo "2. Select 'MPBusinessHub' repository"
echo "3. Click 'Update from Remote' if not automatically updated"
echo "4. Check deployment logs for status"
echo ""
echo "🌐 cPanel URL: https://mpbusinesshub.co.za/cpanel"
echo "📁 Repository: MPBusinessHub"