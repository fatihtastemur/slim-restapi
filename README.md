
#### Create Project
```bash
$ php composer.phar create-project slim/slim-skeleton slim-restapi
```

#### Run
```bash
$ php -S localhost:8080 -t public public/index.php
```

Open browser [http://localhost:8080/](http://localhost:8080/)


**app/settings.php**
```php
'db'=> [
        'host' => DBHOST,
        'dbname' => DBNAME,
        'user' => DBUSER,
        'pass' => DBPASS
    ],
```

**app/dependencies.php**
```php
$containerBuilder->addDefinitions([
        'db' => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $dbSettings = $settings['db'];

            $pdo = new PDO("mysql:host=" . $dbSettings['host'] . ";dbname=" . $dbSettings['dbname'], $dbSettings['user'], $dbSettings['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        },
    ]);
```

#### Create DB Table
```sql
CREATE TABLE books (
	id BIGINT NOT NULL,
	book_name VARCHAR(255),
	book_author VARCHAR(255),
	book_page INT,
	CONSTRAINT `PRIMARY` PRIMARY KEY (id)
);
```

#### Get All Books [GET]
Postman Request [http://localhost:8080/allbooks](http://localhost:8080/allbooks)

**app/routes.php**
```php
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
```

#### Get Book With ID [GET]
Postman Request [http://localhost:8080/book/19](http://localhost:8080/book/19)

**app/routes.php**
```php
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
```

#### Add Book [POST]
Postman Request [http://localhost:8080/addbook](http://localhost:8080/addbook)

Json Data
```json
{
  "book_name":"Test Book",
  "book_author":"Author",
  "book_page":358
}

```

**app/routes.php**
```php
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
```