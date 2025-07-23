# Hospital Management System

A modern, full-featured hospital management system built with Laravel (PHP backend) and Vue 3 (frontend) using Inertia.js. The project features a card-based, gradient-rich UI, real-time notifications, and robust role-based access for admins, staff, and doctors.

## Features<img width="1440" height="812" alt="Screenshot 2025-07-22 at 14 26 38" src="https://github.com/user-attachments/assets/62f7e9ee-b658-48a8-9ad5-621d2194bd2a" />
<img width="1440" height="812" alt="Screenshot 2025-07-22 at 14 26 54" src="https://github.com/user-attachments/assets/3c80bc6d-0946-4303-848d-f6f6318c09e3" />
<img width="1440" height="811" alt="Screenshot 2025-07-22 at 14 27 09" src="https://github.com/user-attachments/assets/9880b6c4-cdd2-4c08-816d-a228c4300fdc" />
<img width="1440" height="812" alt="Screenshot 2025-07-22 at 14 27 41" src="https://github.com/user-attachments/assets/0b18b931-8f99-476d-88d1-0d10c384c3ab" />
<img width="1440" height="777" alt="Screenshot 2025-07-22 at 14 28 23" src="https://github.com/user-attachments/assets/9b9e0b59-a448-49e4-8dad-b46015f48595" />
<img width="1440" height="778" alt="Screenshot 2025-07-22 at 14 28 51" src="https://github.com/user-attachments/assets/6d6e431c-1b68-49a8-9eab-07e83a401692" />

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
