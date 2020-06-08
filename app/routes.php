<?php
declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;


return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->get("/hello[/{name}]", function (Request $request, Response $response) {
        $name = $request->getAttribute('name');
        $response->getBody()->write("Hello $name");
        return $response;
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

     $app->get('/allbooks', function (Request $request, Response $response, $args) {
         $db = $this->get('db');
         $sth = $db->prepare("SELECT * FROM books" );
         $sth->execute();
         $result = $sth->fetchAll();

         if(!empty($result)) {
             $response->getBody()->write(json_encode($result));
             $response->withHeader('Content-Type', 'application/json');
             $response->withStatus(200);
             return $response;
         }else {
             $errorArray = array('status' => 'False','message' =>'Books not found' );
             $response->getBody()->write(json_encode($errorArray));
             $response->withHeader('Content-Type', 'application/json');
             $response->withStatus(200);
             return $response;
         }
     });

    $app->get('/book/{id}', function (Request $request, Response $response, $args) {
        $id = $request->getAttribute('id');

        $db = $this->get('db');
        $sth = $db->prepare("SELECT * FROM books WHERE id=:id");
        $sth->execute(array(':id'=>$id));
        $result = $sth->fetchAll();

        if(!empty($result)) {
            $response->getBody()->write(json_encode($result));
            $response->withHeader('Content-Type', 'application/json');
            $response->withStatus(200);
            return $response;
        }else {
            $errorArray = array('status' => 'False','message' =>'Book not found' );
            $response->getBody()->write(json_encode($errorArray));
            $response->withHeader('Content-Type', 'application/json');
            $response->withStatus(200);
            return $response;
        }
    });

    $app->post('/addbook', function (Request $request, Response $response, $args) {

       try{

           $data = (array)($request->getParsedBody());
           $book_name = $data['book_name'];
           $book_author =  $data['book_author'];
           $book_page =  intval($data['book_page']);

           /*$response->getBody()->write(json_encode($book_page));
           $response->withHeader('Content-Type', 'application/json');
           return $response;*/

           $db = $this->get('db');
           $sth = $db->prepare("INSERT INTO books (book_name,book_author,book_page) VALUES (?,?,?)");
           $status = $sth->execute(array($book_name, $book_author, $book_page));

           if(!empty($status) && $status == 1) {
               $successArray = array('status' => 'True','message' =>'Book added successfully' );
               $response->getBody()->write(json_encode($successArray));
               $response->withHeader('Content-Type', 'application/json');
               $response->withStatus(200);
               return $response;
           } else {
               $errorArray = array('status' => 'False','message' =>'Book not added' );
               $response->getBody()->write(json_encode($errorArray));
               $response->withHeader('Content-Type', 'application/json');
               $response->withStatus(200);
               return $response;
           }
       }catch(PDOException $ex) {
           echo $ex->getMessage();
       }
    });
};
