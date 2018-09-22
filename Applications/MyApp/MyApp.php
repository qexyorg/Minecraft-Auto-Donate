<?php // Alonity config MyApp | Updated: 22.09.2018 21:18:19

return array (
  'version' => '1.0.0',
  'name' => 'My Simple App',
  'about' => 'Simple App',
  'author' => 'Qexy',
  'routes' => '/Applications/MyApp/Routes/*',
  'items' => 
  array (
    0 => 
    array (
      'title' => 'VIP',
      'price' => 50,
      'value' => 'vip',
    ),
    1 => 
    array (
      'title' => 'Premium',
      'price' => 100,
      'value' => 'premium',
    ),
    2 => 
    array (
      'title' => 'Creative',
      'price' => 200,
      'value' => 'creative',
    ),
  ),
  'unitpay' => 
  array (
    'public' => '1111-11111',
    'private' => '11111111111111111111111111111111',
  ),
  'server' => 
  array (
    'ip' => 'mc.superserver.com',
    'status' => false,
    'online' => 0,
    'slots' => 1000,
    'record' => 0,
    'today' => 0,
    'updated' => 0,
    'cache' => 15,
  ),
  'meta' => 
  array (
    'sitename' => 'SuperServer',
    'sitedesc' => 'Лучший сервер',
    'sitekeys' => 'Alonity, MySite, Qexy',
    'theme_url' => '/Themes/',
    'site_url' => '/',
    'full_site_url' => 'http://superserver.com',
    'token' => 'vUGTrET!FBHt№oUlrnsst]?u;eyYUAn',
    'cache_version_css' => 1,
    'cache_version_js' => 1,
  ),
  'components' => 
  array (
    0 => '/Components/Crypt/*',
    1 => '/Components/Cache/*',
    2 => '/Components/Database/*',
    3 => '/Components/Filters/*',
    4 => '/Components/File/*',
  ),
  'mysqli' => 
  array (
    'host' => '127.0.0.1',
    'port' => 3306,
    'charset' => 'utf8',
    'timeout' => 3,
    'database' => 'database',
    'user' => 'root',
    'password' => '',
  ),
);

?>