# LunaCMS

LunaCMS is a lightweight and extensible Content Management System (CMS) built with PHP. It leverages modern PHP components such as Twig for templating, Doctrine DBAL for database interactions, and PHPMailer for email handling. This README provides comprehensive instructions on installing, configuring, and extending LunaCMS to suit your development needs.

## Features

- **MVC Architecture**: Clear separation of concerns with Controllers and Views.
- **Templating with Twig**: Flexible and powerful templating engine.
- **Database Abstraction**: Uses Doctrine DBAL for robust database interactions.
- **Email Handling**: Integrated PHPMailer for sending emails.
- **Asset Management**: Utilizes Gulp for compiling SCSS and JavaScript assets.
- **Routing System**: Simple and effective routing for handling HTTP requests.
- **CLI Tool**: Easy project setup with the `SiteGenerator`.

## Installation

### Prerequisites

- **PHP 8.0 or higher**
- **Composer**
- **Node.js and npm**
- **Gulp CLI**: Install globally using `npm install --global gulp-cli`

### Steps

1. **Clone the Repository**

   ```bash
   git clone https://github.com/mrmanchot/lunacms.git
   ```

2. **Navigate to the Project Directory**

   ```bash
   cd lunacms
   ```

3. **Install PHP Dependencies**

   ```bash
   composer install
   ```

4. **Initialize npm and Install Node Dependencies**

   If `package.json` does not exist, initialize npm:

   ```bash
   npm init -y
   ```

   Then, install Gulp and its dependencies:

   ```bash
   npm install --save-dev gulp sass gulp-sass gulp-concat gulp-uglify gulp-clean-css
   ```

5. **Generate the Project Structure**

   Use the CLI script to generate the necessary directories and files.

   ```bash
   php create_site.php /path/to/new/site
   ```

6. **Configure the Project**

   Edit the `config/config.json` file with your project-specific settings.

7. **Build Assets with Gulp**

   ```bash
   gulp
   ```


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

## Configuration

All configurations are managed through the `config/config.json` file. Below is an explanation of each configuration option.

```json
{
    "base_path": /path/to/project",
    "lang": "en",
    "charset": "UTF-8",
    "database": {
        "host": "localhost",
        "dbname": "lunacms",
        "user": "root",
        "password": ""
    },
    "debug": true,
    "site": {
        "name": "Your Site Name",
        "base_url": "https://www.yoursite.com/"
    },
    "mail": {
        "host": "smtp.example.com",
        "port": 587,
        "username": "your_email@example.com",
        "password": "your_email_password",
        "encryption": "tls",
        "from_address": "no-reply@example.com",
        "from_name": "Your Site Name",
        "reply_to_address": "support@example.com",
        "reply_to_name": "Support Team"
    }
}
```

### Configuration Options

- **base_path**: Absolute path to the project base directory.
- **lang**: Default language for the site.
- **charset**: Character encoding used in templates.
- **database**:
  - **host**: Database host.
  - **dbname**: Database name.
  - **user**: Database username.
  - **password**: Database password.
- **debug**: Enable or disable debug mode. When enabled, detailed error messages are shown.
- **site**:
  - **name**: Name of your site.
  - **base_url**: Base URL of your site.
- **mail**:
  - **host**: SMTP server host.
  - **port**: SMTP server port.
  - **username**: SMTP username.
  - **password**: SMTP password.
  - **encryption**: Encryption type (`tls` or `ssl`).
  - **from_address**: Default sender email address.
  - **from_name**: Default sender name.
  - **reply_to_address**: Default reply-to email address.
  - **reply_to_name**: Default reply-to name.

## Routing

LunaCMS uses a simple routing system to map URLs to controller actions. Routes are defined in the `config/routes.php` file.

### Defining Routes

Routes can include dynamic parameters enclosed in curly braces \{\}.

```php
<?php

use App\Controllers\PageController;

return [
    '' => PageController::class,
    'about' => PageController::class,
    'contact' => PageController::class,
    'blog/{slug}' => PageController::class,
];
```

### Supported HTTP Methods

Currently, LunaCMS supports `GET` and `POST` methods. You can define routes for each method using the `Routing::get` and `Routing::post` methods.

```php
use LunaCMS\Routing;

// Define a GET route
Routing::get('about', PageController::class);

// Define a POST route
Routing::post('contact', ContactController::class);
```

## Controllers

Controllers handle the business logic of your application. They extend the abstract `LunaCMS\Controller` class, which provides common functionalities such as rendering templates, accessing the database, and sending emails.

### Extending a Controller

To create a new controller, extend the `Controller` class and implement the `dataAssignment` method.

