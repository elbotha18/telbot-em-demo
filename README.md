# Telbot Expense Manager

<p align="center"><img src="https://laravel.com/assets/img/components/logo-laravel.svg"></p>

<p align="center">
<a href="https://laravel.com/docs/10.x">Laravel 10.x</a> Expense Manager
</p>

Expense Manager is a web application built with Laravel 10, designed to help manage personal and group expenses.

This project is based on the [QuickAdminPanel Expense Manager](https://quickadminpanel.com/demo/expense-manager). Thanks to the [Quick Admin Panel](https://quickadminpanel.com) team for their work on the original project!

## Implementations

The following changes were made to the original QuickAdminPanel demo to create the Expense Manager application:

- Updated to Laravel 10
- Import Functionality
- Implemented [Chart.js](https://www.chartjs.org/) to better display the income and expense reports
- Added Entities to allow for groups to view their combined records as well as their personal records.

## Installation
1. Clone the repository to your local machine.
2. Run composer install to install the required dependencies.
3. Create a new MySQL database and configure the .env file with your database credentials.
4. Run php artisan migrate to migrate the database tables.
5. Run php artisan db:seed to seed the database with some initial data.
6. Run php artisan serve to start the application.

## Usage

The Expense Manager application allows users to track their income and expenses. Users can create, edit, and delete records, as well as view reports on their income and expenses. The application also includes import functionality to allow users to upload a CSV file of their income and expenses.

In addition to personal records, the application also includes Entities, which allow groups to view their combined records as well as their personal records.

## License

The Expense Manager project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
