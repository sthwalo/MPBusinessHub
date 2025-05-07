#!/bin/bash

# Build React app
cd client
npm run build

# Commit and push changes
cd ..
git add .
git commit -m "Build for deployment"
git push origin main

# Output success message
echo "Changes pushed to repository. Check cPanel Git Version Control for deployment status."