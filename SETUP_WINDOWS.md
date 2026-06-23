# Local Setup on Windows

## 1. Install and verify tools

Open Laragon Terminal and run:

```bat
php -v
composer --version
node -v
npm -v
```

Required: PHP 8.1+, Composer 2, Node 18+ and npm.

## 2. Create the MySQL database

1. Open Laragon and click `Start All`.
2. Open HeidiSQL.
3. Open `books-api\sql\schema.sql`.
4. Press `F9` to execute the complete script.
5. Run:

```sql
USE books_api;
SELECT id, name, email, role FROM users;
SELECT id, title, author, created_by FROM books;
```

Expected: two users and three books.

## 3. Configure and run the Slim API

Open Laragon Terminal in `books-api`:

```bat
copy .env.example .env
php -r "echo bin2hex(random_bytes(32));"
```

Copy the generated value into `.env` as `JWT_SECRET`. Then run:

```bat
composer install
composer dump-autoload
php -S localhost:8000 -t public
```

Open these addresses:

```text
http://localhost:8000/
http://localhost:8000/api/books
```

Both must return JSON. Keep this terminal running.

## 4. Run the Vue frontend

Open a second terminal in `frontend`:

```bat
npm install
npm run dev
```

Open `http://localhost:5173`. Confirm:

1. Books load from MySQL.
2. Login works with `member@books.test` / `password`.
3. Create `Garrah Mobile Development`.
4. Open Profile and confirm the user details load.

## 5. Verify the production build locally

For a local production preview, temporarily set `.env.production` to:

```text
VITE_API_BASE_URL=http://localhost:8000
```

Then run:

```bat
npm run build
dir dist
npm run preview
```

Open `http://localhost:4173`. The `dist` folder must contain `index.html`
and an `assets` folder.

Before cloud deployment, replace the production URL with the real public API
URL and run `npm run build` again.

## Common fixes

| Problem | Correction |
|---|---|
| `php` or `composer` not recognised | Use Laragon Terminal |
| Database connection failed | Check MySQL is running and `.env` credentials |
| CORS error | Add the frontend URL to `CORS_ALLOWED_ORIGINS`, then restart API |
| Login always fails | Re-run `schema.sql`; use password `password` |
| Frontend shows placeholder API URL | Correct `.env.production` and rebuild |
| Android cannot reach `localhost` | Build with the public Railway API URL |
