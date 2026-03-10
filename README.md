# 🐦 Budgie - Personal Finance Management App

Budgie is a modern, minimal personal finance web application built with pure PHP, HTML, CSS, and JavaScript. It helps users track their expenses, manage accounts, and plan their financial future without requiring bank connections.

## 🚀 Features

### Core Functionality
- **User Authentication**: Secure login and registration system
- **Account Management**: Create, manage, and track multiple financial accounts
- **Expense Tracking**: Record and categorize expenses with flexible frequency options
- **Income Management**: Track regular and one-time income sources
- **Financial Forecasting**: Project future account balances based on current data
- **Account Sharing**: Share account visibility with family members (read-only)

### Advanced Features
- **Interest Calculations**: Automatic interest calculations for savings accounts
- **Exception System**: Modify specific transactions without affecting recurring patterns
- **Subscription Plans**: Free and Premium tiers with different limits
- **Admin Panel**: User management and system administration
- **Responsive Design**: Mobile-friendly interface

## 📁 Project Structure

```
budgie/
├── public/
│   ├── actions/                 # Form handlers (require auth + CSRF)
│   ├── auth/                    # Login / signup processors
│   ├── css/                     # Stylesheets
│   ├── js/                      # Frontend scripts
│   ├── uploads/                 # User-uploaded files
│   ├── *.php                    # All user-facing pages
│   └── .htaccess                # Apache rules scoped to /public
├── src/
│   ├── config/                  # App config + DB helpers
│   ├── includes/                # Header / Footer components
│   ├── security/                # Security utilities
│   └── services/                # Forecast engine, activity logger, etc.
├── tests/                       # Lightweight PHP test harness
├── database/                    # Schema + sample data
├── docker/ + Dockerfile         # Containerized deployment
└── README, deploy scripts, env files, etc.
```

## 🎨 Design Features

- **Modern UI**: Clean, minimal design with consistent color scheme
- **Responsive Layout**: Works on desktop, tablet, and mobile devices
- **Interactive Charts**: Financial data visualization using Chart.js
- **Modal Dialogs**: Smooth user interactions for forms and confirmations
- **Filtering**: Search and filter functionality for data tables
- **Card-based Layout**: Organized information display

## 🛠️ Technology Stack

- **Backend**: PHP 8.2+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Charts**: Chart.js
- **Styling**: Custom CSS with CSS Grid and Flexbox
- **Icons**: Emoji-based icons for simplicity

## 📱 Pages Overview

### Authentication
- **Login**: Simple email/password authentication
- **Signup**: User registration with validation

### Main Application
- **Dashboard**: Overview with statistics and charts
- **Accounts**: Manage financial accounts with interest rates
- **Expenses**: Track recurring and one-time expenses
- **Incomes**: Record salary and other income sources
- **Forecasts**: Project future financial status
- **Profile**: User settings and account management
- **Sharing**: Invite others to view accounts
- **Subscriptions**: Manage billing and plan upgrades

### Administration
- **Admin Panel**: User management and system statistics

## 🎯 Key Features by Page

### Dashboard (index.php)
- Welcome message with user's name
- Financial summary cards (total balance, monthly expenses/income)
- Interactive charts showing financial trends
- Quick action buttons
- Recent activity feed

### Accounts (accounts.php)
- Account listing with balances and interest rates
- Add/edit/delete account functionality
- Interest and tax rate configuration
- Account filtering and search

### Expenses (expenses.php)
- Expense tracking with flexible frequencies
- Account association
- Date range management
- Filtering by name, account, and frequency

### Incomes (incomes.php)
- Income source management
- Recurring income tracking
- Account allocation
- Similar filtering capabilities to expenses

### Forecasts (forecasts.php)
- Future balance projections
- Interactive chart showing account evolution
- Scenario analysis (optimistic, realistic, pessimistic)
- Monthly target selection

## 🔧 Implementation Notes

### Current Status
Budgie now ships with:
- ✅ Secure authentication (login/signup/logout, CSRF, rate limiting)
- ✅ Accounts/expenses/incomes CRUD with subscription guardrails
- ✅ Forecast engine (interest, taxation, exceptions) + Chart.js visualisation
- ✅ Activity logging surfaceable from the admin dashboard
- ✅ Dockerized PHP 8.2 + MySQL stack with env-based configuration
- ✅ Lightweight PHP unit tests for the forecasting core (`php tests/run.php`)

### Potential Enhancements
1. **Stripe integration** for the Premium subscriptions
2. **Email/notification system** for invites and account activity
3. **Extended test coverage** (auth flows, actions, admin endpoints)
4. **API layer or mobile client** if needed
5. **Advanced security** such as 2FA and IP-based alerts

## 🎨 Design System

### Colors
- Primary: `#2563eb` (Blue)
- Success: `#10b981` (Green)
- Danger: `#ef4444` (Red)
- Warning: `#f59e0b` (Orange)
- Gray Scale: `#f8fafc` to `#0f172a`

### Typography
- Font Family: System fonts (-apple-system, BlinkMacSystemFont, etc.)
- Headings: 1.5rem - 3rem
- Body: 1rem
- Small: 0.875rem

### Components
- Cards with subtle shadows
- Rounded corners (8px border-radius)
- Consistent button styles
- Modal dialogs
- Responsive tables
- Interactive charts

## 📊 Subscription Plans

### Free Plan
- 2 accounts maximum
- 7 expenses per account
- 2 incomes per account
- Basic forecasting
- Email support

### Premium Plan (€9.99/month)
- Unlimited accounts
- Unlimited expenses and incomes
- Advanced forecasting
- Account sharing
- Excel/PDF exports
- Priority support
- API access

## 🚀 Getting Started

### Option 1: Docker Deployment (Recommended)

1. **Clone the repository**
2. **Run the deployment script:**
   - **Windows:** Double-click `deploy.bat`
   - **Linux/Mac:** Run `./deploy.sh`
3. **Access the application:**
   - Application: http://localhost:8082
   - phpMyAdmin: http://localhost:8083
4. **Default credentials:**
   - Database: `budgie_db`
   - Username: `budgie_user`
   - Password: `budgie_password`

### Option 2: Manual Setup

1. **Set up a local PHP server** (Apache/Nginx with PHP 8.2+) and point the document root to `public/`
2. **Create MySQL database** using `database/schema.sql`
3. **Configure environment** by copying `env.example` to `.env`
4. **Open the application** in your browser
5. **Navigate through the pages** to see the complete UI

### ✅ Automated Tests

Unit-style checks for the forecasting engine live in `tests/`. Run them with:

```bash
php tests/run.php
```

They execute without touching the real database by using an in-memory repository, making them safe to run locally or in CI.

### 🕵️ Activity Logging

Key business actions (auth events, account/expense/income CRUD) are logged in the `activity_logs` table, including metadata, IP, and user agent. Admins can review the latest entries from the administration dashboard. This audit trail helps during demos and makes it easier to trace suspicious behaviour.

## 📝 License

This project is part of an academic assignment for personal finance management application development.

---

**Note**: The repository now contains the full PHP backend (auth, CRUD, forecasting, logging). Use the Docker or manual setup instructions above to run it end-to-end.
