# Video Script - Target 6 to 7 Minutes

Keep your webcam visible in a corner for the introduction. Hold your student ID
near the camera long enough to read it. Do not expose passwords, JWT secrets or
database credentials.

## 0:00-0:30 - Identity and purpose

"Assalamualaikum and hello. My name is Garrah Thabit Mohammed Ahmed Algalal,
matric number A24CS4013. This is my Chapter 13 demonstration for SCSM2223.
I will demonstrate the production MySQL database, deployed Slim API, deployed
Vue frontend, and Android application wrapped using Capacitor."

Show your face and student ID.

## 0:30-1:30 - Production MySQL

"This is my production MySQL service. The server is running and the database
contains the users, books and audit log tables."

Show the Railway MySQL service or HeidiSQL connected to it. Open `books` and
show `Garrah Mobile Development`. Also show `users`. Do not show passwords.

## 1:30-3:00 - Deployed Slim API

"This is the public production URL of my Slim PHP API. First, I will demonstrate
a GET endpoint that retrieves books from MySQL."

Run `GET /api/books` in Postman and show status 200 and JSON.

"Next, I log in to receive a JWT, then use a POST endpoint to write a unique
record to the database."

Login as the member. Use the token for:

```json
{
  "title": "Garrah Mobile Development",
  "author": "Garrah Thabit",
  "year": 2026,
  "genre": "Cross-Platform Development"
}
```

Show status 201 and JSON. Refresh MySQL and show the new row.

## 3:00-4:20 - Deployed Vue frontend

"The Vue application was built using `npm run build`. This is the generated
dist folder containing the production files."

Show the terminal build command and `dist`. Open the public Netlify URL.

"The footer displays the public API address. The books shown here are loaded
from the Slim API and MySQL database."

Login, search `Garrah`, and show the record. Open Profile.

## 4:20-5:50 - Capacitor Android application

"Capacitor is installed in the Vue project. The configuration shows the app
name UTM Books, app ID com.utm.books, and web directory dist."

Show `package.json` and `capacitor.config.json`. Briefly show:

```bat
npm run build
npx cap sync android
```

Open the running emulator.

"The application launches successfully as an Android app. It uses the same
production API rather than localhost."

Login and search `Garrah`, or create another uniquely named book such as
`Garrah Android Record`. Show that it appears.

## 5:50-6:20 - Final confirmation

"This demonstration confirms that MySQL contains live data, the Slim API
supports GET and POST through a public URL, the deployed Vue frontend
communicates with the API, and the Capacitor Android application also connects
to the same backend. Thank you."

Return briefly to MySQL or Postman and show the mobile-created record.
