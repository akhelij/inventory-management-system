<?php

namespace App\Enums;

enum PermissionEnum
{
    public const READ_USERS = 'read users';

    public const CREATE_USERS = 'create users';

    public const UPDATE_USERS = 'update users';

    public const DELETE_USERS = 'delete users';

    public const READ_ROLES_PERMISSIONS = 'read roles & permissions';

    public const CREATE_ROLES_PERMISSIONS = 'create roles & permissions';

    public const UPDATE_ROLES_PERMISSIONS = 'update roles & permissions';

    public const DELETE_ROLES_PERMISSIONS = 'delete roles & permissions';

    public const READ_PRODUCTS = 'read products';

    public const CREATE_PRODUCTS = 'create products';

    public const UPDATE_PRODUCTS = 'update products';

    public const DELETE_PRODUCTS = 'delete products';

    public const READ_ORDERS = 'read orders';

    public const CREATE_ORDERS = 'create orders';

    public const UPDATE_ORDERS = 'update orders';

    public const DELETE_ORDERS = 'delete orders';

    public const UPDATE_ORDERS_STATUS = 'update orders status';

    public const READ_CATEGORIES = 'read categories';

    public const CREATE_CATEGORIES = 'create categories';

    public const UPDATE_CATEGORIES = 'update categories';

    public const DELETE_CATEGORIES = 'delete categories';

    public const READ_CUSTOMERS = 'read customers';

    public const CREATE_CUSTOMERS = 'create customers';

    public const UPDATE_CUSTOMERS = 'update customers';

    public const DELETE_CUSTOMERS = 'delete customers';

    public const READ_REPAIRS = 'read repairtickets';

    public const CREATE_REPAIRS = 'create repairtickets';

    public const UPDATE_REPAIRS = 'update repairtickets';

    public const DELETE_REPAIRS = 'delete repairtickets';

    public const ACTIVITY_LOGS = 'activity logs';
}
