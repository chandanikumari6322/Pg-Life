# PG Life — Student Accommodation Website (Completed)
Internshala Web Development Training — Final Project

This is **your original design**, kept exactly as you built it (HTML/CSS/images), now
wired to a real PHP + MySQL backend with working signup/login, AJAX filters, an
"interested" heart button, and a React-powered listing page.

## ⚠️ IMPORTANT if you had the older version of this project
This version adds a `bookings` table and a proper `index.php`. Re-import `pglife.sql`
in phpMyAdmin (it now starts with `DROP DATABASE IF EXISTS pglife;` so it rebuilds
cleanly) — otherwise "Book Now" will show a database error.

If login/signup isn't working, open **`http://localhost/pglife/setup_check.php`**
first — it tells you exactly what's wrong (MySQL not running, database not imported,
missing table, etc.) instead of guessing.

## 🔐 ADMIN PANEL (approve/reject bookings)

- URL: `http://localhost/pglife/admin/login.php`
- Default login: **username `admin`**, **password `admin123`**
  (change these in `admin/admin_auth.php` before putting this online!)
- The dashboard shows every booking request with the student's name, contact,
  property, and rent, with **Approve** / **Reject** buttons (AJAX, no reload).
- The moment a booking is approved or rejected, the student sees a **notification
  badge** next to "My Bookings" in their navbar (a red number), and opening
  `my_bookings.php` shows the new status with a message like
  "🎉 Your booking has been approved by the owner!".

## What was added on top of your design
- `pglife.sql` — full database (5 tables: users, properties, amenities, property_amenities, interested_users)
- `includes/db.php` — database connection
- `includes/nav_state.php` — shows Login/Signup or "Hi, Name / Logout" depending on session
- `includes/auth_modals.php` — your signup/login modal markup, now shared across pages
- `api/signup.php`, `api/login.php`, `api/logout.php` — real authentication (passwords hashed)
- `api/get_properties.php`, `api/get_property.php` — JSON endpoints for property data
- `api/toggle_interest.php` — mark/unmark interest, used by the heart icon
- `api/book_property.php` — creates a booking request, used by the "Book Now" button
- `setup_check.php` — visit this in the browser to diagnose DB/login problems
- `index.php` — the real homepage entry point (visiting the folder now shows this properly)
- `js/custom/app.js` — wires your existing signup/login forms to the backend via AJAX (jQuery, no reload)
- `js/custom/details.js` — wires the "interested" heart on the detail page via AJAX
- `react/PropertyList.jsx` — React component that renders the property cards on `property_list.php`
  (fetches from `api/get_properties.php`, re-fetches on filter/sort clicks — no page reload)
- `home.php`, `property_list.php`, `property_detail.php` — your original `.html` pages,
  converted to PHP so they can talk to the database (visually identical to what you built)

Your CSS, images, jQuery and Bootstrap files were **not touched**.

---

## 1. HOW TO RUN LOCALLY (XAMPP)

1. Install XAMPP if you don't have it: https://www.apachefriends.org
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Copy the `pglife` folder into:
   - Windows: `C:\xampp\htdocs\pglife`
   - Mac: `/Applications/XAMPP/htdocs/pglife`
4. Go to `http://localhost/phpmyadmin` → click **Import** → choose `pglife.sql` → click **Go**.
   This creates the database with sample properties, amenities, and the linking tables
   (including `bookings` for the Book Now button).
5. Open `http://localhost/pglife/index.php`
6. Click a city (e.g. Mumbai) → you'll land on `property_list.php` with **real cards from
   the database**, rendered by React, with working Filter and Sort.
7. Click **Signup**, fill the form → creates a real account and logs you in automatically.
8. Click the heart icon on any card, or open a property and click the heart there →
   it's saved to the database via AJAX (try refreshing — it stays marked).
9. Open a property detail page and click **Book Now** → creates a booking request
   (button turns into "Pending" and stays disabled — try refreshing, it stays that way).
10. Click **Logout** to test the session ending.

### If your MySQL username/password is different
Edit `includes/db.php`:
```php
$dbuser = "root";
$dbpass = "";   // change if needed
```

