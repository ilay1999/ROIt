<?php 
    //GET параметры если не пришло ничего то по умолчанию 0
    $ShownID = $_GET['getproduct']??'0';
    $BeforeID = $_GET['beforeid']??'0';

    //Вынимает товары по указанному ИД группы
    function ProdDisplay($ProductID){
        include 'DBConnect.php';
        $prodArr = [];
        $products = mysqli_query($connect, "SELECT * FROM `products` WHERE `id_group`='$ProductID'");
        while ($shData = mysqli_fetch_assoc($products)){
            array_push($prodArr, array(
                'id_group' => $shData['id_group'], 
                'name' => $shData['name']
                )
            );
        };
        return $prodArr;
    };

    //Собирает продукцию к отображению
    function ShowProduct($ProductID){
        $prodArr = [];
        $ProductArr = ProdSummaraizer($ProductID);
        foreach($ProductArr as $item){
            $Products = ProdDisplay($item['id_group']);
            foreach($Products as $item){
                array_push($prodArr, $item);
            };
        };
        return $prodArr;
    };

    //Ищет детей-родителей обьеденяет в 1  массив для подсчета товаров в категории
    function ProdSummaraizer($GroupID){
        include 'DBConnect.php';
        $groupArrClear = array();
        $groupArrSearch = [];
        array_push($groupArrClear, array(
            'id_group' => $GroupID)
        );
        $groups = mysqli_query($connect, "SELECT * FROM `groups` WHERE `id_parent`='$GroupID'");
        while($shData = mysqli_fetch_assoc($groups)){
            array_push($groupArrSearch, array(
                'id_group' => $shData['id'])
            );
        };
        foreach($groupArrSearch as $item){
            $id = $item['id_group'];
            $groups = mysqli_query($connect, "SELECT * FROM `groups` WHERE `id_parent`='$id'");
            while($shData = mysqli_fetch_assoc($groups)){
                array_push($groupArrSearch, array(
                    'id_group' => $shData['id'])
                );
                
            };
        }
        foreach($groupArrSearch as $item)
        {
            array_push($groupArrClear, $item);
        };
        return $groupArrClear;
    };

    //Считает количество товаров в категории с учетом подкатегорий
    function ProdCounter($GetGroupID) {
        $Product = [];
        $ProdArr = ProdSummaraizer($GetGroupID);
        foreach($ProdArr as $item){
            $ProductSum = ProdDisplay($item['id_group']);
            foreach($ProductSum as $item){
                array_push($Product, array($item));
            };
        };
        $ProdCounter = count($Product);
        return $ProdCounter;
    };

    //Показывает все товары в магазине
    function ShowAll(){
        include 'DBConnect.php';
        $prodArr = [];
        $products = mysqli_query($connect, "SELECT * FROM `products`");
        while ($shData = mysqli_fetch_assoc($products)){
            array_push($prodArr, array(
                'id' => $shData['id'],
                'name' => $shData['name'],
            ));
        };
        return $prodArr;
    };

    //Древо  меню
    function MenuParent(){
        $Build = [];
        $Parent = [];
        include 'DBConnect.php';
        $groups = mysqli_query($connect, "SELECT * FROM `groups`");
        while ($shData = mysqli_fetch_assoc($groups)){
            array_push($Build, array(
                'id' => $shData['id'],
                'id_parent' => $shData['id_parent'],
                'name' => $shData['name'],
            ));
        };
        foreach($Build as $item => $Key){
            $Parent[$Key['id_parent']][$Key['id']] = $Key;
        };
        $MenuBuilder = $Parent[0];
        CreateMenu($MenuBuilder, $Parent);
        return $MenuBuilder;
    };

    //Создает ссылки на подмассивы категорий
    function CreateMenu(&$MenuBuilder, $Parent){
        foreach($MenuBuilder as $item => $key){
            if(!isset($item['Follow'])){
                $MenuBuilder[$item]['Follow'] = array();
            };
            if(array_key_exists($item, $Parent)){
                $MenuBuilder[$item]['Follow'] = $Parent[$item];
                CreateMenu($MenuBuilder[$item]['Follow'], $Parent);
            }; 
        };
    };

    //Выводит пункты меню
    function ProductMenu($Build, $Childs, $Counter, $Parent){
        foreach($Build as $Item){
            if(array_key_exists('Follow', $Item)){
                if($Item['id_parent'] == 0 && $Counter <= 0){
                    ?>
                        <ul>
                            <li><a href="Readytasks.php?getproduct=<?= $Item['id'] ?>"><?= $Item['name']; echo "(", ProdCounter($Item['id']), ")";?></a></li>
                    <?php
                    $Counter++;
                    $Parent = $Item['id_parent'];
                    $Childs = $Item['id'];
                }else if($Item['id_parent'] == 0 && $Counter > 0){
                    ?>
                        </ul>
                            <li><a href="Readytasks.php?getproduct=<?= $Item['id'] ?>"><?= $Item['name']; echo "(", ProdCounter($Item['id']), ")";?></a></li>
                    <?php
                    $Parent = $Item['id_parent'];
                    $Counter = 0;
                    $Childs = $Item['id'];
                }else if($Item['id_parent'] == $Childs){
                    ?>
                        <ul>
                            <li><a href="Readytasks.php?getproduct=<?= $Item['id'] ?>"><?= $Item['name']; echo "(", ProdCounter($Item['id']), ")";?></a></li>
                    <?php
                    $Parent = $Item['id_parent'];
                    $Childs = $Item['id'];
                }else if($Item['id_parent'] == $Parent && $Item['id'] != $Childs){
                    $Parent = $Item['id_parent'];
                    $Childs = $Item['id'];
                    ?>
                        <li><a href="Readytasks.php?getproduct=<?= $Item['id'] ?>"><?= $Item['name']; echo "(", ProdCounter($Item['id']), ")";?></a></li>
                    <?php
                }else if($Parent == $Item['id_parent'] && $Item['id'] == $Childs){
                    ?>
                        </ul>
                            <li><a href="Readytasks.php?getproduct=<?= $Item['id'] ?>"><?= $Item['name']; echo "(", ProdCounter($Item['id']), ")";?></a></li>
                    <?php
                    $Parent = $Item['id_parent'];
                    $Childs = $Item['id'];
                }else{
                    ?>
                        </ul>
                    <?php
                }; 
                ProductMenu($Item['Follow'], $Childs, $Counter, $Parent);
            };
        };
    };
 
