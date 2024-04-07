<?php

namespace App\Enums;

enum PermissionEnum
{
    const READ_USERS = 'read users';
    const CREATE_USERS = 'create users';
    const UPDATE_USERS = 'update users';
    const DELETE_USERS = 'delete users';

    const READ_ROLES_PERMISSIONS = 'read roles & permissions';
    const CREATE_ROLES_PERMISSIONS = 'create roles & permissions';
    const UPDATE_ROLES_PERMISSIONS = 'update roles & permissions';
    const DELETE_ROLES_PERMISSIONS = 'delete roles & permissions';

    const READ_PRODUCTS = 'read products';
    const CREATE_PRODUCTS = 'create products';
    const UPDATE_PRODUCTS = 'update products';
    const DELETE_PRODUCTS = 'delete products';

    const READ_ORDERS = 'read orders';
    const CREATE_ORDERS = 'create orders';
    const UPDATE_ORDERS = 'update orders';
    const DELETE_ORDERS = 'delete orders';

    const READ_CATEGORIES = 'read categories';
    const CREATE_CATEGORIES = 'create categories';
    const UPDATE_CATEGORIES = 'update categories';
    const DELETE_CATEGORIES = 'delete categories';

    const READ_CUSTOMERS = 'read customers';
    const CREATE_CUSTOMERS = 'create customers';
    const UPDATE_CUSTOMERS = 'update customers';
    const DELETE_CUSTOMERS = 'delete customers';

    const ACTIVITY_LOGS = 'activity logs';
    const UPDATE_ORDERS_STATUS = 'update orders status';
}
