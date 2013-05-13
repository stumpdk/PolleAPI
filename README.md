PolleAPI
========

The PolleAPI is a simple API module made for the crowdsourcing project politietsregisterblade.dk, and is used to serve data internal and external for users who wants to use the data.

To use the API-module, proceed with the following steps:
1) Download the code, and put the "public"-folder in a public folder on your server, and the API-folder in a non-public folder.
2) If you want to enable statistics, create a table for statistics in your MySQL-database.
3) Setup Config.php.
4) Add new API calls in the RequestHandler class, following the examples, and you are good to go!
