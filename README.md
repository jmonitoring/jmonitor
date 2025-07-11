Jmonitor
=========

Easy monitoring for PHP web server. 

Getting Started
---------------
To get started, require the project using [Composer](https://getcomposer.org/).  
It should install for you a PSR-18 httpclient for you if you don't have one already.

```bash
composer require jmonitoring/jmonitor
```

```php
use Jmonitor\Jmonitor;
use Jmonitor\Collector\Apache\ApacheCollector;

$jmonitor = new Jmonitor('apiKey');

// Add some collectors 
$jmonitor->addCollector(new ApacheCollector('https://example.com/server-status'));
$jmonitor->addCollector(new SystemCollector());
// see the documentation below for more collectors

// send metrics periodically to jmonitor (ex. every 15 seconds)
$jmonitor->collect();
```


You can customize yout HttpClient, for example, if you want to use the [Symfony HttpClient](https://symfony.com/doc/current/http_client.html#psr-18-and-psr-17) or Guzzle.

```bash
composer require symfony/http-client nyholm/psr7
```

```php
use Symfony\Component\HttpClient\Psr18Client;

$httpClient = ... // create or retrieve your Symfony HttpClient instance
$client = new Psr18Client()->withOptions(...);

$jmonitor = new Jmonitor('apiKey', $client);

```

Collectors
-----------
- ### System
  Collects system metrics like CPU usage, memory usage, disk usage, etc.  
  Only Linux is supported for now, feel free to open an issue if you need support for another OS.

  ```php
  use Jmonitor\Collector\System\SystemCollector;
    
  $collector = new SystemCollector();
  ```

- ### Apache 
  Collects Apache server metrics from a server status URL.  
  You'll need to enable the `mod_status` module in Apache and set up a server status URL.
  There are some resources to help you with that:
  - Apache doc :https://httpd.apache.org/docs/current/mod/mod_status.html.
  - Blogpost in English : https://statuslist.app/apache/apache-status-page-simple-setup-guide/
  - Blogpost in French : https://www.blog.florian-bogey.fr/activer-et-configurer-le-server-status-apache-mod_status.html  

  Then you'll be able to use the `ApacheCollector` class to collect metrics from the server status URL.

  ```php
  use Jmonitor\Collector\Apache\ApacheCollector;
  
  $collector = new ApacheCollector('https://example.com/server-status');
  ```

- ### Mysql 
    Collects MySQL server variables and status.  
    You'll need to use PDO or Doctrine to connect to your MySQL database. If you need support for other drivers, like Mysqli, please open an issue.
    
  ```php
  use Jmonitor\Collector\Mysql\MysqlCollector;
  use Jmonitor\Collector\Mysql\Adapter\PdoAdapter;
  use Jmonitor\Collector\Mysql\Adapter\DoctrineAdapter;
  use Jmonitor\Collector\Mysql\MysqlStatusCollector;
  use Jmonitor\Collector\Mysql\MysqlVariablesCollector;
  
  // with PDO
  $adapter = new PdoAdapter($pdo); // retrieve your \PDO connection
  
  // or Doctrine DBAL
  $adapter = new DoctrineAdapter($connection) /* retrieve your Doctrine\DBAL\Connection connection*/ );
  
  // Mysql has multiple collectors, use the same adapter for all of them
  $collector = new MysqlStatusCollector($adapter);
  $collector = new MysqlVariablesCollector($adapter);
  ```

- ### Php
  Collects PHP metrics like loaded extensions, some ini settings, opcache status, etc.  
  Php FPM status URL support is coming soon.

  ```php
  use Jmonitor\Collector\Php\PhpCollector
  
  $collector = new PhpCollector();
  ```

- ### Redis
  Collects Redis metrics from info command.
  
  ```php
  use Jmonitor\Collector\Redis\RedisCollector;
  
  // You can use any Redis client that supports the info command, like Predis or PhpRedis.
  $redisClient = new \Redis([...]);
  $redisClient = new Predis\Client();
  // also support \RedisArray, \RedisCluster, Relay... feel free to open an issue if you need support for another client.
  
  $collector = new RedisCollector($redis);
  ```
