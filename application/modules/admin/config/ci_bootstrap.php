<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| CI Bootstrap 3 Configuration
| -------------------------------------------------------------------------
| This file lets you define default values to be passed into views 
| when calling MY_Controller's render() function. 
| 
| See example and detailed explanation from:
| 	/application/config/ci_bootstrap_example.php
*/

$config['ci_bootstrap'] = array(

	// Site name
	'site_name' => 'Lamoga Service',

	// Default page title prefix
	'page_title_prefix' => '',

	// Default page title
	'page_title' => 'Lamoga Service',

	// Default meta data
	'meta_data'	=> array(
		'author'		=> 'Lamoga Service Developer',
		'description'	=> 'Lamoga Service',
		'keywords'		=> 'Lamoga Service'
	),
	
	// Default scripts to embed at page head or end
	'scripts' => array(
		'head'	=> array(
			'assets/dist/admin/adminlte.min.js',
			'assets/dist/admin/lib.min.js',
			'assets/dist/admin/app.min.js'
		),
		'foot'	=> array(
		),
	),

	// Default stylesheets to embed at page head
	'stylesheets' => array(
		'screen' => array(
			'assets/dist/admin/adminlte.min.css',
			'assets/dist/admin/lib.min.css',
			'assets/dist/admin/app.min.css',
            'assets/custom.css',
		)
	),

	// Default CSS class for <body> tag
	'body_class' => '',
	
	// Multilingual settings
	'languages' => array(
	),

	// Menu items
	'menu' => array(
		'home' => array(
			'name'		=> 'Home',
			'url'		=> '',
			'icon'		=> 'fa fa-home',
		),
        'service' => array(
            'name'		=> 'Services',
            'url'		=> 'service',
            'icon'		=> 'ion ion-edit',
        ),
        'auto' => array(
            'name'		=> 'Text Blocks',
            'url'		=> 'auto',
            'icon'		=> 'fa fa-cogs',
            'children'  => array(
                'General'			=> 'auto/text/general',
                'Whatsapp'			=> 'auto/text/whatsapp',
                'Facebook'		=> 'auto/text/facebook',
                'Telegram'	=> 'auto/text/telegram',
                'Consultant'		=> 'auto/text/consultant',
                'Consultant Notification'	=> 'auto/text/consultant_notification',
            )
        ),
        'online' => array(
            'name'		=> 'Online Numbers',
            'url'		=> 'user/online',
            'icon'		=> 'fa fa-users',
        ),
        'connection' => array(
            'name'		=> 'Connection',
            'url'		=> 'user/connection',
            'icon'		=> 'fa fa-connectdevelop',
        ),
        'received' => array(
            'name'		=> 'Chat History',
            'url'		=> 'whatsapp/received',
            'icon'		=> 'fa fa-comments',
        ),
        'error_log' => array(
            'name'		=> 'Error Log',
            'url'		=> 'whatsapp/error_log',
            'icon'		=> 'fa fa-exclamation-triangle',
        ),
        'chatbot_history' => array(
            'name'		=> 'Chat Bot History',
            'url'		=> 'chatBot/chatbot_history',
            'icon'		=> 'fa fa-comments',
        ),
        'whatsapp' => array(
            'name'		=> 'Whatsapp',
            'url'		=> 'whatsapp',
            'icon'		=> 'fa fa-whatsapp',	// can use Ionicons instead of FontAwesome
            'children'  => array(
                'Agent List'		=> 'whatsapp/agent',
                'Bot Messages'		=> 'whatsapp/step',
            )
        ),
		'customers' => array(
			'name'		=> 'Customers',
			'url'		=> 'user/customers',
			'icon'		=> 'fa fa-users'
		),
        'consultants' => array(
            'name'		=> 'Consultants',
            'url'		=> 'user/consultants',
            'icon'		=> 'fa fa-user-md'
        ),
        'setting' => array(
            'name'		=> 'Setting',
            'url'		=> 'whatsapp/setting',
            'icon'		=> 'fa fa-gear',
        ),
//		'user' => array(
//			'name'		=> 'Users',
//			'url'		=> 'user',
//			'icon'		=> 'fa fa-users',
//			'children'  => array(
//				'List'			=> 'user',
//				'Create'		=> 'user/create',
//				'User Groups'	=> 'user/group',
//			)
//		),
//		'blog' => array(
//			'name'		=> 'Blog',
//			'url'		=> 'blog',
//			'icon'		=> 'ion ion-edit',	// can use Ionicons instead of FontAwesome
//			'children'  => array(
//				'Blog Posts'		=> 'blog/post',
//				'Blog Categories'	=> 'blog/category',
//				'Blog Tags'			=> 'blog/tag',
//			)
//		),
//		'cover_photo' => array(
//			'name'		=> 'Cover Photos',
//			'url'		=> 'cover_photo',
//			'icon'		=> 'ion ion-image',	// can use Ionicons instead of FontAwesome
//		),
//		'panel' => array(
//			'name'		=> 'Admin Panel',
//			'url'		=> 'panel',
//			'icon'		=> 'fa fa-cog',
//			'children'  => array(
//				'Admin Users'			=> 'panel/admin_user',
//				'Create Admin User'		=> 'panel/admin_user_create',
//				'Admin User Groups'		=> 'panel/admin_user_group',
//			)
//		),
//		'util' => array(
//			'name'		=> 'Utilities',
//			'url'		=> 'util',
//			'icon'		=> 'fa fa-cogs',
//			'children'  => array(
//				'Database Versions'		=> 'util/list_db',
//			)
//		),
		'logout' => array(
			'name'		=> 'Sign Out',
			'url'		=> 'panel/logout',
			'icon'		=> 'fa fa-sign-out',
		)
	),

	// Login page
	'login_url' => 'admin/login',

	// Restricted pages
	'page_auth' => array(
		'user/create'				=> array('webmaster', 'admin', 'manager'),
		'user/group'				=> array('webmaster', 'admin', 'manager'),
		'panel'						=> array('webmaster'),
		'panel/admin_user'			=> array('webmaster'),
		'panel/admin_user_create'	=> array('webmaster'),
		'panel/admin_user_group'	=> array('webmaster'),
		'util'						=> array('webmaster'),
		'util/list_db'				=> array('webmaster'),
		'util/backup_db'			=> array('webmaster'),
		'util/restore_db'			=> array('webmaster'),
		'util/remove_db'			=> array('webmaster'),
	),

	// AdminLTE settings
	'adminlte' => array(
		'body_class' => array(
			'webmaster'	=> 'skin-purple',
			'admin'		=> 'skin-red',
			'manager'	=> 'skin-black',
			'staff'		=> 'skin-blue',
		)
	),

	// Useful links to display at bottom of sidemenu
	'useful_links' => array(
//		array(
//			'auth'		=> array('webmaster', 'admin', 'manager', 'staff'),
//			'name'		=> 'Frontend Website',
//			'url'		=> '',
//			'target'	=> '_blank',
//			'color'		=> 'text-aqua'
//		),
//		array(
//			'auth'		=> array('webmaster', 'admin'),
//			'name'		=> 'API Site',
//			'url'		=> 'api',
//			'target'	=> '_blank',
//			'color'		=> 'text-orange'
//		),
	),

	// Debug tools
	'debug' => array(
		'view_data'	=> false,
		'profiler'	=> false
	),
);

/*
| -------------------------------------------------------------------------
| Override values from /application/config/config.php
| -------------------------------------------------------------------------
*/
$config['sess_cookie_name'] = 'ci_session_admin';