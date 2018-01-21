<?php
/*
 * Copyright 2015 by Adrienne Beebe, Yonah Biers-Ariel, Connor Hargus, Phuong Le, 
 * Xun Wang, and Allen Tucker. This program is part of RMHP-Homebase, which is free 
 * software.  It comes with absolutely no warranty. You can redistribute and/or 
 * modify it under the terms of the GNU General Public License as published by the 
 * Free Software Foundation (see <http://www.gnu.org/licenses/ for more information).
 */

/**
 * Functions to create, update, and retrieve information from the
 * dbSCL table in the database.  This table is used with the SCL
 * class.  Sub Call Lists are generated by editShift and viewed and edited
 * with subCallList.php
 * @version May 1, 2008
 * @author Maxwell Palmer
 */
include_once('dbinfo.php');
include_once('domain/SCL.php');

/**
 * adds a SCL to the table
 */
function insert_dbSCL($scl) {
    if (!$scl instanceof SCL) {
        die("Invalid argument for dbSCL->insert_dbSCL($) function call");
    }
    $persons = $scl->get_persons();
    if ($persons) {
        for ($i = 0; $i < count($persons); ++$i) {
            $persons[$i] = implode("+", $persons[$i]);
            //echo $i.":\t";
            //print_r($persons[$i]);
        }
        $persons = implode(",", $persons);
    }
    $con=connect();
    $query = "INSERT INTO dbSCL VALUES
				(\"" . $scl->get_id() . "\",\"" . $persons . "\",\"" . $scl->get_status() . "\",\"" . $scl->get_vacancies() . "\",\"" . $scl->get_time() . "\")";
    $result = mysqli_query($con,$query);
    if (!$result) {
        echo "unable to insert into dbSCL: " . $scl->get_id() . mysqli_error($con);
        mysqli_close($con);
        return false;
    }
    mysqli_close($con);
    return true;
}

/**
 * deletes SCl from db
 */
function delete_dbSCL($scl) {
    if (!$scl instanceof SCL)
        die("Invalid argument for dbSCL->delete_dbSCL($scl) function call");
    $con=connect();
    $query = "DELETE FROM dbSCL WHERE id=\"" . $scl->get_id() . "\"";
    $result = mysqli_query($con,$query);
    if (!$result) {
        echo "unable to delete dbSCL: " . $scl->get_id() . mysqli_error($con);
        mysqli_close($con);
        return false;
    }
    mysqli_close($con);
    return true;
}

/**
 * updates a SCL in the database by deleting it and re-inserting it.
 */
function update_dbSCL($scl) {
    if (!$scl instanceof SCL)
        die("Invalid argument for dbSCL->update_dbSCL($) function call");
    delete_dbSCL($scl);      // try to delete
    if (insert_dbSCL($scl))  // and then insert
        return true;
    else
        return false;
}

/**
 * @return returns a SCL object, or an error string if the SubCallList is not in the database
 */
function select_dbSCL($id) {
    $con=connect();
    $query = "SELECT * FROM dbSCL WHERE id=\"" . $id . "\"";
    $result = mysqli_query($con,$query);
    mysqli_close($con);
    if (!$result) {
        echo "Entry " . $id . " is not in the database. " . mysqli_error($con);
        return null;
    } else {
        $result_row = mysqli_fetch_row($result);
        if ($result_row) {
            $persons = explode(",", $result_row[1]);
            for ($i = 0; $i < count($persons); ++$i) {
                $persons[$i] = explode("+", $persons[$i]);
            }
            if (!$persons[0][0])
                $persons = null;
            return new SCL($result_row[0], $persons, $result_row[2], $result_row[3], $result_row[4]);
        }
        return null;
    }
}

?>
