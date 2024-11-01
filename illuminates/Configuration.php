<?php

namespace Illuminate;

class Configuration
{
    public const ROOT_NAMESPACE = __NAMESPACE__;

    public const API_RESPONSE_STATUS_OK = 200;
    public const API_RESPONSE_STATUS_ERROR = -1;
    public const API_RESPONSE_STATUS_ERROR_AUTH = 401;
    public const API_RESPONSE_STATUS_REDIRECT = 2;
    public const API_RESPONSE_STATUS_UNAUTHENTICATE = 5;

    public const DEFAULT_CURRENT_PAGE = 1;
    public const DEFAULT_PAGINATION_ITEMS = 10;
    public const ALLOWED_ITEMS_PER_PAGE = [10, 20, 50, 100];

    public const ALLOWED_ITEMS_SORT_ORDER = ['DESC', 'ASC'];

    //global asset version
    public const ASSETS_VERSION = '1.1';

    //global status
    public const STATUS_ACTIVE = 1;
    public const STATUS_UNACTIVE = 0;

    public const EXPIRATION_TIME = 600000;

    public const CRISP_ID     = 'f5733b35-0234-49e3-aca0-ed27b30b8ee1';
    public const APP_API_URL  = 'https://api-wp.transcy.io';
    public const APP_URL      = 'https://wp.transcy.io';
    public const APP_BUILD    = 'prod';

    public const RESOURCES_POSTS            = ['post', 'page', 'product', 'nav_menu_item'];
    public const RESOURCES_NO_TRANSLATE     = ['shop_order', 'attachment'];
    public const RESOURCES_TERMS            = ['category', 'nav_menu', 'product_cat', 'product_tag', 'post_tag'];

    public const TAG_TRANSLATE              = '<tc>';

    public const POST_STATUS_DEACTIVE       = 'transcy-';

    public const TERM_TAXONOMY_DEACTIVE     = 'transcy-';

    public const POST_STATUS_NOT_SYNC       = ['auto-draft'];
    public const POST_STATUS_SYNC           = ['publish', 'draft', 'pending', 'private'];
}
