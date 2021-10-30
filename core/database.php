<?php
include '../libs/db_backup_import.php';
    class database{
        //! DB Params
        private string $host = 'localhost:3306'; // server name
        private string $db_name = 'site_auth.secur'; //database name
        private string $username ='root'; // database user name
        private string $password = 'root'; // database user password

        //! DB Connect
        public function connect(){
            $conn = NULL;
            try {
                $conn = new PDO('mysql:host=' .$this->host. ';dbname=' .$this->db_name, $this->username,$this->password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch(PDOException $e) {
                if ($e->getCode()===1049) {
                    $file = dirname(__FILE__,2).'\db.sql'; // sql data file
                    $args = file_get_contents($file); // get contents
                    $conn = mysqli_import_sql( $args, $this->host, $this->username, $this->password, null);
                    if($conn==='complete dumping database !') {
                        $conn = new PDO('mysql:host=' .$this->host. ';dbname=' .$this->db_name, $this->username,$this->password);
                        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        return $conn;
                    }
                }else{
                    //die("ERROR: Could not connect. " . $e->getMessage().(int)$e->getCode( ));
                    die(json_encode(array("message" => "ERROR: Could not connect. " . $e->getMessage()), JSON_THROW_ON_ERROR));
                }
                //throw new MyDbException( $e->getMessage( ) , (int)$e->getCode( ) );
            }

            return $conn;
        }
    }