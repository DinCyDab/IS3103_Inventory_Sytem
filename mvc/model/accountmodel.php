<?php 
require_once __DIR__ . "/../api/config.php";

class AccountModel{
    private $config;
    
    public function __construct(){
        $this->config = new Config();
    }
    
    public function createAccount($account_ID, $first_name, $last_name, $password, $email, $contact_number, $role){
        $sql = "INSERT INTO account(account_ID, first_name, last_name, password, email, contact_number, role)
                VALUES('$account_ID', '$first_name', '$last_name', '$password', '$email', '$contact_number', '$role')";
        return $this->config->query($sql);
    }
    
    public function deleteAccount($account_ID){
        $sql = "DELETE FROM account WHERE account_ID = $account_ID";
        return $this->config->query($sql);
    }
    
    public function loadAccount($filter){
        $sql = "SELECT * FROM account " . $filter;
        return $this->config->read($sql);
    }
    
    public function getAccountById($account_ID){
        $sql = "SELECT * FROM account WHERE account_ID = $account_ID";
        $result = $this->config->read($sql);
        return !empty($result) ? $result[0] : null;
    }

    // Get total accounts count for pagination
    public function getTotalAccountsCount($filter = ""){
        $sql = "SELECT COUNT(*) as total FROM account " . $filter;
        $result = $this->config->readOne($sql);
        return isset($result['total']) ? (int)$result['total'] : 0;
    }
    
    public function updateAccount($account_ID, $first_name, $last_name, $hashed_password, $email, $contact_number, $role, $status){
        $sql = "UPDATE account
                SET first_name = '$first_name',
                    last_name = '$last_name',
                    password = '$hashed_password',
                    email = '$email',
                    contact_number = '$contact_number',
                    role = '$role',
                    status = '$status'
                WHERE account_ID = $account_ID";
        return $this->config->query($sql);
    }
    
    public function updateAccountWithoutPassword($account_ID, $first_name, $last_name, $email, $contact_number, $role, $status){
        $sql = "UPDATE account
                SET first_name = '$first_name',
                    last_name = '$last_name',
                    email = '$email',
                    contact_number = '$contact_number',
                    role = '$role',
                    status = '$status'
                WHERE account_ID = $account_ID";
        return $this->config->query($sql);
    }
    
    public function close(){
        $this->config->close();
    }
}
?>