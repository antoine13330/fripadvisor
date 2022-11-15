# Fripadvisor

 ## Get started ##
 Go to project's root and launch ```symfony serve```
 Server will launch on <http://127.0.0.1:8000>.
 
 
If you didn't already:
 - create a database named ```fripadvisor``` on your mysql server on port 3306 ( or change in DATABASE_URL DB_NAME)
 - configure your DATABASE_URL in .env.local file  where ```mysql://DB_USER@127.0.0.1:3306/DB_NAME?serverVersion=14&charset=utf8```. U can set a passphrase to your db.
 - if you want fake datas use in console ```php bin/console doctrine:fixtures:load```

Now to get the JWT_TOKEN ( to insert in the authorization of HTTP Request ) , use <http://127.0.0.1:8000/api/login_check>.

You are set up to request our Fripadvisor API. See the Documentation for more infos !

 ### Documentation ####
 
After launching server, you can access the documentation at adress <http://127.0.0.1:8000/api/doc>.

### Debugging ####

- If your server did start but you can't make any request due to unaccessible DB, check your db credentials in DATABASE_URL. 
  - If it didn't solve the problem check your mysql server status.  ( Check if port 3306 is used with ``` netstat -ano in cmd```. If so : use ```taskkill /F /PID pid_using_port```.
