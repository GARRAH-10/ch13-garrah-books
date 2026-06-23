# Production Deployment

This route uses Railway for the Slim API and MySQL, and Netlify for Vue.
Keep the API and database in one Railway project so they can communicate
privately.

## A. Put the package on GitHub

Create one private or public repository and upload this folder. Never upload:

- `books-api/.env`
- `books-api/vendor`
- `frontend/node_modules`
- `frontend/dist`

## B. Create MySQL on Railway

1. Create an empty Railway project.
2. Add a MySQL database service.
3. Open the MySQL service and locate its connection variables.
4. Enable its TCP proxy/public connection temporarily for HeidiSQL.
5. In HeidiSQL, connect using Railway's public host, public port, user and
   password.
6. Execute `books-api/sql/schema.sql`.
7. Show the `users`, `books` and `audit_log` tables.

## C. Deploy the Slim API on Railway

1. Add a service from the GitHub repository.
2. Set its root directory to `/books-api`.
3. Railway detects the root `Dockerfile`.
4. Add these service variables:

```text
DB_HOST=<Railway MySQL private host>
DB_PORT=<Railway MySQL private port>
DB_NAME=<Railway MySQL database>
DB_USER=<Railway MySQL user>
DB_PASS=<Railway MySQL password>
DB_CHARSET=utf8mb4
JWT_SECRET=<64-character random value>
JWT_TTL=3600
JWT_ISSUER=books-api
CORS_ALLOWED_ORIGINS=https://YOUR-NETLIFY-SITE.netlify.app
LOGIN_RATE_LIMIT=20
LOGIN_WINDOW_SECONDS=60
APP_DEBUG=false
```

Generate `JWT_SECRET` locally:

```bat
php -r "echo bin2hex(random_bytes(32));"
```

5. Deploy and generate a public Railway domain.
6. Open the domain and `/api/books`; both must return JSON.

## D. Deploy Vue on Netlify

1. Import the same GitHub repository into Netlify.
2. Set base/package directory to `frontend`.
3. Build command: `npm run build`.
4. Publish directory: `dist`.
5. Add environment variable:

```text
VITE_API_BASE_URL=https://YOUR-RAILWAY-API-DOMAIN
```

6. Deploy and copy the public Netlify URL.
7. Return to Railway and set `CORS_ALLOWED_ORIGINS` to the exact Netlify URL.
8. Redeploy the API, then trigger a new Netlify build.
9. Test login, book loading and book creation on the public site.

## E. Create the Android app

In `frontend`, ensure `.env.production` contains the public Railway API URL.
Then run:

```bat
npm install
npm run build
npx cap add android
npx cap sync android
npx cap open android
```

If `android` already exists, skip `npx cap add android`.

In Android Studio:

1. Wait for Gradle sync.
2. Open Device Manager and start an emulator, or connect a USB-debugging phone.
3. Select the device and click Run.
4. Login and create or search for a book.

## Evidence to retain

- Railway MySQL tables and rows.
- Railway API public URL.
- JSON from GET and POST.
- Netlify public Vue URL.
- `dist` folder.
- `capacitor.config.json`.
- Android emulator showing live API data.
