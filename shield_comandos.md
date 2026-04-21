php artisan migrate:fresh --seed

php artisan shield:generate --all --panel=admin

php artisan shield:seeder --all --force

php artisan db:seed --class=ShieldSeeder

php artisan permission:cache-reset 

 php artisan shield:super-admin

