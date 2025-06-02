# NYC Property Violation Aggregator

This full-stack app aggregates sidewalk violation data from [NYC OpenData](https://data.cityofnewyork.us/resource/6kbp-uz6m.json) and enriches it using real-time scraping from [BISweb](https://a810-bisweb.nyc.gov/bisweb/bispi00.jsp). Built with Laravel (backend), React (frontend), and Puppeteer (scraper).

## 🔧 Stack

- Laravel 10 (API + DB)
- React (frontend UI) V - ^16.3.0
- Puppeteer Node.js script (scraper) - >=6.9.0
- MySQL (persistent data)

## 🚀 How It Works

1. `php artisan import:violations` loads public OpenData sidewalk violations into the DB.
2. React frontend allows users to search by address.
3. When address is found in DB, system automatically scrapes BIS data (Legal Adult Use).
4. Data is paginated, searchable, and rate-limited to 10 requests/day.

## 🧪 Setup 

```bash
git clone https://github.com/your-username/nyc-violation-app.git


## Start laravel backend:
cd backend
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan import:violations
php artisan serve


## Start React frontend:

cd ../frontend
npm install
npm start

## Run the scraper manually (for dev):
cd ../scraper
node scrape_bis.js "110" "W 97 ST"

##
We fetch violations from NYC OpenData and persist them in a database for fast lookup. Scraping BIS is done on-demand, only when a matching address is searched, to avoid unnecessary load and legal risk. This hybrid approach balances speed, data freshness, and resource efficiency.
