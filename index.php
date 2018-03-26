<?php
    $file_db;
    init();
    function init(){
        createTables();
        if(isDeleteFacturaButtonTriggered()){
            $factura_id = $_POST['remover-factura'];
            deleteFacturaById($factura_id); //It deletes all the products associate to it and then remove it from Facturas table
        }
        else if(isDeleteProductoButtonTriggered()){
            $producto_id = $_POST['producto_id'];
            $result = deleteProductoById($producto_id); //It update the bill's taxes and total fields as well
        }
        else if(isSaveButtonTriggeredWithValues()){
            $cliente_nm = $_POST['cliente_nm'];
            $fecha = $_POST['fecha'];
            insertNuevaFactura($cliente_nm,$fecha);
        }
        else if(isSaveButtonTriggeredtoUpdateFactura()){
            $cliente_nm = $_POST['cliente_nm'];
            $fecha = $_POST['fecha'];
            $factura_id = $_POST['factura_id'];
            updateFechaAndClienteFromFactura($fecha,$cliente_nm,$factura_id);
        }
    }

    function isSaveButtonTriggeredWithValues(){
       return isset($_POST['fecha']) && isset($_POST['cliente_nm']) && isset($_POST['guardar-factura']) && !isset($_POST['factura_id']);
    }

    function isSaveButtonTriggeredtoUpdateFactura(){
        return isset($_POST['fecha']) && isset($_POST['cliente_nm']) && isset($_POST['guardar-factura']) && isset($_POST['factura_id']);
    }

    function isSaveProductButtonTriggered(){
        return isset($_POST['cantidad']) && isset($_POST['descripcion']) && isset($_POST['valor_unitario']) && isset($_POST['guardar-producto']) && isset($_POST['factura_id']);
    }

    function isOpenFacturaButtonTriggered(){
        return isset($_POST['abrir-factura']) || isset($_POST['guardar-producto']);
    }

    function isDeleteFacturaButtonTriggered(){
        return isset($_POST['remover-factura']);
    }

    function isDeleteProductoButtonTriggered(){
        return isset($_POST['remover-producto']);
    }

    function createTables(){
        //set default timezone
        date_default_timezone_set('UTC');
        try{
            global $file_db;
            $file_db = new PDO('sqlite:messaging.sqlite3');
            //Set errormode to exceptions
            $file_db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            //Create tables
            $file_db->exec("CREATE TABLE IF NOT EXISTS Factura(factura_id integer not null primary key autoincrement,
                                                                cliente_nm varchar(100),
                                                                fecha varchar(50),
                                                                impuesto float,
                                                                total float)");
            $file_db->exec("CREATE TABLE IF NOT EXISTS Producto(producto_id integer not null primary key autoincrement,
                                                                factura_id integer not null,
                                                                cantidad integer,
                                                                descripcion text,
                                                                valor_unitario float,
                                                                subtotal float,
                                                                FOREIGN KEY (factura_id) REFERENCES Factura(factura_id))");
        }
        catch(PDOException $e){
            // Print PDOException message
            echo $e->getMessage();
        }
    }

    function insertNuevoProducto(){
        global $file_db;
        $factura_id = $_POST['factura_id'];
        $cantidad = $_POST['cantidad'];
        $valor_unitario = $_POST['valor_unitario'];
        $descripcion = $_POST['descripcion'];
        $subtotal = $valor_unitario * $cantidad;
        //guardar Producto y actualizar la factura;
        try{
            //Create insert statement
            $insertarProducto = "INSERT INTO Producto (factura_id, cantidad, descripcion, valor_unitario, subtotal)
                                 VALUES (:factura_id,:cantidad,:descripcion,:valor_unitario, :subtotal)";
            $stmt = $file_db->prepare($insertarProducto);
            // Bind parameters to statement variables
            $stmt->bindParam(':factura_id', $factura_id);
            $stmt->bindParam(':cantidad',$cantidad);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':valor_unitario', $valor_unitario);
            $stmt->bindParam(':subtotal', $subtotal);
            //Execute insert
            $stmt->execute();
            updateFactura($factura_id,$subtotal);
        }
        catch(PDOException $e){
            // Print PDOException message
            echo $e->getMessage();
        }

    }

    //It work in both sides when a product is added and when a product is delete we just have to pass the subtotal of the product
    //If is a product removal the sutotal will be negative
    function updateFactura($factura_id,$subtotal){
        $facturaResult = getFacturaById($factura_id);
        foreach($facturaResult as $row){
            $factura_id = $row['factura_id'];
            $cliente_nm = $row['cliente_nm'];
            $fecha = $row['fecha'];
            $impuesto = $row['impuesto'];
            $total = $row['total'];
        }
        $subimpuesto = ($subtotal * 0.13);
        $impuesto = $impuesto + $subimpuesto;
        $total = $total + ($subtotal + $subimpuesto);
        try{
            global $file_db;
            $updateFactura = "UPDATE Factura SET impuesto = {$impuesto} , total = {$total} WHERE factura_id = {$factura_id}";
            $stmt = $file_db->prepare($updateFactura);
            $stmt->execute();
        }
        catch(PDOException $e){
            // Print PDOException message
            echo $e->getMessage();
        }
    }

    function updateFechaAndClienteFromFactura($newfecha,$newcliente_nm,$factura_id){
        try{
            global $file_db;
            $updateFactura = "UPDATE Factura SET  cliente_nm = '{$newcliente_nm}', fecha = '{$newfecha}' WHERE factura_id = {$factura_id}";
            $stmt = $file_db->prepare($updateFactura);
            $stmt->execute();
        }
        catch(PDOException $e){
            // Print PDOException message
            echo "se cayo en el update de fecha y cliente";
            echo $e->getMessage();
        }
    }

    function insertNuevaFactura($cliente_nm, $fecha){
        global $file_db;
        $impuesto = 0.00;
        $total = 0.00;
        try{
            //Create insert statement
            $insertarFactura = "INSERT INTO Factura (cliente_nm, fecha, impuesto, total) VALUES (:cliente_nm,:fecha,:impuesto,:total)";
            $stmt = $file_db->prepare($insertarFactura);
            // Bind parameters to statement variables
            $stmt->bindParam(':cliente_nm', $cliente_nm);
            $stmt->bindParam(':fecha',$fecha);
            $stmt->bindParam(':impuesto', $impuesto);
            $stmt->bindParam(':total', $total);
            //Execute insert
            $stmt->execute();
        }
        catch(PDOException $e){
            // Print PDOException message
            echo $e->getMessage();
        }
        
    }

    function getFacturaById($factura_id){
        global $file_db;
        try{
            return $file_db->query("SELECT * FROM Factura where factura_id = '$factura_id'");
        }
        catch(PDOException $e){
            // Print PDOException message
            echo $e->getMessage();
        }
    }

    function getProductsByFacturaId($factura_id){
        global $file_db;
        try{
            return $file_db->query("SELECT * FROM Producto where factura_id = '$factura_id'");
        }
        catch(PDOException $e){
            // Print PDOException message
            echo $e->getMessage();
        }
    }

    function getProductById($producto_id){
        global $file_db;
        try{
            return $file_db->query("SELECT * FROM Producto where producto_id = '$producto_id'");
        }
        catch(PDOException $e){
            // Print PDOException message
            echo $e->getMessage();
        }
    }

    function deleteFacturaById($factura_id){
        global $file_db;
        try{
            $deleteAllProductsByFactura = "DELETE FROM Producto where factura_id='$factura_id'";
            $stmt = $file_db->prepare($deleteAllProductsByFactura);
            $stmt->execute();
            $deleteFactura = "DELETE FROM Factura where factura_id='$factura_id'";
            $stmt = $file_db->prepare($deleteFactura);
            $stmt->execute();
        }
        catch(PDOException $e){
            // Print PDOException message
            echo $e->getMessage();
        }
    }

    function deleteProductoById($producto_id){
        global $file_db;
        try{
            $productResult = getProductById($producto_id);
            foreach($productResult as $row) {
                $subtotal = $row['subtotal'] * -1;
                $factura_id = $row['factura_id'];
            }
            updateFactura($factura_id,$subtotal);
            
            $deleteProductById = "DELETE FROM Producto where producto_id='$producto_id'";
            $stmt = $file_db->prepare($deleteProductById);
            $stmt->execute();
        }
        catch(PDOException $e){
            // Print PDOException message
            echo $e->getMessage();
        }
        
    }

    function fillAllReceiptsTable(){
        global $file_db;
        $result = $file_db->query('SELECT * FROM Factura');
        foreach($result as $row) {
            $factura_id = $row['factura_id'];
            $cliente_nm = $row['cliente_nm'];
            echo "<tr>";
            echo "<td>$factura_id</td>";
            echo "<td>$cliente_nm</td>";
            echo "<td><form method='POST' action='index.php'><input type='hidden' name='remover-factura' value='$factura_id'><input type='submit' value='Eliminar' class='btn btn-danger'></form>";
            echo "<form method='POST' action='index.php'><input type='hidden' name='abrir-factura' value='$factura_id'><input type='submit' value='Abrir' class='btn btn-info'></form></td>";
            echo "</tr>";
        }
    }

    function fillFacturaSection(){
        if(isOpenFacturaButtonTriggered()){
            if(isset($_POST['abrir-factura'])){
                $factura_id = $_POST['abrir-factura'];
            }
            else if(isSaveProductButtonTriggered()){
                $factura_id = $_POST['factura_id'];
                insertNuevoProducto();
            }
            
            $result = getFacturaById($factura_id);
            foreach($result as $row){
                $factura_id = $row['factura_id'];
                $cliente_nm = $row['cliente_nm'];
                $fecha = $row['fecha'];
                $impuesto = $row['impuesto'];
                $total = $row['total'];
            }
            echo "<form method='POST' action='index.php'>";
            echo "<div class='form-group'>";
            echo "<label for='factura_id'>Numero Factura</label>";
            echo "<input type='text' id='factura_id' name='factura_id' value='$factura_id' class='form-control' disabled>";
            echo "</div>";
            echo "<div class='form-group'>";
            echo "<label for='fecha'>Fecha</label>";
            echo "<input type='datetime-local' id='fecha' name='fecha' value='$fecha' class='form-control'>";
            echo "</div>";
            echo "<div class='form-group'>";
            echo "<label for='cliente_nm'>Nombre Cliente</label>";
            echo "<input type='text' id='cliente_nm' name='cliente_nm' value='$cliente_nm' class='form-control'>";
            echo "</div>";
            echo "<div class='table-responsive'>";
            echo "<table class='table'><thead class='thead-dark'><tr>";
            echo "<th scope='col'>Cantidad</th><th scope='col'>Descripcion</th><th scope='col'>Valor Unitario</th><th scope='col'>Subtotal</th><th scope='col'>Action</th>";
            echo "</tr></thead>";
            echo "<tbody>";
            $productsresult = getProductsByFacturaId($factura_id);
            foreach($productsresult as $row){
                $producto_id = $row['producto_id'];
                $cantidad = $row['cantidad'];
                $descripcion = $row['descripcion'];
                $valor_unitario = $row['valor_unitario'];
                $subtotal = $row['subtotal'];
                echo "<input type='hidden' name='producto_id' value='$producto_id'>";
                echo "<tr><td>$cantidad</td><td>$descripcion</td><td>$valor_unitario</td><td>$subtotal</td>";
                echo "<td><input type='submit' name='remover-producto' value='Remover' class='btn btn-danger'></td></tr>";
            }
            echo "<tr>";
            echo "<input type='hidden' name='factura_id' value='$factura_id'>";
            echo "<td><input type='number' id='cantidad' name='cantidad'></td>";
            echo "<td><input type='text' id='descripcion' name='descripcion'></td>";
            echo "<td><input type='number' id='valor_unitario' name='valor_unitario' step='0.01'></td>";
            echo "<td><input type='number' id='subtotal' name='subtotal' step='0.01' disabled></td>";
            echo "<td><input type='submit' name='guardar-producto' value='Guardar Producto' class='btn btn-primary'></td>";
            echo "</tr>";
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            echo "<div class='form-row'>";
            echo "<div class='form-group col-md-6'>";
            echo "<label for='impuesto'>Impuesto</label>";
            echo "<input type='number' id='impuesto' name='impuesto' step='0.01' value='$impuesto' class='form-control' disabled>";
            echo "</div>";
            echo "<div class='form-group col-md-6'>";
            echo "<label for='total'>Total</label>";
            echo "<input type='number' id='total' name='total' step='0.01' value='$total' class='form-control' disabled>";
            echo "</div>";
            echo "<input type='submit' name='guardar-factura' value='Guardar' class='btn btn-primary'>";
            echo "</div>";
            echo "</form>";
        }
        else{
            echo "<form method='POST' action='index.php'>";
            echo "<div class='form-group'>";
            echo "<label for='factura_id'>Numero Factura</label>";
            echo "<input type='text' id='factura_id' name='factura_id' class='form-control' disabled>";
            echo "</div>";
            echo "<div class='form-group'>";
            echo "<label for='fecha'>Fecha</label>";
            echo "<input type='datetime-local' id='fecha' name='fecha' class='form-control'>";
            echo "</div>";
            echo "<div class='form-group'>";
            echo "<label for='cliente_nm'>Nombre Cliente</label>";
            echo "<input type='text' id='cliente_nm' name='cliente_nm' class='form-control'>";
            echo "</div>";
            echo "<div class='form-group'>";
            echo "<label for='impuesto'>Impuesto</label>";
            echo "<input type='number' id='impuesto' name='impuesto' step='0.01' value='0.00' class='form-control' disabled>";
            echo "</div>";
            echo "<div class='form-group'>";
            echo "<label for='total'>Total</label>";
            echo "<input type='number' id='total' name='total' step='0.01' value='0.00' class='form-control' disabled>";
            echo "</div>";
            echo "<input type='submit' name='guardar-factura' value='Guardar' class='btn btn-primary'>";
            echo "</form>";
        }
        
    }
?>
<!DOCTYPE html>
<html>
    <head>   
        <meta charset="utf-8">
        <title>Tarea 4</title>
        <link rel="stylesheet" href="styles/style.css">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    </head>
    <body>
        <header></header>
        <main class="container">
            <div class="row">
                <section class="col-md-4" id="receipts">
                    <p>Facturas</p>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Numero</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    fillAllReceiptsTable();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <section class="col-md-8" id="factura">
                <p>Factura</p>
                    <?php 
                        fillFacturaSection();
                    ?>
                </section>
            </div>
        </main>
        <footer></footer>
    </body>
</html>