- yêu cầu: 
    + mysql > 5.6 
    + php >7.3
    + composer
    + nginx/apache tùy
- trên linux có thể dùng laradock, trên win có thể dùng xamp

- cài đặt các service cần thiết, cấu hình connect tới các service, config apache/nginx
cd vào thư mục dự án:
run: 
    composer i
    cp .env-example .env
    php artisan key:generate
    php artisan migrate
    php artisan db:seed
    chmod -Rf 777 public storage bootstrap
    
- Với Maria-Mysql cài ở Win cần cấu hình trong mysql.ini theo link: https://stackoverflow.com/questions/3466872/why-cant-a-text-column-have-a-default-value-in-mysql
  để có thể chạy được php artisan migrate (trong Laravel)
    
