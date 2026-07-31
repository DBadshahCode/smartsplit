<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ── Public routes (no auth required) ────────────────────────────
$routes->get('/auth/login', 'Auth::login');
$routes->post('/auth/loginUser', 'Auth::loginUser');
$routes->get('/auth/logout', 'Auth::logout');

// ── Authenticated routes (any logged-in user) ────────────────────
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('/', 'Home::index');

    // Expense Types
    $routes->get('/expensetype', 'ExpenseType::index');
    $routes->get('/expensetype/getExpenseTypes', 'ExpenseType::getExpenseTypes');
    $routes->post('/expensetype/addExpenseType', 'ExpenseType::addExpenseType');
    $routes->delete('/expensetype/deleteExpenseType/(:num)', 'ExpenseType::deleteExpenseType/$1');

    // Expenses
    $routes->get('/expense', 'Expense::index');
    $routes->get('/expense/getExpenses', 'Expense::getExpenses');
    $routes->get('/expense/getExpense/(:num)', 'Expense::getExpense/$1');
    $routes->post('/expense/addExpense', 'Expense::addExpense');
    $routes->post('/expense/updateExpense/(:num)', 'Expense::updateExpense/$1');
    $routes->delete('/expense/deleteExpense/(:num)', 'Expense::deleteExpense/$1');

    // Absent Days
    $routes->get('absentday', 'AbsentDay::index');
    $routes->get('absentday/getExpenses', 'AbsentDay::getExpenses');
    $routes->get('absentday/getAbsentDays/(:segment)', 'AbsentDay::getAbsentDays/$1');
    $routes->post('absentday/upsert', 'AbsentDay::upsert');
    $routes->delete('absentday/delete/(:num)', 'AbsentDay::delete/$1');

    // User search (used by expense/extra expense forms for all users)
    $routes->get('/user/search', 'User::search');

    // Final Distribution — view only for all authenticated users
    $routes->get('/finaldistribution', 'FinalDistribution::index');
    $routes->get('/finaldistribution/getDistribution/(:segment)', 'FinalDistribution::getDistribution/$1');

    $routes->get('profile', 'Profile::index');
    $routes->post('profile/updateInfo', 'Profile::updateInfo');
    $routes->post('profile/updatePassword', 'Profile::updatePassword');
    $routes->get('profile/getLatestDistributionMonth', 'Profile::getLatestDistributionMonth');
    $routes->get('profile/getDistributionByMonth/(:segment)', 'Profile::getDistributionByMonth/$1');
    $routes->get('finaldistribution/getLatestMonth', 'FinalDistribution::getLatestMonth');

});

// ── Admin-only routes ────────────────────────────────────────────
$routes->group('', ['filter' => 'admin'], function ($routes) {

    // Expennses
    $routes->post('expense/bulkDeleteExpenses', 'Expense::bulkDeleteExpenses');

    // Users management — full CRUD
    $routes->get('/user', 'User::index');
    $routes->get('/user/getUsers', 'User::getUsers');
    $routes->post('/user/addUser', 'User::addUser');
    $routes->delete('/user/deleteUser/(:num)', 'User::deleteUser/$1');
    $routes->post('/user/updateRole/(:num)', 'User::updateRole/$1');
    $routes->post('/user/resetPassword/(:num)', 'User::resetPassword/$1');

    // Final Distribution — generate (write action, admin only)
    $routes->post('/finaldistribution/generateDistribution/(:segment)', 'FinalDistribution::generateDistribution/$1');
    $routes->get('/finaldistribution/exportExcel/(:segment)', 'FinalDistribution::exportExcel/$1');

});

// ── Migration routes (protected by secret key) ──────────────────────────────
$routes->get('/migrate/(:any)', function ($key) {
    if ($key !== 'SmartSplit2026') {
        return 'Unauthorized';
    }

    $migrate = \Config\Services::migrations();
    $seeder = \Config\Database::seeder();

    try {
        $migrate->latest();
        $seeder->call('UserSeeder');
        $seeder->call('ExpenseTypeSeeder');
        return 'Migration + Seeding successful.';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

$routes->get('/migrate-only/(:any)', function ($key) {
    if ($key !== 'SmartSplit2026') {
        return 'Unauthorized';
    }

    try {
        \Config\Services::migrations()->latest();
        return 'Migration successful.';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

$routes->get('/seed-only/(:any)', function ($key) {
    if ($key !== 'SmartSplit2026') {
        return 'Unauthorized';
    }

    $seeder = \Config\Database::seeder();

    try {
        $seeder->call('UserSeeder');
        $seeder->call('ExpenseTypeSeeder');
        $seeder->call('ExpenseSeeder');
        return 'Seeding successful.';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});