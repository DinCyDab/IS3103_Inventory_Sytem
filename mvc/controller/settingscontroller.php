<?php 
    //Reference your model here
    //Change the dashboardmodel.php to your specific model
    require_once __DIR__ . "/../model/settingsmodel.php";
    require_once __DIR__ . "/../model/accountmodel.php";

    //Change the ControllerName to your controller name
    class SettingsController{
        private $model;
        public function __construct(){
            //Change DashboardModel() to your class model
            $this->model = new SettingsModel();
        }

        public function handleUpdateInfo($account_ID, $first_name, $last_name){
            $this->model->updateInfo($account_ID, $first_name, $last_name);

            $this->reloadAndUpdateSession($account_ID);

            $this->model->close();

            header("Location: index.php?view=settings&msg=1");
            exit();
        }
        
        private function reloadAndUpdateSession($account_ID){
            $account_model = new AccountModel();
            $_SESSION["account"] = $account_model->loadAccount("WHERE account_ID = '$account_ID'")[0];

            $account_model->close();
        }

        public function handlePassword($account_ID, $current_password, $new_password, $confirm_password){
            if($new_password != $confirm_password){
                header("Location: index.php?view=settings&msg=2");
                exit();
            }

            $account_model = new AccountModel();

            $result = $account_model->loadAccount("WHERE account_ID = '$account_ID'")[0];

            $account_model->close();

            if(!password_verify($current_password, $result["password"])){
                header("Location: index.php?view=settings&msg=3");
                exit();
            }

            $hash_password = password_hash($new_password, PASSWORD_DEFAULT);

            $this->model->updatePassword($account_ID, $hash_password);

            header("Location: index.php?view=settings&msg=1");
            exit();
        }
    }
?>