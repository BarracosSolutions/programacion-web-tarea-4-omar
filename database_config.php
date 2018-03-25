<?php 
    //set default timezone
    date_default_timezone_set('UTC');
    try{
        /*******************************/
        /* Create database and         */
        /* open connections            */
        /*******************************/
        //Connect to SQLite database in file
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
        
        /**************************************
        // * Close db connections                *
        // **************************************/
 
        // Close file db connection
        $file_db = null;
    }
    catch(PDOException $e){
        // Print PDOException message
        echo $e->getMessage();
    }
?>

<!-- <?php
 
//   // Set default timezone
//   date_default_timezone_set('UTC');
 
//   try {
//     /**************************************
//     * Create databases and                *
//     * open connections                    *
//     **************************************/
 
//     // Create (connect to) SQLite database in file
//     $file_db = new PDO('sqlite:messaging.sqlite3');
//     // Set errormode to exceptions
//     $file_db->setAttribute(PDO::ATTR_ERRMODE, 
//                             PDO::ERRMODE_EXCEPTION);

 
 
//     /**************************************
//     * Create tables                       *
//     **************************************/
 
//     ///// Create table messages
//     $file_db->exec("CREATE TABLE if not exists book(
// 	title_id	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
// 	title	VARCHAR ( 500 ),
// 	pages	INTEGER)");
// 	$file_db->exec("CREATE TABLE if not exists author (
// 	author_id	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
// 	title_id	INTEGER NOT NULL,
// 	author	VARCHAR ( 500 ) ) ");
 
//     //Create table messages with different time format
//     // $memory_db->exec("CREATE TABLE messages (
//                       // id INTEGER PRIMARY KEY, 
//                       // title TEXT, 
//                       // message TEXT, 
//                       // time TEXT)");
 
 
//     /**************************************
//     * Set initial data                    *
//     **************************************/
 
//     // Array with some test data to insert to database             
//     $books = array(
//                   array('title_id' => NULL,'title' => 'Game of Thrones 1',
//                         'pages' => 654),
//                   array('title_id' => NULL, 'title' => 'Game of Thrones 2',
//                         'pages' => 786),
//                   array('title_id' => NULL,'title' => 'Game of Thrones 3',
//                         'pages' => 967)
//                 );
 
 
//     /**************************************
//     * Play with databases and tables      *
//     **************************************/
// 	// Insert into author table
// 	$insertA = "INSERT INTO author (author_id,title_id, author) 
//                 VALUES (:author_id,:title_id,:author)";
// 	$author1 = "George R. R. Martin";
// 	$title_id1 = 1;
// 	$title_id2 = 3;
// 	$title_id3 = 3;
// 	$author_id1 = NULL;
// 	$author_id2 = NULL;
// 	$author_id3 = NULL;
// 	$stmtA = $file_db->prepare($insertA);
// 	// Bind parameters to statement variables
// 	$stmtA->bindParam(':author_id',$author_id1);
//     $stmtA->bindParam(':title_id', $title_id1);
//     $stmtA->bindParam(':author',$author1);
// 	$stmtA->execute();
// 	$stmtA->bindParam(':author_id',$author_id2);
// 	$stmtA->bindParam(':title_id', $title_id2);
//     $stmtA->bindParam(':author', $author1);
// 	$stmtA->execute();
// 	$stmtA->bindParam(':author_id',$author_id3);
// 	$stmtA->bindParam(':title_id', $title_id3);
//     $stmtA->bindParam(':author', $author1);
// 	$stmtA->execute();
	
	
// 	//////////////////////////////////////////////////////////////////
	
//     // Prepare INSERT statement to SQLite3 file db
//     $insert = "INSERT INTO book (title_id,title, pages) 
//                 VALUES (:title_id,:title,:pages)";
//     $stmt = $file_db->prepare($insert);
 
//     // Bind parameters to statement variables
// 	$stmt->bindParam(':title_id', $title_id);
//     $stmt->bindParam(':title', $title);
//     $stmt->bindParam(':pages', $pages);
 
