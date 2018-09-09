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

    // ------------------------------------------------------------------------ Departments ------------------------------------------------------------------------ //
    public function getDepartments($fresh, $UntisData){
      if($fresh){
        $ServerDepartments = $UntisData->Departments();
        $response = $this->insertDepartments($ServerDepartments);
        if($response['success']=true){
          $data = $this->getDepartmentsSQL();
          return json_encode($data, true);
        } else {
          return json_encode($response, true);
        }
      } else {
        $data = $this->getDepartmentsSQL();
        return json_encode($data, true);
      }
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
    public function getDepartmentsSQL(){
      $prefixDepartments = $this->ini_array['msql_prefix']."Departments";
      $prefixDepartmentsHis = $this->ini_array['msql_prefix']."Departments_his";

      $sqlq_Get = "SELECT * from $prefixDepartments ORDER BY name";
      $sql_Get = mysqli_query($this->connection, $sqlq_Get);
      $Departments = array();
      if (mysqli_num_rows($sql_Get) > 0) {
        while($row = mysqli_fetch_assoc($sql_Get)) {
          $sqlq_GetHistory = "SELECT * from $prefixDepartmentsHis WHERE itm_id=$row[id]";
          $sql_GetHistory = mysqli_query($this->connection, $sqlq_GetHistory);
          $row['history'] = array();
          if (mysqli_num_rows($sql_GetHistory) > 0) {
            while($rowHistory = mysqli_fetch_assoc($sql_GetHistory)) {
              array_push($row['history'], $rowHistory);
            }
          }
          array_push($Departments, $row);
        }
      }
      return $Departments;
    }
    public function getSingleDepartment($id){
      $prefixDepartments = $this->ini_array['msql_prefix']."Departments";
      $prefixDepartmentsHis = $this->ini_array['msql_prefix']."Departments_his";

      $sqlq_Get = "SELECT * from $prefixDepartments WHERE id=$id";
      $sql_Get = mysqli_query($this->connection, $sqlq_Get);
      if (mysqli_num_rows($sql_Get) > 0) {
        $row = mysqli_fetch_assoc($sql_Get);
        $sqlq_GetHistory = "SELECT * from $prefixDepartmentsHis WHERE itm_id=$id";
        $sql_GetHistory = mysqli_query($this->connection, $sqlq_GetHistory);
        $row['history'] = array();
        if (mysqli_num_rows($sql_GetHistory) > 0) {
          while($rowHistory = mysqli_fetch_assoc($sql_GetHistory)) {
            array_push($row['history'], $rowHistory);
          }
        }
        return $row;
      }
      return null;
    }

    public function getSchoolClasses($fresh, $UntisData){
      if($fresh){
        $ServerSchoolClasses = $UntisData->SchoolClasses();
        // return json_encode($ServerSchoolClasses, true);
        $response = $this->insertSchoolClasses($ServerSchoolClasses);
        if($response['success']=true){
          $data = $this->getSchoolClassesSQL();
          return json_encode($data, true);
        } else {
          return json_encode($response, true);
        }
      } else {
        $data = $this->getSchoolClassesSQL();
        return json_encode($data, true);
      }
    }
    public function insertSchoolClasses($data){
      $prefixSchoolClasses = $this->ini_array['msql_prefix']."SchoolClasses";
      $prefixSchoolClassesHis = $this->ini_array['msql_prefix']."SchoolClasses_his";

      $sqlq_ids = "SELECT id from $prefixSchoolClasses";
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
      foreach ($data as $SchoolClassObject) {
        $id = $SchoolClassObject['id'];
        $name = $SchoolClassObject['name'];
        $longName = $SchoolClassObject['longName'];
        $displayname = $SchoolClassObject['displayname'];
        $didsArray = $SchoolClassObject['dids'];
        $dids = json_encode($didsArray, true);

        unset($SchoolClassObject['forename']);
        unset($SchoolClassObject['externKey']);
        unset($SchoolClassObject['klasseId']);
        unset($SchoolClassObject['klasseOrStudentgroupId']);
        unset($SchoolClassObject['title']);
        unset($SchoolClassObject['alternatename']);
        unset($SchoolClassObject['classteacher']);
        unset($SchoolClassObject['classteacher2']);
        unset($SchoolClassObject['buildingId']);
        unset($SchoolClassObject['restypeId']);
        unset($SchoolClassObject['can']);
        unset($SchoolClassObject['capacity']);

        $hash = md5(json_encode($SchoolClassObject, true));

        array_push($newIDs, $id);

        $sqlq_check = "SELECT * from $prefixSchoolClasses WHERE (id = $id) LIMIT 1";
        $sql_check = mysqli_query($this->connection, $sqlq_check);
        if(mysqli_num_rows($sql_check) == 0){
          // ------------------------------------------- Insert Database --------------------------------------------- //
          $sqlq_Insert_SchoolClass = "INSERT INTO $prefixSchoolClasses (id, name, longname, displayname, dids, last_update, hash)
          VALUES ($id, '$name', '$longName', '$displayname', '$dids', NOW(), '$hash')";
          array_push($insertedIDs, $id);
          if (!mysqli_query($this->connection, $sqlq_Insert_SchoolClass)) {
            return array("status"=>"failed", "query"=>$sqlq_Insert_SchoolClass, "error"=>"SQL error: ".mysqli_connect_error());
          }
        } else {
          $SqlOldData = mysqli_fetch_array($sql_check, MYSQLI_ASSOC);
          $SqlHash = $SqlOldData['hash'];
          if($SqlHash!=$hash){
            // ----------------------------------------- Insert into history ----------------------------------------- //
            array_push($changedIDs, $id);
            $sqlq_Insert_SchoolClass_History = "INSERT INTO $prefixSchoolClassesHis (itm_id, name, longname, displayname, dids, last_update, hash, status)
            VALUES ($SqlOldData[id], '$SqlOldData[name]', '$SqlOldData[longname]', '$SqlOldData[displayname]', '$SqlOldData[dids]', '$SqlOldData[last_update]', '$SqlOldData[hash]', 'UPDATE')";
            if (!mysqli_query($this->connection, $sqlq_Insert_SchoolClass_History)) {
              return array("status"=>"failed", "query"=>$sqlq_Insert_SchoolClass_History, "error"=>"SQL error: ".mysqli_connect_error());
            }
            // ----------------------------------------- Update Database --------------------------------------------- //
            $sqlq_Update_SchoolClass = "UPDATE $prefixSchoolClasses SET name='$name', longname='$longName', displayname='$displayname', dids='$dids', last_update=NOW(), hash='$hash' WHERE id=$id";
            if (!mysqli_query($this->connection, $sqlq_Update_SchoolClass)) {
              return array("status"=>"failed", "query"=>$sqlq_Update_SchoolClass, "error"=>"SQL error: ".mysqli_connect_error());
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
    public function getSchoolClassesSQL(){
      $prefixSchoolClasses = $this->ini_array['msql_prefix']."SchoolClasses";
      $prefixSchoolClassesHis = $this->ini_array['msql_prefix']."SchoolClasses_his";

      $sqlq_Get = "SELECT * from $prefixSchoolClasses ORDER BY name";
      $sql_Get = mysqli_query($this->connection, $sqlq_Get);
      $SchoolClasses = array();
      if (mysqli_num_rows($sql_Get) > 0) {
        while($row = mysqli_fetch_assoc($sql_Get)) {
          $sqlq_GetHistory = "SELECT * from $prefixSchoolClassesHis WHERE itm_id=$row[id]";
          $sql_GetHistory = mysqli_query($this->connection, $sqlq_GetHistory);
          $row['history'] = array();
          if (mysqli_num_rows($sql_GetHistory) > 0) {
            while($rowHistory = mysqli_fetch_assoc($sql_GetHistory)) {
              array_push($row['history'], $rowHistory);
            }
          }
          array_push($SchoolClasses, $row);
        }
      }
      return $SchoolClasses;
    }
    public function getSingleSchoolClass($id){
      $prefixSchoolClasses = $this->ini_array['msql_prefix']."SchoolClasses";
      $prefixSchoolClassesHis = $this->ini_array['msql_prefix']."SchoolClasses_his";

      $sqlq_Get = "SELECT * from $prefixSchoolClasses WHERE id=$id";
      $sql_Get = mysqli_query($this->connection, $sqlq_Get);
      if (mysqli_num_rows($sql_Get) > 0) {
        $row = mysqli_fetch_assoc($sql_Get);
        $sqlq_GetHistory = "SELECT * from $prefixSchoolClassesHis WHERE itm_id=$id";
        $sql_GetHistory = mysqli_query($this->connection, $sqlq_GetHistory);
        $row['history'] = array();
        if (mysqli_num_rows($sql_GetHistory) > 0) {
          while($rowHistory = mysqli_fetch_assoc($sql_GetHistory)) {
            array_push($row['history'], $rowHistory);
          }
        }
        return $row;
      }
      return null;
    }
  }
?>
