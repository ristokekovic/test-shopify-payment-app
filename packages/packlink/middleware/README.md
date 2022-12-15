<img src="https://the-eawards.com/madrid17/wp-content/uploads/2017/10/PRO_1200-820x212.png" width="250">

# Packlink middleware Laravel package

## Set up tests

To set up tests, copy `tests/.env.example` to `/tests/.env` and
fill in proper parameters for database. Make sure to have the database
created on server.

## Artisan console commands

`php artisan packlink:maintenance:start` - starts maintenance mode

`php artisan packlink:maintenance:stop` - stops maintenance mode

`php artisan packlink:migrate` - migrate application
