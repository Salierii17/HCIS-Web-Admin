# Filament HRIS - Human Resource Information System 🚀

A comprehensive, web-based Human Resource Information System (HRIS) built with the elegant **FilamentPHP v3** admin panel on top of the **Laravel** framework. This project streamlines key HR processes, featuring modules for recruitment, training, and attendance tracking, with a dedicated API to connect to a mobile attendance application.

## ✨ Core Features

This HRIS is designed with modularity and efficiency in mind.

-   **👤 Recruitment Module:**

    Develop a web-based platform to enable candidates to easily apply for jobs and Human Capital teams to efficiently manage job postings, track applications, and allow candidates to monitor the updated status of their recruitment process.

-   **🎓 Training Module:**

    Implement a notification system that ensures employees receive scheduled training and provides immediate access to assessment training records, enhancing the training experience and ensuring compliance.

-   **🕒 Attendance Module:**

    Introduce a location-based attendance tracking system capable of monitoring employees' locations through a geo-verification feature during their 8-hour workday, accommodating both remote and office work setups.

-   **Role-Based Access Control (RBAC):** Powered by **Filament Shield**, providing robust and easy-to-manage roles and permissions (e.g., Super Admin, HR Manager, Employee).
---

## 🛠️ Tech Stack

This project leverages the power of the TALL stack and the Laravel ecosystem.

-   **Framework:** Laravel 11
-   **Admin Panel:** FilamentPHP v3
-   **Role & Permission Management:** `bezhanSalleh/filament-shield`
-   **UI/Frontend:** Livewire, Alpine.js, Tailwind CSS
-   **Database:** MySQL / PostgreSQL
-   **API Authentication:** Laravel Sanctum

---

## 🚀 Getting Started

Follow these instructions to get a local copy of the project up and running for development and testing purposes.

### Prerequisites

-   PHP 8.2+
-   Composer
-   Node.js & NPM
-   A database server (e.g., MySQL, PostgreSQL)

### Installation

1.  **Clone the repository:**

    ```bash
    git clone [https://github.com/your-username/your-repo.git](https://github.com/your-username/your-repo.git)
    cd your-repo
    ```

2.  **Install dependencies:**

    ```bash
    composer install
    npm install && npm run build
    ```

3.  **Set up your environment file:**

    ```bash
    cp .env.example .env
    ```

    Next, open the `.env` file and configure your database connection (`DB_*` variables) and application URL (`APP_URL`).

4.  **Generate application key:**

    ```bash
    php artisan key:generate
    ```

5.  **Run database migrations:**
    _This will create all necessary tables, including those for Filament Shield._

    ```bash
    php artisan migrate
    ```

6.  **Install Filament Shield and create a Super Admin:**
    _This is the most important step for setting up roles and permissions. The command will auto-discover your Filament resources, generate policies, and prompt you to create a Super Admin role and user._

    ```bash
    php artisan shield:install
    ```

    **Follow the interactive prompts carefully.** When asked if you want to create a `super_admin` role and user, answer **yes**.

7.  **(Optional) Seed additional demo data:**
    _If you have other seeders to populate the database with dummy employees, courses, etc., run them now._

    ```bash
    php artisan db:seed
    ```

8.  **Start the development server:**
    ```bash
    php artisan serve
    ```

You can now access the application at `http://127.0.0.1:8000`.

### Logging In

Use the credentials you created for the **Super Admin** user during the `php artisan shield:install` step to log in.


### Accessing the Application
### Accessing the Application
* **Admin Panel:** You can access the main application and admin login page at `http://127.0.0.1:8000/`. Use the credentials you created for the **Super Admin** user during the `shield:install` step.
* **Candidate Portal:** The public-facing careers page for candidates to apply for jobs is available at `http://127.0.0.1:8000/career`.

Candidate Portal: The public-facing careers page for candidates to apply for jobs is available at http://127.0.0.1:8000/career.

---

## 📱 Mobile App Integration

The attendance module exposes several API endpoints to communicate with the companion mobile application.

* **Authentication:** API requests are protected using **Laravel Sanctum** with Bearer Tokens. The mobile app must first authenticate via the `/login` endpoint to receive an API token for subsequent requests.
* **Base URL:** All API endpoints are prefixed with `http://your-domain.com/api/v1/`.
* **Key Endpoint Groups:**
    * **Authentication:** `POST /login`, `POST /logout`
    * **Attendance Management:** `POST /attendance` (Clock In), `PUT /attendance/{id}` (Clock Out), `GET /attendance` (History).
    * **Attendance Correction:** `POST /attendance-requests`
    * **User Profile:** `GET /profile`, `PUT /profile`, `POST /profile/photo`, `PUT /profile/password`
    * **Notifications:** `GET /notifications`, `POST /notifications/{id}/read`

For detailed API documentation, please refer to the Postman collection or the `API_DOCS.md` file in this repository.

---
