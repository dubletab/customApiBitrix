<?php

include('/ConnectDB.php');

function insertData($link, $data){
    $sguid = $data['guid'];
    $sname = $data['name'];
    $snameUpper = strtoupper(($sname));
    $scod = $data['code'];
    $sparentCod = $data['parentCode'];
    $sdescr = $data['descr'];


    $queryMargin = "SELECT max(left_margin) as lmargin, max(right_margin) as rmargin from b_iblock_section where b_iblock_section.iblock_id = 11";
    $resultMargin = mysqli_query($link, $queryMargin);
    $rowMargin = mysqli_fetch_array($resultMargin);
    $lmargin = $rowMargin['lmargin'] + 1;
    $rmargin = $rowMargin['rmargin'] + 1;


    if($sparentCod == null){
        $query = "INSERT into b_iblock_section 
        (TIMESTAMP_X, MODIFIED_BY, DATE_CREATE, CREATED_BY, IBLOCK_ID, IBLOCK_SECTION_ID, ACTIVE, GLOBAL_ACTIVE, SORT, NAME, LEFT_MARGIN, RIGHT_MARGIN, DEPTH_LEVEL, DESCRIPTION, DESCRIPTION_TYPE, SEARCHABLE_CONTENT, CODE, XML_ID, TMP_ID) 
        values (now(), 2317, now(), 2317, 11, 21, 'Y', 'Y', 500, '$sname', '$lmargin', '$rmargin', 2, '$sdescr', 'text', '$snameUpper', '$scod', '$sguid', '$sparentCod')";
        $result = mysqli_query($link, $query);
        $last_id = mysqli_insert_id($link);
    }else{
        $query = "INSERT into b_iblock_section 
        (TIMESTAMP_X, MODIFIED_BY, DATE_CREATE, CREATED_BY, IBLOCK_ID, IBLOCK_SECTION_ID, ACTIVE, GLOBAL_ACTIVE, SORT, NAME, LEFT_MARGIN, RIGHT_MARGIN, DEPTH_LEVEL, DESCRIPTION, DESCRIPTION_TYPE, SEARCHABLE_CONTENT, CODE, XML_ID, TMP_ID) 
        values (now(), 2317, now(), 2317, 11, null, 'Y', 'Y', 500, '$sname', '$lmargin', '$rmargin', null, '$sdescr', 'text', '$snameUpper', '$scod', '$sguid', '$sparentCod')";
        $result = mysqli_query($link, $query);
        $last_id = mysqli_insert_id($link);

        /* если есть родитель ставим уровень вложенности*/
        $queryParent = "SELECT ID, DEPTH_LEVEL from b_iblock_section where code = '$sparentCod'";
        $resultParent = mysqli_query($link, $queryParent);
        $rowParent = mysqli_fetch_array($resultParent);
        $idParent = $rowParent['ID'];
        $lvlParent = $rowParent['DEPTH_LEVEL'];
        $lvl = $lvlParent + 1;

        $query = "UPDATE b_iblock_section
                    set 
                        DEPTH_LEVEL = $lvl,
                        IBLOCK_SECTION_ID = $idParent
                    where b_iblock_section.iblock_id = 11 and b_iblock_section.xml_id = '$sguid'";
        
        $result = mysqli_query($link, $query);

    }

    /*ставим уровень вложенности детям*/
    $queryForChild = "SELECT DEPTH_LEVEL as dl from b_iblock_section where id = '$last_id' and b_iblock_section.iblock_id = 11";
    $resultForChild = mysqli_query($link, $queryForChild);
    $rowForChild = mysqli_fetch_array($resultForChild);
    $lvlForChild = $rowForChild['dl'];
    $lvl = $lvlForChild + 1;
    $queryUpdateChild = "UPDATE b_iblock_section
                            set
                            DEPTH_LEVEL = $lvl,
                            IBLOCK_SECTION_ID = $last_id
                        where b_iblock_section.iblock_id = 11 and b_iblock_section.tmp_id = '$scod'";
    $resultUpdateChild = mysqli_query($link, $queryUpdateChild);

}

function updateData($link, $data){
    $sguid = $data['guid'];
    $sname = $data['name'];
    $snameUpper = strtoupper(($sname));
    $scod = $data['code'];
    $sparentCod = $data['parentCode'];
    $sdescr = $data['descr'];


    if($sparentCod == null){
        $query = "UPDATE b_iblock_section
        set
            b_iblock_section.name = '$sname',
            b_iblock_section.DESCRIPTION = '$sdescr',
            b_iblock_section.SEARCHABLE_CONTENT = '$snameUpper',
            b_iblock_section.CODE = '$scod',
            b_iblock_section.TMP_ID = null,
            b_iblock_section.DEPTH_LEVEL = 2
        where b_iblock_section.iblock_id = 11 and b_iblock_section.xml_id = '$sguid'";
        $result = mysqli_query($link, $query);
    }else{
        /* если есть родитель ставим уровень вложенности*/
        $queryParent = "SELECT ID, DEPTH_LEVEL as dl from b_iblock_section where code = '$sparentCod'";
        $resultParent = mysqli_query($link, $queryParent);
        $rowParent = mysqli_fetch_array($resultParent);
        $idParent = $rowParent['ID'];
        echo $idParent;
        $lvlParent = $rowParent['dl'];
        echo '_' . $lvlParent . '_';
        $lvl = $lvlParent + 1;
        echo ' ' . $lvl;


        $query = "UPDATE b_iblock_section
        set
            b_iblock_section.name = '$sname',
            b_iblock_section.DESCRIPTION = '$sdescr',
            b_iblock_section.SEARCHABLE_CONTENT = '$snameUpper',
            b_iblock_section.CODE = '$scod',
            b_iblock_section.TMP_ID = '$sparentCod',
            DEPTH_LEVEL = $lvl,
            IBLOCK_SECTION_ID = $idParent
        where b_iblock_section.iblock_id = 11 and b_iblock_section.xml_id = '$sguid'";
        $result = mysqli_query($link, $query);

    }

    /*ставим уровень вложенности детям*/
    $queryForChild = "SELECT DEPTH_LEVEL as dl, id from b_iblock_section where xml_id = '$sguid' and b_iblock_section.iblock_id = 11";
    $resultForChild = mysqli_query($link, $queryForChild);
    $rowForChild = mysqli_fetch_array($resultForChild);
    $idForChild = $rowForChild['id'];
    $lvlForChild = $rowForChild['dl'];
    $lvl = $lvlForChild + 1;
    $queryUpdateChild = "UPDATE b_iblock_section
                            set
                            DEPTH_LEVEL = $lvl,
                            IBLOCK_SECTION_ID = $idForChild
                        where b_iblock_section.iblock_id = 11 and b_iblock_section.tmp_id = '$scod'";
    $resultUpdateChild = mysqli_query($link, $queryUpdateChild);


    

}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    echo $data['guid'];

    $link = connectToDB();

    $sid = $data['guid'];
    /* получаем все неотправленные сообщения из таблице исходящей очереди */
    $query = "SELECT count(*) as ncount FROM b_iblock_section
                where b_iblock_section.xml_id = '$sid' and b_iblock_section.iblock_id = 11";

    $result = mysqli_query($link, $query);

    $row = mysqli_fetch_array($result);

    if ($row['ncount'] == 0) {
        echo ' insert!';
        insertData($link, $data);
    } else {
        updateData($link, $data);
        echo ' upd!';
    }

    mysqli_close($link);

    header('HTTP/1.1 201 Created');
} else {
    header('HTTP/1.1 403 Deny method');
}
?>