---

## 2. FOLDER STRUCTURE
```
pglife/
├── home.php                  -> Landing page (your design, city links now query the DB)
├── property_list.php         -> Listing page — React renders cards from the database
├── property_detail.php       -> Detail page — dynamic, AJAX interest button
├── pglife.sql                -> Import this into phpMyAdmin first!
├── includes/
│   ├── db.php                -> DB connection
│   ├── nav_state.php         -> Login/Signup vs logged-in nav
│   └── auth_modals.php       -> Shared signup/login modal HTML
├── api/
│   ├── signup.php / login.php / logout.php
│   ├── get_properties.php    -> JSON list (filters: city, gender, sort)
│   ├── get_property.php      -> JSON single property + amenities + images
│   ├── toggle_interest.php   -> Mark/unmark interested
│   └── book_property.php     -> Create a booking request
├── admin/
│   ├── login.php / logout.php / admin_auth.php
│   ├── dashboard.php         -> View & approve/reject bookings
│   └── update_booking.php    -> AJAX endpoint used by the dashboard
├── my_bookings.php           -> User's own bookings + status/notifications
├── react/PropertyList.jsx    -> React component (CDN + Babel, no npm needed)
├── js/custom/
│   ├── app.js                -> AJAX signup/login form handling
│   └── details.js            -> AJAX interest button on detail page
├── css/, js/, img/           -> Your original design files (untouched)
```

---

## 3. DEPLOYING ONLINE (free hosting with PHP + MySQL)
Recommended: **InfinityFree** (infinityfree.net) or **000webhost**

1. Sign up, create a hosting account.
2. Upload everything into `htdocs`/`public_html` (via File Manager or FileZilla).
3. In the hosting's phpMyAdmin, create a database and import `pglife.sql`.
4. Update `includes/db.php` with the host/db credentials the hosting panel gives you.
5. Visit your live URL, e.g. `https://yourname.infinityfreeapp.com/home.php`

## 4. PUSH TO GITHUB
```bash
git init
git add .
git commit -m "PG Life - Student Accommodation Website"
git branch -M main
git remote add origin https://github.com/<your-username>/pglife.git
git push -u origin main
```

---

## 5. WHAT TO SUBMIT ON INTERNSHALA
1. **Live project URL**
2. **GitHub repository link**
3. **Database schema file** → attach `pglife.sql` (already included)
4. **Screenshots**:
   - Property Listing Page (`property_list.php?city=Mumbai`)
   - Property Detail Page (`property_detail.php?id=1`)
   - AJAX interaction — e.g. clicking the heart icon and the count updating instantly,
     or clicking a gender filter and cards refreshing without a page reload
5. **A short document explaining the project and approach** — starter text:

> This project (PG Life) is a responsive student accommodation website. It began as a
> hand-built HTML/CSS/Bootstrap frontend and was then connected to a normalized MySQL
> database (users, properties, amenities, property_amenities, interested_users) through
> a PHP backend using prepared statements for security and password hashing for user
> accounts. The signup and login forms submit via jQuery AJAX so the page never reloads.
> The property listing page uses a React component (loaded via CDN, no build tools) that
> fetches data from a PHP JSON API and re-fetches automatically whenever the gender
> filter or price sort is changed, giving instant results. Marking a property as
> "interested" also happens via AJAX and persists in the database per user. The project
> was deployed on [hosting provider] and the source is on GitHub.

---

## 6. TROUBLESHOOTING
- **"Database connection failed"** → check `includes/db.php` credentials, make sure MySQL is running in XAMPP.
- **Listing page shows "No properties found"** → make sure you imported `pglife.sql`.
- **Signup/Login does nothing** → open browser console (F12); check that `js/jquery.js`
  loads before `js/custom/app.js` (already ordered correctly in the pages).
- **Heart icon says nothing happens** → you need to be logged in first (that's correct behavior — it opens the login modal).
- **React cards don't render** → check internet connection (React/Babel load from CDN) and browser console for errors.
#   P g - L i f e  
 