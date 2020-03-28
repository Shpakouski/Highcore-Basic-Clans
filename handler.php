<?php
session_start();
// base settings
define('ROOT', __DIR__);
// db settings
define('DBMS', 'mysql');
define('DB_HOST', 'localhost');
define('DB_NAME', 'highcore');
define('DB_USER', 'root');
define('DB_PASS', '');

class SQL
{
    private static $instance;
    private $db;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new SQL();
        }
        return self::$instance;
    }

    private function __construct()
    {
        setlocale(LC_ALL, 'ru_RU.UTF8');
        $this->db = new \PDO(DBMS . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        $this->db->exec('SET NAMES UTF8');
        $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    public function query($query, $params = [])
    {
        $q = $this->db->prepare($query);
        $q->execute($params);
        if ($q->errorCode() != \PDO::ERR_NONE) {
            $info = $q->errorInfo();
            die($info[2]);
        }
        return $q->fetchAll();
    }

    public function insert($table, $object)
    {
        $columns = array();
        $masks = array();
        foreach ($object as $key => $value) {
            $columns[] = $key;
            $masks[] = ":$key";
            if ($value === null) {
                $object[$key] = 'NULL';
            }
        }
        $columns_s = implode(',', $columns);
        $masks_s = implode(',', $masks);
        $query = "INSERT INTO $table ($columns_s) VALUES ($masks_s)";
        $q = $this->db->prepare($query);
        $q->execute($object);
        if ($q->errorCode() != PDO::ERR_NONE) {
            $info = $q->errorInfo();
            die($info[2]);
        }
        return $this->db->lastInsertId();
    }

    public function update($table, $object, $where)
    {
        $sets = array();
        foreach ($object as $key => $value) {
            $sets[] = "$key=:$key";
            if ($value === NULL) {
                $object[$key] = 'NULL';
            }
        }
        $sets_s = implode(',', $sets);
        $query = "UPDATE $table SET $sets_s WHERE $where";
        $q = $this->db->prepare($query);
        $q->execute($object);
        if ($q->errorCode() != PDO::ERR_NONE) {
            $info = $q->errorInfo();
            die($info[2]);
        }
        return $q->rowCount();
    }

    public function delete($table, $where)
    {
        $query = "DELETE FROM $table WHERE $where";
        $q = $this->db->prepare($query);
        $q->execute();
        if ($q->errorCode() != PDO::ERR_NONE) {
            $info = $q->errorInfo();
            die($info[2]);
        }
        return $q->rowCount();
    }
}

$db = SQL::getInstance();
//1. Создание клана (название клана (макс 12 символов без специальных символов и
//пробелов), описание (макс 30 символов), список участников (для описания
//участника достаточно полей id и name)) / удаление клана.
function createClan($user_id, $clan_name, $clan_description, $db)
{
    $errors = [];
    if (!validateClanName($clan_name)) array_push($errors, "Username must be maximum 12 characters, without special symbols. </br>");
    if (!validateClanDescription($clan_description)) array_push($errors, "Description must be maximum 30 characters.</br>");
    if (count($errors) !== 0) {
        foreach ($errors as $error) {
            echo $error;
        }
        return;
    }
//create clan
    $db->query("INSERT INTO clans (name,description)
values('{$clan_name}','{$clan_description}');");
//get new clan id
    $clan_id = $db->query("SELECT clans.id FROM clans WHERE clans.`name` = '{$clan_name}';")[0]['id'];
//set clan leader
    $db->query("UPDATE users
SET clans_id = '{$clan_id}', roles_id = 3
WHERE id = '{$user_id}';");
}

function validateClanName($name)
{
    return preg_match('/^\w{1,12}$/', $name);
}

function validateClanDescription($description)
{
    return preg_match('/^.{1,30}$/', $description);
}

function deleteClan($role_id, $clan_id, $db)
{
    if (!isClanLeader($role_id)) {
        echo "You're not the clan leader and can't delete clan! </br>";
        return;
    }
    $db->query("DELETE FROM clans
WHERE clans.id = '{$clan_id}';");
}

/*
2. Роли для участников клана:
- Кланлидер: может редактировать описание клана, удалять клан, удалять
других участников, повышать или понижать в звании других участников
клана. Участник, который создал клан - становится кланлидером
поумолчанию.
- Заместитель: может редактировать описание клана, повышать до
заместителя
- Солдат не имеет никаких привилегий. Любой новых игрок, который приходит
в клан, становится солдатом.
*/
function isClanLeader($role_id)
{
    return $role_id === 3;
}

function isCoLeader($role_id)
{
    return $role_id === 2;
}

function isSoldier($role_id)
{
    return $role_id === 1;
}

//4. Смена описания клана.
function editDescription($role_id, $clan_id, $clan_description, $db)
{
    if ($role_id < 2) {
        echo "You don't have enought rights for that! </br>";
        return;
    }
    $db->query("UPDATE clans
SET description= '{$clan_description}'
WHERE id = '{$clan_id}';");
}

//5. Повышение/понижение ролей участников клана.
function promote($role_id, $member_id, $role_member_id, $db)
{
    if ($role_id < 2) {
        echo "You don't have enought rights for that! </br>";
        return;
    }
    if (isCoLeader($role_id) && $role_member_id >= 2) {
        echo "Maximum promote rank reached for co-leader! </br>";
        return;
    }
    if (!($role_member_id >= 3)) {
        $role_member_id++;
        $db->query("UPDATE users
	SET roles_id = '{$role_member_id}'
	WHERE id = '{$member_id}';");
    } else {
        echo 'Maximum promote rank reached </br>';
        return;
    }
}

function demote($role_id, $member_id, $role_member_id, $db)
{
    if ($role_id < 2) {
        echo "You don't have enought rights for that! </br>";
        return;
    }
    if (isCoLeader($role_id) && isCoLeader($role_member_id)) {
        echo "He's co-leader too! </br>";
        return;
    }
    if (!($role_member_id < 2)) {
        $role_member_id--;
        $db->query("UPDATE users
	SET roles_id = '{$role_member_id}'
	WHERE id = '{$member_id}';");
    } else {
        echo 'Minimum demote rank reached </br>';
        return;
    }
}

//3. Добавление новых участников в клан/удаление участников из клана (удалить из
//клана можно только участника с ролью солдат).
function deleteClanMember($role_id, $member_id, $db)
{
    if (!isClanLeader($role_id)) {
        echo "You don't have enought rights for that! </br>";
        return;
    }
    if (!isSoldier($member_id)){
        echo "You can only remove soldiers! </br>";
        return;
    }
    $db->query("UPDATE users
SET clans_id = null, roles_id = null
WHERE id = '{$member_id}';");
}

function addedClanMember($member_id, $clans_id, $db)
{
    $db->query("UPDATE users
	SET clans_id = '{$clans_id}', roles_id = 1
	WHERE id = '{$member_id}';");
}

//6. Получение списка кланов и их участников.
function getClanList($db)
{
    $clans = $db->query("SELECT clans.`name` FROM clans");
    foreach ($clans as $clan) {
        echo $clan['name'], '</br>';
    };
}

function getClanMembers($clan_id, $db)
{
    $members = $db->query("SELECT
users.`name`
FROM
clans
INNER JOIN users ON users.clans_id = clans.id
WHERE
clans.id = '{$clan_id}';");
    foreach ($members as $member) {
        echo $member['name'], '</br>';
    };
}

/*
Примечание: данные приходят извне посредством POST запросов.
Используем чистый PHP без фреймворков и сторонних сервисов и библиотек.
Интерфейс или формы для ввода делать не нужно.
Авторизацию делать не нужно.
Важен ваш подход к решению задачи, ваш код, работа с БД.
*/