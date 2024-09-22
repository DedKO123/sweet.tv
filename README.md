# Sweet TV RSS Report Generator

- [Install](#install)

Prerequisites:
- Docker
## Install

1. Clone the repository:
```
git clone https://github.com/DedKO123/sweet.tv.git
```
2. Navigate into the project directory:
```
cd sweet.tv
```
3. Copy the .env.example file to .env:
```
cp .env.example .env
```
4. Set up your database credentials in the .env file.
5. Install the composer dependencies:
```
 composer install
```
6. Build the Docker containers:
```
./vendor/bin/sail up -d
```

7. Generate the application key:
```
./vendor/bin/sail artisan key:generate
```
8. Run the database migrations:
```
./vendor/bin/sail artisan migrate
```
9. Visit the application in your browser:
```
http://localhost
```
