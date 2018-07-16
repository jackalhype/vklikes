# VkLikes GEO service.
# This software run "as is".

# VK API requires access_token.
# Be sure the access_token stored in app/Models/Parsers/VkApiAbstractClient.php is live.
# E.g. hit this url, replacing ACCESS_TOKEN part:  https://api.vk.com/method/users.get?v=4.100&fields=sex,bdate,city,country,photo,photo_medium,photo_big,contacts,last_seen,status,followers_count,domain,site&user_ids=1,2,3,4,5
&access_token=ACCESS_TOKEN
# If it's not live, make your own by creating your Vk Application:
# https://vk.com/editapp?act=create

# INSTALLATION GUIDE:
# 1. Site Setup:
# 1.1 Edit .env.php file, DB connect especially
# 1.2 Create DBs:
#    vklikes - main table
#    test_vklikes - for phpunit tests
# 1.3 Nginx - Redirect all to public/index.php
# 2. Migrations, cache
php artisan clear:cache
php artisan clear:view
php artisan migrate:install
php artisan migrate

# 3. Fill Dictionaries
php artisan parse_vk:countries
php artisan parse_vk:regions -c 1
# main cities only, ~18:
php artisan parse_vk:cities -c 1 --lang=en
# other cities, few minutes execution:
php artisan parse_vk:cities -c 1 --all --lang=en

# 4. Tests run this way:
vendor/phpunit/phpunit/phpunit