//     // Loop thru all messages and execute prepared insert statement
//     foreach ($books as $m) {
//       // Set values to bound variables
// 	  $title_id = $m['title_id'];
//       $title = $m['title'];
//       $pages = $m['pages'];
//       // Execute statement
//       $stmt->execute();
//     }
 
//     // Select all data from file db messages table 
//     // $result = $file_db->query('SELECT * FROM books');
 
//     // Loop thru all data from messages table 
//     // and insert it to file db
//     // foreach ($result as $m) {
//       // // Bind values directly to statement variables
//       // $stmt->bindValue(':id', $m['id'], SQLITE3_INTEGER);
//       // $stmt->bindValue(':title', $m['title'], SQLITE3_TEXT);
//       // $stmt->bindValue(':pages', $m['pages'], SQLITE3_TEXT);
 
//       // // Execute statement
//       // $stmt->execute();
//     // }
// 	// $resultA = $file_db->query('SELECT * FROM author');
 
//     // // Loop thru all data from messages table 
//     // // and insert it to file db
//     // foreach ($resultA as $m) {
//       // // Bind values directly to statement variables
//       // $stmt->bindValue(':id', $m['author_id'], SQLITE3_INTEGER);
//       // $stmt->bindValue(':title', $m['title_id'], SQLITE3_TEXT);
//       // $stmt->bindValue(':pages', $m['author'], SQLITE3_TEXT);
 
//       // // Execute statement
//       // $stmt->execute();
//     // }
 
//     // // Quote new title
//     // $new_title = $memory_db->quote("Hi''\'''\\\"\"!'\"");
//     // // Update old title to new title
//     // $update = "UPDATE messages SET title = {$new_title} 
//                 // WHERE datetime(time) > 
//                 // datetime('2012-06-01 15:48:07')";
//     // // // Execute update
//     // $memory_db->exec($update);
 
//     // Select all data from memory db messages table 
//     $result = $file_db->query('SELECT * FROM book');
 
//     foreach($result as $row) {
//       echo "Id: " . $row['title_id'] . "\n";
//       echo "Title: " . $row['title'] . "\n";
//       echo "Pages: " . $row['pages'] . "\n";
//       echo "\n";
//     }
	
// 	$resultA = $file_db->query('SELECT * FROM author');
 
//     foreach($resultA as $row) {
//       echo "Author Id: " . $row['author_id'] . "\n";
//       echo "Title Id: " . $row['title_id'] . "\n";
//       echo "Author: " . $row['author'] . "\n";
//       echo "\n";
//     }
	
	
// 	 //SELECT ALL DATA FROM FILE DB MESSAGES TABLE 
//     $RESULT = $FILE_DB->QUERY('SELECT * FROM BOOKS');
 
//     //LOOP THRU ALL DATA FROM MESSAGES TABLE 
//     //AND INSERT IT TO FILE DB
//     FOREACH ($RESULT AS $M) {
//       // BIND VALUES DIRECTLY TO STATEMENT VARIABLES
//       $STMT->BINDVALUE(':ID', $M['ID'], SQLITE3_INTEGER);
//       $STMT->BINDVALUE(':TITLE', $M['TITLE'], SQLITE3_TEXT);
//       $STMT->BINDVALUE(':PAGES', $M['PAGES'], SQLITE3_TEXT);
 
//       // EXECUTE STATEMENT
//       $STMT->EXECUTE();
//     }
 
//     // /**************************************
//     // * Drop tables                         *
//     // **************************************/
 
//     // // Drop table messages from file db
//     // $file_db->exec("DROP TABLE messages");
//     // // Drop table messages from memory db
//     // $memory_db->exec("DROP TABLE messages");
 
 
//     // /**************************************
//     // * Close db connections                *
//     // **************************************/
 
//     // // Close file db connection
//     // $file_db = null;
//     // // Close memory db connection
//     // $memory_db = null;
//   }
//   catch(PDOException $e) {
//     // Print PDOException message
//     echo $e->getMessage();
//   }
?> -->