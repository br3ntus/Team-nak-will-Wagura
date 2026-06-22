# Wagura

Wagura is a pet health management system for dog and cat owners in Laguna, Philippines. It was designed as a Web Development project with a connected Business Intelligence component.

## About the webpage

The project follows the approved proposal structure:

- A **User panel** for pet owners to register, log in, enroll pets, and manage pet health data.
- An **Admin panel** for platform administrators to manage users, pets, articles, and daily insights.
- A **public section** with guides and tips for local pet care concerns such as rabies prevention, humid climate health, and stray animal awareness.
- A **dashboard-driven** workflow that supports decision-making through summary statistics, visualization, and analytics.

The feature set is based on the initial proposal:

- User authentication (registration and login)
- Pet enrollment and management
- Health log creation for feeding, weight, symptoms, and vet visits
- PH-specific pet care articles and daily insight posts
- Search and filter support for articles and insights
- Admin content management and user oversight
- Integration with XAMPP/PHP/MySQL for real backend data storage

## What is already implemented

### User functionality

- Register and log in through `register_page.php` and `login.php`
- View the dashboard at `user/dashboard_page.php`
- Add and edit pets using `user/add_pet_page.php` and `user/edit_pet_page.php`
- Log health entries through `user/add_log_page.php`
- Read articles on `user/articles_page.html`
- Browse daily insights on `user/daily_insights_page.html`
- View each pet's profile and health history on `user/my_pet_page.php`

### Admin functionality

- Admin login via `admin/admin_login_page.php`
- Admin dashboard at `admin/admin_dashboard.php`
- Manage users with `admin/manage_users.html`
- Manage pets with `admin/manage_pets.html`
- Create and manage articles with `admin/manage_articles.html` and `admin/add_edit_article.html`
- Create and manage daily insights with `admin/manage_insights.html` and `admin/add_edit_insight.html`
- Dashboard overview cards for registered users, enrolled pets, article count, and insight count
- Recent activity tables for users and pets

### Data architecture

- Core backend is built in **PHP** and targets **MySQL** via XAMPP
- Admin dashboard pulls real database data into frontend JS through `window.WaguraAdminBackendData`
- Some pages use client-side scripting for interactive page behavior, while others rely on the PHP backend for persistence
- `predict_api.py` provides a separate Business Intelligence prediction API in Python

## Business Intelligence implementation

This project implements the Business Intelligence with an integrated admin dashboard and a Python Linear Regression API.

### BI requirement coverage

The final BI requirement specifies:

1. A working web-based dashboard integrated into the web system
2. At least one BI technique from class
3. Clear data visualization and insights

Wagura currently uses **Linear Regression** as the primary BI technique.

### Admin Dashboard BI section

The admin dashboard includes a BI panel that shows:

- Predicted future pet weight
- Weekly weight growth rate
- Model accuracy as **R²**
- Insight text summarizing pet trend predictions
- A visual trend line of actual weight logs and predicted future weight

The BI section is implemented in `admin/admin_dashboard.php` and includes a responsive dashboard card layout.

### Python API: `predict_api.py`

A zero-dependency Python API server is available at:

- `http://127.0.0.1:5000/api/predict`

The API accepts a JSON payload like:

```json
{
  "pet_name": "Coco",
  "breed": "Aspin Dog",
  "days": 31,
  "logs": [
    { "date": "2026-03-01", "weight": 7.2 },
    { "date": "2026-03-10", "weight": 7.8 },
    { "date": "2026-03-20", "weight": 8.1 }
  ]
}
```

And returns:

- `predicted_weight`
- `prediction_date`
- `growth_rate_weekly`
- `r_squared`
- `insight_text`
- `actual_data`

### Linear Regression details

The prediction engine calculates a least-squares linear regression from weight log dates to weight values. It returns:

- slope `m`
- intercept `b`
- coefficient of determination `R²`

This enables the dashboard to present both predictions and confidence insights, aligning with the BI requirement for statistical analysis.

### Fallback behavior

If the Python API is unavailable, `admin/admin_dashboard.php` performs the same linear regression logic in PHP as a fallback and still generates a BI result.

## How to run the project

1. Place the project folder under XAMPP's `htdocs`
2. Start Apache and MySQL
3. Start the Python API server with:
   ```bash
   python predict_api.py
   ```
4. Open the app in your browser at:
   - `http://localhost/Team-nak-will-Wagura/login.php` for users
   - `http://localhost/Team-nak-will-Wagura/admin/admin_login_page.php` for admin

> If the Python API is not running, the admin dashboard still shows predictions using the PHP fallback model.

## Project structure highlights

- `admin/` — admin pages and dashboard entry points
- `user/` — user-facing pages for dashboard, pets, logs, articles, insights
- `js/admin/` — admin page scripts and dashboard UI helpers
- `js/user/` — user page scripts and local page state management
- `css/` — shared and page-specific styling
- `predict_api.py` — Python BI prediction API
- `database/` (if present) — database schema and SQL support files
- `reference/` — project proposal, BI requirement, and admin dashboard design references

## Notes

- The project is designed around a hybrid architecture: PHP/MySQL for backend persistence and Python for BI analytics.
- The admin dashboard is the main BI interface, showing both analytics cards and trend visualization.
