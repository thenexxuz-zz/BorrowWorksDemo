# BorrowWorksDemo

## JokeAPI
Simple Joke API that will fetch jokes for you.

### Setup
Copy `code/.env.test` to `code/.env` 

Composer install in the code/ directory along with other :
```bash
cd code/
composer install
bin/console assets:install
touch var/jokes.db
chmod 666 var/jokes.db
bin/console doctrine:migrations:migrate
bin/console app:populate
```

Run the following commands from the project root to build the Docker images and start server:
```bash
docker build -t php-base php/
docker build -t nginx-base nginx/
docker-compose up
```
Now the server is running on port 8000. Documentation on how to use the API can be found on the endpoint `/docs`
