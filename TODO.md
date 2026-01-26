# Setup PostgreSQL for Railway Deployment

## Steps to Complete:

1. **Create PostgreSQL Database on Railway**
    - Go to your Railway project dashboard
    - Add a new PostgreSQL database service
    - Get the EXTERNAL connection details (not internal) from the database service

2. **Set Environment Variables in Railway**
    - In your Railway service settings, add these environment variables:
        - `DB_CONNECTION=pgsql`
        - `DB_HOST=<external_postgres_host>` (use the public/external host, not postgres.railway.internal)
        - `DB_PORT=<your_postgres_port>`
        - `DB_DATABASE=<your_postgres_database>`
        - `DB_USERNAME=<your_postgres_username>`
        - `DB_PASSWORD=<your_postgres_password>`
    - Also ensure `APP_KEY` is set (generate with `php artisan key:generate`)

3. **Update Application Configuration**
    - [x] Updated Dockerfile to include pdo_pgsql extension
    - [x] Updated nixpacks.toml to include PostgreSQL support

4. **Deploy and Test**
    - Redeploy your application on Railway
    - Check the deploy logs for any database connection errors
    - Test that the application can connect to the database and run migrations

## Current Issue:

- Error: "could not translate host name 'postgres.railway.internal' to address: Unknown host"
- **Solution**: Use the EXTERNAL PostgreSQL connection details instead of the internal hostname
- From your Railway variables, use DATABASE_PUBLIC_URL for the connection

## Environment Variables to Set:

Based on your Railway PostgreSQL variables, set these in your app service (use individual variables, NOT DATABASE_URL):

- `DB_CONNECTION=pgsql`
- `DB_HOST=metro.proxy.rlwy.net` (extracted from DATABASE_PUBLIC_URL)
- `DB_PORT=36087` (extracted from DATABASE_PUBLIC_URL)
- `DB_DATABASE=railway`
- `DB_USERNAME=postgres`
- `DB_PASSWORD=evZkbTYWPEKspaUhrfAOpTgQAXAYZxvG`
- Also ensure `APP_KEY` is set (generate with `php artisan key:generate`)

**Important**: Do NOT set `DB_HOST` to the full `DATABASE_PUBLIC_URL` - it should be just the domain part.

## Notes:

- The application defaults to SQLite locally, but will use PostgreSQL on Railway via environment variables
- Make sure to run `php artisan migrate` after deployment to create the database tables
- If you encounter issues, check Railway's deploy logs for specific error messages
