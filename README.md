# AirAdvise-API 🌍💨  
*The headless API for AirAdvise, built with Laravel.*  

## Overview  
AirAdvise-API provides the headless API that powers the **[AirAdvise](https://github.com/Punvireakroth/AirAdvise)** mobile app.


## Tech Stack 🛠️  
- **Laravel** – PHP framework  
- **MySQL** – Database  
- **REST API** – JSON-based endpoints  
- **Sanctum** – Authentication and Authorization
- **Guzzle HTTP Client** – External API integration  

## Installation & Setup  
### 1. Clone the Repository  
```bash
git clone https://github.com/Punvireakroth/AirAdvise-API.git

cd AirAdvise-API
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Set Up Environment Variables
- Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

- Configure database credentials in the .env file.

### 4. Run Database Migrations

```bash
php artisan migrate --seed
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Start the Development Server

```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000`


### API Endpoints 🔗

Comming soon...

### License 📜

- ✅ The source code is open for viewing, modification, and educational use
- ✅ Contributions to the project are welcome
- ❌ Commercial deployment of this codebase as a competing service is not permitted without prior written agreement
- ❌ The project name and branding are protected and require explicit permission for use

For business inquiries, commercial licensing, or any questions, please contact [vireakrothpun@gmail.com]

See the [LICENSE](LICENSE) file for the full AGPL-3.0 terms.