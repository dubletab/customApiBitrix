<?php

include('/ConnectDB.php');


function insertData($link, $data, $idPropertyGUID){
    $sid = $data['guid'];
    $sname = $data['fullname'];
    $snameUpper = strtoupper(($sname));
    $idInfoBlock = 26;

    $query = "insert into b_iblock_element
            (active, sort, name, searchable_content, code, iblock_id, date_create)
            values ('Y', 500, '$sname', '$snameUpper', '$sid', $idInfoBlock, now())";

    $result = mysqli_query($link, $query);
    $last_id = mysqli_insert_id($link);

    foreach ($data as $key => $value) {

        $idProperty;
        if($key == "firstName") $idProperty = 85;
        elseif($key == "secondName") $idProperty = 86;
        elseif($key == "middleName") $idProperty = 87;
        elseif($key == "depthCode") $idProperty = 88;
        elseif($key == "guid") $idProperty = 89;
        elseif($key == "number") $idProperty = 90;
        elseif($key == "position") $idProperty = 91;
        elseif($key == "dateBirth") $idProperty = 92;
        elseif($key == "sex") $idProperty = 93;
        elseif($key == "fullname") $idProperty = 94;
        elseif($key == "phone") $idProperty = 95;
        else null;

        $query = "insert into b_iblock_element_property
        (iblock_property_id, iblock_element_id, value)
        values ($idProperty, $last_id, '$value')";

        $result = mysqli_query($link, $query);
        
    }


}


function updateData($link, $data, $idPropertyGUID){
    $sid = $data['guid'];
    $sname = $data['fullname'];
    $snameUpper = strtoupper(($sname));
    $idInfoBlock = 26;

    $query = "UPDATE b_iblock_element 
                    SET 
                        name = '$sname',
                        searchable_content = '$snameUpper'
                    WHERE
                        iblock_id = $idInfoBlock
                        AND
                        code = '$sid'";

    $result = mysqli_query($link, $query);


    /* Обновляем значения полей*/

    $query = "SELECT 
            b_iblock_element.id AS idinfoblock
        FROM
            b_iblock_element
        WHERE
            b_iblock_element.code = '$sid'";

        $result = mysqli_query($link, $query);

        $row = mysqli_fetch_array($result);
        $idInfoBlockElement = $row['idinfoblock'];


    foreach ($data as $key => $value) {


        $idProperty;
        if($key == "firstName") $idProperty = 85;
        elseif($key == "secondName") $idProperty = 86;
        elseif($key == "middleName") $idProperty = 87;
        elseif($key == "depthCode") $idProperty = 88;
        elseif($key == "guid") $idProperty = 89;
        elseif($key == "number") $idProperty = 90;
        elseif($key == "position") $idProperty = 91;
        elseif($key == "dateBirth") $idProperty = 92;
        elseif($key == "sex") $idProperty = 93;
        elseif($key == "fullname") $idProperty = 94;
        elseif($key == "phone") $idProperty = 95;
        else null;

        $query = "UPDATE b_iblock_element_property 
                    SET 
                        value = '$value'
                    WHERE
                        iblock_element_id = $idInfoBlockElement
                        AND
                        iblock_property_id = '$idProperty'";

        $result = mysqli_query($link, $query);

    }

}




if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    $idPropertyGUID = 89;
    echo $data['guid'];

    $link = connectToDB();

    $sid = $data['guid'];
    /* получаем все неотправленные сообщения из таблице исходящей очереди */
    $query = "SELECT count(*) as ncount FROM b_iblock_element
                inner join b_iblock_property on b_iblock_element.iblock_id = b_iblock_property.iblock_id
                inner join b_iblock_element_property on b_iblock_element_property.iblock_property_id = b_iblock_property.id and b_iblock_element_property.IBLOCK_ELEMENT_ID = b_iblock_element.id
                where b_iblock_element_property.value = '$sid'
                AND b_iblock_element_property.IBLOCK_PROPERTY_ID = $idPropertyGUID";

    $result = mysqli_query($link, $query);

    $row = mysqli_fetch_array($result);

    if ($row['ncount'] == 0) {
        echo ' insert!';
        insertData($link, $data, $idPropertyGUID);
    } else {
        updateData($link, $data, $idPropertyGUID);
        echo ' upd!1';
    }

    mysqli_close($link);

    header('HTTP/1.1 201 Created');
} else {
    header('HTTP/1.1 403 Deny method');
    echo 'Deny method';
}

?>