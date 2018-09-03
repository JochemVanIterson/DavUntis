<?php
  class untisReceiver{
    public  function __construct($ini_array) {
      $this->ini_array = $ini_array;
      $this->init_SQL();
    }
    private function init_SQL(){
      $SQLservername = $this->ini_array["msql_server"];
      $SQLusername = $this->ini_array["msql_user"];
      $SQLpassword = $this->ini_array["msql_pwd"];
      $SQLdbname = $this->ini_array["msql_db"];

      $this->connection = mysqli_connect($SQLservername, $SQLusername, $SQLpassword, $SQLdbname) or die("Error " . mysqli_error($this->connection));
    }

    public function insertDepartments($data){
      $prefixDepartments = $this->ini_array['msql_prefix']."Departments";
      $prefixDepartmentsHis = $this->ini_array['msql_prefix']."Departments_his";

      $sqlq_ids = "SELECT id from $prefixDepartments";
      $sql_ids = mysqli_query($this->connection, $sqlq_ids);
      $oldIDs = array();
      if (mysqli_num_rows($sql_ids) > 0) {
        while($row = mysqli_fetch_assoc($sql_ids)) {
          array_push($oldIDs, $row['id']);
        }
      }

      $insertedIDs = array();
      $changedIDs = array();
      $newIDs = array();
      foreach ($data as $departmentObject) {
        $id = $departmentObject['id'];
        $name = $departmentObject['name'];
        $label = $departmentObject['label'];
        $hash = md5(json_encode($departmentObject, true));

        array_push($newIDs, $id);

        $sqlq_check = "SELECT * from $prefixDepartments WHERE (id = $id) LIMIT 1";
        $sql_check = mysqli_query($this->connection, $sqlq_check);
    		if(mysqli_num_rows($sql_check) == 0){
          // ------------------------------------------- Insert Database --------------------------------------------- //
          $sqlq_Insert_Department = "INSERT INTO $prefixDepartments (id, name, label, last_update, hash)
      		VALUES ($id, '$name', '$label', NOW(), '$hash')";
          array_push($insertedIDs, $id);
      		if (!mysqli_query($this->connection, $sqlq_Insert_Department)) {
      			return array("status"=>"failed", "query"=>$sqlq_Insert_Department, "error"=>"SQL error: ".mysqli_connect_error());
      		}
    		} else {
          $SqlOldData = mysqli_fetch_array($sql_check, MYSQLI_ASSOC);
          $SqlHash = $SqlOldData['hash'];
          if($SqlHash!=$hash){
            // ----------------------------------------- Insert into history ----------------------------------------- //
            array_push($changedIDs, $id);
            $sqlq_Insert_Department_History = "INSERT INTO $prefixDepartmentsHis (itm_id, name, label, last_update, hash, status)
        		VALUES ($SqlOldData[id], '$SqlOldData[name]', '$SqlOldData[label]', '$SqlOldData[last_update]', '$SqlOldData[hash]', 'UPDATE')";
        		if (!mysqli_query($this->connection, $sqlq_Insert_Department_History)) {
        			return array("status"=>"failed", "query"=>$sqlq_Insert_Department_History, "error"=>"SQL error: ".mysqli_connect_error());
        		}
            // ----------------------------------------- Update Database --------------------------------------------- //
            $sqlq_Update_Department = "UPDATE $prefixDepartments SET name='$name', label='$label', last_update=NOW(), hash='$hash' WHERE id=$id";
        		if (!mysqli_query($this->connection, $sqlq_Update_Department)) {
        			return array("status"=>"failed", "query"=>$sqlq_Update_Department, "error"=>"SQL error: ".mysqli_connect_error());
        		}
          }
        }
      }
      $removedIDs = array_diff($oldIDs, $newIDs);
      return array("status"=>"success", "changes"=>array(
        "old"=>$oldIDs,
        "inserted"=>$insertedIDs,
        "changed"=>$changedIDs,
        "removed"=>$removedIDs
      ));
    }
  }
?>
