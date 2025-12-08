# Render Environment Variables Setup Guide

## Problem
The database password authentication is failing because the `DB_PASSWORD` environment variable in Render is incorrect or contains extra characters.

## Solution: Update Environment Variables in Render

### Step 1: Go to Render Dashboard
1. Log in to [Render Dashboard](https://dashboard.render.com)
2. Click on your service: **it12**
3. Go to **Environment** tab (in the left sidebar)

### Step 2: Set/Update These Environment Variables

Based on your Neon database, set these **exact** values:

```env
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:6WO6VUaVhnx+6BKdFDkVJuoB8fF7ZEledWoTmxVP69g=
APP_DEBUG=false
APP_URL=https://it12.onrender.com

DB_CONNECTION=pgsql
DB_HOST=ep-ancient-star-a1rc0pwu-pooler.ap-southeast-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=npg_6TDIyzB9vaVj
DB_SSLMODE=require

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### Step 3: Important Notes

⚠️ **CRITICAL**: The `DB_PASSWORD` should be **ONLY** the password part:
- ✅ **Correct**: `npg_6TDIyzB9vaVj`
- ❌ **Wrong**: `endpoint=ep-ancient-star-a1rc0pwu;npg_6TDIyzB9vaVj`

The endpoint information is already in `DB_HOST`, so don't include it in the password!

### Step 4: Save and Redeploy

1. Click **Save Changes** after updating all variables
2. Render will automatically redeploy (or you can manually trigger a redeploy)
3. Wait 5-10 minutes for the deployment to complete
4. Check if the site loads correctly

### Step 5: Verify Database Connection

After deployment, if you still get errors:
1. Check Render logs for database connection errors
2. Verify your Neon database is accessible
3. Make sure the password in Neon matches what you set in Render

## Quick Checklist

- [ ] `DB_PASSWORD` contains ONLY the password (no endpoint info)
- [ ] `DB_HOST` is set correctly
- [ ] `DB_USERNAME` matches your Neon database user
- [ ] `DB_DATABASE` matches your Neon database name
- [ ] `DB_SSLMODE=require` is set
- [ ] `APP_ENV=production` and `APP_DEBUG=false`
- [ ] `APP_URL` matches your Render URL

## If Still Having Issues

1. Double-check your Neon database credentials in the Neon dashboard
2. Make sure your Neon database allows connections from Render's IPs
3. Try using the connection string format if needed (but parse it correctly)
