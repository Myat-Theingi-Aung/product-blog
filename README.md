# Product CRUD Using Symfony

## Requirements
- PHP 8.2 or higher
- Composer
- Node.js and npm
- Symfony CLI (optional but recommended)
- MySQL

## Installation

Clone the repo locally:
```
git clone https://github.com/Myat-Theingi-Aung/product-blog.git
```

`cd` into cloned directory and install dependencies. run below command one by one.
```bash
composer install
npm install
cp .env.example .env
```

### Configuration in `.env` file

Database **eg.**
```
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/your_db_name?serverVersion=8.0"
```

## Database Migration

Run database migrations:
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Server Run

Run the dev server:
```
npm run dev
symfony serve
```

Visit below url:
```
http://localhost:8000
```