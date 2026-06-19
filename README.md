# Wagura

A pet health tracking app for the Philippines. This is a frontend-only prototype, so everything runs in your browser without a backend server.

## What is this?

Wagura lets users log their pet's health, read articles about pet care, and track daily insights. There's also an admin section to manage articles, insights, and user data.

Right now, all the data is stored in your browser using localStorage. That means you can use the app, add pets, save health logs, and everything sticks around even after you refresh the page. But if you clear your browser data, the mock data will reset.

## How to use it

1. Open `landing_page.html` in your browser
2. Log in (use any email/password for the mock login)
3. You'll see the user dashboard with your pets
4. Add a new pet, log health info, read articles, or check daily insights

For the admin panel, open `admin/admin_login_page.html` instead.

## The two main parts

### User section

This is where regular users manage their pets. You can:

- Add and view pets
- Log health information (feeding, weight, vet visits, symptoms)
- Read articles about pet care
- Check daily insights

All pet data is stored locally using the `UserData` object from `js/user/user_data.js`.

### Admin section

Admins can:

- Manage articles (create, view, delete)
- Manage insights (create, view, delete)
- View and manage user data
- View and manage all pets

Admin data is stored locally using the `AdminData` object from `js/admin/admin_data.js`.

## How the data works

Both the user and admin sections use a simple frontend mock data system. Here's what happens:

1. When you first load the app, it checks localStorage for existing data
2. If there's no data yet, it uses the initial mock data (default pets, articles, etc.)
3. When you add or delete something, it saves to localStorage automatically
4. Next time you open the page, your changes are still there

This means you can use the app like a real app, but there's no server involved. It's all in your browser.

## Where are the files?

```
js/
  admin/
    admin_data.js        - Stores and manages admin data
    admin_shared.js      - Shared behaviors for all admin pages
    add_edit_article.js  - Handles article creation
    add_edit_insight.js  - Handles insight creation
    manage_articles.js   - Shows and manages articles list
    manage_insights.js   - Shows and manages insights list
  user/
    user_data.js         - Stores and manages user data
    add_pet_page.js      - Handles pet creation
    (other user page scripts)

admin/
  admin_dashboard.html
  admin_login_page.html
  add_edit_article.html
  add_edit_insight.html
  manage_articles.html
  manage_insights.html
  (other admin pages)

user/
  dashboard_page.html
  add_pet_page.html
  my_pet_page.html
  health_log_page.html
  articles_page.html
  daily_insights_page.html
  (other user pages)

css/
  (styling for all pages)
```

## The key JavaScript files

### `js/admin/admin_data.js`

This is the data layer for the admin side. It handles:

- Loading and saving data from localStorage
- Getting lists of users, pets, articles, insights
- Adding new articles and insights
- Deleting items
- Generating unique IDs

### `js/user/user_data.js`

Same idea but for users. It manages:

- Profile info
- Pet data
- Health logs
- Articles and insights that users see

### `js/admin/admin_shared.js`

Shared behaviors for admin pages:

- Search and filter functionality
- Delete confirmations
- Category button syncing
- Live previews

### Page-specific scripts

Each page has its own script that handles form validation, saving data, and updating the UI. For example:

- `add_edit_article.js` validates the article form and saves to AdminData
- `add_pet_page.js` validates pet info and saves to UserData

## Testing it out

1. **Add a pet**: Go to the user dashboard and create a new pet. It saves automatically.
2. **Log health info**: Record feeding, weight, vet visits, anything you want.
3. **Create an article**: Go to admin > Manage Articles > New Article. Fill it out and click Publish.
4. **Delete something**: Click a delete button and confirm. It removes from the list and storage.
5. **Refresh the page**: Everything you added is still there. That's localStorage working.

## Important to know

- This is a prototype. Everything's frontend-only with fake data.
- There's no real login. Any email/password combo works.
- Data lives in your browser. Clearing browser data will reset everything.
- Search filters work on the page, not a database.
- No validation beyond basic field checks.

## Tech stack

- HTML, CSS, vanilla JavaScript
- No frameworks or libraries (except Font Awesome icons)
- localStorage for data persistence
- DOM manipulation for all interactions