?>
<html>
<head>
<meta charset="utf-8">
    <title>Тестовый вариант</title>
    <link rel="stylesheet" href="main.css"/>
</head>
    <body>
        <a href="Readytasks.php?getproduct=0&beforeid=0">В начало  списка</a>
        <?php
            ?>
                <div>
                    <?php
                        echo '<pre>', var_dump(ProductMenu(MenuParent(), $Childs, 0, 0)), '</pre>';
                    ?>
                </div>
                <div>
                    <pre>
                        <?php
                            echo '<pre>',"============================Отладочная==информация============================", '</pre>';
                            echo '<pre>',"============================ProdShow============================", '</pre>';
                            if($ShownID == 0){
                                $Products = ShowAll();
                            }else{
                                $Products = ShowProduct($ShownID);
                            };
                            foreach($Products as $item){
                                echo '<pre>', var_dump($item), '</pre>';
                                ?>
                                    <tr>
                                        <td><a href="card.php?cardproduct=<?= $item['id_group'] ?>"><?= $item['name']; ?></a></li>
                                    </tr>
                                <?php
                            };
                            echo '<pre>',"============================ProdSummaraizer============================", '</pre>';
                            echo '<pre>', var_dump(ProdSummaraizer($ShownID)), '</pre>';
                            echo '<pre>',"============================ProdCounter============================", '</pre>';
                            echo '<pre>', var_dump(ProdCounter($ShownID)), '</pre>';
                            
                            echo '<pre>',"============================ShowProduct============================", '</pre>';
                            echo '<pre>', var_dump(ShowProduct($ShownID)), '</pre>';
                            echo '<pre>',"============================Menu============================", '</pre>';
                            //echo '<pre>', var_dump(ProductMenu(MenuParent(), $Childs)), '</pre>';
                            echo '<pre>', var_dump(MenuParent()), '</pre>';
                        ?>
                    </pre>
                </div>
            <?php
        ?>
    </body>
</html>
