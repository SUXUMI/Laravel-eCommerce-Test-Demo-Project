## Laravel eCommerce Test Demo Project
-- -
Just for demonstration purposes.<br>
Test project includes built-in approaches (Model/Migration/Factory/Services/Facade/UnitTest/FeatureTest) using Product/Cart/Payment/Order components.

### Usage

<br>

#### Up & run the local work environment
**Docker** is required

Build an image
```dockerfile
docker-compose build
```

Register domain name in hosts file binding `127.0.0.1  laravel.local`

For Windows users run `win.update-hosts.cmd` (Administrator privilege is required)

Run docker container in a detached mode
```dockerfile
docker-compose up -d
```

<br>

#### Run migration & seed
```shell
php artisan migrate && php artisan db:seed 
```

<br>

#### Run tests using interactive shell
```dockerfile
docker exec -it php /bin/sh

php artisan test

# or run separately

php artisan test --filter ProductUnitTest
php artisan test --filter ProductTest
php artisan test --filter CartServiceUnitTest
php artisan test --filter PaymentServiceUnitTest
php artisan test --filter OrderServiceUnitTest
```

<br>

Website url: https://laravel.local <br>
PhpMyAdmin: start http://localhost:8080

<cite>in case of SSL error:</cite> 
- re-generate certificates from `/docker/generate-certs.cmd`
- check port 443 if not already used on local machine

<br>
