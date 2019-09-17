# Утилита для снятия бекапа БД *только MariaDB

### Аргументы:
1. help
2. import
3. export

### Импорт:
Пример выполнения скрипта для импорта:
php -f hotcopy.php import source-dir=/dev/null/backup target-dir=/dev/null/mysql/data database=db_name database-new=db_name_new user=test password=pass 
source-dir - место хранения бекапа
target-dir - место, где находятся файлы БД
database - наименование импортируемой БД
database_new - наименование новой БД
user - пользователь БД
password -  пароль пользователя

### Экспорт:
Пример выполнения скрипта для экспорта:
php -f hotcopy.php export source-dir=/dev/null/backup user=test password=pass database=db_name
source-dir - место хранения бекапа
user - пользователь БД
password - пароль пользователя
database - наименование БД 

### Помощь
Для получения справки о скрипте:
php -f hotcopy.php help
