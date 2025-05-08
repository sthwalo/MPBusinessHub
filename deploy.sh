#!/bin/bash

# Exit on error
set -e

# Ensure clean build
echo "🧹 Cleaning previous builds..."
rm -rf client/dist

# Install and build
echo "🏗️ Building React application..."
cd client
npm install
npm run build
cd ..

# Commit only deployment configs
echo "📦 Staging deployment files..."
git add cpanel.yml
git add server/
git add -f client/dist  # Force add dist directory just for deployment

# Commit and push
echo "🚀 Pushing to Afrihost repository..."
git commit -m "Deploy: $(date '+%Y-%m-%d %H:%M:%S')"
git push origin main

# Clean up - remove dist from git but keep files
echo "🧹 Cleaning up..."
git reset --soft HEAD~1
git restore --staged client/dist