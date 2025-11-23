# Student Disciplinary System

This project is a web application designed to manage student disciplinary actions within an educational institution. It allows for the reporting and management of incidents involving students, providing a structured approach to handling disciplinary issues.

## Features

- **Student Management**: Create, update, and retrieve student information.
- **Incident Reporting**: Report and manage disciplinary incidents.
- **Notifications**: Send notifications related to disciplinary actions.
- **Clean URLs**: Utilizes URL rewriting for user-friendly links.

## File Structure

```
student-disciplinary-system
├── public
│   ├── index.php
│   └── .htaccess
├── src
│   ├── Controller
│   │   ├── StudentController.php
│   │   └── DisciplinaryController.php
│   ├── Model
│   │   ├── Student.php
│   │   └── Incident.php
│   ├── Repository
│   │   └── IncidentRepository.php
│   ├── Service
│   │   └── NotificationService.php
│   ├── View
│   │   └── templates
│   │       ├── layout.php
│   │       ├── students.php
│   │       └── incident.php
│   └── bootstrap.php
├── config
│   └── config.php
├── database
│   └── migrations
│       └── create_incidents_table.sql
├── tests
│   ├── StudentTest.php
│   └── IncidentTest.php
├── composer.json
├── phpunit.xml
├── .env.example
└── README.md
```

## Installation

1. Clone the repository:
   ```
   git clone <repository-url>
   ```

2. Navigate to the project directory:
   ```
   cd student-disciplinary-system
   ```

3. Install dependencies using Composer:
   ```
   composer install
   ```

4. Set up your environment variables by copying `.env.example` to `.env` and updating the values as necessary.

5. Run the database migrations to create the necessary tables:
   ```
   php database/migrations/create_incidents_table.sql
   ```

## Usage

- Access the application by navigating to `http://localhost/student-disciplinary-system/public` in your web browser.
- Use the provided interfaces to manage students and incidents.

## Testing

To run the tests, use PHPUnit:
```
vendor/bin/phpunit
```

## Contributing

Contributions are welcome! Please submit a pull request or open an issue for any enhancements or bug fixes.

## License

This project is licensed under the MIT License. See the LICENSE file for more details.