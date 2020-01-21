<?php
namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'reading_diary_api');

// Project repository
set('repository', 'git@github.com:vladshut/reading-diary-api.git');

// [Optional] Dont allocate tty for git clone to avoid error in gitlab ci. Default value is false.
set('git_tty', false);
set('pry', false);
set('ssh_multiplexing', false);

// Shared files/dirs between deploys
add('shared_files', ['.env']);
add('shared_dirs', ['storage']);

// Writable dirs by web server
add('writable_dirs', []);


host('root@91.235.128.222')
    ->set('deploy_path', '/home/admin/web/api.reading-diary.com/public_html');
set('http_user', 'www-data');

// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

task('config:clear', function () {
    run('cd {{release_path}} && php artisan config:clear');
});

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'artisan:storage:link',
    'artisan:view:clear',
    'artisan:config:cache',
    'config:clear',
    'artisan:cache:clear',
//    'artisan:optimize',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'artisan:migrate');

