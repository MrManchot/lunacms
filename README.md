# LunaCMS - README

## Overview
LunaCMS is a lightweight and extensible PHP library for building content management system (CMS) websites. It leverages modern PHP components such as Twig for templating, Doctrine DBAL for database interactions, PHPMailer for email handling, and Redis for optional caching. LunaCMS is designed to help developers create modular, maintainable, and flexible small to medium-scale websites quickly and efficiently.

## Features
- **MVC Architecture**: Implements the Model-View-Controller pattern for clear separation of logic, presentation, and data.
- **Twig Templating**: Offers a powerful and flexible templating engine for building dynamic web pages.
- **Site Structure Generation**: Automates the creation of the site structure using the `SiteGenerator` command-line tool.
- **Database Management**: Uses Doctrine DBAL for handling database connections and queries.
- **Email Integration**: Built-in support for sending emails using PHPMailer.
- **Routing System**: A simplified routing system for handling HTTP requests and mapping them to controllers.
- **Redis Integration**: Optional support for Redis caching to enhance performance.
- **Asset Management**: Uses Gulp to compile SCSS and JavaScript assets for efficient delivery.

## Requirements
- **PHP**: 7.4 or higher
- **Composer**: For managing PHP dependencies
- **Node.js and npm**: For Gulp and asset management
- **PHP Extensions**:
  - PDO (with MySQL support)
  - cURL
  - PHPMailer
  - (Optional) Redis PHP extension if using Redis caching

## Installation
### Prerequisites
- **PHP 7.4 or higher**
- **Composer**
- **Node.js and npm**
- **Gulp CLI**: Install globally using:
  ```sh
  npm install --global gulp-cli
  ```

### Steps
1. **Clone the Repository**
   ```sh
   git clone https://github.com/yourusername/lunacms.git
   ```

2. **Navigate to the Project Directory**
   ```sh
   cd lunacms
   ```

3. **Install PHP Dependencies**
   ```sh
   composer install
   ```

4. **Install Node.js Dependencies**
   ```sh
   npm install
   ```

5. **Generate the Site Structure**
   Use the CLI script to generate the necessary directories and files.
   ```sh
   php create_site.php /path/to/new/site
   ```

6. **Configure the Project**
   Edit the `config/config.json` file with your project-specific settings.

7. **Build Assets**
   ```sh
   gulp
   ```

## Usage
### Generating a New Site Structure
Use the command-line tool to create a new site structure. This will generate all the necessary directories and configuration files:

```sh
php create_site.php /path/to/new/site
```

### Configuration
LunaCMS uses a configuration file located at `config/config.json`. This file contains:
- **Database Settings**: Configure the database connection (host, user, password, etc.).
- **Mail Server Settings**: For PHPMailer integration.
- **Site Information**: Such as the base URL and site name.
- **Debug Settings**: Enable or disable debug mode.

### Controllers
LunaCMS follows an MVC architecture, using controllers to handle requests. Create new controllers by extending the `Controller` class:

```php
namespace App\Controllers;

use LunaCMS\Controller;

class MyCustomController extends Controller
{
    public function dataAssignment(): void
    {
        $this->template = 'custom_template';
        $this->addVar('meta_title', 'Custom Page Title');
        $this->addVar('meta_description', 'Custom Page Description');
    }
}
```

### Redis Caching
LunaCMS supports Redis caching if the Redis PHP extension is installed. Example usage:
- **Set a value in Redis**:
  ```php
  $this->setRedisValue('key', 'value', $ttl);
  ```
- **Get a value from Redis**:
  ```php
  $value = $this->getRedisValue('key');
  ```
If Redis is not available, these methods will fail silently.

### Sending Emails
You can send emails with PHPMailer using LunaCMS's built-in functionality:

```php
$this->sendEmail('recipient@example.com', 'Recipient Name', 'Subject', 'HTML Body', 'Plain Text Alternative');
```

Make sure to configure the email settings in the `config/config.json` file.

## Routing
Routing in LunaCMS is handled by the `Routing` class. Define your routes in `config/routes.php`:

```php
use App\Controllers\PageController;

return [
    '' => PageController::class,
    '{slug}' => PageController::class,
];
```

This allows you to map URLs to specific controllers easily.

## Project Structure
```
/your-project
│
├── assets
│   ├── js
│   │   └── script.js
│   └── scss
│       └── main.scss
│
├── cache
│   └── twig
│
├── config
│   ├── config.json
│   └── routes.php
│
├── public
│   ├── css
│   ├── js
│   ├── img
│   └── index.php
│
├── src
│   └── Controllers
│       └── PageController.php
│
├── templates
│   ├── includes
│   │   └── base.twig
│   └── index.twig
│
├── vendor
│
└── gulpfile.js
```

### Key Directories and Files
- **assets/**: Contains JavaScript and SCSS source files.
- **cache/twig/**: Stores cached Twig templates. Ensure this directory is writable.
- **config/**: Configuration files.
  - **config.json**: Main configuration file.
  - **routes.php**: Defines the application routes.
- **public/**: The web root directory.
  - **index.php**: The application entry point.
  - **css/**, **js/**, **img/**: Compiled assets.
- **src/Controllers/**: Controller classes.
  - **PageController.php**: Example controller.
- **templates/**: Contains Twig templates.
  - **includes/base.twig**: Base template for reusability.
  - **index.twig**: Example page template.
- **vendor/**: Composer dependencies.
- **gulpfile.js**: Gulp configuration for asset compilation.

## License
LunaCMS is open source and licensed under the MIT License. You are free to use, modify, and distribute it in accordance with the license terms.

## Contributing
Contributions are welcome! If you'd like to contribute, please fork the repository and submit a pull request with your changes.

Feel free to reach out if you need more guidance or if anything is unclear.

