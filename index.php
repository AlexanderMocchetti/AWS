<?php
session_start();

if (!isset($_SESSION["logged"])) {
    header("Location: login.php");
    die;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progetto AWS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/default.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-3">
        <div class="container">
            <a class="navbar-brand" href="#">AWS</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#section1">Installazione Docker</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#section2">Generazione di Certificati SSL self-signed</a>
                    </li>
                    <li class='nav-item'><a href='#section3' class='nav-link'>Dockerfile</a></li>
                    <li class='nav-item'><a href='#section4' class='nav-link'>compose.yaml</a></li>
                    <li class='nav-item'><a href='#section5' class='nav-link'>Avviamento dei container</a></li>
                    <li class='nav-item'><a href='#section6' class='nav-link'>Tecnologie utilizzate</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div id="section1" class="container">
        <h2>Installazione Docker</h2>
        <p>Di seguente riporto i comandi che ho utilizzato per aggiornare le repositories di apt:</p>
        <pre>
            <code>    sudo apt-get update
    sudo apt-get install -y ca-certificates curl
    sudo install -m 0755 -d /etc/apt/keyrings
    sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
    sudo chmod a+r /etc/apt/keyrings/docker.asc

    echo \
    "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
    $(. /etc/os-release &amp;&amp; echo "$VERSION_CODENAME") stable" | \
    sudo tee /etc/apt/sources.list.d/docker.list &gt; /dev/null
    sudo apt-get update</code>
        </pre>
        <p>Grazie ai seguenti comandi sono riuscito ad installare effettivamente docker e docker-compose:</p>
        <pre>
            <code class="language-bash">    sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin</code>
        </pre>
    </div>

    <div id="section2" class="container">
        <h2>Generazione di Certificati SSL self-signed</h2>
        <p>Utilizzando la suite openssl ho generato sia il certificato che il file chiave:</p>
        <pre>
            <code class="language-bash">    openssl req -nodes -new -x509 -keyout progetto/keys/ssl-cert-snakeoil.key -out progetto/keys/ssl-cert-snakeoil.pem</code>
        </pre>
    </div>

    <div id='section3' class='container'>
        <h2>Dockerfile</h2>
        <p>Generazione di un file per la configurazione di un'immagine modificata di php:8.2-apache per permettere l'uso di mysqli, di HTTPS e del redirect automatico da HTTP a HTTPS.</p>
        <pre>
            <code>    FROM php:8.2-apache

    COPY ./public-html/ /var/www/html
    COPY ./keys/ssl-cert-snakeoil.key /etc/ssl/private
    COPY ./keys/ssl-cert-snakeoil.pem /etc/ssl/certs
    
    RUN sed -i '/&lt;\/VirtualHost&gt;/ i RewriteEngine On\nRewriteCond %{HTTPS} off\nRewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI}' /etc/apache2/sites-available/000-default.conf
    
    RUN a2enmod rewrite
    RUN a2enmod ssl
    RUN a2ensite default-ssl
    
    RUN docker-php-ext-install mysqli &amp;&amp; \
        docker-php-ext-enable mysqli</code>
        </pre>
    </div>

    <div id='section4' class='container'>
        <h2>compose.yaml</h2>
        <p>Il file compose.yaml viene usato da Docker come configurazione nell'avviamento e nel mantenimento di applicazioni multi-container.</p>
        <p>Nello specifico questo compose.yaml serve ad inizializzare due container (un web server e un database) connessi fra di loro da una Docker network.</p>
        <p>Inoltre vengono specificate alcune variabili di sistema per il database e la posizione della cartella contenente lo script sql di inizializzazione.</p>
        <pre>
            <code class="language-yaml">    services:
        php-app:
            build: progetto
            depends_on:
                - db
            ports:
                - "80:80"
                - "443:443"
            networks:
                - net
        
        db:
            image: mysql:latest
            environment:
            MYSQL_ROOT_PASSWORD: password
            MYSQL_DATABASE: database
            networks:
                - net
            volumes:
                - "./progetto/sql:/docker-entrypoint-initdb.d"
        
    networks:
        net:</code>
        </pre>
    </div>

    <div id='section5' class='container'>
        <h2>Avviamento dei container</h2>
        <p>Utilizzando il comando "docker compose" possiamo infine avviare l'applicazione.</p>
        <pre>
            <code>    sudo docker compose up -d</code>
        </pre>
    </div>

    <div id='section6' class='container'>
        <h2>Tecnologie utilizzate</h2>
        <ul>
            <li>
                <h5>Docker: </h5>
                <p>Piattaforma di containerizzazione che semplifica lo sviluppo, la distribuzione e la gestione delle applicazioni.</p>
            </li>
            <li>
                <h5>Dockerfile: </h5>
                <p>Un file di testo con istruzioni per creare un'immagine Docker</p>
            </li>
            <li>
                <h5>Docker Compose: </h5>
                <p>Semplifica la gestione di applicazioni multi-container.</p>
            </li>
            <li>
                <h5>Docker Networks: </h5>
                <p>Permettono ai container di comunicare in modo sicuro e isolato. Le ho usate per creare una rete privata in cui i container scambiano dati</p>
            </li>
            <li>
                <h5>PHP: </h5>
                <p>Linguaggio di programazione utilizzato per lo sviluppo di pagine web dinamiche.</p>
            </li>
            <li>
                <h5>Apache: </h5>
                <p>Server web open source ampiamente adottato per la distribuzione di siti e applicazioni web dinamiche.</p>
            </li>
            <li>
                <h5>Bootstrap</h5>
                <p>Framework front-end ampiamente usato per lo sviluppo di interfacce web responsive e stilisticamente gradevoli.</p>
            </li>
        </ul>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>hljs.highlightAll();</script>
</body>
</html>
