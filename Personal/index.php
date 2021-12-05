<?php

include('/home/bitrix/php_scripts_bitrix24/ConnectDB.php');


function insertData($link, $data){
    $idInfoBlock = 11;
    $sid = $data['Ссылка'];
    $sname = $data['Наименование'];
    $sguidDepth = $data['Подразделение'];
    $snameUpper = strtoupper(($sname));


    // Поиск подразделения
    $query = "SELECT id from b_iblock_section where xml_id = '$sguidDepth'";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_array($result);
    $idIblockSection = $row['id'];
    // Инсерт основных полей
    $query = "insert into b_iblock_element
            (timestamp_x, iblock_section_id, active, sort, name, searchable_content, code, iblock_id, date_create, in_sections)
            values (now(), '$idIblockSection', 'Y', 500, '$sname', '$snameUpper', '$sid', $idInfoBlock, now(), 'Y')";

    $result = mysqli_query($link, $query);
    $last_id = mysqli_insert_id($link);



        
    $property101 = $data["Фамилия"];
    $property102 = $data["Имя"];
    $property103 = $data["Отчество"];
    $property105 = $data["Подразделение"];
    $property106 = $data["ПодразделениеКод"];
    $propertyGUID = $data["Ссылка"];
    $property100 = $data["Код"];
    $property96 = $data["Должность"];
    $property104 = $data["ДатаРождения"];
    $property97 = $data["Телефон"];
    $property98 = $data["Расположение"];
  

    $query = "INSERT into b_iblock_element_prop_s11
    (iblock_element_id, property_96, property_97, property_98, property_100, property_101, property_102, property_103, property_104, property_105, property_106)
    values ($last_id, '$property96', '$property97', '$property98', '$property100', '$property101', '$property102', '$property103', '$property104', '$property105', '$property106')";
    $result = mysqli_query($link, $query);
        


    $query = "INSERT INTO b_iblock_section_element
    (iblock_section_id, iblock_element_id)
    values ($idIblockSection, $last_id)";
    $result = mysqli_query($link, $query);

    addInfoFromUsers($link, $data, $last_id);
}


function updateData($link, $data){
    $idInfoBlock = 11;
    $sid = $data['Ссылка'];
    $sname = $data['Наименование'];
    $sguidDepth = $data['Подразделение'];
    $snameUpper = strtoupper(($sname));


    // Поиск подразделения
    $query = "SELECT id from b_iblock_section where xml_id = '$sguidDepth'";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_array($result);
    $idIblockSection = $row['id'];
    // Апдейт основных полей
    $query = "UPDATE b_iblock_element
                    set
                        
                        iblock_section_id = '$idIblockSection',
                        name = '$sname',
                        searchable_content = '$snameUpper',
                        code = '$sid'
                    where
                        iblock_id = $idInfoBlock
                        AND
                        code = '$sid'";

    $result = mysqli_query($link, $query);


    $query = "SELECT id from b_iblock_element where code = '$sid'";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_array($result);
    $last_id = $row['id'];


    $property101 = $data["Фамилия"];
    $property102 = $data["Имя"];
    $property103 = $data["Отчество"];
    $property105 = $data["Подразделение"];
    $property106 = $data["ПодразделениеКод"];
    $propertyGUID = $data["Ссылка"];
    $property100 = $data["Код"];
    $property96 = $data["Должность"];
    $property104 = $data["ДатаРождения"];
    $property97 = $data["Телефон"];
    $property98 = $data["Расположение"];
    $query = "UPDATE b_iblock_element_prop_s11
                set
                    property_96 = '$property96',
                    property_97 = '$property97',
                    property_98 = '$property98',
                    property_100 = '$property100',
                    property_101 = '$property101',
                    property_102 = '$property102',
                    property_103 = '$property103',
                    property_104 = '$property104',
                    property_105 = '$property105',
                    property_106 = '$property106'
                where
                    iblock_element_id = '$last_id'";
    $result = mysqli_query($link, $query);
        


    $query = "UPDATE b_iblock_section_element
                set
                    iblock_section_id = $idIblockSection
                where
                    iblock_element_id = $last_id";
    $result = mysqli_query($link, $query);
    
    addInfoFromUsers($link, $data, $last_id);

}



function addInfoFromUsers($link, $data, $last_id){
    $sname = $data['Наименование'];
    $query = "SELECT count(*) as ncount FROM b_user where admin_notes = '$sname'";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_array($result);

    if ($row['ncount'] == 1) {
        $query = "SELECT personal_street, personal_phone FROM b_user where admin_notes = '$sname'";
        $result = mysqli_query($link, $query);
        $row = mysqli_fetch_array($result);
        $persStreet = $row['personal_street'];
        $persPhone = $row['personal_phone'];

        $query = "UPDATE b_iblock_element_prop_s11
            set
                property_97 = '$persPhone',
                property_98 = '$persStreet'
            where
                iblock_element_id = $last_id";
        $result = mysqli_query($link, $query);

    } else {
        echo 'Сотрудник с таким ФИО не найден\несколько штук: ' . $sname;
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    $link = connectToDB();

    $sid = $data['Ссылка'];
    /* получаем все неотправленные сообщения из таблице исходящей очереди */
    $query = "SELECT count(*) as ncount FROM b_iblock_element where code = '$sid'";
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