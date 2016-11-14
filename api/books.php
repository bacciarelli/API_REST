<?php

include_once 'src/db_confing_inc.php';
include_once 'src/Book.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);
if ($mysqli->connect_errno) {
    echo "Faild to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$mysqli->set_charset('utf8');


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id']) != '' && intval($_GET['id']) > 0) {
        $safeId = $mysqli->real_escape_string($_GET['id']);
        $sql_id = "SELECT * FROM books WHERE id=$safeId";
        $res = $mysqli->query($sql_id);
        $book = new Book();
        $book->loadFromDB($mysqli, $safeId);
        echo json_encode($book);
    } else {
        $sql = "SELECT id FROM books ORDER BY author, name";
        $res = $mysqli->query($sql);
        $books = [];
        while ($row = $res->fetch_assoc()) {
            $book = new Book();
            $book->loadFromDB($mysqli, $row['id']);
            $books[] = $book;
        }

        echo json_encode($books);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (($_POST['author']) != '' && ($_POST['name']) != '' && ($_POST['book_desc']) != '') {
        $author = ($_POST['author']);
        $name = ($_POST['name']);
        $book_desc = ($_POST['book_desc']);

        $book = new Book();
        $message = [];
        if ($book->create($mysqli, $name, $author, $book_desc)) {
            $message ['text'] = "Książka została dodana.";
            echo json_encode($message);
        } else {
            $message ['text'] = "Błąd dodawania książki.";
            echo json_encode($message);
        }
    } else {
        $message ['text'] = "Należy wprowadzić wszystkie dane.";
        echo json_encode($message);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    parse_str(file_get_contents("php://input"), $del_vars);

    $id = $del_vars['id'];
    $book = new Book();
    $book->loadFromDB($mysqli, $id);
    if ($book->deleteFromDB($mysqli)) {
        $message ['text'] = "Książka została usunięta.";
        echo json_encode($message);
    } else {
        $message ['text'] = "Błąd podczas usuwania książki!";
        echo json_encode($message);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    parse_str(file_get_contents("php://input"), $put_vars);
    $author = $put_vars['author'];
    $name = $put_vars['name'];
    $book_desc = $put_vars['book_desc'];
    $id = $put_vars['id'];
    $book = new Book();
    $book->loadFromDB($mysqli, $id);
    
    if(trim($author) == '') {
        $author = $book->getAuthor();
    }
    if(trim($name) == '') {
        $name = $book->getName();
    }
    if(trim($book_desc) == '') {
        $book_desc = $book->getBook_desc();
    }
    
    if ($book->update($mysqli, $name, $author, $book_desc)) {
        $message ['text'] = "Dane książki zostały zmodyfikowane.";
        echo json_encode($message);
    } else {
        $message ['text'] = "Nie udana modyfikacja danych!";
        echo json_encode($message);
    }
}
?>