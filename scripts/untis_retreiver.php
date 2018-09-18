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
      foreach($data as $departmentObject) {
        $id = $departmentObject['id'];
        $name = mysqli_real_escape_string($this->connection, $departmentObject['name']);
        $label = mysqli_real_escape_string($this->connection, $departmentObject['label']);
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

          $sqlq_Insert_Department_History = "INSERT INTO $prefixDepartmentsHis (itm_id, name, label, last_update, hash, status)
          VALUES ($id, '$name', '$label', NOW(), '$hash', 'INSERT')";
          if (!mysqli_query($this->connection, $sqlq_Insert_Department_History)) {
            return array("status"=>"failed", "query"=>$sqlq_Insert_Department_History, "error"=>"SQL error: ".mysqli_connect_error());
          }
    		} else {
          $SqlOldData = mysqli_fetch_array($sql_check, MYSQLI_ASSOC);
          $SqlHash = $SqlOldData['hash'];
          if($SqlHash!=$hash){
            $SqlOldData['name'] = mysqli_real_escape_string($this->connection, $SqlOldData['name']);
            $SqlOldData['label'] = mysqli_real_escape_string($this->connection, $SqlOldData['label']);
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
      $removedIDs = array_values(array_diff($oldIDs, $newIDs));
      foreach($removedIDs as $id) {
        $sqlq_check = "SELECT * from $prefixDepartments WHERE (id = $id) LIMIT 1";
        $sql_check = mysqli_query($this->connection, $sqlq_check);
    		if(mysqli_num_rows($sql_check) > 0){
          $SqlOldData = mysqli_fetch_array($sql_check, MYSQLI_ASSOC);
          $SqlHash = $SqlOldData['hash'];
          if($SqlHash!=$hash){
            $SqlOldData['name'] = mysqli_real_escape_string($this->connection, $SqlOldData['name']);
            $SqlOldData['label'] = mysqli_real_escape_string($this->connection, $SqlOldData['label']);
            // ----------------------------------------- Insert into history ----------------------------------------- //
            array_push($changedIDs, $id);
            $sqlq_Insert_Department_History = "INSERT INTO $prefixDepartmentsHis (itm_id, name, label, last_update, hash, status)
        		VALUES ($SqlOldData[id], '$SqlOldData[name]', '$SqlOldData[label]', '$SqlOldData[last_update]', '$SqlOldData[hash]', 'REMOVED')";
        		if (!mysqli_query($this->connection, $sqlq_Insert_Department_History)) {
        			return array("status"=>"failed", "query"=>$sqlq_Insert_Department_History, "error"=>"SQL error: ".mysqli_connect_error());
        		}
            // ----------------------------------------- Update Database --------------------------------------------- //
            $sqlq_Remove_Department = "DELETE FROM $prefixDepartments WHERE id=$id";
        		if (!mysqli_query($this->connection, $sqlq_Remove_Department)) {
        			return array("status"=>"failed", "query"=>$sqlq_Remove_Department, "error"=>"SQL error: ".mysqli_connect_error());
        		}
          }
        }
      }
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
          $Departments[$row['id']] = $row;
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
        $name = mysqli_real_escape_string($this->connection, $SchoolClassObject['name']);
        $longName = mysqli_real_escape_string($this->connection, $SchoolClassObject['longName']);
        $displayname = mysqli_real_escape_string($this->connection, $SchoolClassObject['displayname']);
        $didsArray = $SchoolClassObject['dids'];
        $dids = mysqli_real_escape_string($this->connection, json_encode($didsArray, true));

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
          $sqlq_Insert_SchoolClass_History = "INSERT INTO $prefixSchoolClassesHis (itm_id, name, longname, displayname, dids, last_update, hash, status)
          VALUES ($id, '$name', '$longName', '$displayname', '$dids', NOW(), '$hash', 'INSERT')";
          if (!mysqli_query($this->connection, $sqlq_Insert_SchoolClass_History)) {
            return array("status"=>"failed", "query"=>$sqlq_Insert_SchoolClass_History, "error"=>"SQL error: ".mysqli_connect_error());
          }
        } else {
          $SqlOldData = mysqli_fetch_array($sql_check, MYSQLI_ASSOC);
          $SqlHash = $SqlOldData['hash'];
          if($SqlHash!=$hash){
            $SqlOldData['name'] = mysqli_real_escape_string($this->connection, $SqlOldData['name']);
            $SqlOldData['longname'] = mysqli_real_escape_string($this->connection, $SqlOldData['longName']);
            $SqlOldData['displayname'] = mysqli_real_escape_string($this->connection, $SqlOldData['displayname']);
            $SqlOldData['dids'] = mysqli_real_escape_string($this->connection, $SqlOldData['dids']);
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
      $removedIDs = array_values(array_diff($oldIDs, $newIDs));
      foreach($removedIDs as $id) {
        $sqlq_check = "SELECT * from $prefixSchoolClasses WHERE (id = $id) LIMIT 1";
        $sql_check = mysqli_query($this->connection, $sqlq_check);
    		if(mysqli_num_rows($sql_check) > 0){
          $SqlOldData = mysqli_fetch_array($sql_check, MYSQLI_ASSOC);
          $SqlHash = $SqlOldData['hash'];
          if($SqlHash!=$hash){
            $SqlOldData['name'] = mysqli_real_escape_string($this->connection, $SqlOldData['name']);
            $SqlOldData['longname'] = mysqli_real_escape_string($this->connection, $SqlOldData['longName']);
            $SqlOldData['displayname'] = mysqli_real_escape_string($this->connection, $SqlOldData['displayname']);
            $SqlOldData['dids'] = mysqli_real_escape_string($this->connection, $SqlOldData['dids']);
            // ----------------------------------------- Insert into history ----------------------------------------- //
            array_push($changedIDs, $id);
            $sqlq_Insert_SchoolClass_History = "INSERT INTO $prefixSchoolClassesHis (itm_id, name, longname, displayname, dids, last_update, hash, status)
        		VALUES ($SqlOldData[id], '$SqlOldData[name]', '$SqlOldData[longname]', '$SqlOldData[displayname]', '$SqlOldData[dids]', '$SqlOldData[last_update]', '$SqlOldData[hash]', 'REMOVED')";
        		if (!mysqli_query($this->connection, $sqlq_Insert_SchoolClass_History)) {
        			return array("status"=>"failed", "query"=>$sqlq_Insert_SchoolClass_History, "error"=>"SQL error: ".mysqli_connect_error());
        		}
            // ----------------------------------------- Update Database --------------------------------------------- //
            $sqlq_Remove_SchoolClass = "DELETE FROM $prefixSchoolClasses WHERE id=$id";
        		if (!mysqli_query($this->connection, $sqlq_Remove_SchoolClass)) {
        			return array("status"=>"failed", "query"=>$sqlq_Remove_SchoolClass, "error"=>"SQL error: ".mysqli_connect_error());
        		}
          }
        }
      }
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
          $SchoolClasses[$row['id']] = $row;
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
    public function getSchoolClassIDsSQL(){
      $prefixSchoolClasses = $this->ini_array['msql_prefix']."SchoolClasses";
      $prefixSchoolClassesHis = $this->ini_array['msql_prefix']."SchoolClasses_his";

      $sqlq_Get = "SELECT id from $prefixSchoolClasses ORDER BY name";
      $sql_Get = mysqli_query($this->connection, $sqlq_Get);
      $SchoolClasses = array();
      if (mysqli_num_rows($sql_Get) > 0) {
        while($row = mysqli_fetch_assoc($sql_Get)) {
          $SchoolClasses[$row['id']] = $row;
        }
      }
      return $SchoolClasses;
    }

    public function getSubjects($fresh, $UntisData){
      if($fresh){
        $ServerSubjects = $UntisData->Subjects();
        $response = $this->insertSubjects($ServerSubjects);
        if($response['success']=true){
          $data = $this->getSubjectsSQL();
          return json_encode($data, true);
        } else {
          return json_encode($response, true);
        }
      } else {
        $data = $this->getSubjectsSQL();
        return json_encode($data, true);
      }
    }
    public function insertSubjects($data){
      $prefixSubjects = $this->ini_array['msql_prefix']."Subjects";
      $prefixSubjectsHis = $this->ini_array['msql_prefix']."Subjects_his";

      $sqlq_ids = "SELECT id from $prefixSubjects";
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
      foreach ($data as $SubjectObject) {
        $id = $SubjectObject['id'];
        $name = mysqli_real_escape_string($this->connection, $SubjectObject['name']);
        $longName = mysqli_real_escape_string($this->connection, $SubjectObject['longName']);
        $displayname = mysqli_real_escape_string($this->connection, $SubjectObject['displayname']);
        $alternatename = mysqli_real_escape_string($this->connection, $SubjectObject['alternatename']);

        unset($SubjectObject['forename']);
        unset($SubjectObject['externKey']);
        unset($SubjectObject['dids']);
        unset($SubjectObject['klasseId']);
        unset($SubjectObject['klasseOrStudentgroupId']);
        unset($SubjectObject['title']);
        unset($SubjectObject['classteacher']);
        unset($SubjectObject['classteacher2']);
        unset($SubjectObject['buildingId']);
        unset($SubjectObject['restypeId']);
        unset($SubjectObject['can']);
        unset($SubjectObject['capacity']);

        $hash = md5(json_encode($SubjectObject, true));

        array_push($newIDs, $id);

        $sqlq_check = "SELECT * from $prefixSubjects WHERE (id = $id) LIMIT 1";
        $sql_check = mysqli_query($this->connection, $sqlq_check);
        if(mysqli_num_rows($sql_check) == 0){
          // ------------------------------------------- Insert Database --------------------------------------------- //
          $sqlq_Insert_Subject = "INSERT INTO $prefixSubjects (id, name, longname, displayname, alternatename, last_update, hash)
          VALUES ($id, '$name', '$longName', '$displayname', '$alternatename', NOW(), '$hash')";
          array_push($insertedIDs, $id);
          if (!mysqli_query($this->connection, $sqlq_Insert_Subject)) {
            return array("status"=>"failed", "query"=>$sqlq_Insert_Subject, "error"=>"SQL error: ".mysqli_connect_error());
          }
          $sqlq_Insert_Subject_History = "INSERT INTO $prefixSubjectsHis (itm_id, name, longname, displayname, alternatename, last_update, hash, status)
          VALUES ($id, '$name', '$longName', '$displayname', '$alternatename', NOW(), '$hash', 'INSERT')";
          if (!mysqli_query($this->connection, $sqlq_Insert_Subject_History)) {
            return array("status"=>"failed", "query"=>$sqlq_Insert_Subject_History, "error"=>"SQL error: ".mysqli_connect_error());
          }
        } else {
          $SqlOldData = mysqli_fetch_array($sql_check, MYSQLI_ASSOC);
          $SqlHash = $SqlOldData['hash'];
          if($SqlHash!=$hash){
            $SqlOldData['name'] = mysqli_real_escape_string($this->connection, $SqlOldData['name']);
            $SqlOldData['longname'] = mysqli_real_escape_string($this->connection, $SqlOldData['longname']);
            $SqlOldData['displayname'] = mysqli_real_escape_string($this->connection, $SqlOldData['displayname']);
            $SqlOldData['alternatename'] = mysqli_real_escape_string($this->connection, $SqlOldData['alternatename']);
            // ----------------------------------------- Insert into history ----------------------------------------- //
            array_push($changedIDs, $id);
            $sqlq_Insert_Subject_History = "INSERT INTO $prefixSubjectsHis (itm_id, name, longname, displayname, alternatename, last_update, hash, status)
            VALUES ($SqlOldData[id], '$SqlOldData[name]', '$SqlOldData[longname]', '$SqlOldData[displayname]', '$SqlOldData[alternatename]', '$SqlOldData[last_update]', '$SqlOldData[hash]', 'UPDATE')";
            if (!mysqli_query($this->connection, $sqlq_Insert_Subject_History)) {
              return array("status"=>"failed", "query"=>$sqlq_Insert_Subject_History, "error"=>"SQL error: ".mysqli_connect_error());
            }
            // ----------------------------------------- Update Database --------------------------------------------- //
            $sqlq_Update_Subject = "UPDATE $prefixSubjects SET name='$name', longname='$longName', displayname='$displayname', alternatename='$alternatename', last_update=NOW(), hash='$hash' WHERE id=$id";
            if (!mysqli_query($this->connection, $sqlq_Update_Subject)) {
              return array("status"=>"failed", "query"=>$sqlq_Update_Subject, "error"=>"SQL error: ".mysqli_connect_error());
            }
          }
        }
      }
      $removedIDs = array_values(array_diff($oldIDs, $newIDs));
      foreach($removedIDs as $id) {
        $sqlq_check = "SELECT * from $prefixSubjects WHERE (id = $id) LIMIT 1";
        $sql_check = mysqli_query($this->connection, $sqlq_check);
    		if(mysqli_num_rows($sql_check) > 0){
          $SqlOldData = mysqli_fetch_array($sql_check, MYSQLI_ASSOC);
          $SqlHash = $SqlOldData['hash'];
          if($SqlHash!=$hash){
            $SqlOldData['name'] = mysqli_real_escape_string($this->connection, $SqlOldData['name']);
            $SqlOldData['longname'] = mysqli_real_escape_string($this->connection, $SqlOldData['longname']);
            $SqlOldData['displayname'] = mysqli_real_escape_string($this->connection, $SqlOldData['displayname']);
            $SqlOldData['alternatename'] = mysqli_real_escape_string($this->connection, $SqlOldData['alternatename']);
            // ----------------------------------------- Insert into history ----------------------------------------- //
            array_push($changedIDs, $id);
            $sqlq_Insert_Subject_History = "INSERT INTO $prefixSubjectsHis (itm_id, name, longname, displayname, alternatename, last_update, hash, status)
        		VALUES ($SqlOldData[id], '$SqlOldData[name]', '$SqlOldData[longname]', '$SqlOldData[displayname]', '$SqlOldData[alternatename]', '$SqlOldData[last_update]', '$SqlOldData[hash]', 'REMOVED')";
        		if (!mysqli_query($this->connection, $sqlq_Insert_Subject_History)) {
        			return array("status"=>"failed", "query"=>$sqlq_Insert_Subject_History, "error"=>"SQL error: ".mysqli_connect_error());
        		}
            // ----------------------------------------- Update Database --------------------------------------------- //
            $sqlq_Remove_Subject = "DELETE FROM $prefixSubjects WHERE id=$id";
        		if (!mysqli_query($this->connection, $sqlq_Remove_Subject)) {
        			return array("status"=>"failed", "query"=>$sqlq_Remove_Subject, "error"=>"SQL error: ".mysqli_connect_error());
        		}
          }
        }
      }
      return array("status"=>"success", "changes"=>array(
        "old"=>$oldIDs,
        "inserted"=>$insertedIDs,
        "changed"=>$changedIDs,
        "removed"=>$removedIDs
      ));
    }
    public function getSubjectsSQL(){
      $prefixSubjects = $this->ini_array['msql_prefix']."Subjects";
      $prefixSubjectsHis = $this->ini_array['msql_prefix']."Subjects_his";

      $sqlq_Get = "SELECT * from $prefixSubjects ORDER BY name";
      $sql_Get = mysqli_query($this->connection, $sqlq_Get);
      $Subjects = array();
      if (mysqli_num_rows($sql_Get) > 0) {
        while($row = mysqli_fetch_assoc($sql_Get)) {
          $sqlq_GetHistory = "SELECT * from $prefixSubjectsHis WHERE itm_id=$row[id]";
          $sql_GetHistory = mysqli_query($this->connection, $sqlq_GetHistory);
          $row['history'] = array();
          if (mysqli_num_rows($sql_GetHistory) > 0) {
            while($rowHistory = mysqli_fetch_assoc($sql_GetHistory)) {
              array_push($row['history'], $rowHistory);
            }
          }
          $Subjects[$row['id']] = $row;
        }
      }
      return $Subjects;
    }
    public function getSingleSubject($id){
      $prefixSubjects = $this->ini_array['msql_prefix']."Subjects";
      $prefixSubjectsHis = $this->ini_array['msql_prefix']."Subjects_his";

      $sqlq_Get = "SELECT * from $prefixSubjects WHERE id=$id";
      $sql_Get = mysqli_query($this->connection, $sqlq_Get);
      if (mysqli_num_rows($sql_Get) > 0) {
        $row = mysqli_fetch_assoc($sql_Get);
        $sqlq_GetHistory = "SELECT * from $prefixSubjectsHis WHERE itm_id=$id";
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

    public function getPeriods($fresh, $UntisData){
      if($fresh){
        $ServerPeriods = $UntisData->Periods();
        $response = $this->insertPeriods($ServerPeriods);
        if($response['success']=true){
          $data = $this->getPeriodsSQL();
          return json_encode($data, true);
        } else {
          return json_encode($response, true);
        }
      } else {
        $data = $this->getPeriodsSQL();
        return json_encode($data, true);
      }
    }
    public function insertPeriods($data){
      $prefixPeriods = $this->ini_array['msql_prefix']."Periods";
      $prefixPeriodsHis = $this->ini_array['msql_prefix']."Periods_his";

      $sqlq_ids = "SELECT id from $prefixPeriods";
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
      foreach ($data as $PeriodObject) {
        $id = $PeriodObject['id'];
        $lesson_id = $PeriodObject['lessonId'];
        $lesson_number = $PeriodObject['lessonNumber'];
        $lesson_code = mysqli_real_escape_string($this->connection, $PeriodObject['lessonCode']);
        $lesson_text = mysqli_real_escape_string($this->connection, $PeriodObject['lessonText']);
        $period_text = mysqli_real_escape_string($this->connection, $PeriodObject['periodText']);
        $has_period_text = $PeriodObject['hasPeriodText']?1:0;
        $dateRaw = date_create_from_format('Ymd', $PeriodObject['date']);
        $date = date_format($dateRaw, 'Y-m-d')." 00:00:00";

        if($PeriodObject['startTime']<1000)$PeriodObject['startTime'] = "0".$PeriodObject['startTime'];
        $start_timeRaw = date_create_from_format('Hi', $PeriodObject['startTime']);
        $start_time = date_format($dateRaw, 'Y-m-d')." ".date_format($start_timeRaw, 'H:i:s');

        if($PeriodObject['endTime']<1000)$PeriodObject['startTime'] = "0".$PeriodObject['endTime'];
        $end_timeRaw = date_create_from_format('Hi', $PeriodObject['endTime']);
        $end_time = date_format($dateRaw, 'Y-m-d')." ".date_format($end_timeRaw, 'H:i:s');

        $date = mysqli_real_escape_string($this->connection, $date);
        $start_time = mysqli_real_escape_string($this->connection, $start_time);
        $end_time = mysqli_real_escape_string($this->connection, $end_time);

        if(array_key_exists("studentGroup",$PeriodObject))$student_group = mysqli_real_escape_string($this->connection, $PeriodObject['studentGroup']);
        else $student_group="";
        $has_info = $PeriodObject['hasInfo']?1:0;
        $code = $PeriodObject['code'];
        $cell_state = mysqli_real_escape_string($this->connection, $PeriodObject['cellState']);
        $priority = $PeriodObject['priority'];

        $schoolclasses = array();
        $teachers = array();
        $subjects = array();
        $rooms = array();
        foreach ($PeriodObject['elements'] as $key => $element) {
          if($element['type']==1){
            array_push($schoolclasses, $element['id']);
          } else if($element['type']==2){
            array_push($teachers, $element['id']);
          } else if($element['type']==3){
            array_push($subjects, $element['id']);
          } else if($element['type']==4){
            array_push($rooms, $element['id']);
          }
        }
        $schoolclasses = mysqli_real_escape_string($this->connection, json_encode($schoolclasses, true));
        $teachers = mysqli_real_escape_string($this->connection, json_encode($teachers, true));
        $subjects = mysqli_real_escape_string($this->connection, json_encode($subjects, true));
        $rooms = mysqli_real_escape_string($this->connection, json_encode($rooms, true));

        unset($PeriodObject['roomCapacity']);
        unset($PeriodObject['studentCount']);

        $hash = md5(json_encode($PeriodObject, true));

        array_push($newIDs, $id);

        $sqlq_check = "SELECT * from $prefixPeriods WHERE (id = $id) LIMIT 1";
        $sql_check = mysqli_query($this->connection, $sqlq_check);
        if(mysqli_num_rows($sql_check) == 0){
          // ------------------------------------------- Insert Database --------------------------------------------- //
          $sqlq_Insert_Period = "INSERT INTO $prefixPeriods (
            id, lesson_id, lesson_number, lesson_code, lesson_text, period_text, has_period_text, date, start_time, end_time, student_group, has_info, code, cell_state, priority, schoolclasses, teachers, subjects, rooms, last_update, hash
          ) VALUES (
            $id, $lesson_id, $lesson_number, '$lesson_code', '$lesson_text', '$period_text', $has_period_text, '$date', '$start_time', '$end_time', '$student_group', $has_info, $code, '$cell_state', $priority, '$schoolclasses', '$teachers', '$subjects', '$rooms', NOW(), '$hash'
          )";
          array_push($insertedIDs, $id);
          if (!mysqli_query($this->connection, $sqlq_Insert_Period)) {
            return array("status"=>"failed", "query"=>$sqlq_Insert_Period, "error"=>"SQL error: ".mysqli_connect_error());
          }
          $sqlq_Insert_Period_History = "INSERT INTO $prefixPeriodsHis (
            itm_id, lesson_id, lesson_number, lesson_code, lesson_text, period_text, has_period_text, date, start_time, end_time, student_group, has_info, code, cell_state, priority, schoolclasses, teachers, subjects, rooms, last_update, hash, status
          ) VALUES (
            $id, $lesson_id, $lesson_number, '$lesson_code', '$lesson_text', '$period_text', $has_period_text, '$date', '$start_time', '$end_time', '$student_group', $has_info, $code, '$cell_state', $priority, '$schoolclasses', '$teachers', '$subjects', '$rooms', NOW(), '$hash', 'INSERT'
          )";
          if (!mysqli_query($this->connection, $sqlq_Insert_Period_History)) {
            return array("status"=>"failed", "query"=>$sqlq_Insert_Period_History, "error"=>"SQL error: ".mysqli_connect_error());
          }
        } else {
          $SqlOldData = mysqli_fetch_array($sql_check, MYSQLI_ASSOC);
          $SqlHash = $SqlOldData['hash'];
          if($SqlHash!=$hash){
            $SqlOldData['lesson_code'] = mysqli_real_escape_string($this->connection, $SqlOldData['lesson_code']);
            $SqlOldData['lesson_text'] = mysqli_real_escape_string($this->connection, $SqlOldData['lesson_text']);
            $SqlOldData['period_text'] = mysqli_real_escape_string($this->connection, $SqlOldData['period_text']);
            $SqlOldData['date'] = mysqli_real_escape_string($this->connection, $SqlOldData['date']);
            $SqlOldData['start_time'] = mysqli_real_escape_string($this->connection, $SqlOldData['start_time']);
            $SqlOldData['end_time'] = mysqli_real_escape_string($this->connection, $SqlOldData['end_time']);
            $SqlOldData['student_group'] = mysqli_real_escape_string($this->connection, $SqlOldData['student_group']);
            $SqlOldData['cell_state'] = mysqli_real_escape_string($this->connection, $SqlOldData['cell_state']);
            $SqlOldData['schoolclasses'] = mysqli_real_escape_string($this->connection, $SqlOldData['schoolclasses']);
            $SqlOldData['teachers'] = mysqli_real_escape_string($this->connection, $SqlOldData['teachers']);
            $SqlOldData['subjects'] = mysqli_real_escape_string($this->connection, $SqlOldData['subjects']);
            $SqlOldData['rooms'] = mysqli_real_escape_string($this->connection, $SqlOldData['rooms']);
            $SqlOldData['last_update'] = mysqli_real_escape_string($this->connection, $SqlOldData['last_update']);
            // ----------------------------------------- Insert into history ----------------------------------------- //
            array_push($changedIDs, $id);
            $sqlq_Insert_Period_History = "INSERT INTO $prefixPeriodsHis (
              itm_id, lesson_id, lesson_number, lesson_code, lesson_text, period_text, has_period_text, date, start_time, end_time, student_group, has_info, code, cell_state, priority, schoolclasses, teachers, subjects, rooms, last_update, hash, status
            ) VALUES (
              $SqlOldData[id], $SqlOldData[lesson_id], $SqlOldData[lesson_number], '$SqlOldData[lesson_code]', '$SqlOldData[lesson_text]', '$SqlOldData[period_text]', $SqlOldData[has_period_text], '$SqlOldData[date]', '$SqlOldData[start_time]', '$SqlOldData[end_time]', '$SqlOldData[student_group]', $SqlOldData[has_info], $SqlOldData[code], '$SqlOldData[cell_state]', $SqlOldData[priority], '$SqlOldData[schoolclasses]', '$SqlOldData[teachers]', '$SqlOldData[subjects]', '$SqlOldData[rooms]', '$SqlOldData[last_update]', '$SqlOldData[hash]', 'UPDATE'
            )";
            if (!mysqli_query($this->connection, $sqlq_Insert_Period_History)) {
              return array("status"=>"failed", "query"=>$sqlq_Insert_Period_History, "error"=>"SQL error: ".mysqli_connect_error());
            }
            // ----------------------------------------- Update Database --------------------------------------------- //
            $sqlq_Update_Period = "UPDATE $prefixPeriods SET
              lesson_id=$lesson_id, lesson_number=$lesson_number, lesson_code='$lesson_code', lesson_text='$lesson_text', period_text='$period_text', has_period_text=$has_period_text, date='$date', start_time='$start_time', end_time='$end_time', student_group='$student_group', has_info=$has_info, code=$code, cell_state='$cell_state', priority=$priority, schoolclasses='$schoolclasses', teachers='$teachers', subjects='$subjects', rooms='$rooms', last_update=NOW(), hash='$hash'
            WHERE id=$id";
            if (!mysqli_query($this->connection, $sqlq_Update_Period)) {
              return array("status"=>"failed", "query"=>$sqlq_Update_Period, "error"=>"SQL error: ".mysqli_connect_error());
            }
          }
        }
      }
      $removedIDs = array_values(array_diff($oldIDs, $newIDs));
      foreach($removedIDs as $id) {
        $sqlq_check = "SELECT * from $prefixPeriods WHERE (id = $id) LIMIT 1";
        $sql_check = mysqli_query($this->connection, $sqlq_check);
    		if(mysqli_num_rows($sql_check) > 0){
          $SqlOldData = mysqli_fetch_array($sql_check, MYSQLI_ASSOC);
          $SqlHash = $SqlOldData['hash'];
          if($SqlHash!=$hash){
            $SqlOldData['lesson_code'] = mysqli_real_escape_string($this->connection, $SqlOldData['lesson_code']);
            $SqlOldData['lesson_text'] = mysqli_real_escape_string($this->connection, $SqlOldData['lesson_text']);
            $SqlOldData['period_text'] = mysqli_real_escape_string($this->connection, $SqlOldData['period_text']);
            $SqlOldData['date'] = mysqli_real_escape_string($this->connection, $SqlOldData['date']);
            $SqlOldData['start_time'] = mysqli_real_escape_string($this->connection, $SqlOldData['start_time']);
            $SqlOldData['end_time'] = mysqli_real_escape_string($this->connection, $SqlOldData['end_time']);
            $SqlOldData['student_group'] = mysqli_real_escape_string($this->connection, $SqlOldData['student_group']);
            $SqlOldData['cell_state'] = mysqli_real_escape_string($this->connection, $SqlOldData['cell_state']);
            $SqlOldData['schoolclasses'] = mysqli_real_escape_string($this->connection, $SqlOldData['schoolclasses']);
            $SqlOldData['teachers'] = mysqli_real_escape_string($this->connection, $SqlOldData['teachers']);
            $SqlOldData['subjects'] = mysqli_real_escape_string($this->connection, $SqlOldData['subjects']);
            $SqlOldData['rooms'] = mysqli_real_escape_string($this->connection, $SqlOldData['rooms']);
            $SqlOldData['last_update'] = mysqli_real_escape_string($this->connection, $SqlOldData['last_update']);
            // ----------------------------------------- Insert into history ----------------------------------------- //
            array_push($changedIDs, $id);
            $sqlq_Insert_Period_History = "INSERT INTO $prefixPeriodsHis (
              itm_id, lesson_id, lesson_number, lesson_code, lesson_text, period_text, has_period_text, date, start_time, end_time, student_group, has_info, code, cell_state, priority, schoolclasses, teachers, subjects, rooms, last_update, hash, status
            ) VALUES ($SqlOldData[id], $SqlOldData[lesson_id], $SqlOldData[lesson_number], '$SqlOldData[lesson_code]', '$SqlOldData[lesson_text]', '$SqlOldData[period_text]', $SqlOldData[has_period_text], '$SqlOldData[date]', '$SqlOldData[start_time]', '$SqlOldData[end_time]', '$SqlOldData[student_group]', $SqlOldData[has_info], $SqlOldData[code], '$SqlOldData[cell_state]', $SqlOldData[priority], '$SqlOldData[schoolclasses]', '$SqlOldData[teachers]', '$SqlOldData[subjects]', '$SqlOldData[rooms]', '$SqlOldData[last_update]', '$SqlOldData[hash]', 'REMOVED'
            )";
        		if (!mysqli_query($this->connection, $sqlq_Insert_Period_History)) {
        			return array("status"=>"failed", "query"=>$sqlq_Insert_Period_History, "error"=>"SQL error: ".mysqli_connect_error());
        		}
            // ----------------------------------------- Update Database --------------------------------------------- //
            $sqlq_Remove_Period = "DELETE FROM $prefixPeriods WHERE id=$id";
        		if (!mysqli_query($this->connection, $sqlq_Remove_Period)) {
        			return array("status"=>"failed", "query"=>$sqlq_Remove_Period, "error"=>"SQL error: ".mysqli_connect_error());
        		}
          }
        }
      }
      return array("status"=>"success", "changes"=>array(
        "old"=>$oldIDs,
        "inserted"=>$insertedIDs,
        "changed"=>$changedIDs,
        "removed"=>$removedIDs
      ));
    }
    public function getPeriodsSQL(){}
    public function getSinglePeriod($id){}
  }
?>
