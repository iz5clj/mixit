@setup
$user = get_current_user();

$repo = 'ssh://git-codecommit.us-east-1.amazonaws.com/v1/repos/fct';
$release_dir = '/home/auxe/domains/fct.auxe.net/releases';
$app_dir = '/home/auxe/domains/fct.auxe.net/public_html';
$laravel_dir = '/home/auxe/domains/fct.auxe.net/laravel';
$release = date('Ymd-His');
$env = 'production';

function logMessage($message) {
    return "echo '\033[32m" .$message. "\033[0m';\n";
}

@endsetup

@servers(['remote' => 'aws03-auxe', 'local' => '127.0.0.1'])

@macro('init', ['on' => 'remote'])
    fetch_repo
    move-storage
    copy-env
    run_composer
    update_symlinks
    {{-- update_permissions --}}
@endmacro

@macro('deploy', ['on' => 'remote'])
    fetch_repo
    delete-storage
    delete-env
    run_composer
    update_symlinks
    cache-clear
    {{-- update_permissions --}}
@endmacro

@task('fetch_repo')
    {{ logMessage('Fetching the repo..')}}
    [ -d {{ $release_dir }} ] || mkdir {{ $release_dir }};
    cd {{ $release_dir }};
    git clone {{ $repo }} --depth=1 {{ $release }};
    echo "Repository cloned"
@endtask

@task('move-storage')
    [ -d {{ $laravel_dir }} ] || mkdir {{ $laravel_dir }};
    mv {{ $release_dir }}/{{ $release }}/storage {{ $laravel_dir }};
    ln -nfs {{ $laravel_dir }}/storage {{ $release_dir }}/{{ $release }}/storage;
    echo "Storage directory has been moved and symlink created.";
@endtask

@task('delete-storage')
    rm -Rf {{ $release_dir }}/{{ $release }}/storage
    ln -nfs {{ $laravel_dir }}/storage {{ $release_dir }}/{{ $release }}/storage;
    echo "Storage directory has been deleted and symlink created.";
@endtask

@task('delete-env')
    rm {{ $release_dir }}/{{ $release }}/env.production;
    ln -nfs {{ $laravel_dir }}/.env {{ $release_dir }}/{{ $release }}/.env;
    echo "Created or updated .env file and created symlink."
@endtask

@task('copy-env')
    mv {{ $release_dir }}/{{ $release }}/env.production {{ $laravel_dir }}/.env;
    ln -nfs {{ $laravel_dir }}/.env {{ $release_dir }}/{{ $release }}/.env;
    echo "Created or updated .env file and created symlink."
@endtask

@task('run_composer')
    cd {{ $release_dir }}/{{ $release }};
    composer install --no-interaction --prefer-dist;
    echo "Good job Michel; composer has installed all modules. (task: run_composer)";
@endtask

@task('update_permissions')
    cd {{ $release_dir }};
    chgrp -R www-data {{ $release }};
    chmod -R ug+rwx {{ $release }};
    chmod -R 775 {{ $release }}/storage;
    chmod -R 775 {{ $release }}/bootstrap/cache;
    echo "Permissions updated";
@endtask

@task('update_symlinks')
    ln -nfs {{ $release_dir }}/{{ $release }}/public {{ $app_dir }};
    {{-- chgrp -h www-data {{ $app_dir }}; --}}
    echo "Symlinks created.(task: update_symlinks)";
@endtask

@task('cache-clear')
    cd {{ $release_dir }}/{{ $release }};
    php artisan cache:clear;
    php artisan clear-compiled;
    php artisan view:clear;
@endtask

@task('purge', ['on' => 'remote'])
    # This will list our releases by modification time and delete all but the 5 most recent.
    purging=$(ls -dt {{ $release_dir }}/* | tail -n +5);

    if [ "$purging" != "" ]; then
        echo Purging old releases: $purging;
        rm -rf $purging;
    else
        echo "No releases found for purging at this time";
    fi
@endtask

@task('user', ['on' => 'local'])
    echo "{{ $user }}";
@endtask