```php
<?php

namespace App\Controllers;

use LunaCMS\Controller;

class PageController extends Controller
{
    public function dataAssignment(): void
    {
        $site = $this->getConfigVar('site');
        $this->template = $this->params['slug'] ?? 'index';
        $this->css[] = '/css/main.css';
        $this->js[] = '/js/main.js';
        $this->addVar('meta_title', 'Title | ' . $site['name']);
        $this->addVar('meta_description', 'Description');
    }
}
```

### Controller Lifecycle

1. **Initialization (`__construct`)**: Sets up Twig, database connection, and mailer.
2. **Initialization (`init`)**: Assigns route parameters and executes the controller logic.
3. **Treatment (`treatment`)**: Optional method for pre-processing.
4. **Data Assignment (`dataAssignment`)**: Assign data to be passed to the view.
5. **Rendering (`display`)**: Renders the Twig template with assigned variables.

## Templating with Twig

LunaCMS uses Twig as its templating engine. Templates are located in the `templates/` directory.

### Creating a Template

Create a new Twig template in the `templates/` directory or its subdirectories.

```twig
{% extends 'includes/base.twig' %}

{% block title %}
    <h1>Welcome to {{ site.name }}</h1>
{% endblock %}

{% block content %}
    <p>{{ content }}</p>
{% endblock %}
```

### Extending Base Templates

Use the `{% extends %}` directive to inherit from base templates.

```twig
{% extends 'includes/base.twig' %}

{% block title %}
    <h1>About Us</h1>
{% endblock %}

{% block content %}
    <p>Information about us.</p>
{% endblock %}
```

### Including Partial Templates

Use the `{% include %}` directive to include partial templates.

```twig
{% include 'includes/header.twig' %}

<div class="content">
    {{ content }}
</div>

{% include 'includes/footer.twig' %}
```

## Asset Management with Gulp

LunaCMS uses Gulp to compile and manage assets such as SCSS and JavaScript files.

### Gulp Tasks

- **`scss`**: Compiles SCSS files to CSS and minifies them.
- **`js`**: Concatenates and minifies JavaScript files.
- **`watch`**: Watches for changes in SCSS and JS files and automatically recompiles them.
- **`default`**: Runs `scss`, `js`, and `watch` tasks.

### Running Gulp

Execute the following command to start Gulp tasks:

```bash
gulp
```

This will compile your assets and start watching for changes.

### Customizing Gulp

You can modify the `gulpfile.js` to add more tasks or adjust existing ones.

```javascript
const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const cleanCSS = require('gulp-clean-css');

gulp.task('scss', () => 
    gulp.src('assets/scss/**/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCSS())
        .pipe(gulp.dest('public/css'))
);

gulp.task('js', () => 
    gulp.src('assets/js/**/*.js')
        .pipe(concat('main.js'))
        .pipe(uglify())
        .pipe(gulp.dest('public/js'))
);

gulp.task('watch', () => {
    gulp.watch('assets/scss/**/*.scss', gulp.series('scss'));
    gulp.watch('assets/js/**/*.js', gulp.series('js'));
});

gulp.task('default', gulp.series('scss', 'js', 'watch'));
```

## Database Interaction

LunaCMS utilizes Doctrine DBAL for database interactions, providing a powerful abstraction layer.

### Accessing the Database Connection

Within your controller, you can access the database connection as follows:

```php
$connection = $this->getConnection();

// Example query
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $connection->prepare($sql);
$stmt->bindValue(1, $userId);
$stmt->execute();
$user = $stmt->fetchAssociative();
```

### Performing Queries

Doctrine DBAL allows you to perform various types of queries, including select, insert, update, and delete.

```php
// Select Query
$users = $connection->fetchAllAssociative('SELECT * FROM users');

// Insert Query
$connection->insert('users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Update Query
$connection->update('users', ['email' => 'john.doe@example.com'], ['id' => 1]);

// Delete Query
$connection->delete('users', ['id' => 1]);
```

## Sending Emails

LunaCMS integrates PHPMailer for sending emails. You can send emails using the `sendEmail` method provided by the `Controller` class.

### Sending an Email

```php
$toEmail = 'recipient@example.com';
$toName = 'Recipient Name';
$subject = 'Welcome to LunaCMS';
$body = '<p>Thank you for registering!</p>';
$altBody = 'Thank you for registering!';

$sent = $this->sendEmail($toEmail, $toName, $subject, $body, $altBody);

if ($sent) {
    // Email sent successfully
} else {
    // Handle the failure
}
```

### Configuring Mail Settings

Ensure that the `mail` section in your `config/config.json` is correctly configured with your SMTP server details.

```json
"mail": {
    "host": "smtp.example.com",
    "port": 587,
    "username": "your_email@example.com",
    "password": "your_email_password",
    "encryption": "tls",
    "from_address": "no-reply@example.com",
    "from_name": "Your Site Name",
    "reply_to_address": "support@example.com",
    "reply_to_name": "Support Team"
}
```