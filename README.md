# $Meat Grid $
### Meat Grid — Meat product demand & supply analysis 🥩📊
[![License](https://img.shields.io/badge/License-MIT-lightgrey.svg)](https://opensource.org/licenses/MIT)  
![HTML](https://img.shields.io/badge/HTML-79.4%25-orange)
![PHP](https://img.shields.io/badge/PHP-20.6%25-purple)
![Project Status](https://img.shields.io/badge/status-Active%20(Beta)-brightgreen)

Short description
- A web-based system for analyzing meat product demand and supply using integrated data from stakeholders to support planning, pricing, and nutrition insights.

Status
- Development: Active (Beta) — core features implemented; improvements and data integrations ongoing.  
- Last updated: 2025-10-19

## Features
- Integrated stakeholder data ingestion (suppliers, retailers, markets)
- Demand & supply analytics and trend visualization
- Pricing & planning tools (forecasting, scenario comparison)
- Nutrition insights and product composition summaries
- Exportable reports (CSV/PDF) and scheduled summaries
- Alerts for shortages, price spikes, or demand surges

## Tech Stack
- Primary: HTML, PHP
- Typical stack: LAMP/LEMP (PHP 8+, MySQL/MariaDB)
- Frontend: semantic HTML, server-rendered views
- Recommended: Apache/Nginx, PHP 8+, Node (optional for tooling)

## Roles
- Admin — manage users, data sources, system settings  
- Supplier — submit stock & supply reports  
- Retailer — publish demand/orders, view forecasts  
- Planner / Analyst — run scenarios and export reports  
- Nutritionist — review nutrition-related summaries

## Quick start
```bash
git clone https://github.com/sabbir-404/meat_grid.git
cd meat_grid

# If the project uses composer (check repository)
# composer install

# Serve with local PHP server for quick dev (if no framework):
php -S localhost:8000 -t public

# Or deploy to your Apache/Nginx + PHP setup and point document root to the project public/ or root folder.
```
Open http://localhost:8000 (or your configured host).

## Environment (concise .env.example)
Copy .env.example → .env and fill values before running.

```text
# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=meat_grid
DB_USER=root
DB_PASS=secret

# App
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=base64:your_app_key_here

# External data / marketplace API
MARKET_API_URL=https://marketdata.example.com
MARKET_API_KEY=your-market-api-key

# File uploads
UPLOAD_DIR=/var/www/meat_grid/uploads

# Email (for alerts/reports)
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=you@example.com
SMTP_PASS=secret

# Optional feature flags
ENABLE_REPORT_SCHEDULER=true
```

## Contributing
- Fork → branch (feat/...) → PR. Include description, screenshots, and small focused changes.

## License & contact
- License: MIT  
- Maintainer: sabbir-404 — https://github.com/sabbir-404
