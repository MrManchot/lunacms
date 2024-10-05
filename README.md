# LunaCMS Library - README

## Overview
LunaCMS is a lightweight and extensible PHP library for building CMS-like websites. It uses modern PHP components such as Twig for templating, Doctrine DBAL for database management, and PHPMailer for email handling. LunaCMS helps developers create modular, maintainable, and flexible websites with ease. The library is suitable for creating small to medium-scale websites.

## Features
- **MVC Architecture**: Clear separation of concerns using Controllers and Views.
- **Twig Templating**: Flexible and powerful templating engine.
- **Site Structure Generation**: Automate the creation of a CMS-like site structure using a command-line script.
- **Doctrine DBAL**: Robust database management using Doctrine DBAL.
- **PHPMailer Integration**: Built-in support for sending emails using PHPMailer.
- **Routing System**: Simplified routing system to handle HTTP requests and execute controller actions.
- **Optional Redis Integration**: Caching functionality with Redis (optional).
- **Asset Management**: Utilizes Gulp for compiling SCSS and JavaScript assets.
- **CLI Tool**: Easy project setup using the `SiteGenerator` command-line script.

## Requirements
- PHP 7.4 or higher
- Composer (for dependency management)
- Node.js and npm (for Gulp and asset management)
- Required PHP Extensions:
  - PDO (with MySQL support)
  - cURL
  - PHPMailer
  - (Optional) Redis PHP extension if Redis caching is to be used

## Installation
### Prerequisites
- **PHP 7.4 or higher**
- **Composer**
- **Node.js and npm**
- **Gulp CLI**: Install globally using `npm install --global gulp-cli`

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
4. **Initialize npm and Install Node Dependencies**
   ```sh
   npm install --save-dev gulp sass gulp-sass gulp-concat gulp-uglify gulp-clean-css
   ```
5. **Generate the Project Structure**
   Use the CLI script to generate the necessary directories and files.
   ```sh
   php create_site.php /path/to/new/site
   ```
6. **Configure the Project**
   Edit the `config/config.json` file with your project-specific settings.
7. **Build Assets with Gulp**
   ```sh
   gulp
   ```

## Usage
### Generating a New Site Structure
You can easily create a new site structure using the provided command-line script. Run the following command to generate a site structure at a specified path:

```sh
php create_site.php /path/to/new/site
```

This command will create a new site directory, including assets, configuration files, public folder, templates, and other necessary elements.

### Configuration
LunaCMS uses a configuration file located at `config/config.json`. This file includes:
- **Database connection information**
- **Mail server settings for PHPMailer**
- **Site information (e.g., base URL)**
- **Debug settings**

Ensure you adjust the configuration as needed for your environment.

### Controllers
LunaCMS uses controllers for handling page requests. To create a new controller, extend the `Controller` class:

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
LunaCMS optionally supports Redis caching. If the Redis PHP extension is not installed, the library will work without it.
- **Setting a value in Redis**: `$this->setRedisValue('key', 'value', $ttl);`
- **Getting a value from Redis**: `$value = $this->getRedisValue('key');`

If Redis is not installed or available, these methods will fail silently without affecting other parts of the library.

### Sending Emails
You can use the built-in functionality to send emails via PHPMailer:

```php
$this->sendEmail('recipient@example.com', 'Recipient Name', 'Subject', 'HTML Body', 'Plain Text Alternative');
```

Make sure to configure your email settings in the `config/config.json` file.

## Example: Routing
Routing is handled using the `Routing` class, which maps URL patterns to specific controllers.
Add your routes in `config/routes.php` like so:

```php
use App\Controllers\PageController;

return [
    '' => PageController::class,
    '{slug}' => PageController::class,
];
```

This allows you to map different URLs to their corresponding controllers easily.

## Project Structure
```
/your-project
│
├── assets
│   ├── js
│   │   └── script.js
│   └── scss
│       └── main.css
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
├── gulpfile.js
```

### Description of Key Directories and Files
- **assets/**: Contains source files for JavaScript and SCSS.
- **cache/twig/**: Stores cached Twig templates. Ensure this directory is writable.
- **config/**: Configuration files.
  - **config.json**: Main configuration file.
  - **routes.php**: Defines application routes.
- **public/**: Web root directory.
  - **index.php**: Entry point for the application.
  - **css/**, **js/**, **img/**: Compiled assets.
- **src/Controllers/**: Contains controller classes.
  - **PageController.php**: Example controller.
- **templates/**: Twig templates.
  - **includes/base.twig**: Base template.
  - **index.twig**: Example page template.
- **vendor/**: Composer dependencies.
- **gulpfile.js**: Gulp configuration for asset management.