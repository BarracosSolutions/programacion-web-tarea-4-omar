<?php
    $file_db;
    init();
    function init(){
        createTables();
        if(isSaveButtonTriggeredWithValues()){
            $cliente_nm = $_POST['cliente_nm'];
            $fecha = $_POST['fecha'];
            insertNuevaFactura($cliente_nm,$fecha);
        }
        else if(isDeleteFacturaButtonTriggered()){
            $factura_id = $_POST['remover-factura'];
            $result = deleteFacturaById($factura_id);
        }
    }

    function isSaveButtonTriggeredWithValues(){
       return isset($_POST['fecha']) && isset($_POST['cliente_nm']) && !isset($_POST['factura_id']);
    }

    function isOpenFacturaButtonTriggered(){
        return isset($_POST['abrir-factura']);
    }

    function isDeleteFacturaButtonTriggered(){
        return isset($_POST['remover-factura']);
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
                                                                fecha varchar(20),
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
        return $file_db->query("SELECT * FROM Factura where factura_id = '$factura_id'");
        
    }

    function deleteFacturaById($factura_id){
        global $file_db;
        $file_db->query("DELETE FROM Factura where factura_id='$factura_id'");
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
            echo "<td><form method='POST' action='index.php'><input type='hidden' name='remover-factura' value='$factura_id'><input type='submit' value='X'></form></td>";
            echo "<td><form method='POST' action='index.php'><input type='hidden' name='abrir-factura' value='$factura_id'><input type='submit' value='Abrir'></form></td>";
            echo "</tr>";
        }
    }

    function fillFacturaSection(){
        if(isOpenFacturaButtonTriggered()){
            $factura_id = $_POST['abrir-factura'];
            $result = getFacturaById($factura_id);
            foreach($result as $row){
                $factura_id = $row['factura_id'];
                $cliente_nm = $row['cliente_nm'];
                $fecha = $row['fecha'];
                $impuesto = $row['impuesto'];
                $total = $row['total'];
            }
            echo "<form method='POST' action='index.php'>";
            echo "<label for='factura_id'>Numero Factura</label>";
            echo "<input type='text' id='factura_id' name='factura_id' value='$factura_id' disabled>";
            echo "<label for='fecha'>Fecha</label>";
            echo "<input type='datetime-local' id='fecha' name='fecha' value='$fecha'>";
            echo "<label for='cliente_nm'>Nombre Cliente</label>";
            echo "<input type='text' id='cliente_nm' name='cliente_nm' value='$cliente_nm'>";
            echo "<label for='impuesto'>Impuesto</label>";
            echo "<input type='number' id='impuesto' name='impuesto' step='0.01' value='$impuesto' disabled>";
            echo "<label for='total'>Total</label>";
            echo "<input type='number' id='total' name='total' step='0.01' value='$total' disabled>";
            echo "<input type='submit' value='Guardar'>";
            echo "</form>";
        }
        else{
            echo "<form method='POST' action='index.php'>";
            echo "<label for='factura_id'>Numero Factura</label>";
            echo "<input type='text' id='factura_id' name='factura_id' disabled>";
            echo "<label for='fecha'>Fecha</label>";
            echo "<input type='datetime-local' id='fecha' name='fecha'>";
            echo "<label for='cliente_nm'>Nombre Cliente</label>";
            echo "<input type='text' id='cliente_nm' name='cliente_nm'>";
            echo "<label for='impuesto'>Impuesto</label>";
            echo "<input type='number' id='impuesto' name='impuesto' step='0.01' value='0.00' disabled>";
            echo "<label for='total'>Total</label>";
            echo "<input type='number' id='total' name='total' step='0.01' value='0.00' disabled>";
            echo "<input type='submit' value='Guardar'>";
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
        <main id="container">
            <section id="receipts">
                <p>Facturas</p>
                <table>
                    <thead>
                        <tr>
                            <th>Numero</th>
                            <th>Cliente</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            fillAllReceiptsTable();
                        ?>
                    </tbody>
                </table>
            </section>
            <section id="Factura">
                <?php 
                    fillFacturaSection();
                ?>
            </section>
        </main>
        <footer></footer>
    </body>
</html>