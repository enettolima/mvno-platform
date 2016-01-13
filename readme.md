# Natural PHP
###The open source framework for natural app development in PHP
***
Natural PHP is a framework that incorporates other open source projects
to provide a feature rich platform for app development.  
Develop fast, develop Naturally !

## Framework Requirements
* PHP 5+
* MYSQL 5+
* [PHP Composer] (https://getcomposer.org/)

### Apache configuration
#### Requirements
* Apache mod_rewrite enabled (Restler APIs and Docs)
* Apache must be able to write to the API cache folder under YOUR_PROJECT/api
* Apache must be able to write to the API docs folder under YOUR_PROJECT/api/docs
#### Instructions
``` 
<Directory /var/www/html/>
      Options Indexes FollowSymLinks
       AllowOverride All
</Directory>
```

####Enable mod_rewrite in Apache  
`a2enmod rewrite`  

####Restart Apache service  
`service apache2 restart`

### Nginx Configuration

```
server {
	listen 80 default;
 
	root /var/www/;
	index index.php index.html index.htm;
 
	server_name localhost:8888;

        access_log /var/www/logs/access_log.txt;
	error_log /var/www/logs/error_log.txt;

	location / {
		try_files $uri $uri/ =404;
	}
 
	error_page 404 /404.html;
 
	error_page 500 502 503 504 /50x.html;
	location = /50x.html {
		root /var/www;
	}
 
    location /api {
        try_files $uri /api/index.php;
        gzip    off;
        fastcgi_pass    unix:/var/run/php5-fpm.sock;
        fastcgi_index   index.php;
        fastcgi_param   SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        include         fastcgi_params;
    }
 
    location /api/doc {
	alias /var/www/api/doc;
    }


	location ~ \.php$ {
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		fastcgi_pass unix:/var/run/php5-fpm.sock;
		fastcgi_index index.php;
		include fastcgi_params;
	}
}

```

## Install with composer
To install the latest stable release:  
`composer create-project opensourcemind/natural-php PROJECT_FOLDER 2.0.5`

To install the latest development release from the master branch:  
`composer create-project -s dev opensourcemind/natural-php PROJECT_FOLDER`

To install the another specific version (i.e. 2.0.3 ):  
`composer create-project opensourcemind/natural-php PROJECT_FOLDER 2.0.3`

##Configure your database
After installing you should edit your database information in the files `bootstrap.php`, if you would like  
to keep both a production and development database config you can just duplicate the bootstrap file  
into a new file named `bootstrap.dev.php` which Natural automatically prefers over production config  
when present.  

Natural also requires some specific tables available in your database and the easiest way to add them  
to your database is to import the file `natural_framework.sql` available in your project folder.

For your conveninece you can just run the initdb.php script located in the tools directory to wipe
any instances of natural_framework database and deploy from the .sql file.

```
php -p tools/initdb.php
```
or
```
./tools/initdb.php
```

Vagrant database credentials
user: root
pass: 123456


## Deploy / Develop with Vagrant
Natural code base includes a Vagrantfile and required provisioning script for an Nginx
environment. Once you install natural-php all you have to do is type `vagrant up`
and you are up and running.

*This requires you to have Vagrant installed and ready to go.