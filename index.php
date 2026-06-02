<?php

    require_once __DIR__ . '/app/Database/Initializer.php';
    require_once __DIR__ . '/App.php';

    DatabaseInitializer::initialize();

    $app = new App();
    
    $app->run()
    
?>
