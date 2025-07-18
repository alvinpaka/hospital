# Hospital Management System

A modern, full-featured hospital management system built with Laravel (PHP backend) and Vue 3 (frontend) using Inertia.js. The project features a card-based, gradient-rich UI, real-time notifications, and robust role-based access for admins, staff, and doctors.

## Features
- Patient, staff, and doctor management
- Appointments, visits, and billing
- Lab results and prescriptions
- Real-time chat and notifications
- Modern dashboard UI with analytics
- Role-based access control
- Settings for appearance, profile, and password

## Tech Stack
- **Backend:** Laravel (PHP)
- **Frontend:** Vue 3, Inertia.js, Vite
- **UI Components:** shadcn/ui
- **Database:** MySQL (or compatible)
- **Testing:** Pest

## Getting Started

### Prerequisites
- PHP >= 8.1
- Composer
- Node.js & npm
- MySQL or compatible database

### Installation
1. **Clone the repository:**
   ```bash
   git clone https://github.com/alvinpaka/hospital.git
   cd hospital
   ```
2. **Install PHP dependencies:**
   ```bash
   composer install
   ```
3. **Install JS dependencies:**
   ```bash
   npm install
   ```
4. **Copy and configure environment:**
   ```bash
   cp .env.example .env
   # Edit .env with your DB and mail settings
   php artisan key:generate
   ```
5. **Run migrations and seeders:**
   ```bash
   php artisan migrate --seed
   ```
6. **Build frontend assets:**
   ```bash
   npm run build
   ```
7. **Start the development server:**
   ```bash
   php artisan serve
   # In another terminal
   npm run dev
   ```

## Running Tests
- Run all tests with Pest:
  ```bash
  ./vendor/bin/pest
  ```

## Folder Structure
- `app/` - Laravel backend (models, controllers, etc.)
- `resources/js/` - Vue 3 frontend (pages, components, stores)
- `database/` - Migrations, seeders, factories
- `routes/` - Laravel route definitions

## Contributing
Pull requests are welcome! For major changes, please open an issue first to discuss what you would like to change.

## License
[MIT](LICENSE) 