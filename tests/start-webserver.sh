if [ '$TRAVIS_PHP_VERSION' = '5.4' ]; then echo 'Starting PHP webserver'; php -S localhost:80 -t ../public & fi