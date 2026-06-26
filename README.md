Rectem Restaurant Ordering System

Setup
1. Extract/place the folder into:
   C:\xampp\htdocs\rectem_cafeteria
   (If keeping the old folder name "campus_cafeteria", update your browser URL and the qb fallback if needed.)

2. Start Apache and MySQL in XAMPP.

3. Open phpMyAdmin and import:
   database/rectem_restaurant.sql

4. Visit:
   http://localhost/rectem_cafeteria/

Seeded accounts
- Admin
  Email: admin@rectemrestaurant.com
  Password: admin123

- Staff
  Email: staff@rectemrestaurant.com
  Password: admin123

- Demo Student
  Email: student@rectemcafeteria.com
  Matric No: RECTEM/24/001
  Password: admin123

Notes
- Student registration requires full name, matric number, email, phone, and password.
- Uploaded meal images are stored in assets/images/foods/.
- AI is intentionally restrained and appears only in:
  student dashboard, food details, checkout/tracking, and admin dashboard/reports.

