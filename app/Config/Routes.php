<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ── Public routes (no auth required) ────────────────────────────
$routes->get('/auth/login',       'Auth::login');
$routes->post('/auth/loginUser',  'Auth::loginUser');
$routes->get('/auth/logout',      'Auth::logout');

// ── Authenticated routes (any logged-in user) ────────────────────
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('/', 'Home::index');

    // Expense Types
    $routes->get('/expensetype',                              'ExpenseType::index');
    $routes->get('/expensetype/getExpenseTypes',              'ExpenseType::getExpenseTypes');
    $routes->post('/expensetype/addExpenseType',              'ExpenseType::addExpenseType');
    $routes->delete('/expensetype/deleteExpenseType/(:num)',  'ExpenseType::deleteExpenseType/$1');

    // Expenses
    $routes->get('/expense',                         'Expense::index');
    $routes->get('/expense/getExpenses',             'Expense::getExpenses');
    $routes->post('/expense/addExpense',             'Expense::addExpense');
    $routes->delete('/expense/deleteExpense/(:num)', 'Expense::deleteExpense/$1');

    // Chapati Expenses
    $routes->get('/chapatiexpense',                                    'ChapatiExpense::index');
    $routes->get('/chapatiexpense/getChapatiExpenses',                 'ChapatiExpense::getChapatiExpenses');
    $routes->post('/chapatiexpense/addChapatiExpense',                 'ChapatiExpense::addChapatiExpense');
    $routes->delete('/chapatiexpense/deleteChapatiExpense/(:num)',     'ChapatiExpense::deleteChapatiExpense/$1');

    // Chapati Absences
    $routes->get('/chapatiabsence',                          'ChapatiAbsence::index');
    $routes->get('/chapatiabsence/getAbsences',              'ChapatiAbsence::getAbsences');
    $routes->post('/chapatiabsence/addAbsence',              'ChapatiAbsence::addAbsence');
    $routes->delete('/chapatiabsence/deleteAbsence/(:num)',  'ChapatiAbsence::deleteAbsence/$1');

    // Chapati Extra Expenses
    $routes->get('/chapatiextraexpense',                         'ChapatiExtraExpense::index');
    $routes->get('/chapatiextraexpense/getExtraExpenses',        'ChapatiExtraExpense::getExtraExpenses');
    $routes->post('/chapatiextraexpense/addExtraExpense',        'ChapatiExtraExpense::addExtraExpense');
    $routes->delete('/chapatiextraexpense/delete/(:num)',        'ChapatiExtraExpense::delete/$1');

    // User search (used by expense/extra expense forms for all users)
    $routes->get('/user/search', 'User::search');

    // Final Distribution — view only for all authenticated users
    $routes->get('/finaldistribution',                             'FinalDistribution::index');
    $routes->get('/finaldistribution/getDistribution/(:segment)', 'FinalDistribution::getDistribution/$1');

});

// ── Admin-only routes ────────────────────────────────────────────
$routes->group('', ['filter' => 'admin'], function ($routes) {

    // Users management — full CRUD
    $routes->get('/user',                         'User::index');
    $routes->get('/user/getUsers',                'User::getUsers');
    $routes->post('/user/addUser',                'User::addUser');
    $routes->delete('/user/deleteUser/(:num)',    'User::deleteUser/$1');
    $routes->post('/user/updateRole/(:num)',      'User::updateRole/$1');

    // Final Distribution — generate (write action, admin only)
    $routes->post('/finaldistribution/generateDistribution/(:segment)', 'FinalDistribution::generateDistribution/$1');

});