# Huong dan chay BE
## Moi truong
> - php: ^8
> - composer: 2.5.5  
> - mysql
> - sqlite

## cac buoc chuan bi
- tao file `.env` tu file `.env.example`
- cap nhat cac truong 
``` 
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lms
DB_USERNAME=root
DB_PASSWORD=
```

## cac lenh chay khoi tao ban dau
> `composer install`  
> `php artisan key:generate`  
> `php artisan migrate:refresh --seed`  
  
## lenh chay server

### Cần chạy tất cả các lệnh trên các terminal khác nhau

> `php artisan serve`  
> `php artisan queue:listen`
