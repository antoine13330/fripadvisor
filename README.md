# Fripadvisor

 ## Get started ##
 Go to project's root and launch ```symfony serve```
 Server will launch on <http://127.0.0.1:8000>.
 
 
If you didn't already:
 - create a database named ```fripadvisor``` on your mysql server on port 3306 ( or change in DATABASE_URL DB_NAME)
 - configure your DATABASE_URL in .env.local file  where ```mysql://DB_USER@127.0.0.1:3306/DB_NAME?serverVersion=14&charset=utf8```. U can set a passphrase to your db.
 - if you want fake datas use in console ```php bin/console doctrine:fixtures:load```

 ### Documentation ####
 
After launching server, you can access the documentation at adress <http://127.0.0.1:8000/api/doc>.


### Debugging ####

If
