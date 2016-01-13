<?php
//error_reporting(E_ALL ^ E_NOTICE);
  define('NATURAL_ROOT_PATH', dirname(__FILE__));
  if (file_exists(NATURAL_ROOT_PATH . '/bootstrap.dev.php')) {
    require(NATURAL_ROOT_PATH . '/bootstrap.dev.php');
  } else {
  /* Define system defaults and main configuration */

  //SET NATURAL INTO DEVELOPMENT MODE
  //CHANGE TO FALSE WHEN RUNNING
  //IN PRODUCTION ENVIRONMENT
  define('NATURAL_DEV_MODE', FALSE);
  define('ENABLE_COLOR_CHANGE', FALSE);

  //SET APPLICATION PATHS
  define('NATURAL_WEB_ROOT'       , './');
  define('TITLE'                  , 'mvno');
  //define('NATURAL_ROOT_PATH'    , dirname(__FILE__));
  define('NATURAL_LIB_PATH'       , NATURAL_ROOT_PATH . '/lib/');
  define('NATURAL_CLASSES_PATH'   , NATURAL_ROOT_PATH . '/lib/classes/');
  define('NATURAL_IMAGE_PATH'     , NATURAL_ROOT_PATH . '/media/images/');
  define('NATURAL_TEMPLATE_PATH'  , NATURAL_ROOT_PATH . '/themes/natural/');
  define('NATURAL_WIDGET_TEMPLATE_PATH'  , NATURAL_ROOT_PATH . '/templates/widgets/');
  define('THEME_PATH'             , 'themes/natural/');
  define('PAGER_LIMIT'            , 25);

  //SET FORMS/MENUS TABLES
  define('FORM_TABLE', 'form_templates');
  define('FIELD_TABLE', 'field_templates');
  define('FIELDSET_TABLE','fieldsets');

  //SET UTIL
  require_once(NATURAL_LIB_PATH.'util.php');

  //Autoloaders
  require_once( NATURAL_ROOT_PATH . '/vendor/autoload.php');

  //E-MAIL SENDER
  define('NATURAL_EMAIL_SENDER', 'noreply@natural.com');

  //WEBSITE DOMAIN
  define('NATURAL_DOMAIN', 'http://localhost:8888');

  //COMPANY NAME
  define('NATURAL_COMPANY', 'mvno LLC');

  //PLATFORM NAME
  define('NATURAL_PLATFORM', 'mvno');

  //DEFINE PROJECT DATABASE NAME
  define('NATURAL_DBNAME', 'mvno');

  //DATABASE INFORMATION USED ACCROSS APP FOR READ/WRITE
  define('NATURAL_PDO_DSN_READ', 'mysql:dbname='.NATURAL_DBNAME.';host=127.0.0.1;port=3306');
  define('NATURAL_PDO_USER_READ', 'root');
  define('NATURAL_PDO_PASS_READ', '3XwgjG0tVg');

  define('NATURAL_PDO_DSN_WRITE', 'mysql:dbname='.NATURAL_DBNAME.';host=127.0.0.1;port=3306');
  define('NATURAL_PDO_USER_WRITE', 'root');
  define('NATURAL_PDO_PASS_WRITE', '3XwgjG0tVg');

  //Temporary DB info to support datamanager
  define('NATURAL_DBHOST', 'localhost');
  define('NATURAL_DBUSER', 'root');
  define('NATURAL_DBPASS', '3XwgjG0tVg');

  //SET MAGIC KEY
  define('NATURAL_MAGIC_KEY', '68eKAgHqaS2mY5VCfE1jdPATwEfU5DD7R0nzCJ2cdnhgA32Ym21U');

  //SET API KEY FOR RESTLER
  define('NATURAL_API_KEY', '8f4ef05b543fb6157b374099100574b3');

  //SET NATURAL CURRENT VERSION
  define('NATURAL_VERSION', 'mvno 1.0b');

  //SET DEFAULT ICONS
  define("NATURAL_EDIT_ICON", "fa fa-pencil");
  define('NATURAL_REMOVE_ICON', 'fa fa-trash-o');

  // Twig Template Engine.
  $loader = new Twig_Loader_Filesystem(NATURAL_ROOT_PATH . '/templates');
  $twig = new Twig_Environment($loader, array(
    'debug' => TRUE,
    'cache' => NATURAL_ROOT_PATH . '/compilation_cache',
    'auto_reload' => TRUE,
  ));
  $twig->addExtension(new Twig_Extension_Debug());

  // Twig Template Engine.
  $loaderwidget = new Twig_Loader_Filesystem(NATURAL_ROOT_PATH . '/templates/widgets');
  $twigwidgets = new Twig_Environment($loaderwidget, array(
    'debug' => TRUE,
    'cache' => NATURAL_ROOT_PATH . '/compilation_cache',
    'auto_reload' => TRUE,
  ));
  $twigwidgets->addExtension(new Twig_Extension_Debug());
  }
?>
