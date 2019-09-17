<?php

if (php_sapi_name() != 'cli') {

    exit;

}

if (!empty($argv[1])) {

    switch ($argv[1]) {

        case 'help':
            echo "\033[92mПример выполнения скрипта для экспорта: " . PHP_EOL .
                'php -f tools/db_hotcopy.php export source-dir=/dev/null/backup user=test password=pass database=db_name' . PHP_EOL .
                'source-dir - место хранения бекапа' . PHP_EOL .
                'user - пользователь БД' . PHP_EOL .
                'password - пароль пользователя' . PHP_EOL .
                "database - наименование БД \033[0m" . PHP_EOL . PHP_EOL;

            echo "\033[92mПример выполнения скрипта для импорта: " . PHP_EOL .
                'php -f tools/db_hotcopy.php import source-dir=/dev/null/backup target-dir=/dev/null/mysql/data database=db_name database-new=db_name_new user=test password=pass ' . PHP_EOL .
                'source-dir - место хранения бекапа' . PHP_EOL .
                'target-dir - место, где находятся файлы БД' . PHP_EOL .
                'database - наименование импортируемой БД' . PHP_EOL .
                'database_new - наименование новой БД' . PHP_EOL .
                'user - пользователь БД' . PHP_EOL .
                "password -  пароль пользователя \033[0m" . PHP_EOL;
            break;

        case 'export':

            foreach ($argv as $arg) {
                $var = explode('=', $arg);
                $v = str_replace('-', '_', $var[0]);
                $$v = $var[1];
            }

            if (empty($source_dir)) {
                echo "\033[31mНет аргумента source-dir \033[0m " . PHP_EOL . 'Для справки вызовите скрипт с аргументом help' . PHP_EOL;
                die;
            }

            if (empty($user)) {
                echo "\033[31mНет аргумента user \033[0m " . PHP_EOL . 'Для справки вызовите скрипт с аргументом help' . PHP_EOL;
                die;
            }

            if (empty($password)) {
                echo "\033[31mНет аргумента password \033[0m " . PHP_EOL . 'Для справки вызовите скрипт с аргументом help' . PHP_EOL;
                die;
            }

            if (empty($database)) {
                echo "\033[31mНет аргумента database \033[0m " . PHP_EOL . 'Для справки вызовите скрипт с аргументом help' . PHP_EOL;
                die;
            }

            echo "\e[33mЗапуск бекапа базы " . $database . "\e[0m" . PHP_EOL;
            $log = exec('/usr/local/mysql/bin/mariabackup --backup --target-dir=' . $source_dir . ' --databases=' . $database . ' --user=' . $user . ' --password=' . $password);
            echo "\e[33mГотово\e[0m" . PHP_EOL;

            echo "\e[33mПодготовка бекапа данных\e[0m" . PHP_EOL;
            $log = exec('/usr/local/mysql/bin/mariabackup --prepare --export --target-dir=' . $source_dir);
            echo "\e[33mГотово\e[0m" . PHP_EOL;

            echo "\e[33mПодготовка SQL схемы для бекапа данных \e[0m " . PHP_EOL;
            $log = exec('/usr/local/mysql/bin/mysqldump --no-data -u' . $user . '  -p' . $password . ' ' . $database . ' > ' . $source_dir . '/' . $database . '.sql');
            echo "\e[33mГотово\e[0m" . PHP_EOL;

            echo "\e[33mУстановка прав для бекапа на пользователя mysql \e[0m " . PHP_EOL;
            $log = exec('chown -R mysql:mysql ' . $source_dir);
            echo "\e[33mГотово\e[0m" . PHP_EOL . PHP_EOL;

            echo "\e[33mБекап завершен\e[0m" . PHP_EOL;
            break;

        case 'import':

            foreach ($argv as $arg) {
                $var = explode('=', $arg);
                $v = str_replace('-', '_', $var[0]);
                $$v = $var[1];
            }

            if (empty($source_dir)) {
                echo "\033[31mНет аргумента source-dir \033[0m " . PHP_EOL . 'Для справки вызовите скрипт с аргументом help' . PHP_EOL;
                die;
            }

            if (empty($target_dir)) {
                $target_dir = "/usr/local/mysql/data";
                //echo "\033[31mНет аргумента target-dir \033[0m " . PHP_EOL . 'Для справки вызовите скрипт с аргументом help' . PHP_EOL;
                //die;
            }

            if (empty($database)) {
                echo "\033[31mНет аргумента database \033[0m " . PHP_EOL . 'Для справки вызовите скрипт с аргументом help' . PHP_EOL;
                die;
            }

            if (empty($database_new)) {
                echo "\033[31mНет аргумента database_new \033[0m " . PHP_EOL . 'Для справки вызовите скрипт с аргументом help' . PHP_EOL;
                die;
            }

            if (empty($user)) {
                echo "\033[31mНет аргумента user \033[0m " . PHP_EOL . 'Для справки вызовите скрипт с аргументом help' . PHP_EOL;
                die;
            }

            if (empty($password)) {
                echo "\033[31mНет аргумента password \033[0m " . PHP_EOL . 'Для справки вызовите скрипт с аргументом help' . PHP_EOL;
                die;
            }

            echo "\e[33mЗапуск импорта " . date('d.m.Y H:i:s') . "\e[0m" . PHP_EOL . PHP_EOL;
            echo "\e[33mЗаливаем дамп импортируемой " . $database . " БД в " . $database_new . "\e[0m" . PHP_EOL;
            $log = exec('mysql -u ' . $user . ' -p' . $password . ' ' . $database_new . ' < ' . $source_dir . '/' . $database . '.sql');
            echo "\e[33mГотово\e[0m" . PHP_EOL;

            echo "\e[33mКопирование файлов БД \e[0m" . PHP_EOL;
            $mysqli = new mysqli('localhost', $user, $password, $database_new);
            $result = $mysqli->query('SHOW TABLES FROM ' . $database_new, MYSQLI_USE_RESULT);
            $result = $result->fetch_all();
            foreach ($result as $res) {
                echo "\e[92mКопирование таблицы: " . $res[0] . " \e[0m" . PHP_EOL;
                $mysqli->query('ALTER TABLE ' . $res[0] . ' DISCARD TABLESPACE');
                $log = exec('cp -p ' . $source_dir . '/' . $database . '/' . $res[0] . '.cfg ' . $target_dir . '/' . $database_new);
                $log = exec('cp -p ' . $source_dir . '/' . $database . '/' . $res[0] . '.ibd ' . $target_dir . '/' . $database_new);
                $log = exec('chown mysql:mysql ' . $target_dir . '/' . $database_new . '/' . $res[0] . '.cfg ');
                $log = exec('chmod 777 ' . $target_dir . '/' . $database_new . '/' . $res[0] . '.ibd ');
                $mysqli->query('ALTER TABLE ' . $res[0] . ' IMPORT TABLESPACE');
            }
            echo "\e[33mГотово\e[0m" . PHP_EOL . PHP_EOL;
            echo "\e[33mИмпорт завершен " . date('d.m.Y H:i:s') . "\e[0m" . PHP_EOL;
            break;

        default:

            echo "\033[32mВведите 1 из аргументов: " . PHP_EOL .
                'help - справка' . PHP_EOL .
                'export - создание бекапа' . PHP_EOL .
                "import - получение бекапа \033[0m" . PHP_EOL;
            break;

    }

} else {

    echo "\033[32mВведите 1 из аргументов: " . PHP_EOL .
        'help - справка' . PHP_EOL .
        'export - создание бекапа' . PHP_EOL .
        "import - получение бекапа \033[0m" . PHP_EOL;
    die;
}





