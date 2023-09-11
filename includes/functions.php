<?php
$host="localhost";
$user="root";
$password="";
$dbname="phpproject";
$conn=mysqli_connect($host,$user,$password,$dbname);
if(!$conn){
    echo "Could not connect with mysql!";
    exit;
}

function showCategory(){
    global $conn;
    $query = "SELECT * FROM category";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_array($result)) {
        echo "<a href='products.php?categoryid=$row[categoryid]' class='list-group-item'>" . $row['categoryname'] . "<span class='badge'></span></a>";
    }
}

function showProduct(){
    global $conn;
    $productid = $_GET["productid"];
    $query = "SELECT * FROM product p , category c where p.productid = $productid and p.categoryid = c.categoryid";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_object($result)) {
        $image = $row->image;
        $image_src = "img/" . $image;
        echo "<br/><img src = " . $image_src . " height='300px'>";
    }
}

function productDetails(){
    global $conn;
    $productid = $_GET["productid"];
    $query = "SELECT * FROM product p , category c where p.productid = $productid and p.categoryid = c.categoryid";
    $result = mysqli_query($conn , $query);
    while ($row = mysqli_fetch_object($result))
    {
        echo "<br/><br/>";
        echo "<h3 style='font-weight: bold';>Name: ".$row->productname."</h3>";
        echo "<h3 style='font-weight: bold';>Category: ".$row->categoryname."</h3>";
        echo "<h3 style='font-weight: bold';>Price: ".$row->price." den</h3>";
        echo "<h3 style='font-weight: bold';>Info:</h3> ".$row->info;
        if($row->paid == 0)
        {
            echo '
			<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
                            <input type="hidden" name="cmd" value="_xclick">
                            <input type="hidden" name="business" value="vlerant.saiti-facilitator@live.com">
                            <input type="hidden" name="item_name" value="'.$row->productname.'">
                            <input type="hidden" name="item_number" value="'.$row->productid.'">
                            <input type="hidden" name="amount" value="'.($row->price * 0.019).'">
                            <input type="hidden" name="no_shipping" value="0">
                            <input type="hidden" name="return" value="http://localhost/sela/includes/paypal.php">
                            <input type="hidden" name="cancel_return" value=""http://localhost/sela/includes/paypal.php">
                            <input type="hidden" name="no_note" value="1">
                            <input type="hidden" name="currency_code" value="USD">
                            <input type="hidden" name="lc" value="US">
                            <input type="image" src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-small.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">


                            </form>';
            }else {
                       echo '     <button disabled style="width:100%:" class="btn btn-success"> Paid </button>';
         }
        

    }
}

function selectOrder(){
    if(!isset($_GET["categoryid"]))
    {
        echo "<br>";
        echo "<h1 align='center'>Please Select a Category !!</h1>";
    }
    else
    {
        echo "<br/>
                <select id='orderSelect' class='form-control'>
                  <option value=''>Select order</option>
                  <option value='price'>Price</option>
                  <option value='productname'>Name</option>
              </select>
              <br/>";
    }
}

function order(){
    global $conn;
    if (isset($_GET['order']))
    {
        $sql = "SELECT * from category c, product p where c.categoryid = ".$_GET["categoryid"]." and c.categoryid = p.categoryid ORDER BY ".$_GET['order'];
    }
    else
    {
        $sql = "SELECT * from category c, product p where c.categoryid = ".$_GET["categoryid"]." and c.categoryid = p.categoryid ";
    }
    $result = mysqli_query($conn ,$sql);
    echo "<form method='GET' align='center'>";
    $i=0;
    while($row = mysqli_fetch_object($result))
    {
        $image = $row->image;
        $image_src = "img/".$image;
        $categoryid = $_GET["categoryid"];
        echo "<a href='description.php?productid=$row->productid' class='btn btn-default btn-xs' target='_blank'>";
        echo "<h3>".$row->productname."</h3>";
        echo "<img src = ".$image_src."><br>";
        echo "<h5>".$row->price." den</h5>";
        echo "</a>";
        $i++;
        if ($i>2)
        {
            echo "<br/> <br/> ";
            $i=0;
        }
        echo "&nbsp &nbsp";
    }
    echo "</form>";
}


?>