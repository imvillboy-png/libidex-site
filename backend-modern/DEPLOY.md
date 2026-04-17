# Libidex Backend - Railway Deployment Guide

## Quick Deploy Steps:

1. **Go to Railway**: https://railway.app
2. **Login** with your account
3. **Click "New Project"** → Select "Empty Project"
4. **Click "Add GitHub Repo"** → Select this repo
5. **Click "Add Service"** → Select "Node"
6. **Configure**:
   - Root Directory: `backend-modern`
   - Build Command: (leave empty - auto-detect)
   - Start Command: `node server.js`
7. **Deploy** button click karo

## After Deploy:

Backend URL mil jayega (e.g., `https://backend-name.up.railway.app`)

Phir frontend update karna:
- `index.html` mein API URL change karna
- Admin panel mein API URL change karna