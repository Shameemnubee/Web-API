Download WAMP on WAMP Server's official website
https://www.wampserver.com/en/

Make sure you have an updated version of Microsoft VC++ installed

Install downloaded WAMP file
Leave the settings as the default ones

Once the installation process has been completed, navigate to your local disk and locate the "wamp" folder
Then to the "www" folder
Lastly, copy and paste my folder "newapi" into the ""www" folder


Launch WAMP and ensure that it is running correctly

Open a web browser and navigate to the following URL:
http://localhost/phpmyadmin/

By default the username should be "root" and leave the password field blank to login

Once logged in, click on "Import" to import my database "newapi.sql" in phpMyAdmin interface

Download and Install Postman on its official website
https://www.postman.com/downloads/


Sign in into postman and put these URLs in the request:

To register users, view users, update users and delete users:
http://localhost/newapi/api/users

To allow the user to login and that generate a token that will be used to allow a user to interact with the blog:
http://localhost/newapi/api/login

To create, read, update or delete posts:
http://localhost/newapi/api/blog

For further explanation, please take a look at the "Documentation.pdf" file.
